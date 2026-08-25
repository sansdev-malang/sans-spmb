<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return \App\Models\SpmbPeriod::where('is_active', true)->value('id') 
                ?? \App\Models\SpmbPeriod::value('id');
        });

        $query = Registration::scopedByAdmin()
            ->with(['user', 'activePayment', 'period', 'wave', 'type'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->where('registration_status', '!=', 'draft');

        // Filter by status if requested
        if ($request->has('status') && in_array($request->status, ['submitted', 'verified', 'taaruf_completed', 'agreement_signed', 'completed', 'failed'])) {
            $query->where('registration_status', $request->status);
        }

        $registrations = $query->latest()->paginate(10);

        // Stats calculation (scoped by period)
        $stats = [
            'total' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->count(),
            'submitted' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('registration_status', 'submitted')->count(),
            'verified' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('registration_status', 'verified')->count(),
            'failed' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('registration_status', 'failed')->count(),
            'paid' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('payment_status', 'paid')->count(),
        ];

        return view('admin.verification', compact('registrations', 'stats'));
    }

    public function dashboard()
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return \App\Models\SpmbPeriod::where('is_active', true)->value('id') 
                ?? \App\Models\SpmbPeriod::value('id');
        });

        // General stats
        $totalCandidates = Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->count();
        $submittedCandidates = Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('registration_status', 'submitted')->count();
        $verifiedCandidates = Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('registration_status', 'verified')->count();
        
        $paidTransactions = \App\Models\Payment::scopedByAdmin()
            ->where('status', 'success')
            ->whereHas('registration', function($q) use ($selectedPeriodId) {
                $q->where('spmb_period_id', $selectedPeriodId);
            })->count();

        $totalRevenue = \App\Models\Payment::scopedByAdmin()
            ->where('status', 'success')
            ->whereHas('registration', function($q) use ($selectedPeriodId) {
                $q->where('spmb_period_id', $selectedPeriodId);
            })->sum('amount');

        // Charts stats (by level)
        $levelStats = Registration::scopedByAdmin()->selectRaw('admission_level, count(*) as count')
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereNotNull('admission_level')
            ->groupBy('admission_level')
            ->get();

        // Charts stats (by status)
        $statusStats = [
            'Draft' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('registration_status', 'draft')->count(),
            'Submitted' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('registration_status', 'submitted')->count(),
            'Verified' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('registration_status', 'verified')->count(),
            'Failed' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('registration_status', 'failed')->count(),
        ];

        // Charts stats (by payment)
        $paymentStats = [
            'Unpaid' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('payment_status', 'unpaid')->count(),
            'Pending' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('payment_status', 'pending')->count(),
            'Paid' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('payment_status', 'paid')->count(),
        ];

        return view('admin.dashboard', compact(
            'totalCandidates',
            'submittedCandidates',
            'verifiedCandidates',
            'paidTransactions',
            'totalRevenue',
            'levelStats',
            'statusStats',
            'paymentStats'
        ));
    }

    public function verify(Request $request, $id)
    {
        $registration = Registration::scopedByAdmin()->findOrFail($id);
        
        $request->validate([
            'notes' => 'nullable|string|max:500',
            'reason' => 'nullable|string|max:500'
        ]);

        $notes = $request->input('notes') ?? $request->input('reason') ?? 'Alhamdulillah, berkas pendaftaran ananda ' . ($registration->candidate_name ?? 'Ahmad Raihan') . ' telah kami terima dan diverifikasi. Silakan persiapkan untuk mengikuti Tes Observasi.';

        $registration->update([
            'registration_status' => 'verified',
            'invalid_fields' => null,
            'committee_notes' => $notes
        ]);

        return redirect()->back()->with('success', 'Candidate registration verified successfully.');
    }

    public function reject(Request $request, $id)
    {
        $registration = Registration::scopedByAdmin()->findOrFail($id);
        
        $request->validate([
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
            'invalid_fields' => 'nullable|string'
        ]);

        $invalidFields = null;
        if ($request->filled('invalid_fields')) {
            $decoded = json_decode($request->invalid_fields, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $invalidFields = $decoded;
            }
        }

        $reason = $request->input('reason') ?? $request->input('notes') ?? 'Berkas ditolak.';

        $registration->update([
            'registration_status' => 'failed',
            'invalid_fields' => $invalidFields,
            'committee_notes' => $reason
        ]);

        return redirect()->back()->with('success', 'Candidate registration rejected with reason.');
    }

    public function completeTaaruf(Request $request, $id)
    {
        $registration = Registration::scopedByAdmin()->findOrFail($id);
        
        $registration->update([
            'registration_status' => 'taaruf_completed',
            'committee_notes' => 'Ujian observasi / ta\'aruf telah selesai dilaksanakan. Silakan mengisi Formulir Pernyataan Kesanggupan Biaya dan Tata Tertib Sekolah.'
        ]);

        return redirect()->back()->with('success', 'Status Ta\'aruf calon siswa berhasil diselesaikan.');
    }
}
