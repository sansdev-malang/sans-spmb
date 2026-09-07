<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\SpmbPeriod;
use App\Models\SpmbUnit;
use App\Models\SpmbWave;
use App\Models\SpmbType;

class AdminFinanceReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        // Units for filter
        $units = SpmbUnit::where('is_active', true)->get();
        if (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $units = $units->where('id', auth()->user()->spmb_unit_id);
        }

        // Base query for registered candidates in the period
        $query = Registration::scopedByAdmin()
            ->with(['unit', 'grade', 'classProgram', 'wave', 'type', 'payments', 'extraServices'])
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

        $allCandidates = $query->get();

        // Candidates who reached DSP / Daftar Ulang phase
        $dspCandidates = $allCandidates->filter(function($c) {
            return in_array($c->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']);
        });

        // Financial Metrics
        $totalGrossDSP = $dspCandidates->sum(fn($c) => $c->getGrossFee());
        $totalDiscount = $dspCandidates->sum(fn($c) => $c->total_discount);
        $totalNetDSP = $dspCandidates->sum(fn($c) => $c->net_fee);
        $totalPaidDSP = $dspCandidates->sum(fn($c) => $c->total_paid_final_fee);
        $totalRemainingDSP = $dspCandidates->sum(fn($c) => $c->remaining_balance);

        // Form Registration Fee
        $totalFormFeePaid = Payment::whereHas('registration', function($q) use ($selectedPeriodId, $request) {
            $q->scopedByAdmin()->where('spmb_period_id', $selectedPeriodId);
            if ($request->filled('unit_id')) {
                $q->where('spmb_unit_id', $request->unit_id);
            }
        })->where('payment_type', 'registration_fee')->whereIn('status', ['success', 'settled', 'paid'])->sum('amount');

        $totalTotalCashIn = $totalPaidDSP + $totalFormFeePaid;

        // Status Count
        $lunasCount = $dspCandidates->filter(fn($c) => $c->remaining_balance <= 0 && $c->net_fee > 0 && $c->total_paid_final_fee > 0)->count();
        $sebagianCount = $dspCandidates->filter(fn($c) => $c->total_paid_final_fee > 0 && $c->remaining_balance > 0)->count();
        $belumBayarCount = $dspCandidates->filter(fn($c) => $c->total_paid_final_fee <= 0)->count();

        // Breakdown per Unit
        $unitBreakdown = [];
        foreach ($units as $u) {
            $uCands = $dspCandidates->where('spmb_unit_id', $u->id);
            $uGross = $uCands->sum(fn($c) => $c->getGrossFee());
            $uDisc = $uCands->sum(fn($c) => $c->total_discount);
            $uNet = $uCands->sum(fn($c) => $c->net_fee);
            $uPaid = $uCands->sum(fn($c) => $c->total_paid_final_fee);
            $uRem = $uCands->sum(fn($c) => $c->remaining_balance);

            $unitBreakdown[] = [
                'unit' => $u,
                'count' => $uCands->count(),
                'gross' => $uGross,
                'discount' => $uDisc,
                'net' => $uNet,
                'paid' => $uPaid,
                'remaining' => $uRem,
                'percentage' => $uNet > 0 ? round(($uPaid / $uNet) * 100, 1) : 0,
            ];
        }

        // Breakdown per Gelombang
        $waves = SpmbWave::where('is_active', true)->get();
        $waveBreakdown = [];
        foreach ($waves as $w) {
            $wCands = $dspCandidates->where('spmb_wave_id', $w->id);
            $wNet = $wCands->sum(fn($c) => $c->net_fee);
            $wPaid = $wCands->sum(fn($c) => $c->total_paid_final_fee);
            $wRem = $wCands->sum(fn($c) => $c->remaining_balance);

            $waveBreakdown[] = [
                'wave' => $w,
                'count' => $wCands->count(),
                'net' => $wNet,
                'paid' => $wPaid,
                'remaining' => $wRem,
                'percentage' => $wNet > 0 ? round(($wPaid / $wNet) * 100, 1) : 0,
            ];
        }

        // List of Candidates with Receivables / Remaining Balance
        $receivableCandidates = $dspCandidates->filter(fn($c) => $c->remaining_balance > 0)
            ->sortByDesc('remaining_balance')
            ->values();

        $periods = SpmbPeriod::orderBy('year', 'desc')->get();
        $selectedPeriod = SpmbPeriod::find($selectedPeriodId);

        return view('admin.finance-reports', compact(
            'totalGrossDSP',
            'totalDiscount',
            'totalNetDSP',
            'totalPaidDSP',
            'totalRemainingDSP',
            'totalFormFeePaid',
            'totalTotalCashIn',
            'lunasCount',
            'sebagianCount',
            'belumBayarCount',
            'unitBreakdown',
            'waveBreakdown',
            'receivableCandidates',
            'units',
            'waves',
            'periods',
            'selectedPeriod'
        ));
    }

    public function export(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        $query = Registration::scopedByAdmin()
            ->with(['unit', 'grade', 'classProgram', 'wave', 'type', 'payments'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed']);

        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        $candidates = $query->get();

        $csvFileName = 'rekap_keuangan_spmb_' . date('Y-m-d_His') . '.csv';
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['No Pendaftaran', 'Nama Siswa', 'Unit Sekolah', 'Jenjang', 'Gelombang', 'Biaya DSP Kotor', 'Diskon', 'Total Tagihan Bersih', 'Total Dibayar', 'Sisa Tagihan (Piutang)', 'Status Pembayaran'];

        $callback = function() use ($candidates, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($candidates as $c) {
                $statusText = 'Belum Bayar';
                if ($c->remaining_balance <= 0 && $c->net_fee > 0 && $c->total_paid_final_fee > 0) {
                    $statusText = 'Lunas';
                } elseif ($c->total_paid_final_fee > 0 && $c->remaining_balance > 0) {
                    $statusText = 'Cicilan / Sebagian';
                }

                fputcsv($file, [
                    'SPMB-' . str_pad($c->id, 5, '0', STR_PAD_LEFT),
                    $c->candidate_name,
                    $c->unit->name ?? '-',
                    $c->grade->name ?? '-',
                    $c->wave->name ?? '-',
                    $c->getGrossFee(),
                    $c->total_discount,
                    $c->net_fee,
                    $c->total_paid_final_fee,
                    $c->remaining_balance,
                    $statusText
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
