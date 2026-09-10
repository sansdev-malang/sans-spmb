<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\SpmbPeriod;
use App\Models\SpmbUnit;
use App\Models\SpmbWave;
use App\Models\SpmbActivityLog;
use App\Models\SpmbFeeCategory;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        // Base query for candidate verification (excluding draft)
        $query = Registration::scopedByAdmin()
            ->with(['user', 'activePayment', 'period', 'wave', 'type'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->where('registration_status', '!=', 'draft');

        // Stats calculation for tabs (scoped by period and optional unit)
        $baseStats = Registration::scopedByAdmin()
            ->where('spmb_period_id', $selectedPeriodId)
            ->where('registration_status', '!=', 'draft');

        if ($request->filled('unit_id')) {
            $baseStats->where('spmb_unit_id', $request->unit_id);
            $query->where('spmb_unit_id', $request->unit_id);
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

        // Filter by status if requested
        if ($request->filled('status') && in_array($request->status, ['submitted', 'verified', 'taaruf_completed', 'agreement_signed', 'completed', 'failed'])) {
            $query->where('registration_status', $request->status);
        }

        // Per page limit
        $perPage = intval($request->input('per_page', 10));
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $registrations = $query->latest()->paginate($perPage)->withQueryString();

        $tabCounts = [
            'all' => (clone $baseStats)->count(),
            'submitted' => (clone $baseStats)->where('registration_status', 'submitted')->count(),
            'verified' => (clone $baseStats)->where('registration_status', 'verified')->count(),
            'taaruf_completed' => (clone $baseStats)->where('registration_status', 'taaruf_completed')->count(),
            'agreement_signed' => (clone $baseStats)->where('registration_status', 'agreement_signed')->count(),
            'completed' => (clone $baseStats)->where('registration_status', 'completed')->count(),
            'failed' => (clone $baseStats)->where('registration_status', 'failed')->count(),
        ];

        return view('admin.verification', compact('registrations', 'tabCounts'));
    }

    public function dashboard()
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });

        $isSuperAdmin = auth()->user()->isSuperAdmin();

        // General stats
        $totalCandidates = Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->count();
        $submittedCandidates = Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->where('registration_status', 'submitted')->count();
        $verifiedCandidates = Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)->whereIn('registration_status', ['verified', 'taaruf_completed', 'agreement_signed', 'completed'])->count();

        // Financial & Payment Metrics
        $financialData = $this->calculateFinancialStats($selectedPeriodId, $isSuperAdmin);

        // Pipeline stages & payment stats
        $pipelineStages = $this->calculatePipelineStages($selectedPeriodId, $totalCandidates);
        $paymentStats = $this->calculatePaymentStats($selectedPeriodId);

        // Charts stats (by grade / level name)
        $levelStats = Registration::scopedByAdmin()
            ->where('registrations.spmb_period_id', $selectedPeriodId)
            ->leftJoin('spmb_grades', 'registrations.spmb_grade_id', '=', 'spmb_grades.id')
            ->selectRaw('COALESCE(spmb_grades.name, registrations.admission_level, "Lainnya") as level_name, count(registrations.id) as count')
            ->groupBy('level_name')
            ->orderBy('count', 'desc')
            ->get();

        // Recent Registrations (5 items)
        $recentRegistrations = Registration::scopedByAdmin()
            ->with(['user', 'unit'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->latest()
            ->limit(5)
            ->get();

        // Recent Logs & Wave Info
        $recentLogs = $isSuperAdmin ? SpmbActivityLog::latest()->limit(5)->get() : collect();
        $activeWaves = SpmbWave::where('is_active', true)->get();
        $totalGuardiansCount = User::whereHas('registrations', function($q) use ($selectedPeriodId) {
            $q->scopedByAdmin()->where('spmb_period_id', $selectedPeriodId);
        })->count();

        return view('admin.dashboard', array_merge([
            'totalCandidates' => $totalCandidates,
            'submittedCandidates' => $submittedCandidates,
            'verifiedCandidates' => $verifiedCandidates,
            'levelStats' => $levelStats,
            'pipelineStages' => $pipelineStages,
            'paymentStats' => $paymentStats,
            'recentRegistrations' => $recentRegistrations,
            'recentLogs' => $recentLogs,
            'activeWaves' => $activeWaves,
            'totalGuardiansCount' => $totalGuardiansCount,
            'registrationFeeLabel' => SpmbFeeCategory::getRegistrationCategoryName(),
            'finalFeeLabel' => SpmbFeeCategory::getTuitionCategoryName(),
        ], $financialData));
    }

    /**
     * Calculate financial and payment metrics for dashboard
     */
    private function calculateFinancialStats($selectedPeriodId, bool $isSuperAdmin): array
    {
        $successPaymentsQuery = Payment::scopedByAdmin()
            ->where('status', 'success')
            ->whereHas('registration', function($q) use ($selectedPeriodId) {
                $q->where('spmb_period_id', $selectedPeriodId);
            });

        $paidTransactions = (clone $successPaymentsQuery)->count();
        $totalGrossRevenue = (clone $successPaymentsQuery)->sum('amount');
        $totalAdminFee = (clone $successPaymentsQuery)->sum('admin_fee');
        $totalNetRevenue = (clone $successPaymentsQuery)->selectRaw('SUM(COALESCE(base_amount, amount - COALESCE(admin_fee, 0))) as total_net')->value('total_net') ?? 0;
        $totalRevenue = $totalNetRevenue;

        // Form Registration Fee Breakdown
        $formFeeNet = (clone $successPaymentsQuery)
            ->where('payment_type', 'registration_fee')
            ->selectRaw('SUM(COALESCE(base_amount, amount - COALESCE(admin_fee, 0))) as total_net')
            ->value('total_net') ?? 0;
        $formFeeAdmin = (clone $successPaymentsQuery)
            ->where('payment_type', 'registration_fee')
            ->sum('admin_fee') ?? 0;
        $formFeeGross = (clone $successPaymentsQuery)
            ->where('payment_type', 'registration_fee')
            ->sum('amount') ?? 0;
        $formFeeTrxCount = (clone $successPaymentsQuery)
            ->where('payment_type', 'registration_fee')
            ->count();

        // DSP / Final Fee Breakdown
        $dspFeeNet = (clone $successPaymentsQuery)
            ->where('payment_type', '!=', 'registration_fee')
            ->selectRaw('SUM(COALESCE(base_amount, amount - COALESCE(admin_fee, 0))) as total_net')
            ->value('total_net') ?? 0;
        $dspFeeAdmin = (clone $successPaymentsQuery)
            ->where('payment_type', '!=', 'registration_fee')
            ->sum('admin_fee') ?? 0;
        $dspFeeGross = (clone $successPaymentsQuery)
            ->where('payment_type', '!=', 'registration_fee')
            ->sum('amount') ?? 0;
        $dspFeeTrxCount = (clone $successPaymentsQuery)
            ->where('payment_type', '!=', 'registration_fee')
            ->count();

        // Total Outstanding / Piutang DSP
        $billingQuery = Registration::scopedByAdmin()
            ->with(['unit', 'grade', 'classProgram', 'wave', 'type', 'payments', 'extraServices'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed']);

        /** @var \Illuminate\Database\Eloquent\Collection<int, Registration> $billingCandidates */
        $billingCandidates = $billingQuery->get();
        $totalDSPGrossBilled = $billingCandidates->sum(fn(Registration $c) => $c->getGrossFee());
        $totalDSPDiscount = $billingCandidates->sum(fn(Registration $c) => $c->total_discount);
        $totalDSPNetBilled = $billingCandidates->sum(fn(Registration $c) => $c->net_fee);
        $totalDSPPaid = $billingCandidates->sum(fn(Registration $c) => $c->total_paid_final_fee);
        $totalDSPRemaining = $billingCandidates->sum(fn(Registration $c) => $c->remaining_balance);

        // Payment Channels Breakdown
        $allSuccessPayments = (clone $successPaymentsQuery)->get();
        $channelStats = [];
        foreach ($allSuccessPayments as $p) {
            $ch = $p->channel_display_name ?: ($p->payment_method ?: 'Lainnya');
            if (!isset($channelStats[$ch])) {
                $channelStats[$ch] = [
                    'channel' => $ch,
                    'logo_url' => $p->getLogoUrl(),
                    'count' => 0,
                    'net' => 0,
                    'admin_fee' => 0,
                    'gross' => 0,
                ];
            }
            $channelStats[$ch]['count']++;
            $channelStats[$ch]['net'] += ($p->base_amount ?: ($p->amount - ($p->admin_fee ?: 0)));
            $channelStats[$ch]['admin_fee'] += ($p->admin_fee ?: 0);
            $channelStats[$ch]['gross'] += $p->amount;
        }
        uasort($channelStats, fn($a, $b) => $b['count'] <=> $a['count']);

        // Recent Payments
        $recentPayments = Payment::scopedByAdmin()
            ->with(['registration.unit', 'registration.user'])
            ->whereHas('registration', function($q) use ($selectedPeriodId) {
                $q->where('spmb_period_id', $selectedPeriodId);
            })
            ->latest()
            ->limit(6)
            ->get();

        // Unit Financial Summary (For Super Admin)
        $unitFinanceSummary = [];
        if ($isSuperAdmin) {
            $units = SpmbUnit::where('is_active', true)->get();
            foreach ($units as $u) {
                $uPayments = Payment::where('status', 'success')
                    ->whereHas('registration', function($q) use ($selectedPeriodId, $u) {
                        $q->where('spmb_period_id', $selectedPeriodId)->where('spmb_unit_id', $u->id);
                    })->get();

                $unitFinanceSummary[] = [
                    'unit' => $u,
                    'reg_count' => Registration::where('spmb_period_id', $selectedPeriodId)->where('spmb_unit_id', $u->id)->count(),
                    'paid_trx' => $uPayments->count(),
                    'net_revenue' => $uPayments->sum(fn($p) => $p->base_amount ?: ($p->amount - ($p->admin_fee ?: 0))),
                    'admin_fee' => $uPayments->sum('admin_fee'),
                    'gross_revenue' => $uPayments->sum('amount'),
                ];
            }
        }

        return compact(
            'paidTransactions',
            'totalRevenue',
            'totalNetRevenue',
            'totalAdminFee',
            'totalGrossRevenue',
            'formFeeNet',
            'formFeeAdmin',
            'formFeeGross',
            'formFeeTrxCount',
            'dspFeeNet',
            'dspFeeAdmin',
            'dspFeeGross',
            'dspFeeTrxCount',
            'totalDSPGrossBilled',
            'totalDSPDiscount',
            'totalDSPNetBilled',
            'totalDSPPaid',
            'totalDSPRemaining',
            'channelStats',
            'recentPayments',
            'unitFinanceSummary'
        );
    }

    /**
     * Calculate pipeline stages breakdown for dashboard
     */
    private function calculatePipelineStages($selectedPeriodId, int $totalCandidates): array
    {
        $statusDefinitions = [
            'draft' => ['label' => 'Draft Formulir', 'color' => 'bg-slate-400', 'dot' => 'bg-slate-400'],
            'submitted' => ['label' => 'Menunggu Verifikasi', 'color' => 'bg-amber-500', 'dot' => 'bg-amber-500'],
            'failed' => ['label' => 'Perlu Revisi Berkas', 'color' => 'bg-rose-500', 'dot' => 'bg-rose-500'],
            'verified' => ['label' => 'Observasi / Ta\'aruf', 'color' => 'bg-indigo-500', 'dot' => 'bg-indigo-500'],
            'taaruf_completed' => ['label' => 'Penandatanganan Akad', 'color' => 'bg-purple-500', 'dot' => 'bg-purple-500'],
            'agreement_signed' => ['label' => 'Pembayaran Biaya Masuk', 'color' => 'bg-blue-500', 'dot' => 'bg-blue-500'],
            'completed' => ['label' => 'Resmi Diterima (Lunas)', 'color' => 'bg-emerald-500', 'dot' => 'bg-emerald-500'],
        ];

        $rawStatusCounts = Registration::scopedByAdmin()
            ->where('spmb_period_id', $selectedPeriodId)
            ->selectRaw('registration_status, count(*) as count')
            ->groupBy('registration_status')
            ->pluck('count', 'registration_status')
            ->toArray();

        $pipelineStages = [];
        foreach ($statusDefinitions as $statusCode => $meta) {
            $count = $rawStatusCounts[$statusCode] ?? 0;
            if ($statusCode === 'failed' && $count === 0) {
                continue;
            }
            $percentage = $totalCandidates > 0 ? round(($count / $totalCandidates) * 100) : 0;
            $pipelineStages[] = [
                'status' => $statusCode,
                'label' => $meta['label'],
                'count' => $count,
                'percentage' => $percentage,
                'color' => $meta['color'],
                'dot' => $meta['dot'],
            ];
        }

        return $pipelineStages;
    }

    /**
     * Calculate registration fee payment stats for dashboard
     */
    private function calculatePaymentStats($selectedPeriodId): array
    {
        return [
            'Paid' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)
                ->where(function($q) {
                    $q->where('payment_status', 'paid')
                      ->orWhereHas('payments', function($pq) {
                          $pq->where('payment_type', 'registration_fee')->where('status', 'success');
                      });
                })->count(),
            'Pending' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)
                ->where('payment_status', 'pending')
                ->whereDoesntHave('payments', function($pq) {
                    $pq->where('payment_type', 'registration_fee')->where('status', 'success');
                })->count(),
            'Unpaid' => Registration::scopedByAdmin()->where('spmb_period_id', $selectedPeriodId)
                ->where('payment_status', 'unpaid')
                ->whereDoesntHave('payments', function($pq) {
                    $pq->where('payment_type', 'registration_fee')->where('status', 'success');
                })->count(),
        ];
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
                'spmb_unit_id' => $registration->spmb_unit_id,
                'registration_id' => $registration->id,
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
                'spmb_unit_id' => $registration->spmb_unit_id,
                'registration_id' => $registration->id,
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
                'spmb_unit_id' => $registration->spmb_unit_id,
                'registration_id' => $registration->id,
            ]));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send candidate taaruf completion notification', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Status Ta\'aruf calon murid berhasil diselesaikan.');
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
                    'message' => 'Tagihan calon murid ini telah lunas sepenuhnya. Pengaturan keringanan & cicilan sudah terkunci dan tidak dapat diubah lagi.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Tagihan calon murid ini telah lunas sepenuhnya dan terkunci.');
        }

        $validated = $request->validate([
            'discount_mode' => 'nullable|in:none,global,selective',
            'discount_amount' => 'nullable|numeric|min:0',
            'item_discounts' => 'nullable|array',
            'discount_notes' => 'nullable|string|max:255',
            'installment_mode' => 'required|in:none,all,selective',
            'installment_allowed_fee_ids' => 'nullable|array',
            'installment_fee_ids' => 'nullable|array',
            'min_installment_amount' => 'nullable|numeric|min:0',
        ]);

        $discountMode = $validated['discount_mode'] ?? 'none';
        if ($discountMode === 'global' && empty($validated['discount_amount'])) {
            $discountMode = 'none';
        }
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
        $allowedFeeIds = null;
        if ($installmentMode === 'selective') {
            $allowedFeeIds = $validated['installment_allowed_fee_ids'] ?? ($validated['installment_fee_ids'] ?? []);
        }

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
                'message' => 'Pengaturan keringanan & kebijakan cicilan calon murid berhasil disimpan.',
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

        return redirect()->back()->with('success', 'Pengaturan keringanan & kebijakan cicilan calon murid berhasil disimpan.');
    }

    /**
     * Change selected academic period in session.
     */
    public function changePeriod(Request $request)
    {
        $request->validate([
            'selected_period_id' => 'required|exists:spmb_periods,id'
        ]);
        session(['selected_period_id' => $request->selected_period_id]);
        return redirect()->back()->with('success', 'Tahun ajaran berhasil diubah.');
    }
}

