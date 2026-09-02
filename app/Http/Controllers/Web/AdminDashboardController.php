<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\SpmbActivityLog;

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

        // Search by Name, WhatsApp, or NIK
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filter by Unit/Jenjang
        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        // Per page limit
        $perPage = intval($request->get('per_page', 10));
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $registrations = $query->latest()->paginate($perPage)->withQueryString();

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

        $isSuperAdmin = auth()->user()->isSuperAdmin();

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

        // Recent Registrations (5 items)
        $recentRegistrations = Registration::scopedByAdmin()
            ->with(['user', 'unit'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->latest()
            ->limit(5)
            ->get();

        // Recent Logs (5 items)
        $recentLogs = $isSuperAdmin ? SpmbActivityLog::latest()->limit(5)->get() : collect();

        // Active Wave/Gelombang Info
        $activeWave = \App\Models\SpmbWave::where('is_active', true)->first();

        // Total registered users
        $totalUsersCount = \App\Models\User::count();

        return view('admin.dashboard', compact(
            'totalCandidates',
            'submittedCandidates',
            'verifiedCandidates',
            'paidTransactions',
            'totalRevenue',
            'levelStats',
            'statusStats',
            'paymentStats',
            'recentRegistrations',
            'recentLogs',
            'activeWave',
            'totalUsersCount'
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

        SpmbActivityLog::log('VERIFY_CANDIDATE', "Memverifikasi berkas pendaftaran ananda " . ($registration->candidate_name ?? 'Draft') . " (ID: {$registration->id})");

        // Trigger notification to candidate
        try {
            $registration->user->notify(new \App\Notifications\SpmbNotification([
                'title' => 'Berkas Pendaftaran Terverifikasi',
                'message' => 'Alhamdulillah, berkas pendaftaran ananda "' . $registration->candidate_name . '" telah diverifikasi. Silakan persiapkan diri untuk mengikuti Observasi/Ta\'Aruf.',
                'url' => route('dashboard.verification', $registration->id),
                'type' => 'success',
            ]));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send candidate verification notification', ['error' => $e->getMessage()]);
        }

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

        SpmbActivityLog::log('REJECT_CANDIDATE', "Menolak berkas pendaftaran ananda " . ($registration->candidate_name ?? 'Draft') . " (ID: {$registration->id}) dengan alasan: {$reason}");

        // Trigger notification to candidate
        try {
            $registration->user->notify(new \App\Notifications\SpmbNotification([
                'title' => 'Perbaikan Berkas Pendaftaran',
                'message' => 'Terdapat berkas pendaftaran ananda "' . $registration->candidate_name . '" yang perlu diperbaiki: ' . $reason,
                'url' => route('dashboard.form', $registration->id),
                'type' => 'warning',
            ]));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send candidate rejection notification', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Candidate registration rejected with reason.');
    }

    public function completeTaaruf(Request $request, $id)
    {
        $registration = Registration::scopedByAdmin()->findOrFail($id);
        
        $registration->update([
            'registration_status' => 'taaruf_completed',
            'committee_notes' => 'Ujian observasi / ta\'aruf telah selesai dilaksanakan. Silakan mengisi Formulir Pernyataan Kesanggupan Biaya dan Tata Tertib Sekolah.'
        ]);

        SpmbActivityLog::log('COMPLETE_TAARUF', "Menyelesaikan tahapan observasi/ta'aruf ananda " . ($registration->candidate_name ?? 'Draft') . " (ID: {$registration->id})");

        // Trigger notification to candidate
        try {
            $registration->user->notify(new \App\Notifications\SpmbNotification([
                'title' => 'Tahapan Ta\'Aruf Selesai',
                'message' => 'Observasi/Ta\'aruf ananda "' . $registration->candidate_name . '" telah selesai dilaksanakan. Silakan isi Surat Pernyataan Kesanggupan Biaya.',
                'url' => route('dashboard.observation', $registration->id),
                'type' => 'success',
            ]));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send candidate taaruf completion notification', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Status Ta\'aruf calon siswa berhasil diselesaikan.');
    }

    public function activityLogs(Request $request)
    {
        $query = SpmbActivityLog::with('user');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                  ->orWhere('user_name', 'like', '%' . $search . '%')
                  ->orWhere('action', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('action_type')) {
            $query->where('action', $request->action_type);
        }

        $perPage = $request->integer('per_page', 10);
        $logs = $query->latest()->paginate($perPage);

        // Get distinct action types for filter dropdown
        $actionTypes = SpmbActivityLog::select('action')->distinct()->pluck('action');

        return view('admin.activity-logs', compact('logs', 'actionTypes'));
    }

    public function updateInstallmentSettings(Request $request, $id)
    {
        $registration = Registration::scopedByAdmin()->findOrFail($id);

        // Guard: Fully paid candidates cannot be modified
        if ($registration->remaining_balance <= 0 && $registration->total_paid_final_fee > 0) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tagihan calon siswa ini telah lunas sepenuhnya. Pengaturan keringanan & cicilan sudah terkunci dan tidak dapat diubah lagi.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Tagihan calon siswa ini telah lunas sepenuhnya dan terkunci.');
        }

        $validated = $request->validate([
            'discount_mode' => 'nullable|in:none,global,selective',
            'discount_amount' => 'nullable|numeric|min:0',
            'item_discounts' => 'nullable|array',
            'discount_notes' => 'nullable|string|max:255',
            'installment_mode' => 'required|in:none,all,selective',
            'installment_allowed_fee_ids' => 'nullable|array',
            'min_installment_amount' => 'nullable|numeric|min:0',
        ]);

        $discountMode = $validated['discount_mode'] ?? 'global';
        $discountAmount = ($discountMode === 'global') ? (float) ($validated['discount_amount'] ?? 0) : 0;
        
        $feeDetails = $registration->getFinalFeeDetails();
        $feeItems = $feeDetails['items'] ?? [];

        $itemDiscounts = null;
        if ($discountMode === 'selective' && !empty($validated['item_discounts']) && is_array($validated['item_discounts'])) {
            $itemDiscounts = [];
            foreach ($validated['item_discounts'] as $key => $val) {
                $num = floatval($val);
                if ($num > 0) {
                    // Check item paid amount
                    $itemPaid = $registration->getItemPaidAmount($key);
                    $matchingItem = collect($feeItems)->first(fn($it) => strcasecmp(trim($it['name']), trim($key)) === 0);
                    $itemGross = (float) ($matchingItem['amount'] ?? 0);
                    $maxAllowedDiscount = max(0, $itemGross - $itemPaid);

                    if ($num > $maxAllowedDiscount) {
                        $num = $maxAllowedDiscount; // Cap discount to unpaid portion
                    }

                    if ($num > 0) {
                        $itemDiscounts[$key] = $num;
                    }
                }
            }
        }

        $installmentMode = $validated['installment_mode'];
        $allowedFeeIds = ($installmentMode === 'selective') ? ($validated['installment_allowed_fee_ids'] ?? []) : null;

        $registration->update([
            'discount_mode' => $discountMode,
            'discount_amount' => $discountAmount,
            'item_discounts' => $itemDiscounts,
            'discount_notes' => $validated['discount_notes'] ?? null,
            'installment_mode' => $installmentMode,
            'installment_allowed_fee_ids' => $allowedFeeIds,
            'min_installment_amount' => $validated['min_installment_amount'] ?? 0,
            'installment_approved_by' => auth()->id(),
            'installment_approved_at' => now(),
        ]);

        $registration->refresh();
        $candidateName = $registration->candidate_name ?? 'ID: ' . $registration->id;
        $totalDiscountFormatted = number_format($registration->total_discount, 0, ',', '.');
        SpmbActivityLog::log(
            'UPDATE_INSTALLMENT_SETTINGS', 
            "Memperbarui kebijakan keringanan/cicilan untuk ananda {$candidateName} (Diskon: Rp {$totalDiscountFormatted} [{$discountMode}], Mode Cicilan: {$installmentMode})"
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan keringanan & kebijakan cicilan calon siswa berhasil disimpan.',
                'data' => [
                    'id' => $registration->id,
                    'discount_mode' => $registration->discount_mode,
                    'discount_amount' => (float) $registration->discount_amount,
                    'item_discounts' => $registration->item_discounts,
                    'total_discount' => (float) $registration->total_discount,
                    'discount_notes' => $registration->discount_notes,
                    'installment_mode' => $registration->installment_mode,
                    'installment_allowed_fee_ids' => $registration->installment_allowed_fee_ids,
                    'min_installment_amount' => (float) $registration->min_installment_amount,
                    'gross_fee' => (float) $registration->total_gross_final_fee,
                    'net_fee' => (float) $registration->net_final_fee,
                    'total_paid' => (float) $registration->total_paid_final_fee,
                    'remaining_balance' => (float) $registration->remaining_final_fee,
                    'is_fully_paid' => (bool) ($registration->remaining_final_fee <= 0),
                    'paid_percentage' => $registration->net_final_fee > 0 ? min(100, round(($registration->total_paid_final_fee / $registration->net_final_fee) * 100)) : 100,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Pengaturan keringanan & kebijakan cicilan calon siswa berhasil disimpan.');
    }
}

