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
use Illuminate\Support\Facades\Storage;

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
            $units = SpmbUnit::orderBy('id', 'asc')->get();
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
            'confirmed_present' => (clone $baseQuery)->where('observation_attendance_status', 'confirmed_present')->count(),
            'reschedule_requested' => (clone $baseQuery)->where('observation_attendance_status', 'reschedule_requested')->count(),
            'has_result' => (clone $baseQuery)->whereNotNull('observation_result_path')->count(),
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
        } elseif ($statusFilter === 'confirmed_present') {
            $query->where('observation_attendance_status', 'confirmed_present');
        } elseif ($statusFilter === 'reschedule_requested') {
            $query->where('observation_attendance_status', 'reschedule_requested');
        } elseif ($statusFilter === 'has_result') {
            $query->whereNotNull('observation_result_path');
        } elseif ($statusFilter === 'no_result') {
            $query->whereNull('observation_result_path');
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

        // Order by priority:
        // 1. Belum punya jadwal (butuh tindakan segera)
        // 2. Sudah dijadwalkan (urut jadwal terdekat)
        // 3. Sudah selesai Ta'aruf
        $registrations = $query->orderByRaw("
            CASE 
                WHEN registration_status = 'verified' AND observation_date IS NULL THEN 1
                WHEN registration_status = 'verified' AND observation_date IS NOT NULL THEN 2
                ELSE 3
            END ASC
        ")
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
            'observation_room' => 'nullable|string|max:255',
            'observation_address' => 'nullable|string|max:500',
            'observation_interviewer' => 'nullable|string|max:255',
            'observation_notes' => 'nullable|string|max:2000',
        ]);

        $updateData = [
            'observation_date' => $request->observation_date,
            'observation_time' => $request->observation_time,
            'observation_location' => $request->observation_location,
            'observation_room' => $request->observation_room,
            'observation_address' => $request->observation_address,
            'observation_interviewer' => $request->observation_interviewer,
            'observation_notes' => $request->observation_notes,
        ];

        // Reset attendance status when admin sets a new schedule after a reschedule request or date change
        if ($registration->observation_attendance_status === 'reschedule_requested' || ($registration->observation_date && $registration->observation_date->format('Y-m-d') !== \Carbon\Carbon::parse($request->observation_date)->format('Y-m-d'))) {
            $updateData['observation_attendance_status'] = null;
            $updateData['observation_attendance_notes'] = null;
            $updateData['observation_attendance_confirmed_at'] = null;
        }

        $registration->update($updateData);

        // Log Activity
        SpmbActivityLog::log(
            'UPDATE_TAARUF_SCHEDULE',
            "Mengatur jadwal Ta'aruf ananda {$registration->candidate_name} (ID: {$registration->id}) pada " . \Carbon\Carbon::parse($request->observation_date)->translatedFormat('d F Y') . " ({$request->observation_time})"
        );

        // Send in-app notification to candidate user
        try {
            if ($registration->user) {
                $formattedDate = \Carbon\Carbon::parse($request->observation_date)->translatedFormat('l, d F Y');
                $locText = $request->observation_location;
                if ($request->observation_room) {
                    $locText .= ' (' . $request->observation_room . ')';
                }
                Notification::send($registration->user, new SpmbNotification([
                    'title' => 'Jadwal Ta\'aruf Telah Ditetapkan',
                    'message' => "Jadwal sesi Ta'aruf ananda {$registration->candidate_name} dijadwalkan pada {$formattedDate} pukul {$request->observation_time} di {$locText}.",
                    'url' => route('dashboard.observation', $registration->id),
                    'type' => 'info',
                    'spmb_unit_id' => $registration->spmb_unit_id,
                    'registration_id' => $registration->id,
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
            'observation_room' => null,
            'observation_address' => null,
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
     * Upload observation result file & notes for a candidate.
     */
    public function uploadResult(Request $request, $id)
    {
        $registration = Registration::with('user')->findOrFail($id);

        $request->validate([
            'result_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'result_notes' => 'nullable|string|max:2000',
        ]);

        // Delete old file if exists
        if ($registration->observation_result_path && Storage::disk('public')->exists($registration->observation_result_path)) {
            Storage::disk('public')->delete($registration->observation_result_path);
        }

        $path = $request->file('result_file')->store('observation_results', 'public');

        $registration->update([
            'observation_result_path' => $path,
            'observation_result_notes' => $request->input('result_notes'),
            'observation_result_uploaded_at' => now(),
        ]);

        SpmbActivityLog::log(
            'UPLOAD_OBSERVATION_RESULT',
            "Mengunggah berkas hasil observasi ananda {$registration->candidate_name} (ID: {$registration->id})"
        );

        // Send in-app notification to candidate user
        try {
            if ($registration->user) {
                $registration->user->notify(new SpmbNotification([
                    'title' => 'Laporan Hasil Observasi Tersedia',
                    'message' => "Berkas laporan hasil evaluasi & observasi kesiapan belajar ananda {$registration->candidate_name} telah diunggah oleh panitia unit dan dapat dilihat/diunduh.",
                    'url' => route('dashboard.observation', $registration->id),
                    'type' => 'success',
                    'spmb_unit_id' => $registration->spmb_unit_id,
                    'registration_id' => $registration->id,
                ]));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send observation result notification to candidate', ['error' => $e->getMessage()]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Berkas hasil observasi ananda {$registration->candidate_name} berhasil diunggah.",
                'file_url' => asset('storage/' . $path),
                'file_name' => basename($path),
                'uploaded_at' => now()->translatedFormat('d M Y, H:i'),
                'notes' => $registration->observation_result_notes,
            ]);
        }

        return redirect()->back()->with('success', "Berkas hasil observasi ananda {$registration->candidate_name} berhasil diunggah.");
    }

    /**
     * Delete observation result file.
     */
    public function deleteResult(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);

        if (in_array($registration->registration_status, ['taaruf_completed', 'agreement_signed', 'completed'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Berkas hasil observasi tidak dapat dihapus karena tahapan Ta'aruf telah diselesaikan."
                ], 422);
            }
            return redirect()->back()->with('error', "Berkas hasil observasi tidak dapat dihapus karena tahapan Ta'aruf telah diselesaikan.");
        }

        if ($registration->observation_result_path && Storage::disk('public')->exists($registration->observation_result_path)) {
            Storage::disk('public')->delete($registration->observation_result_path);
        }

        $registration->update([
            'observation_result_path' => null,
            'observation_result_notes' => null,
            'observation_result_uploaded_at' => null,
        ]);

        SpmbActivityLog::log(
            'DELETE_OBSERVATION_RESULT',
            "Menghapus berkas hasil observasi ananda {$registration->candidate_name} (ID: {$registration->id})"
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Berkas hasil observasi ananda {$registration->candidate_name} berhasil dihapus."
            ]);
        }

        return redirect()->back()->with('success', "Berkas hasil observasi ananda {$registration->candidate_name} berhasil dihapus.");
    }

    /**
     * Download observation result file.
     */
    public function downloadResult(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);

        if (empty($registration->observation_result_path) || !Storage::disk('public')->exists($registration->observation_result_path)) {
            return redirect()->back()->with('error', "Berkas hasil observasi tidak ditemukan.");
        }

        $filePath = Storage::disk('public')->path($registration->observation_result_path);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $safeName = \Illuminate\Support\Str::slug($registration->candidate_name ?: 'Calon Murid') . '-Hasil-Observasi.' . $extension;

        return response()->download($filePath, $safeName);
    }

    /**
     * Complete Ta'aruf for a candidate (transition to taaruf_completed).
     */
    public function completeTaaruf(Request $request, $id)
    {
        $registration = Registration::with('user')->findOrFail($id);

        // VALIDATION REQUIREMENT: Observation result file MUST be uploaded first!
        if (empty($registration->observation_result_path)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Hasil observasi belum diunggah. Mohon unggah berkas hasil observasi terlebih dahulu sebelum menyelesaikan sesi Ta'aruf ananda {$registration->candidate_name}."
                ], 422);
            }
            return redirect()->back()->with('error', "Hasil observasi belum diunggah. Mohon unggah berkas hasil observasi terlebih dahulu sebelum menyelesaikan sesi Ta'aruf ananda {$registration->candidate_name}.");
        }

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
                    'spmb_unit_id' => $registration->spmb_unit_id,
                    'registration_id' => $registration->id,
                ]));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send candidate taaruf completion notification', ['error' => $e->getMessage()]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Tahap Ta'aruf ananda {$registration->candidate_name} berhasil diselesaikan. Status pendaftar kini beralih ke tahap Surat Pernyataan Kesanggupan."
            ]);
        }

        return redirect()->back()->with('success', "Tahap Ta'aruf ananda {$registration->candidate_name} berhasil diselesaikan. Status pendaftar kini beralih ke tahap Surat Pernyataan Kesanggupan.");
    }

    /**
     * Revert / Cancel Ta'aruf completion for a candidate (back to verified).
     */
    public function revertTaaruf(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);

        if (in_array($registration->registration_status, ['agreement_signed', 'completed'])) {
            return redirect()->back()->with('error', "Status Ta'aruf ananda {$registration->candidate_name} tidak dapat dikembalikan karena orang tua telah menandatangani Surat Pernyataan Kesanggupan.");
        }

        $registration->update([
            'registration_status' => 'verified',
            'committee_notes' => 'Berkas pendaftaran telah diverifikasi. Silakan menunggu jadwal observasi / ta\'aruf.',
        ]);

        SpmbActivityLog::log(
            'REVERT_TAARUF',
            "Membatalkan penyelesaian Ta'aruf ananda {$registration->candidate_name} (ID: {$registration->id}) dan mengembalikan ke tahap penjadwalan/observasi."
        );

        return redirect()->back()->with('success', "Status ananda {$registration->candidate_name} berhasil dikembalikan ke tahap Observasi / Ta'aruf.");
    }

    /**
     * Update unit-specific default Ta'aruf configuration (template, location, instructions, required items).
     */
    public function updateUnitSettings(Request $request, $unitId)
    {
        $request->validate([
            'taaruf_title' => 'required|string|max:255',
            'taaruf_default_location' => 'required|string|max:255',
            'taaruf_default_room' => 'nullable|string|max:255',
            'taaruf_default_address' => 'nullable|string|max:500',
            'taaruf_instructions' => 'nullable|string|max:3000',
            'taaruf_required_items' => 'nullable|string|max:3000',
        ]);

        $unit = SpmbUnit::findOrFail($unitId);

        $unit->update([
            'taaruf_title' => $request->taaruf_title,
            'taaruf_default_location' => $request->taaruf_default_location,
            'taaruf_default_room' => $request->taaruf_default_room,
            'taaruf_default_address' => $request->taaruf_default_address,
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
