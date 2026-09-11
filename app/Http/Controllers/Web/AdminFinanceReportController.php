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
use App\Services\SimpleXlsxService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class AdminFinanceReportController extends Controller
{
    /**
     * Prepare complete financial dataset based on active filters.
     */
    protected function prepareFinanceData(Request $request): array
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        $registrationFeeLabel = SpmbFeeCategory::getRegistrationCategoryName();
        $finalFeeLabel = SpmbFeeCategory::getTuitionCategoryName();

        // Units for filter
        $units = SpmbUnit::orderBy('id', 'asc')->get();
        if (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $units = $units->where('id', auth()->user()->spmb_unit_id);
        }

        $waves = SpmbWave::orderBy('id', 'asc')->get();
        $types = SpmbType::orderBy('id', 'asc')->get();
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

        // School branding for Print / PDF header
        $schoolName = Setting::get('school_name', 'Sekolah Anak Saleh');
        $schoolLogo = Setting::get('school_logo_url') ?: Setting::get('school_logo');
        $logoUrl = null;
        if ($schoolLogo && (filter_var($schoolLogo, FILTER_VALIDATE_URL) || str_starts_with($schoolLogo, 'http'))) {
            $logoUrl = $schoolLogo;
        } elseif ($schoolLogo && file_exists(public_path(ltrim($schoolLogo, '/')))) {
            $logoUrl = asset(ltrim($schoolLogo, '/'));
        } elseif ($schoolLogo && file_exists(public_path('storage/' . ltrim($schoolLogo, '/')))) {
            $logoUrl = asset('storage/' . ltrim($schoolLogo, '/'));
        } else {
            $logoUrl = file_exists(public_path('logo/paud.png')) ? asset('logo/paud.png') : null;
        }

        $activeTab = $request->input('tab', 'recap');

        return compact(
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
            'sortedReceivables',
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
        );
    }

    public function index(Request $request)
    {
        $data = $this->prepareFinanceData($request);

        // Paginate receivables collection for web table view
        $currentPage = LengthAwarePaginator::resolveCurrentPage('page');
        $perPage = 15;
        $sortedReceivables = $data['sortedReceivables'];
        $currentItems = $sortedReceivables->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedReceivables = new LengthAwarePaginator($currentItems, $sortedReceivables->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        $data['paginatedReceivables'] = $paginatedReceivables;

        return view('admin.finance-reports', $data);
    }

    /**
     * Export Excel (.xlsx) based on active tab.
     */
    public function export(Request $request)
    {
        $data = $this->prepareFinanceData($request);
        $tab = $request->input('tab', 'recap');

        $unitCode = 'ALL';
        if ($request->filled('unit_id')) {
            $u = SpmbUnit::find($request->unit_id);
            if ($u) $unitCode = strtoupper($u->code ?: Str::slug($u->name));
        } elseif (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $u = SpmbUnit::find(auth()->user()->spmb_unit_id);
            if ($u) $unitCode = strtoupper($u->code ?: Str::slug($u->name));
        }

        $period = $data['selectedPeriod'];
        $periodLabel = $period ? Str::slug($period->name ?? $period->year) : 'SPMB';

        if ($tab === 'cashflow') {
            return $this->exportCashflowXlsx($data, $unitCode, $periodLabel);
        } elseif ($tab === 'receivables') {
            return $this->exportReceivablesXlsx($data, $unitCode, $periodLabel);
        }

        return $this->exportRecapXlsx($data, $unitCode, $periodLabel);
    }

    /**
     * Export PDF (.pdf) based on active tab.
     */
    public function exportPdf(Request $request)
    {
        $data = $this->prepareFinanceData($request);
        $tab = $request->input('tab', 'recap');

        $unitCode = 'ALL';
        $unitFilterLabel = 'Semua Jenjang / Unit';
        if ($request->filled('unit_id')) {
            $u = SpmbUnit::find($request->unit_id);
            if ($u) {
                $unitCode = strtoupper($u->code ?: Str::slug($u->name));
                $unitFilterLabel = $u->name;
            }
        } elseif (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $u = SpmbUnit::find(auth()->user()->spmb_unit_id);
            if ($u) {
                $unitCode = strtoupper($u->code ?: Str::slug($u->name));
                $unitFilterLabel = $u->name;
            }
        }

        $period = $data['selectedPeriod'];
        $periodName = $period ? ($period->name ?? $period->year) : 'SPMB';
        $periodLabel = $period ? Str::slug($period->name ?? $period->year) : 'SPMB';
        $printedAt = now()->translatedFormat('d F Y, H:i') . ' WIB';
        $printedBy = auth()->user()->name ?? 'Administrator';

        $data['unitFilterLabel'] = $unitFilterLabel;
        $data['periodName'] = $periodName;
        $data['printedAt'] = $printedAt;
        $data['printedBy'] = $printedBy;

        if ($tab === 'cashflow') {
            $filename = 'Laporan-Arus-Kas-Saluran-Pembayaran-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.pdf';
            $pdf = Pdf::loadView('admin.finance-cashflow-pdf', $data)->setPaper('a4', 'landscape');
        } elseif ($tab === 'receivables') {
            $filename = 'Buku-Rekapitulasi-Piutang-Murid-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.pdf';
            $data['receivables'] = $data['sortedReceivables'];
            $pdf = Pdf::loadView('admin.finance-receivables-pdf', $data)->setPaper('a4', 'landscape');
        } else {
            $filename = 'Laporan-Rekapitulasi-Keuangan-Unit-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.pdf';
            $pdf = Pdf::loadView('admin.finance-recap-pdf', $data)->setPaper('a4', 'landscape');
        }

        return response()->make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Tab 1: Export Recap by Unit & Wave (.xlsx)
     */
    protected function exportRecapXlsx(array $data, string $unitCode, string $periodLabel)
    {
        $filename = 'Rekapitulasi-Keuangan-Unit-Gelombang-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.xlsx';

        $columns = [
            'No.',
            'Unit Sekolah',
            'Murid Tagihan DSP (Anak)',
            'Target Tagihan DSP Netto (Rp)',
            'Pokok DSP Terbayar (Rp)',
            'Sisa Piutang DSP (Rp)',
            'Pokok Formulir (Rp)',
            'Total Kas Pokok Bersih (Rp)',
            'MDR / Admin Fee Gateway (Rp)',
            'Total Mutasi Bruto (Rp)',
            'Tingkat Capaian DSP (%)',
        ];

        $rows = [];
        $i = 1;
        foreach ($data['unitBreakdown'] as $ub) {
            $rows[] = [
                $i++,
                $ub['unit']->name,
                (int) $ub['count'],
                (float) $ub['net'],
                (float) $ub['paid'],
                (float) $ub['remaining'],
                (float) $ub['form_fee_net'],
                (float) $ub['total_net_cash'],
                (float) $ub['admin_fee'],
                (float) $ub['gross_mutasi'],
                $ub['percentage'] . '%',
            ];
        }

        return SimpleXlsxService::download($columns, $rows, $filename, 'Rekap Unit');
    }

    /**
     * Tab 2: Export Cashflow & Payment Channels (.xlsx)
     */
    protected function exportCashflowXlsx(array $data, string $unitCode, string $periodLabel)
    {
        $filename = 'Arus-Kas-Saluran-Pembayaran-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.xlsx';

        $columns = [
            'No.',
            'Metode / Saluran Pembayaran',
            'Jumlah Transaksi (Trx)',
            'Total Kas Pokok Bersih (Rp)',
            'Total Biaya Admin / MDR (Rp)',
            'Total Mutasi Bruto (Rp)',
        ];

        $rows = [];
        $i = 1;
        foreach ($data['channelStats'] as $ch) {
            $rows[] = [
                $i++,
                $ch['name'],
                (int) $ch['count'],
                (float) $ch['net'],
                (float) $ch['admin_fee'],
                (float) $ch['gross'],
            ];
        }

        return SimpleXlsxService::download($columns, $rows, $filename, 'Arus Kas & Saluran');
    }

    /**
     * Tab 3: Export Receivables & Arrears (.xlsx)
     */
    protected function exportReceivablesXlsx(array $data, string $unitCode, string $periodLabel)
    {
        $filename = 'Buku-Rekapitulasi-Piutang-Murid-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.xlsx';

        $columns = [
            'No.',
            'No. Registrasi',
            'Nama Calon Murid',
            'Unit Sekolah',
            'Tingkat / Kelas',
            'Gelombang',
            'Jalur Masuk',
            'Nama Orang Tua / Wali',
            'No. WhatsApp Ortu',
            'Tagihan DSP Bruto (Rp)',
            'Diskon / Keringanan (Rp)',
            'Tagihan DSP Netto (Rp)',
            'Total Terbayar (Rp)',
            'Sisa Piutang DSP (Rp)',
            'Status Pelunasan DSP',
            'Tahapan Registrasi',
        ];

        $rows = [];
        $i = 1;
        foreach ($data['sortedReceivables'] as $c) {
            $gross = (float) $c->getGrossFee();
            $discount = (float) $c->total_discount;
            $net = (float) $c->net_fee;
            $paidDSP = (float) $c->total_paid_final_fee;
            $remaining = (float) $c->remaining_balance;

            $statusDSP = 'BELUM BAYAR';
            if ($c->is_dispensation) {
                $statusDSP = 'DISPENSASI';
            } elseif ($paidDSP > 0 && $remaining > 0) {
                $statusDSP = 'CICILAN / SEBAGIAN';
            }

            $parentName = $c->guardian_name ?: ($c->father_name ?: ($c->mother_name ?: '-'));
            $parentPhone = $c->parent_phone ?: ($c->father_phone ?: ($c->mother_phone ?: '-'));

            $rows[] = [
                $i++,
                $c->id_label ?? '-',
                $c->candidate_name ?? '-',
                $c->unit->name ?? '-',
                $c->grade->name ?? ($c->admission_level ?? '-'),
                $c->wave->name ?? '-',
                $c->type->name ?? '-',
                $parentName,
                $parentPhone,
                $gross,
                $discount,
                $net,
                $paidDSP,
                $remaining,
                $statusDSP,
                strtoupper($c->registration_status ?? '-'),
            ];
        }

        return SimpleXlsxService::download($columns, $rows, $filename, 'Buku Piutang');
    }
}
