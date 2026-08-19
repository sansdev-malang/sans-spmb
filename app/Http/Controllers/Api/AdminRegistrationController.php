<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Registration;

class AdminRegistrationController extends Controller
{
    /**
     * Display a listing of candidate registrations.
     */
    public function index(Request $request)
    {
        $query = Registration::with(['user', 'activePayment']);

        // Filtering by registration status
        if ($request->has('registration_status')) {
            $query->where('registration_status', $request->registration_status);
        }

        // Filtering by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search candidate name
        if ($request->has('search')) {
            $query->where('candidate_name', 'like', '%' . $request->search . '%');
        }

        $registrations = $query->latest()->paginate(15);

        return response()->json([
            'registrations' => $registrations
        ]);
    }

    /**
     * Display details of a specific registration.
     */
    public function show($id)
    {
        $registration = Registration::with(['user', 'payments'])->find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Registration not found'
            ], 404);
        }

        return response()->json([
            'registration' => $registration
        ]);
    }

    /**
     * Verify / approve candidate's uploaded documents.
     */
    public function verify(Request $request, $id)
    {
        $registration = Registration::find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Registration not found'
            ], 404);
        }

        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        $registration->update([
            'registration_status' => 'verified',
            'committee_notes' => $request->notes ?? 'Alhamdulillah, berkas ananda ' . ($registration->candidate_name ?? 'Ahmad Raihan') . ' telah kami terima dan diverifikasi. Silakan persiapkan ananda untuk mengikuti Tes Observasi secara daring.'
        ]);

        return response()->json([
            'message' => 'Registration verified successfully',
            'registration' => $registration
        ]);
    }

    /**
     * Reject candidate's registration.
     */
    public function reject(Request $request, $id)
    {
        $registration = Registration::find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Registration not found'
            ], 404);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $registration->update([
            'registration_status' => 'failed',
            'committee_notes' => $request->reason
        ]);

        return response()->json([
            'message' => 'Registration marked as failed/rejected',
            'registration' => $registration
        ]);
    }
}
