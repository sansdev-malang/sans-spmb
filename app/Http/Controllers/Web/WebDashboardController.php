<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\SpmbFormStep;
use App\Models\SpmbFormField;
use App\Models\SpmbPaymentChannel;
use App\Models\SpmbUnit;
use App\Models\SpmbGrade;
use App\Models\SpmbClassProgram;
use App\Services\WinpayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebDashboardController extends Controller
{
    protected $winpayService;

    public function __construct(WinpayService $winpayService)
    {
        $this->winpayService = $winpayService;
    }

    private function getRegistration($id)
    {
        return Registration::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
    }

    public function getRegistrationFee($registration)
    {
        $unitId = $registration->spmb_unit_id;

        // 1. Try finding fee category for registration form (match 'Formulir', 'Pendaftaran', 'Registrasi')
        $feeCategory = \App\Models\SpmbFeeCategory::where(function($q) {
            $q->where('name', 'like', '%Formulir%')
              ->orWhere('name', 'like', '%Pendaftaran%')
              ->orWhere('name', 'like', '%Registrasi%');
        })->first() ?? \App\Models\SpmbFeeCategory::first();

        if ($feeCategory && $unitId) {
            $fee = \App\Models\SpmbFee::where('spmb_fee_category_id', $feeCategory->id)
                ->where('spmb_unit_id', $unitId)
                ->where('is_active', true)
                ->first();

            if (!$fee) {
                $fee = \App\Models\SpmbFee::where('spmb_fee_category_id', $feeCategory->id)
                    ->where('spmb_unit_id', $unitId)
                    ->first();
            }

            if ($fee) {
                return $fee;
            }
        }

        // 2. Check if unit has a configured registration_fee
        if ($registration->unit && !empty($registration->unit->registration_fee) && $registration->unit->registration_fee > 0) {
            return (object) [
                'id' => null,
                'name' => 'Formulir Pendaftaran ' . ($registration->unit->name ?? ''),
                'amount' => (float) $registration->unit->registration_fee,
                'payment_gateway' => ['winpay'],
                'is_active' => true,
            ];
        }

        // 3. Fallback to any active fee in the registration category
        if ($feeCategory) {
            $fee = \App\Models\SpmbFee::where('spmb_fee_category_id', $feeCategory->id)
                ->where('is_active', true)
                ->first();
            if ($fee) {
                return $fee;
            }
        }

        // 4. Default fallback object
        return (object) [
            'id' => null,
            'name' => 'Formulir Pendaftaran',
            'amount' => 350000.0,
            'payment_gateway' => ['winpay'],
            'is_active' => true,
        ];
    }

    public function getFinalFeeDetails($registration)
    {
        return $registration->getFinalFeeDetails();
    }

    private function checkAccessGate($registration, $stage)
    {
        $status = $registration->registration_status;
        $formPaid = $registration->payments()->where('payment_type', 'registration_fee')->where('status', 'success')->exists();

        switch ($stage) {
            case 'payment':
                return null;

            case 'form':
                if (!$formPaid) {
                    return redirect()->route('dashboard.payment', $registration->id)->with('error', 'Silakan lakukan pembayaran biaya pendaftaran terlebih dahulu untuk membuka formulir.');
                }
                return null;

            case 'verification':
                if ($status === 'draft') {
                    return redirect()->route('dashboard.form', $registration->id)->with('error', 'Silakan lengkapi dan kirim formulir pendaftaran terlebih dahulu.');
                }
                return null;

            case 'observation':
                if (in_array($status, ['draft', 'submitted'])) {
                    return redirect()->route('dashboard.verification', $registration->id)->with('error', 'Pendaftaran Anda belum terverifikasi oleh Panitia.');
                }
                return null;

            case 'result':
                if (!in_array($status, ['agreement_signed', 'completed'])) {
                    return redirect()->route('dashboard.detail', $registration->id)->with('error', 'Tahapan seleksi final belum dibuka.');
                }
                return null;
        }

        return null;
    }

    public function index()
    {
        // 1. Clean up empty placeholder registrations (incomplete auto-drafts)
        Registration::where('user_id', auth()->id())
            ->where(function($q) {
                $q->whereNull('candidate_name')
                  ->orWhere('candidate_name', '');
            })->delete();

        // 2. Clean up any draft registrations that do NOT have a successful registration_fee payment
        // This ensures abandoned checkouts are cleaned up and don't clutter the dashboard.
        $drafts = Registration::where('user_id', auth()->id())
            ->where('registration_status', 'draft')
            ->get();

        foreach ($drafts as $draft) {
            $hasPayment = $draft->payments()
                ->where('payment_type', 'registration_fee')
                ->where('status', 'success')
                ->exists();
            if (!$hasPayment) {
                $draft->delete();
            }
        }

        // 3. Only query registrations that are successfully paid or submitted/verified
        $registrations = Registration::with(['unit', 'grade', 'period', 'wave', 'type', 'classProgram', 'extraServices', 'payments'])
            ->where('user_id', auth()->id())
            ->where(function($q) {
                $q->whereHas('payments', function($pq) {
                    $pq->where('payment_type', 'registration_fee')
                       ->where('status', 'success');
                })
                ->orWhere('registration_status', '!=', 'draft');
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        $units = SpmbUnit::where('is_active', true)->get();
        $grades = SpmbGrade::where('is_active', true)->get();
        $waves = \App\Models\SpmbWave::where('is_active', true)->get();
        $types = \App\Models\SpmbType::where('is_active', true)->get();

        return view('web.dashboard-index', compact('registrations', 'units', 'grades', 'waves', 'types'));
    }
    
    public function createRegistration(Request $request)
    {
        $request->validate([
            'candidate_name' => 'required|string|max:255',
            'spmb_unit_id' => 'required|exists:spmb_units,id',
            'spmb_grade_id' => 'required|exists:spmb_grades,id',
            'spmb_type_id' => 'required|exists:spmb_types,id',
            'spmb_wave_id' => 'required|exists:spmb_waves,id',
        ]);
        
        $activePeriod = \App\Models\SpmbPeriod::where('is_active', true)->first();
        $grade = \App\Models\SpmbGrade::find($request->spmb_grade_id);

        $registration = Registration::create([
            'user_id' => auth()->id(),
            'candidate_name' => $request->candidate_name,
            'spmb_unit_id' => $request->spmb_unit_id,
            'spmb_grade_id' => $request->spmb_grade_id,
            'admission_level' => $grade ? ($grade->name === 'KB' ? 'Play Group' : $grade->name) : null,
            'spmb_period_id' => $activePeriod?->id,
            'spmb_wave_id' => $request->spmb_wave_id,
            'spmb_type_id' => $request->spmb_type_id,
            'registration_status' => 'draft',
            'payment_status' => 'unpaid'
        ]);
        
        return redirect()->route('dashboard.payment', $registration->id);
    }

    private function getFormDetails($registration)
    {
        $unitId = $registration->spmb_unit_id;
        $steps = SpmbFormStep::with(['fields' => function($q) use ($unitId) {
                $q->where(function($sub) use ($unitId) {
                    $sub->whereDoesntHave('units')
                        ->orWhereHas('units', function($u) use ($unitId) {
                            $u->where('spmb_units.id', $unitId);
                        });
                })->orderBy('order');
            }])
            ->where(function($q) use ($unitId) {
                $q->whereDoesntHave('units')
                    ->orWhereHas('units', function($u) use ($unitId) {
                        $u->where('spmb_units.id', $unitId);
                    });
            })
            ->orderBy('order')
            ->get();
        $stepsCount = $steps->count();
        $stepsCompleted = 0;
        $allStepsCompleted = true;

        $previousCompleted = true;
        foreach ($steps as $step) {
            $isCompleted = true;
            $hasRequiredField = false;

            foreach ($step->fields as $field) {
                if ($field->is_required) {
                    $hasRequiredField = true;
                    $val = $registration->getFieldValue($field->field_name);
                    if (empty($val)) {
                        $isCompleted = false;
                        break;
                    }
                }
            }

            if (!$hasRequiredField) {
                $isSaved = !empty($registration->additional_info['step_' . $step->id . '_saved']) || !empty($registration->guardian_name);
                $isCompleted = $previousCompleted && $isSaved;
            } else {
                $isCompleted = $isCompleted && $previousCompleted;
            }

            $step->is_completed = $isCompleted;
            if ($isCompleted) {
                $stepsCompleted++;
            } else {
                $allStepsCompleted = false;
                $previousCompleted = false;
            }
        }

        return [
            'steps' => $steps,
            'stepsCount' => $stepsCount,
            'stepsCompleted' => $stepsCompleted,
            'allStepsCompleted' => $allStepsCompleted,
        ];
    }

    public function detail($id)
    {
        $registration = $this->getRegistration($id);
        
        $formPaid = $registration->payments()->where('payment_type', 'registration_fee')->where('status', 'success')->exists();
        $status = $registration->registration_status;

        // Load active payment based on phase
        $activePayment = null;
        if ($status === 'agreement_signed') {
            $activePayment = $registration->activeFinalPayment;
        } else {
            $activePayment = $registration->activeRegistrationPayment;
        }

        $formDetails = $this->getFormDetails($registration);
        $allStepsCompleted = $formDetails['allStepsCompleted'];
        $stepsCompleted = $formDetails['stepsCompleted'];
        $stepsCount = $formDetails['stepsCount'];

        $fee = $this->getRegistrationFee($registration);
        $feeAmount = $fee ? $fee->amount : 350000;
        $feeGateway = $fee ? ($fee->payment_gateway === 'bni' ? 'BNI SNAP' : 'Winpay') : 'Winpay';

        // Build 7-step timeline
        $timeline = [
            'registration_fee' => [
                'label' => 'Pembayaran Formulir',
                'description' => 'Membayar biaya seleksi pendaftaran Rp ' . number_format($feeAmount, 0, ',', '.'),
                'status' => $formPaid ? 'completed' : 'in_progress',
            ],
            'form_fill' => [
                'label' => 'Pengisian Formulir',
                'description' => 'Mengisi data lengkap calon siswa, orang tua, & dokumen.',
                'status' => ($status !== 'draft') ? 'completed' : ($formPaid ? 'in_progress' : 'not_started'),
            ],
            'verification' => [
                'label' => 'Verifikasi Berkas',
                'description' => 'Pemeriksaan berkas persyaratan oleh panitia SPMB.',
                'status' => in_array($status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']) ? 'completed' : ($status === 'failed' ? 'failed' : ($status === 'submitted' ? 'in_progress' : 'not_started')),
            ],
            'observation' => [
                'label' => 'Observasi / Ta\'aruf',
                'description' => 'Tes kesiapan belajar calon siswa secara daring.',
                'status' => in_array($status, ['taaruf_completed', 'agreement_signed', 'completed']) ? 'completed' : ($status === 'verified' ? 'in_progress' : 'not_started'),
            ],
            'agreement' => [
                'label' => 'Persetujuan Pernyataan',
                'description' => 'Menandatangani kesepakatan biaya dan tata tertib.',
                'status' => in_array($status, ['agreement_signed', 'completed']) ? 'completed' : ($status === 'taaruf_completed' ? 'in_progress' : 'not_started'),
            ],
            'final_payment' => [
                'label' => 'Administrasi Akhir',
                'description' => 'Pelunasan biaya masuk yayasan dan SPP bulanan.',
                'status' => ($status === 'completed') ? 'completed' : ($status === 'agreement_signed' ? 'in_progress' : 'not_started'),
            ],
            'completed' => [
                'label' => 'Kelulusan & Selesai',
                'description' => 'Resmi bergabung dengan Sekolah Anak Saleh.',
                'status' => ($status === 'completed') ? 'completed' : 'not_started',
            ],
        ];

        $observationDetails = null;
        if (in_array($status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed'])) {
            $observationDetails = [
                'title' => 'Tes Observasi secara daring',
                'datetime' => 'Sabtu, 26 Okt 2024. 08:00 - 10:00 WIB',
                'zoom_link' => 'https://zoom.us/j/9876543210',
                'guide_link' => 'https://sekolah-anak-saleh.sch.id/panduan-observasi.pdf'
            ];
        }

        $committeeMessage = $this->getCommitteeMessage($registration);

        $feeDb = $this->getRegistrationFee($registration);
        $feeGateways = $feeDb ? (is_array($feeDb->payment_gateway) ? $feeDb->payment_gateway : [$feeDb->payment_gateway]) : ['winpay'];
        $channels = \App\Models\SpmbPaymentChannel::where('is_active', true)
            ->whereHas('gateway', function($q) use ($feeGateways) {
                $q->whereIn('code', $feeGateways);
            })
            ->orderBy('type')
            ->orderBy('name')
            ->get();
        $feeGateway = reset($feeGateways) ?: 'winpay';

        return view('web.dashboard', compact('registration', 'activePayment', 'timeline', 'committeeMessage', 'observationDetails', 'stepsCompleted', 'stepsCount', 'feeAmount', 'feeGateway', 'channels'));
    }

    public function form($id)
    {
        $registration = $this->getRegistration($id);
        $gate = $this->checkAccessGate($registration, 'form');
        if ($gate) return $gate;

        $formDetails = $this->getFormDetails($registration);
        $steps = $formDetails['steps'];
        $allStepsCompleted = $formDetails['allStepsCompleted'];
        
        return view('web.form', compact('registration', 'steps', 'allStepsCompleted'));
    }

    public function submitForm($id)
    {
        $registration = $this->getRegistration($id);
        $gate = $this->checkAccessGate($registration, 'form');
        if ($gate) return $gate;

        $formDetails = $this->getFormDetails($registration);
        
        if (!$formDetails['allStepsCompleted']) {
            return redirect()->back()->with('error', 'Silakan lengkapi seluruh tahapan formulir terlebih dahulu.');
        }

        if (in_array($registration->registration_status, ['draft', 'failed'])) {
            $registration->update([
                'registration_status' => 'submitted',
                'committee_notes' => 'Formulir pendaftaran berhasil dikirim kembali. Berkas pendaftaran ananda sedang dalam proses verifikasi ulang oleh panitia SPMB.'
            ]);

            // Trigger notification to all admins
            try {
                $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                    'title' => 'Formulir Pendaftaran Dikirim',
                    'message' => 'Calon siswa "' . $registration->candidate_name . '" baru saja mengirimkan formulir & berkas pendaftaran untuk diverifikasi.',
                    'url' => route('admin.verification') . '?search=' . urlencode($registration->candidate_name),
                    'type' => 'info',
                ]));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send form submission notification', ['error' => $e->getMessage()]);
            }

            return redirect()->route('dashboard.detail', $id)->with('success', 'Formulir pendaftaran berhasil dikirim kembali! Silakan menunggu verifikasi berkas dari panitia.');
        }

        return redirect()->route('dashboard.detail', $id);
    }

    public function payment($id)
    {
        $registration = $this->getRegistration($id);
        $gate = $this->checkAccessGate($registration, 'payment');
        if ($gate) return $gate;

        // Determine active payment based on phase
        if (in_array($registration->registration_status, ['agreement_signed', 'completed'])) {
            $activePayment = $registration->activeFinalPayment;
            $feeDetails = $this->getFinalFeeDetails($registration);

            // Filter out already fully paid items
            $fullyPaidItemNames = [];
            foreach ($feeDetails['items'] as $item) {
                $itemGross = (float) ($item['amount'] ?? 0);
                $itemDiscount = $registration->getItemDiscountAmount($item['name'], $item['id'] ?? null);
                $itemNet = max(0, $itemGross - $itemDiscount);
                $itemPaid = $registration->getItemPaidAmount($item['name'], $item['id'] ?? null);
                if (($itemNet - $itemPaid) <= 0 && $itemNet > 0) {
                    $fullyPaidItemNames[] = $item['name'];
                }
            }

            // Exclude already fully paid items from payable items list
            $unpaidItems = [];
            foreach ($feeDetails['items'] as &$item) {
                if (!in_array($item['name'], $fullyPaidItemNames)) {
                    if (!isset($item['gateways'])) {
                        $feeRow = \App\Models\SpmbFee::where('name', $item['name'])
                            ->where('spmb_unit_id', $registration->spmb_unit_id)
                            ->first();
                        $item['gateways'] = $feeRow ? (is_array($feeRow->payment_gateway) ? $feeRow->payment_gateway : [$feeRow->payment_gateway]) : ['winpay'];
                    }
                    $unpaidItems[] = $item;
                }
            }
            unset($item);
            $paidItemNames = $fullyPaidItemNames;
            $latestSuccessPayment = $registration->payments()
                ->where('status', 'success')
                ->where('payment_type', 'final_fee')
                ->latest()
                ->first();

            $showSuccessDetails = false;
            if ($registration->payment_status === 'paid') {
                $showSuccessDetails = true;
            } elseif ($latestSuccessPayment && (!$activePayment || $activePayment->status === 'success') && request()->query('items') === null) {
                $showSuccessDetails = true;
            }

            if ($showSuccessDetails) {
                $feeDetails['items'] = $latestSuccessPayment->payment_info['selected_items'] ?? [];
                $feeDetails['total'] = $latestSuccessPayment->base_amount ?? ($latestSuccessPayment->amount - $latestSuccessPayment->admin_fee);
                $feeAmount = $latestSuccessPayment->amount;
            } else {
                $allSnapshotItems = $feeDetails['items'];
                $selectedItemsQuery = request()->query('items');
                $itemAmountMap = [];

                if (is_array($selectedItemsQuery)) {
                    $itemAmountMap = $selectedItemsQuery;
                    $indices = array_keys($selectedItemsQuery);
                } elseif (is_string($selectedItemsQuery) && trim($selectedItemsQuery) !== '') {
                    $pairs = explode(',', $selectedItemsQuery);
                    $indices = [];
                    foreach ($pairs as $pair) {
                        $pair = trim($pair);
                        if (str_contains($pair, ':')) {
                            [$k, $v] = explode(':', $pair, 2);
                            $k = trim($k);
                            $itemAmountMap[$k] = (float) trim($v);
                            $indices[] = $k;
                        } else {
                            $itemAmountMap[$pair] = null;
                            $indices[] = $pair;
                        }
                    }
                } else {
                    $indices = null;
                }

                if ($indices !== null) {
                    $filteredItems = [];
                    foreach ($indices as $idx) {
                        $candItem = null;
                        $customAmt = $itemAmountMap[$idx] ?? null;

                        if (isset($allSnapshotItems[$idx])) {
                            $candItem = $allSnapshotItems[$idx];
                        } elseif (isset($unpaidItems[$idx])) {
                            $candItem = $unpaidItems[$idx];
                        } else {
                            foreach ($unpaidItems as $uItem) {
                                if ((isset($uItem['id']) && (string)$uItem['id'] === (string)$idx) || strcasecmp(trim($uItem['name']), (string)$idx) === 0) {
                                    $candItem = $uItem;
                                    break;
                                }
                            }
                        }

                        if ($candItem && !in_array($candItem['name'], $paidItemNames)) {
                            $candItem['custom_amount_requested'] = $customAmt;
                            $filteredItems[] = $candItem;
                        }
                    }
                    $feeDetails['items'] = !empty($filteredItems) ? $filteredItems : $unpaidItems;
                } else {
                    $feeDetails['items'] = $unpaidItems;
                }

                $selectedTotal = (float) array_sum(array_column($feeDetails['items'], 'amount'));
                $feeDetails['total'] = $selectedTotal;
                $discountAmount = (float) ($registration->discount_amount ?? 0);

                $isGlobalInstallment = ($registration->installment_mode === 'all');
                $isSelectiveInstallment = ($registration->installment_mode === 'selective');

                // Annotate items with installment allowed flag and item-level paid tracking
                $selectedItemsPaid = 0;
                $hasInstallmentItemInSelection = false;
                $mandatorySelectedRemaining = 0;
                $installmentSelectedRemaining = 0;
                $totalTransactionPrincipal = 0;

                foreach ($feeDetails['items'] as &$item) {
                    $item['is_installment_allowed'] = $registration->isFeeInstallmentAllowed($item['name'], $item['id'] ?? null);
                    
                    $itemGross = (float) ($item['amount'] ?? 0);
                    $itemPaid = $isGlobalInstallment ? 0 : $registration->getItemPaidAmount($item['name']);
                    $itemRemaining = max(0, $itemGross - $itemPaid);
                    $minItemInstallment = min($itemRemaining, (float) ($registration->min_installment_amount ?: 500000));

                    $item['paid_amount'] = $itemPaid;
                    $item['remaining_amount'] = $itemRemaining;
                    $item['min_installment'] = $minItemInstallment;

                    if (($isGlobalInstallment || $item['is_installment_allowed']) && isset($item['custom_amount_requested']) && $item['custom_amount_requested'] !== null) {
                        $userCustomAmt = (float) $item['custom_amount_requested'];
                        $itemPayAmount = min($itemRemaining, max($minItemInstallment, $userCustomAmt));
                    } else {
                        $itemPayAmount = $itemRemaining;
                    }

                    $item['amount_to_pay'] = $itemPayAmount;
                    $totalTransactionPrincipal += $itemPayAmount;
                    $selectedItemsPaid += $itemPaid;

                    if ($isGlobalInstallment || $item['is_installment_allowed']) {
                        $hasInstallmentItemInSelection = true;
                        $installmentSelectedRemaining += $itemRemaining;
                    } else {
                        $mandatorySelectedRemaining += $itemRemaining;
                    }
                }
                unset($item);

                if ($isGlobalInstallment) {
                    $grossFee = $registration->getGrossFee() ?: $selectedTotal;
                    $netFee = $registration->net_fee;
                    $totalPaid = (float) ($registration->total_paid_final_fee ?? 0);
                    $remainingBalance = (float) ($registration->remaining_balance ?? $netFee);
                    $canInstallment = ($remainingBalance > 0);
                    $minInstallment = (float) ($registration->min_installment_amount ?: 500000);
                    $minPaymentRequired = min($remainingBalance, max(1, $minInstallment));
                } else {
                    $grossFee = $selectedTotal;
                    $netFee = max(0, $selectedTotal - $discountAmount);
                    $totalPaid = $selectedItemsPaid;
                    $remainingBalance = max(0, $netFee - $totalPaid);

                    if ($isSelectiveInstallment && $hasInstallmentItemInSelection) {
                        $canInstallment = true;
                        $minInstallment = (float) ($registration->min_installment_amount ?: 500000);
                        $minPart = min($installmentSelectedRemaining, $minInstallment);
                        $minPaymentRequired = min($remainingBalance, max(1, $mandatorySelectedRemaining + $minPart));
                    } else {
                        $canInstallment = false;
                        $minPaymentRequired = $remainingBalance;
                    }
                }

                $installmentMode = $canInstallment ? ($registration->installment_mode ?? 'selective') : 'none';
                $discountNotes = $registration->discount_notes;

                $feeAmount = ($activePayment && $activePayment->status === 'pending') 
                    ? (float) $activePayment->amount 
                    : (float) $totalTransactionPrincipal;
            }
            
            // Calculate the intersection of gateways for the selected items
            $commonGateways = null;
            foreach ($feeDetails['items'] as $item) {
                $itemGateways = $item['gateways'] ?? ['winpay'];
                if ($commonGateways === null) {
                    $commonGateways = $itemGateways;
                } else {
                    $commonGateways = array_intersect($commonGateways, $itemGateways);
                }
            }
            $feeGateways = !empty($commonGateways) ? array_values($commonGateways) : ['winpay'];
            $feeName = 'Pelunasan Biaya Administrasi Akhir';
        } else {
            $activePayment = $registration->activeRegistrationPayment;
            $fee = $this->getRegistrationFee($registration);
            $feeAmount = $activePayment ? $activePayment->amount : ($fee ? $fee->amount : 350000);
            $feeDetails = null;
            $feeGateways = $fee ? (is_array($fee->payment_gateway) ? $fee->payment_gateway : [$fee->payment_gateway]) : ['winpay'];
            $feeName = $fee ? $fee->name : 'Formulir Pendaftaran';
            $grossFee = $feeAmount;
            $discountAmount = 0;
            $discountNotes = null;
            $netFee = $feeAmount;
            $totalPaid = 0;
            $remainingBalance = $feeAmount;
            $installmentMode = 'none';
            $minPaymentRequired = $feeAmount;
        }

        // Auto-heal inconsistent pending payment status if no active pending transaction exists
        if ($registration->payment_status === 'pending' && (!$activePayment || $activePayment->status !== 'pending')) {
            $hasSuccess = $registration->payments()->where('status', 'success')->exists();
            $registration->update([
                'payment_status' => $hasSuccess ? 'partially_paid' : 'unpaid'
            ]);
            $registration->refresh();
        }

        $channels = SpmbPaymentChannel::where('is_active', true)
            ->whereHas('gateway', function($q) use ($feeGateways) {
                $q->whereIn('code', $feeGateways);
            })
            ->orderBy('type')
            ->orderBy('name')
            ->get();
        $feeGateway = reset($feeGateways) ?: 'winpay';

        return view('web.payment', compact(
            'registration', 'activePayment', 'channels', 'feeAmount', 'feeGateway', 
            'feeDetails', 'feeName', 'grossFee', 'discountAmount', 'discountNotes', 
            'netFee', 'totalPaid', 'remainingBalance', 'installmentMode', 'minPaymentRequired'
        ));
    }

    public function verification($id)
    {
        $registration = $this->getRegistration($id);
        $gate = $this->checkAccessGate($registration, 'verification');
        if ($gate) return $gate;
        
        $committeeMessage = $this->getCommitteeMessage($registration);
        
        return view('web.verification', compact('registration', 'committeeMessage'));
    }

    public function observation($id)
    {
        $registration = $this->getRegistration($id);
        $gate = $this->checkAccessGate($registration, 'observation');
        if ($gate) return $gate;
        
        // Fetch dynamic agreement letter template for the candidate's unit
        $agreementTemplate = \App\Models\SpmbAgreementTemplate::where('spmb_unit_id', $registration->spmb_unit_id)->first();
        
        if ($agreementTemplate) {
            $replacements = [
                '{{nama_calon_siswa}}' => $registration->candidate_name ?? '',
                '{{nama_wali}}' => $registration->signature_name ?: ($registration->father_name ?: ($registration->mother_name ?: '')),
                '{{nama_unit}}' => $registration->unit?->name ?? '',
                '{{nama_kelas}}' => $registration->grade?->name ?? '',
                '{{tahun_ajaran}}' => $registration->period?->year ?? '2026-2027',
            ];
            
            $agreementTemplate->title = str_replace(array_keys($replacements), array_values($replacements), $agreementTemplate->title);
            $agreementTemplate->content = str_replace(array_keys($replacements), array_values($replacements), $agreementTemplate->content);
            
            // Align colons in the metadata block using custom vanilla CSS grid rows
            $metadataPattern = '/<p>(?:<[^>]+>)*(Nama Murid|Nama Calon Siswa|Nama Orangtua\/Wali|Nama Orang\s*Tua\s*\/\s*Wali|Tahun Ajaran|Layanan Pendidikan|Unit & Program)(?:<[^>]+>)*\s*:\s*(.*?)<\/p>/i';
            $metadataReplacement = '<div class="metadata-row text-slate-750 dark:text-slate-300"><div>$1</div><div>:</div><div class="font-bold">$2</div></div>';
            $agreementTemplate->content = preg_replace($metadataPattern, $metadataReplacement, $agreementTemplate->content);
        }

        $observationDetails = null;
        if (in_array($registration->registration_status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed'])) {
            $observationDetails = [
                'title' => $registration->unit?->taaruf_title ?? 'Sesi Ta\'aruf Tatap Muka',
                'location' => $registration->observation_location ?: ($registration->unit?->taaruf_default_location ?? 'Sekolah Anak Saleh'),
                'notes' => $registration->observation_notes ?: ($registration->unit?->taaruf_instructions ?? '')
            ];
        }
        
        return view('web.observation', compact('registration', 'observationDetails', 'agreementTemplate'));
    }

    public function submitAgreement(Request $request, $id)
    {
        $registration = $this->getRegistration($id);
        if ($registration->registration_status !== 'taaruf_completed') {
            return redirect()->back()->with('error', 'Tahapan ini belum aktif.');
        }

        $request->validate([
            'agree_rules' => 'required|accepted',
            'agree_fees' => 'required|accepted',
            'signature_name' => 'required|string|max:255',
        ]);

        // Capture snapshot of final fee details at this exact moment
        $feeDetails = $this->getFinalFeeDetails($registration);

        $registration->update([
            'registration_status' => 'agreement_signed',
            'payment_status' => 'unpaid', // reset to unpaid for final fees
            'final_fee_snapshot' => $feeDetails,
            'signature_name' => $request->signature_name,
            'signed_at' => \Carbon\Carbon::now(),
            'committee_notes' => 'Pernyataan kesanggupan ditandatangani oleh: ' . $request->signature_name . '. Silakan lakukan pembayaran biaya administrasi seleksi akhir.'
        ]);

        // Trigger notification to all admins
        try {
            $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                'title' => 'Surat Pernyataan Disetujui',
                'message' => 'Surat pernyataan & rincian biaya masuk untuk calon siswa "' . $registration->candidate_name . '" telah ditandatangani oleh ' . $request->signature_name . '.',
                'url' => route('admin.payments.data') . '?search=' . urlencode($registration->candidate_name),
                'type' => 'success',
            ]));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send agreement signature notification', ['error' => $e->getMessage()]);
        }

        return redirect()->route('dashboard.result', $id)->with('success', 'Pernyataan kesanggupan berhasil disetujui. Silakan pelajari rincian administrasi di bawah ini.');
    }

    public function result($id)
    {
        $registration = $this->getRegistration($id);
        $gate = $this->checkAccessGate($registration, 'result');
        if ($gate) return $gate;
        
        $feeDetails = $this->getFinalFeeDetails($registration);
        
        // Patch gateways for backward compatibility
        if (isset($feeDetails['items']) && is_array($feeDetails['items'])) {
            foreach ($feeDetails['items'] as &$item) {
                if (!isset($item['gateways'])) {
                    $feeRow = \App\Models\SpmbFee::where('name', $item['name'])
                        ->where('spmb_unit_id', $registration->spmb_unit_id)
                        ->first();
                    $item['gateways'] = $feeRow ? (is_array($feeRow->payment_gateway) ? $feeRow->payment_gateway : [$feeRow->payment_gateway]) : ['winpay'];
                }
            }
            unset($item);
        }
        
        // Calculate fully paid item names
        $fullyPaidItemNames = [];
        if (isset($feeDetails['items']) && is_array($feeDetails['items'])) {
            foreach ($feeDetails['items'] as $item) {
                $itemGross = (float) ($item['amount'] ?? 0);
                $itemDiscount = $registration->getItemDiscountAmount($item['name'], $item['id'] ?? null);
                $itemNet = max(0, $itemGross - $itemDiscount);
                $itemPaid = $registration->getItemPaidAmount($item['name'], $item['id'] ?? null);
                if (($itemNet - $itemPaid) <= 0 && $itemNet > 0) {
                    $fullyPaidItemNames[] = $item['name'];
                }
            }
        }
        $paidItemNames = $fullyPaidItemNames;
        
        $grossFee = $registration->getGrossFee() ?: ($feeDetails['total'] ?? 0);
        $discountAmount = (float) ($registration->discount_amount ?? 0);
        $discountNotes = $registration->discount_notes;
        $netFee = $registration->net_fee;
        $totalPaid = $registration->total_paid_final_fee;
        $remainingBalance = $registration->remaining_balance;
        $installmentMode = $registration->installment_mode ?? 'none';
        $minPaymentRequired = $registration->getMinimumPaymentRequired();

        // Annotate items with installment allowed flag
        if (isset($feeDetails['items']) && is_array($feeDetails['items'])) {
            foreach ($feeDetails['items'] as &$item) {
                $item['is_installment_allowed'] = $registration->isFeeInstallmentAllowed($item['name'], $item['id'] ?? null);
            }
            unset($item);
        }
        
        return view('web.result', compact(
            'registration', 'feeDetails', 'paidItemNames', 'grossFee', 
            'discountAmount', 'discountNotes', 'netFee', 'totalPaid', 
            'remainingBalance', 'installmentMode', 'minPaymentRequired'
        ));
    }

    public function saveStep(Request $request, $id, $stepId)
    {
        $step = SpmbFormStep::with('fields')->findOrFail($stepId);
        $registration = $this->getRegistration($id);

        // 1. Build dynamic validation rules
        $rules = [];
        foreach ($step->fields as $field) {
            if ($field->type === 'file') {
                $hasFile = !empty($registration->getFieldValue($field->field_name));
                $rules[$field->field_name] = ($field->is_required && !$hasFile) ? 'required|file|mimes:pdf,jpg,jpeg,png|max:2048' : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048';
            } else {
                $rules[$field->field_name] = $field->is_required ? 'required' : 'nullable';
                if ($field->type === 'email') {
                    $rules[$field->field_name] .= '|email';
                } elseif ($field->type === 'number') {
                    $rules[$field->field_name] .= '|numeric';
                }
            }
        }

        $validated = $request->validate($rules);

        // 2. Save fields dynamically
        $physicalColumns = [
            'candidate_name', 'nickname', 'nik', 'family_card_no', 'gender', 'birth_place', 
            'birth_date', 'religion', 'previous_school', 'admission_level',
            'address', 'house_number', 'rt', 'rw', 'kelurahan', 'kecamatan', 'city', 'province',
            'father_name', 'father_nik', 'father_address', 'father_phone',
            'mother_name', 'mother_nik', 'mother_address', 'mother_phone',
            'guardian_name', 'guardian_nik', 'guardian_address', 'guardian_phone', 'parent_phone',
            'student_photo_path', 'birth_certificate_path', 'family_card_path', 'diploma_certificate_path',
            'student_card_path', 'special_needs_assessment_path', 'payment_receipt_path',
            'spmb_wave_id', 'spmb_type_id', 'spmb_period_id', 'spmb_class_program_id'
        ];

        $additionalInfo = $registration->additional_info ?? [];

        foreach ($step->fields as $field) {
            $fieldName = $field->field_name;

            if ($field->type === 'file') {
                if ($request->hasFile($fieldName)) {
                    // Delete old file if exists
                    $oldPath = $registration->getFieldValue($fieldName);
                    if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                    $path = $request->file($fieldName)->store('documents', 'public');
                    if (in_array($fieldName, $physicalColumns)) {
                        $registration->{$fieldName} = $path;
                    } else {
                        $additionalInfo[$fieldName] = $path;
                    }
                }
            } else {
                $val = $request->input($fieldName);
                if ($fieldName === 'class_program') {
                    $program = SpmbClassProgram::where('name', $val)->first();
                    $registration->spmb_class_program_id = $program ? $program->id : null;
                } elseif ($fieldName === 'extra_services') {
                    // Handled below via pivot sync to keep DB normalized
                } elseif (in_array($fieldName, $physicalColumns)) {
                    $registration->{$fieldName} = $val;
                } else {
                    $additionalInfo[$fieldName] = $val;
                }
            }
        }

        // Sync extra services if the step has extra_services field
        if ($step->fields->where('field_name', 'extra_services')->count() > 0) {
            $registration->extraServices()->sync(array_filter((array)$request->input('extra_services', [])));
        }

        $additionalInfo['step_' . $stepId . '_saved'] = true;
        $registration->additional_info = $additionalInfo;
        $registration->save();

        // 3. Check if all steps are completed. If yes, transition status to 'submitted'!
        $unitId = $registration->spmb_unit_id;
        $allSteps = SpmbFormStep::with(['fields' => function($q) use ($unitId) {
                $q->where(function($sub) use ($unitId) {
                    $sub->whereDoesntHave('units')
                        ->orWhereHas('units', function($u) use ($unitId) {
                            $u->where('spmb_units.id', $unitId);
                        });
                })->orderBy('order');
            }])
            ->where(function($q) use ($unitId) {
                $q->whereDoesntHave('units')
                    ->orWhereHas('units', function($u) use ($unitId) {
                        $u->where('spmb_units.id', $unitId);
                    });
            })
            ->orderBy('order')
            ->get();
        $allCompleted = true;
        foreach ($allSteps as $s) {
            foreach ($s->fields as $f) {
                if ($f->is_required) {
                    $val = $registration->getFieldValue($f->field_name);
                    if (empty($val)) {
                        $allCompleted = false;
                        break 2;
                    }
                }
            }
        }

        if ($allCompleted && in_array($registration->registration_status, ['draft', 'failed'])) {
            $registration->update([
                'registration_status' => 'submitted',
                'committee_notes' => 'Formulir & berkas pendaftaran berhasil dikirim. Berkas pendaftaran ananda sedang dalam proses verifikasi oleh panitia SPMB.'
            ]);

            // Trigger notification to all admins
            try {
                $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                    'title' => 'Formulir Pendaftaran Dikirim',
                    'message' => 'Calon siswa "' . $registration->candidate_name . '" baru saja mengirimkan formulir & berkas pendaftaran untuk diverifikasi.',
                    'url' => route('admin.verification') . '?search=' . urlencode($registration->candidate_name),
                    'type' => 'info',
                ]));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send form submission notification', ['error' => $e->getMessage()]);
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Langkah "' . $step->title . '" berhasil disimpan.',
                'allCompleted' => $allCompleted,
                'redirect' => $allCompleted ? route('dashboard.detail', $id) : null
            ]);
        }

        if ($allCompleted) {
            return redirect()->route('dashboard.detail', $id)->with('success', 'Formulir pendaftaran berhasil dikirim! Silakan menunggu verifikasi berkas dari panitia.');
        }

        return redirect()->back()->with('success', 'Langkah "' . $step->title . '" berhasil disimpan.');
    }

    public function updateCandidateInfo(Request $request, $id)
    {
        $registration = $this->getRegistration($id);
        
        $unitId = $registration->spmb_unit_id ?: 1;
        $validGrades = \App\Models\SpmbGrade::where('spmb_unit_id', $unitId)
            ->where('is_active', true)
            ->pluck('name')
            ->map(fn($n) => $n === 'KB' ? 'Play Group' : $n)
            ->toArray();

        $request->validate([
            'candidate_name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'nik' => 'required|string|digits:16',
            'gender' => 'required|string|in:male,female',
            'birth_place' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'religion' => 'required|string|max:100',
            'previous_school' => 'nullable|string|max:255',
            'admission_level' => 'required|string|in:' . implode(',', $validGrades),
            'class_program' => 'required|string',
        ]);

        $program = SpmbClassProgram::where('name', $request->class_program)->first();
        $registration->update(array_merge(
            $request->only([
                'candidate_name', 'nickname', 'nik', 'gender',
                'birth_place', 'birth_date', 'religion',
                'previous_school', 'admission_level'
            ]),
            ['spmb_class_program_id' => $program ? $program->id : null]
        ));

        return redirect()->back()->with('success', 'Candidate personal information saved. Please fill step 2.');
    }

    public function updateParentInfo(Request $request, $id)
    {
        $request->validate([
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'parent_phone' => 'required|string|min:10|max:15',
        ]);

        $registration = $this->getRegistration($id);
        $registration->update($request->only([
            'father_name', 'mother_name', 'parent_phone'
        ]));

        return redirect()->back()->with('success', 'Parent information saved. Please fill step 3.');
    }

    public function uploadDocuments(Request $request, $id)
    {
        $request->validate([
            'birth_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'family_card' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $registration = $this->getRegistration($id);

        if ($request->hasFile('birth_certificate')) {
            $birthCertPath = $request->file('birth_certificate')->store('documents', 'public');
            $registration->birth_certificate_path = $birthCertPath;
        }

        if ($request->hasFile('family_card')) {
            $familyCardPath = $request->file('family_card')->store('documents', 'public');
            $registration->family_card_path = $familyCardPath;
        }

        $registration->registration_status = 'submitted';
        $registration->save();

        return redirect()->back()->with('success', 'Documents uploaded and form submitted successfully!');
    }

    public function chargePayment(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $registration = $this->getRegistration($id);

        $lockKey = 'charge_lock_reg_' . $registration->id;
        $lock = Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Sedang memproses permintaan pembayaran sebelumnya. Silakan tunggu beberapa saat.');
        }

        try {
            $status = $registration->registration_status;

            // Determine payment type
            if ($status === 'agreement_signed') {
                $paymentType = 'final_fee';
                $feeDetails = $this->getFinalFeeDetails($registration);

                // Filter out already fully paid items
                $fullyPaidItemNames = [];
                foreach ($feeDetails['items'] as $item) {
                    $itemGross = (float) ($item['amount'] ?? 0);
                    $itemDiscount = $registration->getItemDiscountAmount($item['name'], $item['id'] ?? null);
                    $itemNet = max(0, $itemGross - $itemDiscount);
                    $itemPaid = $registration->getItemPaidAmount($item['name'], $item['id'] ?? null);
                    if (($itemNet - $itemPaid) <= 0 && $itemNet > 0) {
                        $fullyPaidItemNames[] = $item['name'];
                    }
                }

                // Exclude already fully paid items from original feeDetails items list
                $unpaidItems = [];
                foreach ($feeDetails['items'] as &$item) {
                    if (!in_array($item['name'], $fullyPaidItemNames)) {
                        if (!isset($item['gateways'])) {
                            $feeRow = \App\Models\SpmbFee::where('name', $item['name'])
                                ->where('spmb_unit_id', $registration->spmb_unit_id)
                                ->first();
                            $item['gateways'] = $feeRow ? (is_array($feeRow->payment_gateway) ? $feeRow->payment_gateway : [$feeRow->payment_gateway]) : ['winpay'];
                        }
                        $unpaidItems[] = $item;
                    }
                }
                unset($item);
                $paidItemNames = $fullyPaidItemNames;
                $allSnapshotItems = $feeDetails['items'];
                // Apply manual checked items filter if passed in query string or POST input
                $selectedItemsQuery = $request->input('items') ?? request()->query('items');
                $itemAmountMap = [];

                if (is_array($selectedItemsQuery)) {
                    $itemAmountMap = $selectedItemsQuery;
                    $indices = array_keys($selectedItemsQuery);
                } elseif (is_string($selectedItemsQuery) && trim($selectedItemsQuery) !== '') {
                    $pairs = explode(',', $selectedItemsQuery);
                    $indices = [];
                    foreach ($pairs as $pair) {
                        $pair = trim($pair);
                        if (str_contains($pair, ':')) {
                            [$k, $v] = explode(':', $pair, 2);
                            $k = trim($k);
                            $itemAmountMap[$k] = (float) trim($v);
                            $indices[] = $k;
                        } else {
                            $itemAmountMap[$pair] = null;
                            $indices[] = $pair;
                        }
                    }
                } else {
                    $indices = null;
                }

                if ($indices !== null) {
                    $filteredItems = [];
                    foreach ($indices as $idx) {
                        $candItem = null;
                        $customAmt = $itemAmountMap[$idx] ?? null;

                        if (isset($allSnapshotItems[$idx])) {
                            $candItem = $allSnapshotItems[$idx];
                        } elseif (isset($unpaidItems[$idx])) {
                            $candItem = $unpaidItems[$idx];
                        } else {
                            foreach ($unpaidItems as $uItem) {
                                if ((isset($uItem['id']) && (string)$uItem['id'] === (string)$idx) || strcasecmp(trim($uItem['name']), (string)$idx) === 0) {
                                    $candItem = $uItem;
                                    break;
                                }
                            }
                        }

                        if ($candItem && !in_array($candItem['name'], $paidItemNames)) {
                            $candItem['custom_amount_requested'] = $customAmt;
                            $filteredItems[] = $candItem;
                        }
                    }
                    $feeDetails['items'] = !empty($filteredItems) ? $filteredItems : $unpaidItems;
                } else {
                    $feeDetails['items'] = $unpaidItems;
                }

                $selectedTotal = (float) array_sum(array_column($feeDetails['items'], 'amount'));
                $feeDetails['total'] = $selectedTotal;
                $discountAmount = (float) ($registration->discount_amount ?? 0);

                $isGlobalInstallment = ($registration->installment_mode === 'all');
                $isSelectiveInstallment = ($registration->installment_mode === 'selective');
                $inputItemAmounts = $request->input('item_amounts', []);

                $totalCalculatedPrincipal = 0;
                $processedSelectedItems = [];

                foreach ($feeDetails['items'] as $item) {
                    $isInstallmentAllowed = $registration->isFeeInstallmentAllowed($item['name'], $item['id'] ?? null);
                    $itemGross = (float) ($item['amount'] ?? 0);
                    $itemPaid = $isGlobalInstallment ? 0 : $registration->getItemPaidAmount($item['name']);
                    $itemRemaining = max(0, $itemGross - $itemPaid);

                    if ($itemRemaining <= 0) continue;

                    $minItemInstallment = min($itemRemaining, (float) ($registration->min_installment_amount ?: 500000));

                    if ($isGlobalInstallment || $isInstallmentAllowed) {
                        if (isset($inputItemAmounts[$item['name']])) {
                            $rawCustom = str_replace(['.', ',', ' '], '', $inputItemAmounts[$item['name']]);
                            $itemAmountToPay = floatval($rawCustom);
                        } elseif (isset($item['custom_amount_requested']) && $item['custom_amount_requested'] !== null) {
                            $itemAmountToPay = floatval($item['custom_amount_requested']);
                        } else {
                            $itemAmountToPay = $itemRemaining;
                        }

                        if ($itemAmountToPay < $minItemInstallment) {
                            return redirect()->back()->with('error', "Nominal cicilan untuk {$item['name']} tidak boleh kurang dari batas minimal Rp " . number_format($minItemInstallment, 0, ',', '.'));
                        }

                        if ($itemAmountToPay > $itemRemaining) {
                            $itemAmountToPay = $itemRemaining;
                        }
                    } else {
                        $itemAmountToPay = $itemRemaining;
                    }

                    $totalCalculatedPrincipal += $itemAmountToPay;
                    $itemCopy = $item;
                    $itemCopy['amount'] = $itemAmountToPay;
                    $processedSelectedItems[] = $itemCopy;
                }

                $amount = $totalCalculatedPrincipal;
                $feeDetails['items'] = $processedSelectedItems;

                if ($amount <= 0) {
                    return redirect()->back()->with('error', 'Seluruh item yang dipilih sudah lunas.');
                }

                $finalFee = \App\Models\SpmbFee::where('spmb_unit_id', $registration->spmb_unit_id)
                    ->where('spmb_fee_category_id', 2)
                    ->first();
                $gateways = $finalFee ? (is_array($finalFee->payment_gateway) ? $finalFee->payment_gateway : [$finalFee->payment_gateway]) : ['winpay'];
            } elseif (in_array($status, ['draft', 'submitted', 'verified'])) {
                if ($registration->payment_status === 'paid') {
                    return redirect()->back()->with('error', 'Biaya pendaftaran Anda sudah lunas.');
                }
                $paymentType = 'registration_fee';
                $fee = $this->getRegistrationFee($registration);
                $amount = $fee ? $fee->amount : 350000;
                $gateways = $fee ? (is_array($fee->payment_gateway) ? $fee->payment_gateway : [$fee->payment_gateway]) : ['winpay'];
            } else {
                return redirect()->back()->with('error', 'Tidak ada tagihan pembayaran aktif pada tahapan ini.');
            }

            // Resolve active gateway based on the user's selected payment_method
            $activeChannel = \App\Models\SpmbPaymentChannel::where('code', $request->payment_method)
                ->where('is_active', true)
                ->whereHas('gateway', function($q) use ($gateways) {
                    $q->whereIn('code', $gateways);
                })
                ->first();
            
            $gateway = 'winpay';
            if ($activeChannel && $activeChannel->gateway) {
                $gateway = $activeChannel->gateway->code;
            } else {
                $gateway = reset($gateways) ?: 'winpay';
            }

            // Fetch fee configurations dynamically from settings
            $feeBniVa = floatval(\App\Models\Setting::get('fee_bni_va', 1500));
            $feeBniQris = floatval(\App\Models\Setting::get('fee_bni_qris', 0.7)) / 100;
            $feeWinpayVa = floatval(\App\Models\Setting::get('fee_winpay_va', 4500));

            // Calculate dynamic admin fee based on payment method and active gateway
            $adminFee = $feeWinpayVa;
            if ($activeChannel && $activeChannel->gateway && $activeChannel->gateway->code === 'bni') {
                if ($activeChannel->type === 'qris') {
                    $adminFee = round($amount * $feeBniQris);
                } else {
                    $adminFee = $feeBniVa;
                }
            } else {
                $adminFee = $feeWinpayVa;
            }

            $totalAmount = $amount + $adminFee;

            // Generate cryptographically unique invoice number (anti-collision)
            $randomHex = strtoupper(bin2hex(random_bytes(3)));
            $invoiceBase = 'INV-SPMB-' . date('Ymd') . '-' . $registration->id . '-' . $randomHex;

            // Step 1: Create local pending payment first (Anti-Orphan Architecture)
            DB::beginTransaction();
            try {
                $initialPaymentInfo = ['gateway' => $gateway];
                if ($paymentType === 'final_fee') {
                    $initialPaymentInfo['selected_items'] = $feeDetails['items'];
                }

                $payment = Payment::create([
                    'registration_id' => $registration->id,
                    'invoice_number' => $invoiceBase,
                    'amount' => $totalAmount,
                    'base_amount' => $amount,
                    'admin_fee' => $adminFee,
                    'payment_method' => $request->payment_method,
                    'reference_id' => null,
                    'payment_info' => $initialPaymentInfo,
                    'status' => 'pending',
                    'payment_type' => $paymentType
                ]);

                $itemsToStore = ($paymentType === 'final_fee') ? ($feeDetails['items'] ?? []) : [
                    [
                        'id' => $fee->id ?? null,
                        'name' => $fee->name ?? 'Formulir Pendaftaran',
                        'amount' => $amount,
                    ]
                ];

                foreach ($itemsToStore as $pIt) {
                    $feeId = (!empty($pIt['id']) && \App\Models\SpmbFee::where('id', $pIt['id'])->exists()) ? $pIt['id'] : null;
                    \App\Models\PaymentItem::create([
                        'payment_id' => $payment->id,
                        'spmb_fee_id' => $feeId,
                        'fee_name' => $pIt['name'] ?? 'Biaya Administrasi',
                        'amount' => $pIt['amount'] ?? 0,
                    ]);
                }

                $registration->update([
                    'payment_status' => 'pending'
                ]);

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Failed to create local pending payment in web dashboard', ['error' => $e->getMessage()]);
                return redirect()->back()->with('error', 'Gagal membuat tagihan pembayaran: ' . $e->getMessage());
            }

            // Step 2: Request payment transaction to Gateway
            try {
                $candidateName = $registration->candidate_name ?: ($registration->student_name ?: ($registration->name ?: 'Calon Siswa'));
                $unitCode = $registration->unit?->code ?: ($registration->unit?->name ? strtoupper(substr($registration->unit->name, 0, 4)) : 'SPMB');

                if ($paymentType === 'final_fee') {
                    if (isset($feeDetails['items']) && count($feeDetails['items']) === 1) {
                        $feeTypeName = $feeDetails['items'][0]['name'] ?? 'Administrasi';
                    } else {
                        $feeTypeName = 'Administrasi';
                    }
                } else {
                    $feeTypeName = $fee ? $fee->name : 'Formulir';
                }

                $studentPaymentName = trim("{$unitCode} - {$feeTypeName} - {$candidateName}");
                $studentPhone = $registration->parent_phone ?? $registration->phone ?? null;
                $gatewayService = \App\Services\PaymentGatewayFactory::make($gateway);
                $response = $gatewayService->createPayment($totalAmount, $invoiceBase, $request->payment_method, $studentPaymentName, $studentPhone);
            } catch (\Throwable $e) {
                $response = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }

            if (!$response['success']) {
                $payment->update([
                    'status' => 'failed',
                    'payment_info' => array_merge($payment->payment_info ?: [], ['failure_reason' => $response['message']])
                ]);

                // Revert registration payment_status so the UI is not left in pending state
                $hasPrevSuccess = $registration->payments()->where('status', 'success')->exists();
                $registration->update([
                    'payment_status' => $hasPrevSuccess ? 'partially_paid' : 'unpaid'
                ]);

                return redirect()->back()->with('error', 'Gagal memproses pembayaran melalui gateway: ' . $response['message']);
            }

            // Step 3: Update local payment record with gateway response
            $paymentData = $response['data'];
            $invoiceNo = $paymentData['trxId'] ?? $paymentData['partnerReferenceNo'] ?? $invoiceBase;
            $refId = $paymentData['referenceId'] ?? $paymentData['partnerReferenceNo'] ?? null;

            $payment->update([
                'invoice_number' => $invoiceNo,
                'reference_id' => $refId,
                'payment_info' => array_merge(is_array($paymentData) ? $paymentData : [], $paymentType === 'final_fee' ? ['selected_items' => $feeDetails['items']] : []),
            ]);

            return redirect()->back()->with('success', 'Invoice pembayaran berhasil diterbitkan.');
        } finally {
            $lock->release();
        }
    }

    public function simulatePaymentCallback($id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return redirect()->back()->with('error', 'Transaksi pembayaran tidak ditemukan.');
        }

        // Verify ownership
        $registration = Registration::where('id', $payment->registration_id)
            ->where('user_id', auth()->id())
            ->first();
        if (!$registration) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Format Payload asli SNAP BI
            $payload = [
                'trxId' => $payment->invoice_number,
                'paymentStatus' => 'SUCCESS',
                'responseCode' => '2002500',
                'paymentAmount' => [
                    'value' => number_format($payment->amount, 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'additionalInfo' => [
                    'invoiceNumber' => $payment->invoice_number
                ]
            ];

            // Dispatch directly to PaymentController callback to avoid local webserver deadlock
            $callbackReq = \Illuminate\Http\Request::create(
                '/api/payments/callback',
                'POST',
                [],
                [],
                [],
                [
                    'HTTP_CONTENT_TYPE' => 'application/json',
                    'HTTP_X_TIMESTAMP' => date('c'),
                    'HTTP_X_SIGNATURE' => 'SIMULATED_SIGNATURE',
                    'HTTP_X_DEVELOPER_SIMULATOR' => 'true',
                ],
                json_encode($payload)
            );

            $callbackResponse = app(\App\Http\Controllers\Api\PaymentController::class)->callback($callbackReq);

            if ($callbackResponse->getStatusCode() === 200) {
                $registration->refresh();
                if ($payment->payment_type === 'final_fee' || in_array($registration->registration_status, ['agreement_signed', 'completed'])) {
                    return redirect()->route('dashboard.result', $registration->id)->with('success', 'Alhamdulillah! Pembayaran administrasi sebesar Rp ' . number_format($payment->amount, 0, ',', '.') . ' berhasil diselesaikan.');
                }
                return redirect()->route('dashboard.form', $registration->id)->with('success', 'Alhamdulillah! Pembayaran biaya pendaftaran berhasil. Silakan lengkapi formulir pendaftaran.');
            } else {
                Log::error('Simulate callback failed', [
                    'status' => $callbackResponse->getStatusCode(),
                    'body' => $callbackResponse->getContent()
                ]);
                return redirect()->back()->with('error', 'Gagal memproses callback: ' . $callbackResponse->getContent());
            }

        } catch (\Throwable $e) {
            Log::error('Simulate callback exception', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal memproses simulasi pembayaran: ' . $e->getMessage());
        }
    }

    public function cancelPayment($id)
    {
        $payment = Payment::findOrFail($id);
        
        // Verify ownership
        $registration = Registration::where('id', $payment->registration_id)
            ->where('user_id', auth()->id())
            ->first();
        if (!$registration) {
            abort(403, 'Unauthorized action.');
        }

        $regId = $payment->registration_id;

        // Tutup / Delete VA di Payment Gateway (Winpay SNAP BI DELETE /v1.0/transfer-va/delete-va)
        $gatewayCode = $payment->payment_info['gateway'] ?? 'winpay';
        try {
            $gatewayService = \App\Services\PaymentGatewayFactory::make($gatewayCode ?: 'winpay');
            if (method_exists($gatewayService, 'cancelPayment')) {
                $gatewayService->cancelPayment($payment->invoice_number, $payment->payment_info ?: []);
            }
        } catch (\Throwable $gwEx) {
            Log::warning('Gateway cancel API call warning', [
                'payment_id' => $payment->id,
                'invoice' => $payment->invoice_number,
                'error' => $gwEx->getMessage()
            ]);
        }

        DB::beginTransaction();
        try {
            $payment->update([
                'status' => 'cancelled',
                'payment_info' => array_merge($payment->payment_info ?: [], [
                    'cancelled_at' => now()->toIso8601String()
                ])
            ]);

            $registration = Registration::find($payment->registration_id);
            $hasSuccess = $registration->payments()->where('status', 'success')->exists();
            $registration->update([
                'payment_status' => $hasSuccess ? 'partially_paid' : 'unpaid'
            ]);

            // Preserve selected items index query parameters upon redirection
            $itemsQuery = '';
            if (isset($payment->payment_info['selected_items']) && is_array($payment->payment_info['selected_items'])) {
                $selectedNames = array_column($payment->payment_info['selected_items'], 'name');
                $feeDetails = $this->getFinalFeeDetails($registration);
                if (isset($feeDetails['items']) && is_array($feeDetails['items'])) {
                    // Collect names of successful payments to compute the unpaid list
                    $paidItemNames = [];
                    $successfulPayments = $registration->payments()
                        ->where('status', 'success')
                        ->where('payment_type', 'final_fee')
                        ->get();
                    foreach ($successfulPayments as $p) {
                        if (isset($p->payment_info['selected_items']) && is_array($p->payment_info['selected_items'])) {
                            foreach ($p->payment_info['selected_items'] as $item) {
                                $paidItemNames[] = $item['name'];
                            }
                        }
                    }

                    $unpaidItems = [];
                    foreach ($feeDetails['items'] as $item) {
                        if (!in_array($item['name'], $paidItemNames)) {
                            $unpaidItems[] = $item;
                        }
                    }

                    // Resolve indices
                    $indices = [];
                    foreach ($unpaidItems as $idx => $item) {
                        if (in_array($item['name'], $selectedNames)) {
                            $indices[] = $idx;
                        }
                    }
                    if (!empty($indices)) {
                        $itemsQuery = '?items=' . implode(',', $indices);
                    }
                }
            }

            DB::commit();
            return redirect()->to(route('dashboard.payment', $regId) . $itemsQuery)->with('success', 'Transaksi pembayaran berhasil dibatalkan. Silakan pilih kembali metode pembayaran Anda.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }

    public function downloadReceipt($id)
    {
        $payment = \App\Models\Payment::findOrFail($id);
        
        // Verifikasi kepemilikan ATAU akses Admin
        $user = auth()->user();
        $registration = \App\Models\Registration::find($payment->registration_id);
        
        $hasAccess = ($user->role === 'admin' || $user->role === 'super_admin' || $user->role === 'superadmin')
            || ($registration && $registration->user_id === $user->id);

        if (!$hasAccess || !$registration) {
            abort(403, 'Unauthorized action.');
        }

        if ($payment->status !== 'success') {
            return redirect()->back()->with('error', 'Bukti pembayaran hanya tersedia untuk transaksi yang sudah lunas.');
        }

        $isSettlement = request()->query('type') === 'settlement';
        $filterItemName = request()->query('item_name');
        $filterItemId = request()->query('item_id');

        $feeDetails = $this->getFinalFeeDetails($registration);
        $allSuccessfulPayments = $registration->payments()
            ->where('status', 'success')
            ->where('payment_type', 'final_fee')
            ->orderBy('created_at')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('web.payment-receipt-pdf', compact(
            'payment', 
            'registration', 
            'isSettlement', 
            'filterItemName', 
            'filterItemId', 
            'feeDetails',
            'allSuccessfulPayments'
        ));
        
        $candidateSlug = $registration->candidate_name ? \Illuminate\Support\Str::slug($registration->candidate_name) : $registration->id;
        $filename = $isSettlement 
            ? 'Kwitansi-Utama-Pelunasan-SPMB-' . $candidateSlug . ($filterItemName ? '-' . \Illuminate\Support\Str::slug($filterItemName) : '') . '.pdf'
            : 'Bukti-Bayar-SPMB-' . $payment->invoice_number . '.pdf';

        $response = response()->make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
        
        if (request()->has('download_token')) {
            $cookie = cookie('download_status_' . request()->query('download_token'), 'success', 5, null, null, false, false);
            $response->withCookie($cookie);
        }
        
        return $response;
    }

    public function downloadAdmissionLetter($id)
    {
        $registration = \App\Models\Registration::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        if ($registration->registration_status !== 'completed') {
            return redirect()->back()->with('error', 'Surat kelulusan hanya tersedia jika status pendaftaran sudah lengkap/lunas.');
        }
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('web.admission-letter-pdf', compact('registration'));
        
        $response = response()->make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="SKP-SANS-' . str_pad($registration->id, 4, '0', STR_PAD_LEFT) . '.pdf"',
        ]);
        
        if (request()->has('download_token')) {
            $cookie = cookie('download_status_' . request()->query('download_token'), 'success', 5, null, null, false, false);
            $response->withCookie($cookie);
        }
        
        return $response;
    }

    private function getCommitteeMessage($registration)
    {
        $status = $registration->registration_status;
        $feeDb = $this->getRegistrationFee($registration);
        $feeAmount = $feeDb ? $feeDb->amount : 350000;
        $formPaid = $registration->payments()->where('payment_type', 'registration_fee')->where('status', 'success')->exists();

        $committeeMessage = $registration->committee_notes;
        
        // If candidate is verified/approved, clear any old rejection notes from view
        if (in_array($status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed'])) {
            if ($committeeMessage && (str_contains($committeeMessage, 'perlu diperbaiki') || str_contains($committeeMessage, 'Mohon maaf') || str_contains($committeeMessage, 'ditolak'))) {
                $committeeMessage = null;
            }
        }

        $defaultMessages = [
            'Pembayaran formulir pendaftaran berhasil diterima. Silakan isi dan lengkapi formulir pendaftaran Anda.',
            'Pembayaran formulir terkonfirmasi. Silakan lengkapi formulir pendaftaran Anda pada menu di atas.',
            'Selamat datang! Silakan lakukan pembayaran biaya pendaftaran formulir sebesar Rp ' . number_format($feeAmount, 0, ',', '.') . ' untuk membuka formulir.',
            'Formulir Anda telah disimpan. Berkas Anda sedang diperiksa oleh Panitia SPMB. Mohon tunggu proses verifikasi selesai.',
            'Formulir pendaftaran berhasil dikirim. Berkas pendaftaran ananda sedang dalam proses verifikasi oleh panitia SPMB.'
        ];
        
        if (empty($committeeMessage) || in_array($committeeMessage, $defaultMessages)) {
            if ($status === 'draft') {
                if (!$formPaid) {
                    return 'Selamat datang! Silakan lakukan pembayaran biaya pendaftaran formulir sebesar Rp ' . number_format($feeAmount, 0, ',', '.') . ' untuk membuka formulir.';
                } else {
                    return 'Pembayaran formulir terkonfirmasi. Silakan lengkapi formulir pendaftaran Anda pada menu di atas.';
                }
            } elseif ($status === 'submitted') {
                return 'Formulir pendaftaran berhasil dikirim. Berkas pendaftaran ananda sedang dalam proses verifikasi oleh panitia SPMB.';
            } elseif ($status === 'verified') {
                return 'Alhamdulillah, berkas pendaftaran ananda ' . ($registration->candidate_name ?? 'Ananda') . ' telah kami terima dan diverifikasi. Silakan persiapkan untuk mengikuti sesi Ta\'aruf tatap muka di unit sekolah.';
            } elseif ($status === 'taaruf_completed') {
                return 'Sesi Ta\'aruf offline selesai dilakukan. Silakan mengisi dan menyetujui Formulir Pernyataan Kesanggupan untuk memproses biaya administrasi akhir.';
            } elseif ($status === 'agreement_signed') {
                $finalFees = $this->getFinalFeeDetails($registration);
                return 'Pernyataan kesanggupan disetujui. Silakan selesaikan pembayaran biaya administrasi akhir sebesar Rp ' . number_format($finalFees['total'], 0, ',', '.') . ' untuk menyelesaikan pendaftaran.';
            } elseif ($status === 'completed') {
                return 'Selamat! Pendaftaran ananda ' . ($registration->candidate_name ?? 'Ananda') . ' dinyatakan selesai dan resmi diterima di Sekolah Anak Saleh. Selamat bergabung!';
            } elseif ($status === 'failed') {
                return 'Mohon maaf, berkas pendaftaran Anda tidak lolos verifikasi. Silakan hubungi admin panitia.';
            }
        }

        return $committeeMessage;
    }
}
