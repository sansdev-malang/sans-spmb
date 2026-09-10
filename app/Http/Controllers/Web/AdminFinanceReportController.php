<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\SpmbPeriod;
use App\Models\SpmbUnit;
use App\Models\SpmbWave;
use App\Models\SpmbType;
use App\Models\Setting;
use App\Models\SpmbFeeCategory;

class AdminFinanceReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        $registrationFeeLabel = SpmbFeeCategory::getRegistrationCategoryName();
        $finalFeeLabel = SpmbFeeCategory::getTuitionCategoryName();

        // Units for filter
        $units = SpmbUnit::where('is_active', true)->get();
        if (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $units = $units->where('id', auth()->user()->spmb_unit_id);
        }

        $waves = SpmbWave::where('is_active', true)->get();
        $types = SpmbType::where('is_active', true)->get();
        $periods = SpmbPeriod::orderBy('year', 'desc')->get();
        $selectedPeriod = SpmbPeriod::find($selectedPeriodId);

        // 1. Success Payments Query (for Net Cash, Admin Fee, and Gross Mutasi)
        $successPaymentsQuery = Payment::scopedByAdmin()
            ->whereIn('status', ['success', 'settled', 'paid'])
            ->whereHas('registration', function($q) use ($selectedPeriodId, $request) {
                $q->where('spmb_period_id', $selectedPeriodId);
                if ($request->filled('unit_id')) {
                    $q->where('spmb_unit_id', $request->unit_id);
                }
                if ($request->filled('wave_id')) {
                    $q->where('spmb_wave_id', $request->wave_id);
                }
                if ($request->filled('type_id')) {
                    $q->where('spmb_type_id', $request->type_id);
                }
            });

        if ($request->filled('start_date')) {
            $successPaymentsQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $successPaymentsQuery->whereDate('created_at', '<=', $request->end_date);
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, Payment> $allSuccessPayments */
        $allSuccessPayments = $successPaymentsQuery->with(['registration.unit', 'registration.grade'])->get();

        // Financial Inflow Metrics
        $totalPaidTransactions = $allSuccessPayments->count();
        $totalGrossRevenue = (float) $allSuccessPayments->sum('amount');
        $totalAdminFee = (float) $allSuccessPayments->sum('admin_fee');
        $totalNetCashIn = (float) $allSuccessPayments->sum(fn($p) => (float) ($p->base_amount ?: ($p->amount - ($p->admin_fee ?: 0))));

        // Form Registration Fee Breakdown
        $formPayments = $allSuccessPayments->where('payment_type', 'registration_fee');
        $formFeeNet = (float) $formPayments->sum(fn($p) => (float) ($p->base_amount ?: ($p->amount - ($p->admin_fee ?: 0))));
        $formFeeAdmin = (float) $formPayments->sum('admin_fee');
        $formFeeGross = (float) $formPayments->sum('amount');
        $formFeeTrxCount = $formPayments->count();

        // DSP / Final Fee Breakdown
        $dspPayments = $allSuccessPayments->where('payment_type', '!=', 'registration_fee');
        $dspFeeNet = (float) $dspPayments->sum(fn($p) => (float) ($p->base_amount ?: ($p->amount - ($p->admin_fee ?: 0))));
        $dspFeeAdmin = (float) $dspPayments->sum('admin_fee');
        $dspFeeGross = (float) $dspPayments->sum('amount');
        $dspFeeTrxCount = $dspPayments->count();

        // 2. Base query for registered candidates in the period
        $candidatesQuery = Registration::scopedByAdmin()
            ->with(['unit', 'grade', 'classProgram', 'wave', 'type', 'payments', 'extraServices', 'user'])
            ->where('spmb_period_id', $selectedPeriodId);

        if ($request->filled('unit_id')) {
            $candidatesQuery->where('spmb_unit_id', $request->unit_id);
        }
        if ($request->filled('wave_id')) {
            $candidatesQuery->where('spmb_wave_id', $request->wave_id);
        }
        if ($request->filled('type_id')) {
            $candidatesQuery->where('spmb_type_id', $request->type_id);
        }

        $allCandidates = $candidatesQuery->get();

        // Candidates who reached DSP / Daftar Ulang phase (Billing is generated)
        $dspCandidates = $allCandidates->filter(function($c) {
            return in_array($c->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']);
        });

        // Biaya Masuk & DSP Target Metrics
        $totalGrossDSP = (float) $dspCandidates->sum(fn($c) => $c->getGrossFee());
        $totalDiscount = (float) $dspCandidates->sum(fn($c) => $c->total_discount);
        $totalNetDSP = (float) $dspCandidates->sum(fn($c) => $c->net_fee);
        $totalPaidDSP = (float) $dspCandidates->sum(fn($c) => $c->total_paid_final_fee);
        $totalRemainingDSP = (float) $dspCandidates->sum(fn($c) => $c->remaining_balance);

        // Collection Rate (% Capaian Pelunasan DSP terhadap Tagihan Bersih)
        $collectionRate = $totalNetDSP > 0 ? round(($totalPaidDSP / $totalNetDSP) * 100, 1) : 0;

        // Status Counts for DSP candidates
        $lunasCount = $dspCandidates->filter(fn($c) => $c->remaining_balance <= 0 && $c->net_fee > 0 && $c->total_paid_final_fee > 0)->count();
        $sebagianCount = $dspCandidates->filter(fn($c) => $c->total_paid_final_fee > 0 && $c->remaining_balance > 0)->count();
        $belumBayarCount = $dspCandidates->filter(fn($c) => $c->total_paid_final_fee <= 0)->count();
        $totalDSPCandidatesCount = $dspCandidates->count();

        // 3. Breakdown per Unit Sekolah
        $unitBreakdown = [];
        foreach ($units as $u) {
            $uCands = $dspCandidates->where('spmb_unit_id', $u->id);
            $uGross = (float) $uCands->sum(fn($c) => $c->getGrossFee());
            $uDisc = (float) $uCands->sum(fn($c) => $c->total_discount);
            $uNet = (float) $uCands->sum(fn($c) => $c->net_fee);
            $uPaid = (float) $uCands->sum(fn($c) => $c->total_paid_final_fee);
            $uRem = (float) $uCands->sum(fn($c) => $c->remaining_balance);

            $uPayments = $allSuccessPayments->filter(fn($p) => $p->registration && $p->registration->spmb_unit_id == $u->id);
            $uFormNet = (float) $uPayments->where('payment_type', 'registration_fee')->sum(fn($p) => (float) ($p->base_amount ?: ($p->amount - ($p->admin_fee ?: 0))));
            $uAdminFee = (float) $uPayments->sum('admin_fee');
            $uGrossMutasi = (float) $uPayments->sum('amount');
            $uTotalNetCash = $uPaid + $uFormNet;

            $unitBreakdown[] = [
                'unit' => $u,
                'count' => $uCands->count(),
                'gross' => $uGross,
                'discount' => $uDisc,
                'net' => $uNet,
                'paid' => $uPaid,
                'remaining' => $uRem,
                'form_fee_net' => $uFormNet,
                'admin_fee' => $uAdminFee,
                'total_net_cash' => $uTotalNetCash,
                'gross_mutasi' => $uGrossMutasi,
                'percentage' => $uNet > 0 ? round(($uPaid / $uNet) * 100, 1) : 0,
            ];
        }

        // 4. Breakdown per Gelombang
        $waveBreakdown = [];
        foreach ($waves as $w) {
            $wCands = $dspCandidates->where('spmb_wave_id', $w->id);
            $wGross = (float) $wCands->sum(fn($c) => $c->getGrossFee());
            $wDisc = (float) $wCands->sum(fn($c) => $c->total_discount);
            $wNet = (float) $wCands->sum(fn($c) => $c->net_fee);
            $wPaid = (float) $wCands->sum(fn($c) => $c->total_paid_final_fee);
            $wRem = (float) $wCands->sum(fn($c) => $c->remaining_balance);

            $waveBreakdown[] = [
                'wave' => $w,
                'count' => $wCands->count(),
                'gross' => $wGross,
                'discount' => $wDisc,
                'net' => $wNet,
                'paid' => $wPaid,
                'remaining' => $wRem,
                'percentage' => $wNet > 0 ? round(($wPaid / $wNet) * 100, 1) : 0,
            ];
        }

        // 5. Payment Channels Breakdown (QRIS, VA BCA, Mandiri, dll)
        $channelStats = [];
        foreach ($allSuccessPayments as $p) {
            $chName = $p->channel_display_name ?: ($p->payment_method ?: 'Lainnya');
            if (!isset($channelStats[$chName])) {
                $channelStats[$chName] = [
                    'name' => $chName,
                    'count' => 0,
                    'net' => 0,
                    'admin_fee' => 0,
                    'gross' => 0,
                    'logo' => $p->getLogoUrl(),
                ];
            }
            $channelStats[$chName]['count']++;
            $channelStats[$chName]['net'] += (float) ($p->base_amount ?: ($p->amount - ($p->admin_fee ?: 0)));
            $channelStats[$chName]['admin_fee'] += (float) ($p->admin_fee ?: 0);
            $channelStats[$chName]['gross'] += (float) $p->amount;
        }
        uasort($channelStats, fn($a, $b) => $b['gross'] <=> $a['gross']);

        // Recent 5 Transactions for Audit Log in Cashflow
        $recentTransactions = $allSuccessPayments->sortByDesc('id')->take(5);

        // 6. Receivables List & Search/Filter
        $receivableQuery = $dspCandidates->filter(fn($c) => $c->remaining_balance > 0);

        // Filter by receivable status (unpaid vs partial)
        if ($request->filled('receivable_status')) {
            if ($request->receivable_status === 'unpaid') {
                $receivableQuery = $receivableQuery->filter(fn($c) => $c->total_paid_final_fee <= 0);
            } elseif ($request->receivable_status === 'partial') {
                $receivableQuery = $receivableQuery->filter(fn($c) => $c->total_paid_final_fee > 0 && $c->remaining_balance > 0);
            }
        }

        // Search in receivables
        if ($request->filled('search')) {
            $keyword = strtolower(trim($request->search));
            $receivableQuery = $receivableQuery->filter(function($c) use ($keyword) {
                return str_contains(strtolower($c->candidate_name ?? ''), $keyword)
                    || str_contains(strtolower($c->nik ?? ''), $keyword)
                    || str_contains(strtolower($c->parent_phone ?? ''), $keyword)
                    || str_contains(strtolower($c->id_label ?? ''), $keyword)
                    || str_contains(strtolower('spmb-' . str_pad($c->id, 5, '0', STR_PAD_LEFT)), $keyword);
            });
        }

        $sortedReceivables = $receivableQuery->sortByDesc('remaining_balance')->values();

        // Paginate receivables collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage('page');
        $perPage = 15;
        $currentItems = $sortedReceivables->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedReceivables = new LengthAwarePaginator($currentItems, $sortedReceivables->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        // School branding for Print / PDF header
        $schoolName = Setting::get('school_name', 'Sekolah Anak Saleh');
        $schoolLogo = Setting::get('school_logo');
        $brandingPath = 'storage/branding/whsokYPk9uLYyz6SCRmuMTQgD2UxVTqmTEMoz36r.png';
        if ($schoolLogo && file_exists(public_path('storage/' . ltrim($schoolLogo, '/')))) {
            $logoUrl = asset('storage/' . ltrim($schoolLogo, '/'));
        } elseif (file_exists(public_path($brandingPath))) {
            $logoUrl = asset($brandingPath);
        } else {
            $logoUrl = asset('storage/branding/whsokYPk9uLYyz6SCRmuMTQgD2UxVTqmTEMoz36r.png');
        }

        $activeTab = $request->input('tab', 'recap');

        return view('admin.finance-reports', compact(
            'totalNetCashIn',
            'totalGrossRevenue',
            'totalAdminFee',
            'totalPaidTransactions',
            'formFeeNet',
            'formFeeAdmin',
            'formFeeGross',
            'formFeeTrxCount',
            'dspFeeNet',
            'dspFeeAdmin',
            'dspFeeGross',
            'dspFeeTrxCount',
            'totalGrossDSP',
            'totalDiscount',
            'totalNetDSP',
            'totalPaidDSP',
            'totalRemainingDSP',
            'collectionRate',
            'lunasCount',
            'sebagianCount',
            'belumBayarCount',
            'totalDSPCandidatesCount',
            'unitBreakdown',
            'waveBreakdown',
            'channelStats',
            'recentTransactions',
            'paginatedReceivables',
            'units',
            'waves',
            'types',
            'periods',
            'selectedPeriod',
            'schoolName',
            'logoUrl',
            'activeTab',
            'registrationFeeLabel',
            'finalFeeLabel'
        ));
    }

    public function export(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        $registrationFeeLabel = SpmbFeeCategory::getRegistrationCategoryName();
        $finalFeeLabel = SpmbFeeCategory::getTuitionCategoryName();

        $query = Registration::scopedByAdmin()
            ->with(['unit', 'grade', 'classProgram', 'wave', 'type', 'payments'])
            ->where('spmb_period_id', $selectedPeriodId);

        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }
        if ($request->filled('wave_id')) {
            $query->where('spmb_wave_id', $request->wave_id);
        }
        if ($request->filled('type_id')) {
            $query->where('spmb_type_id', $request->type_id);
        }

        $candidates = $query->get();

        $csvFileName = 'laporan_keuangan_spmb_' . date('Y-m-d_His') . '.csv';
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            'ID Registrasi',
            'Nama Calon Murid',
            'Unit Sekolah',
            'Jenjang',
            'Gelombang',
            'Jalur Pendaftaran',
            'Nama Orang Tua / Wali',
            'No. WhatsApp',
            'Status Pendaftaran',
            'Status ' . $registrationFeeLabel,
            'Pokok ' . $registrationFeeLabel . ' Bersih',
            'Admin Fee ' . $registrationFeeLabel,
            'Total ' . $registrationFeeLabel . ' Dibayar (Bruto)',
            'Tagihan ' . $finalFeeLabel . ' Bruto',
            'Diskon / Keringanan Disetujui',
            'Tagihan ' . $finalFeeLabel . ' Bersih',
            'Pokok ' . $finalFeeLabel . ' Terbayar',
            'Sisa Piutang ' . $finalFeeLabel,
            'Status Pelunasan ' . $finalFeeLabel
        ];

        $callback = function() use ($candidates, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($file, $columns);

            foreach ($candidates as $c) {
                $isDSPStage = in_array($c->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']);
                
                $gross = $isDSPStage ? $c->getGrossFee() : 0;
                $discount = $isDSPStage ? $c->total_discount : 0;
                $net = $isDSPStage ? $c->net_fee : 0;
                $paidDSP = $isDSPStage ? $c->total_paid_final_fee : 0;
                $remaining = $isDSPStage ? $c->remaining_balance : 0;

                $statusDSP = 'Belum Tahap DSP';
                if ($isDSPStage) {
                    if ($remaining <= 0 && $net > 0 && $paidDSP > 0) {
                        $statusDSP = 'LUNAS';
                    } elseif ($paidDSP > 0 && $remaining > 0) {
                        $statusDSP = 'CICILAN / SEBAGIAN';
                    } else {
                        $statusDSP = 'BELUM BAYAR';
                    }
                }

                // Form Fee payment
                $formPayment = $c->payments->where('payment_type', 'registration_fee')->whereIn('status', ['success', 'settled', 'paid'])->first();
                $formNet = $formPayment ? ($formPayment->base_amount ?: ($formPayment->amount - ($formPayment->admin_fee ?: 0))) : 0;
                $formAdmin = $formPayment ? ($formPayment->admin_fee ?: 0) : 0;
                $formGross = $formPayment ? $formPayment->amount : 0;
                $formStatus = $formPayment ? 'LUNAS' : ($c->payment_status === 'paid' ? 'LUNAS' : 'BELUM BAYAR');

                fputcsv($file, [
                    $c->id_label,
                    $c->candidate_name,
                    $c->unit->name ?? '-',
                    $c->grade->name ?? '-',
                    $c->wave->name ?? '-',
                    $c->type->name ?? '-',
                    $c->father_name ?: ($c->mother_name ?: ($c->guardian_name ?: '-')),
                    $c->parent_phone ?? '-',
                    strtoupper($c->registration_status),
                    $formStatus,
                    $formNet,
                    $formAdmin,
                    $formGross,
                    $gross,
                    $discount,
                    $net,
                    $paidDSP,
                    $remaining,
                    $statusDSP
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
