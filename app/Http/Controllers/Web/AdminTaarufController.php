<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\SpmbPeriod;
use App\Models\SpmbUnit;
use App\Models\SpmbActivityLog;
use App\Notifications\SpmbNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class AdminTaarufController extends Controller
{
    /**
     * Display a listing of candidates for Ta'aruf / Observation scheduling.
     */
    public function index(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', SpmbPeriod::where('is_active', true)->value('id') ?? 1);

        // Fetch Units (scoped if admin is assigned to a specific unit)
        $user = auth()->user();
        if ($user && $user->spmb_unit_id) {
            $units = SpmbUnit::where('id', $user->spmb_unit_id)->get();
            $currentUnitId = $user->spmb_unit_id;
        } else {
            $units = SpmbUnit::where('is_active', true)->get();
            $currentUnitId = $request->get('unit_id');
            if (!$currentUnitId && $units->isNotEmpty()) {
                $currentUnitId = $units->first()->id;
            }
        }

        $currentUnit = $units->firstWhere('id', $currentUnitId) ?? $units->first();

        // Base Query
        $baseQuery = Registration::with(['user', 'unit', 'grade', 'classProgram'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->scopedByAdmin();

        if ($currentUnitId) {
            $baseQuery->where('spmb_unit_id', $currentUnitId);
        }

        // Only include candidates who have reached or passed the verification/ta'aruf stage
        $taarufEligibleStatuses = ['verified', 'taaruf_completed', 'agreement_signed', 'completed'];
        $baseQuery->whereIn('registration_status', $taarufEligibleStatuses);

        // Compute Stats Counts for the current unit / scope
        $counts = [
            'total' => (clone $baseQuery)->count(),
            'unscheduled' => (clone $baseQuery)->where('registration_status', 'verified')->whereNull('observation_date')->count(),
            'scheduled' => (clone $baseQuery)->where('registration_status', 'verified')->whereNotNull('observation_date')->count(),
            'completed' => (clone $baseQuery)->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed'])->count(),
        ];

        // Apply Status Filter
        $statusFilter = $request->get('status', 'all');
        $query = clone $baseQuery;

        if ($statusFilter === 'unscheduled') {
            $query->where('registration_status', 'verified')->whereNull('observation_date');
        } elseif ($statusFilter === 'scheduled') {
            $query->where('registration_status', 'verified')->whereNotNull('observation_date');
        } elseif ($statusFilter === 'completed') {
            $query->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed']);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%")
                  ->orWhere('observation_location', 'like', "%{$search}%")
                  ->orWhere('observation_interviewer', 'like', "%{$search}%");
            });
        }

        // Date Filter
        if ($request->filled('date')) {
            $query->whereDate('observation_date', $request->date);
        }

        // Order by observation date or registration id
        $registrations = $query->orderByRaw('CASE WHEN observation_date IS NULL THEN 0 ELSE 1 END DESC')
            ->orderBy('observation_date', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.taaruf', compact(
            'registrations',
            'units',
            'currentUnit',
            'currentUnitId',
            'counts',
            'statusFilter',
            'selectedPeriodId'
        ));
    }

    /**
     * Store or update the Ta'aruf schedule for a specific candidate.
     */
    public function updateSchedule(Request $request, $id)
    {
        $registration = Registration::with(['user', 'unit'])->findOrFail($id);

        if (in_array($registration->registration_status, ['taaruf_completed', 'agreement_signed', 'completed'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Jadwal Ta'aruf ananda {$registration->candidate_name} tidak dapat diubah karena tahapan Ta'aruf sudah selesai."
                ], 422);
            }
            return redirect()->back()->with('error', "Jadwal Ta'aruf ananda {$registration->candidate_name} tidak dapat diubah karena tahapan Ta'aruf sudah selesai.");
        }

        $request->validate([
            'observation_date' => 'required|date',
            'observation_time' => 'required|string|max:100',
            'observation_location' => 'required|string|max:255',
            'observation_interviewer' => 'nullable|string|max:255',
            'observation_notes' => 'nullable|string|max:2000',
        ]);

        $registration->update([
            'observation_date' => $request->observation_date,
            'observation_time' => $request->observation_time,
            'observation_location' => $request->observation_location,
            'observation_interviewer' => $request->observation_interviewer,
            'observation_notes' => $request->observation_notes,
        ]);

        // Log Activity
        SpmbActivityLog::log(
            'UPDATE_TAARUF_SCHEDULE',
            "Mengatur jadwal Ta'aruf ananda {$registration->candidate_name} (ID: {$registration->id}) pada " . \Carbon\Carbon::parse($request->observation_date)->translatedFormat('d F Y') . " ({$request->observation_time})"
        );

        // Send in-app notification to candidate user
        try {
            if ($registration->user) {
                $formattedDate = \Carbon\Carbon::parse($request->observation_date)->translatedFormat('l, d F Y');
                Notification::send($registration->user, new SpmbNotification([
                    'title' => 'Jadwal Ta\'aruf Telah Ditetapkan',
                    'message' => "Jadwal sesi Ta'aruf ananda {$registration->candidate_name} dijadwalkan pada {$formattedDate} pukul {$request->observation_time} di {$request->observation_location}.",
                    'url' => route('dashboard.observation', $registration->id),
                    'type' => 'info',
                ]));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send taaruf schedule notification', ['error' => $e->getMessage()]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Jadwal Ta'aruf ananda {$registration->candidate_name} berhasil disimpan.",
                'registration' => $registration
            ]);
        }

        return redirect()->back()->with('success', "Jadwal Ta'aruf ananda {$registration->candidate_name} berhasil disimpan.");
    }

    /**
     * Delete/Reset Ta'aruf schedule for a candidate.
     */
    public function deleteSchedule(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);

        if (in_array($registration->registration_status, ['taaruf_completed', 'agreement_signed', 'completed'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Jadwal Ta'aruf ananda {$registration->candidate_name} tidak dapat dibatalkan karena tahapan Ta'aruf sudah selesai."
                ], 422);
            }
            return redirect()->back()->with('error', "Jadwal Ta'aruf ananda {$registration->candidate_name} tidak dapat dibatalkan karena tahapan Ta'aruf sudah selesai.");
        }

        $registration->update([
            'observation_date' => null,
            'observation_time' => null,
            'observation_location' => null,
            'observation_interviewer' => null,
            'observation_notes' => null,
        ]);

        SpmbActivityLog::log(
            'DELETE_TAARUF_SCHEDULE',
            "Membatalkan/menghapus jadwal Ta'aruf ananda {$registration->candidate_name} (ID: {$registration->id})"
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Jadwal Ta'aruf ananda {$registration->candidate_name} berhasil dibatalkan."
            ]);
        }

        return redirect()->back()->with('success', "Jadwal Ta'aruf ananda {$registration->candidate_name} berhasil dibatalkan.");
    }

    /**
     * Complete Ta'aruf for a candidate (transition to taaruf_completed).
     */
    public function completeTaaruf(Request $request, $id)
    {
        $registration = Registration::with('user')->findOrFail($id);

        $registration->update([
            'registration_status' => 'taaruf_completed',
        ]);

        SpmbActivityLog::log(
            'COMPLETE_TAARUF',
            "Menyelesaikan tahapan observasi/ta'aruf ananda {$registration->candidate_name} (ID: {$registration->id})"
        );

        try {
            if ($registration->user) {
                Notification::send($registration->user, new SpmbNotification([
                    'title' => 'Tahap Ta\'aruf Selesai',
                    'message' => "Alhamdulillah, ananda {$registration->candidate_name} telah menyelesaikan tahapan Ta'aruf. Silakan lanjutkan pengisian Surat Pernyataan Kesanggupan.",
                    'url' => route('dashboard.observation', $registration->id),
                    'type' => 'success',
                ]));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send candidate taaruf completion notification', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', "Tahap Ta'aruf ananda {$registration->candidate_name} berhasil diselesaikan. Status pendaftar kini beralih ke tahap Surat Pernyataan Kesanggupan.");
    }

    /**
     * Update unit-specific default Ta'aruf configuration (template, location, instructions, required items).
     */
    public function updateUnitSettings(Request $request, $unitId)
    {
        $request->validate([
            'taaruf_title' => 'required|string|max:255',
            'taaruf_default_location' => 'required|string|max:255',
            'taaruf_instructions' => 'nullable|string|max:3000',
            'taaruf_required_items' => 'nullable|string|max:3000',
        ]);

        $unit = SpmbUnit::findOrFail($unitId);

        $unit->update([
            'taaruf_title' => $request->taaruf_title,
            'taaruf_default_location' => $request->taaruf_default_location,
            'taaruf_instructions' => $request->taaruf_instructions,
            'taaruf_required_items' => $request->taaruf_required_items,
        ]);

        SpmbActivityLog::log(
            'UPDATE_UNIT_TAARUF_SETTINGS',
            "Memperbarui template panduan & ketentuan Ta'aruf untuk unit {$unit->name}"
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pengaturan & panduan Ta'aruf unit {$unit->name} berhasil diperbarui.",
                'unit' => $unit
            ]);
        }

        return redirect()->back()->with('success', "Pengaturan & panduan Ta'aruf unit {$unit->name} berhasil diperbarui.");
    }
}
