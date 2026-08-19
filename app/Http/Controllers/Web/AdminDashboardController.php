<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Registration::with(['user', 'activePayment', 'period', 'wave', 'type'])->where('registration_status', '!=', 'draft');

        // Filter by status if requested
        if ($request->has('status') && in_array($request->status, ['submitted', 'verified', 'failed'])) {
            $query->where('registration_status', $request->status);
        }

        $registrations = $query->latest()->paginate(10);

        // Stats calculation
        $stats = [
            'total' => Registration::count(),
            'submitted' => Registration::where('registration_status', 'submitted')->count(),
            'verified' => Registration::where('registration_status', 'verified')->count(),
            'failed' => Registration::where('registration_status', 'failed')->count(),
            'paid' => Registration::where('payment_status', 'paid')->count(),
        ];

        return view('admin.peninjauan', compact('registrations', 'stats'));
    }

    public function dashboard()
    {
        // General stats
        $totalCandidates = Registration::count();
        $submittedCandidates = Registration::where('registration_status', 'submitted')->count();
        $verifiedCandidates = Registration::where('registration_status', 'verified')->count();
        $paidTransactions = \App\Models\Payment::where('status', 'success')->count();
        $totalRevenue = \App\Models\Payment::where('status', 'success')->sum('amount');

        // Charts stats (by level)
        $levelStats = Registration::selectRaw('admission_level, count(*) as count')
            ->whereNotNull('admission_level')
            ->groupBy('admission_level')
            ->get();

        // Charts stats (by status)
        $statusStats = [
            'Draft' => Registration::where('registration_status', 'draft')->count(),
            'Submitted' => Registration::where('registration_status', 'submitted')->count(),
            'Verified' => Registration::where('registration_status', 'verified')->count(),
            'Failed' => Registration::where('registration_status', 'failed')->count(),
        ];

        // Charts stats (by payment)
        $paymentStats = [
            'Unpaid' => Registration::where('payment_status', 'unpaid')->count(),
            'Pending' => Registration::where('payment_status', 'pending')->count(),
            'Paid' => Registration::where('payment_status', 'paid')->count(),
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
        $registration = Registration::findOrFail($id);
        
        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        $registration->update([
            'registration_status' => 'verified',
            'committee_notes' => $request->notes ?? 'Alhamdulillah, berkas pendaftaran ananda ' . ($registration->candidate_name ?? 'Ahmad Raihan') . ' telah kami terima dan diverifikasi. Silakan persiapkan untuk mengikuti Tes Observasi.'
        ]);

        return redirect()->back()->with('success', 'Candidate registration verified successfully.');
    }

    public function reject(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);
        
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $registration->update([
            'registration_status' => 'failed',
            'committee_notes' => $request->reason
        ]);

        return redirect()->back()->with('success', 'Candidate registration rejected with reason.');
    }
}
