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
        if (method_exists($registration, 'getRegistrationFee')) {
            $fee = $registration->getRegistrationFee();
            if ($fee) {
                return $fee;
            }
        }

        $unitId = $registration->spmb_unit_id;

        // 1. Try finding fee category for registration form (match 'Formulir', 'Pendaftaran', 'Registrasi', 'Enrollment', 'Registration')
        $feeCategory = \App\Models\SpmbFeeCategory::where(function($q) {
            $q->where('name', 'like', '%Formulir%')
              ->orWhere('name', 'like', '%Pendaftaran%')
              ->orWhere('name', 'like', '%Registrasi%')
              ->orWhere('name', 'like', '%Enrollment%')
              ->orWhere('name', 'like', '%Registration%');
        })->first();

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

        // 3. Fallback to any fee matching registration keywords
        $feeByName = \App\Models\SpmbFee::where('spmb_unit_id', $unitId)
            ->where('is_active', true)
            ->where(function($q) {
                $q->where('name', 'like', '%Formulir%')
                  ->orWhere('name', 'like', '%Pendaftaran%')
                  ->orWhere('name', 'like', '%Registrasi%')
                  ->orWhere('name', 'like', '%Enrollment%')
                  ->orWhere('name', 'like', '%Registration%');
            })->first();
        if ($feeByName) {
            return $feeByName;
        }

        // 4. Fallback to any active fee in the registration category
        if ($feeCategory) {
            $fee = \App\Models\SpmbFee::where('spmb_fee_category_id', $feeCategory->id)
                ->where('is_active', true)
                ->first();
            if ($fee) {
                return $fee;
            }
        }

        // 5. Default fallback object
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
                if ($formPaid && !in_array($status, ['agreement_signed', 'completed'])) {
                    session(['active_candidate_id' => $registration->id]);
                    if ($status === 'draft') {
                        return redirect()->route('dashboard.form', $registration->id)->with('info', 'Biaya pendaftaran telah lunas. Silakan lengkapi formulir pendaftaran.');
                    }
                    return redirect()->route('dashboard')->with('error', 'Tidak ada tagihan pembayaran aktif untuk ' . ($registration->candidate_name ?? 'calon murid') . '.');
                }
                return null;

            case 'form':
                if (!$formPaid) {
                    session(['active_candidate_id' => $registration->id]);
                    return redirect()->route('dashboard')->with('error', 'Menu Formulir untuk ' . ($registration->candidate_name ?? 'calon murid') . ' masih terkunci. Selesaikan pembayaran biaya pendaftaran terlebih dahulu.');
                }
                return null;

            case 'verification':
                if ($status === 'draft') {
                    session(['active_candidate_id' => $registration->id]);
                    return redirect()->route('dashboard')->with('error', 'Menu Verifikasi Data untuk ' . ($registration->candidate_name ?? 'calon murid') . ' masih terkunci. Lengkapi dan kirim formulir pendaftaran terlebih dahulu.');
                }
                return null;

            case 'observation':
                if (!in_array($status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed'])) {
                    session(['active_candidate_id' => $registration->id]);
                    return redirect()->route('dashboard')->with('error', 'Menu Ta\'aruf untuk ' . ($registration->candidate_name ?? 'calon murid') . ' masih terkunci. Berkas pendaftaran belum selesai diverifikasi oleh panitia.');
                }
                return null;

            case 'result':
                if (!in_array($status, ['agreement_signed', 'completed'])) {
                    session(['active_candidate_id' => $registration->id]);
                    return redirect()->route('dashboard')->with('error', 'Menu Administrasi untuk ' . ($registration->candidate_name ?? 'calon murid') . ' masih terkunci. Selesaikan tahapan sebelumnya terlebih dahulu.');
                }
                return null;
        }

        return null;
    }

    public function index(Request $request)
    {
        if ($request->has('candidate_id')) {
            session(['active_candidate_id' => (int)$request->query('candidate_id')]);
        }

        // Query all candidate registrations for this user with all required relationships
        $registrations = Registration::with(['unit', 'grade', 'period', 'wave', 'type', 'classProgram', 'extraServices', 'payments'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Query pending unpaid draft registrations for this candidate
        $pendingDrafts = Registration::with(['unit', 'grade', 'period', 'wave', 'type', 'payments'])
            ->where('user_id', auth()->id())
            ->where('registration_status', 'draft')
            ->where('payment_status', '!=', 'paid')
            ->whereDoesntHave('payments', function($pq) {
                $pq->where('payment_type', 'registration_fee')->where('status', 'success');
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        $units = SpmbUnit::with([
            'grades' => function($q) {
                $q->where('is_active', true)->orderBy('id', 'asc');
            },
            'extraServices' => function($q) {
                $q->wherePivot('is_active', true)->orderBy('spmb_extra_services.id', 'asc');
            },
            'waves' => function($q) {
                $q->wherePivot('is_active', true)->orderBy('id', 'asc');
            },
            'types' => function($q) {
                $q->wherePivot('is_active', true)->orderBy('id', 'asc');
            },
            'classPrograms' => function($q) {
                $q->wherePivot('is_active', true)->orderBy('id', 'asc');
            },
            'periods' => function($q) {
                $q->wherePivot('is_active', true)->orderBy('id', 'asc');
            }
        ])->where('is_active', true)->get();

        $grades = SpmbGrade::where('is_active', true)->get();
        $waves = \App\Models\SpmbWave::where('is_active', true)->get();
        $types = \App\Models\SpmbType::where('is_active', true)->get();
        $periods = \App\Models\SpmbPeriod::where('is_active', true)->orderBy('id', 'desc')->get();
        $classPrograms = \App\Models\SpmbClassProgram::where('is_active', true)->orderBy('id', 'asc')->get();
        $extraServices = \App\Models\SpmbExtraService::where('is_active', true)->orderBy('id', 'asc')->get();
        $activePeriod = \App\Models\SpmbPeriod::where('is_active', true)->first();

        // Fetch all active registration fees for dynamic grade fee resolution
        $registrationFees = \App\Models\SpmbFee::where('is_active', true)
            ->where(function($q) {
                $q->where('spmb_fee_category_id', 1)
                  ->orWhere('name', 'like', '%Enrollment%')
                  ->orWhere('name', 'like', '%Registration%')
                  ->orWhere('name', 'like', '%Formulir%')
                  ->orWhere('name', 'like', '%Pendaftaran%');
            })
            ->get();

        // Build dynamic registration fee mapping per unit from master database
        $unitFeeMap = [];
        foreach ($units as $u) {
            $dummyReg = new Registration(['spmb_unit_id' => $u->id]);
            $dummyReg->setRelation('unit', $u);
            $feeObj = $dummyReg->getRegistrationFee();
            $unitFeeMap[$u->id] = [
                'unit_id' => $u->id,
                'unit_code' => strtoupper($u->code),
                'amount' => (float) ($feeObj->amount ?? 350000),
                'name' => $feeObj->name ?? 'Enrollment Fee',
                'formatted' => 'Rp ' . number_format($feeObj->amount ?? 350000, 0, ',', '.')
            ];
        }

        // Share registrations with layout to prevent duplicate database query
        $allUserRegistrations = $registrations;

        return view('web.dashboard-index', compact('registrations', 'pendingDrafts', 'units', 'grades', 'waves', 'types', 'periods', 'classPrograms', 'extraServices', 'activePeriod', 'allUserRegistrations', 'unitFeeMap', 'registrationFees'));
    }
    
    public function history(Request $request)
    {
        $user = auth()->user();
        
        $registrations = Registration::with([
                'unit', 
                'grade', 
                'period', 
                'wave', 
                'type', 
                'classProgram', 
                'extraServices', 
                'payments' => function($q) {
                    $q->with('items')->orderBy('created_at', 'desc');
                }
            ])
            ->where('user_id', $user->id)
            ->where('registration_status', 'completed')
            ->orderBy('id', 'desc')
            ->get();

        if ($request->has('id')) {
            $requestedReg = Registration::where('id', $request->query('id'))->where('user_id', $user->id)->first();
            if ($requestedReg && $requestedReg->registration_status !== 'completed') {
                session(['active_candidate_id' => $requestedReg->id]);
                return redirect()->route('dashboard')->with('error', 'Menu Status Akhir untuk ' . ($requestedReg->candidate_name ?? 'calon murid') . ' masih terkunci. Menu ini hanya dapat diakses setelah ananda resmi dinyatakan diterima.');
            }
        }

        if ($registrations->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'Menu Status Akhir terkunci. Menu ini hanya dapat diakses setelah ananda resmi dinyatakan diterima.');
        }

        $selectedId = $request->query('id', $registrations->first()?->id);
        $selectedRegistration = $registrations->firstWhere('id', (int)$selectedId) ?? $registrations->first();
        session(['active_candidate_id' => $selectedRegistration->id]);

        return view('web.history', compact('user', 'registrations', 'selectedRegistration'));
    }
    
    public function createRegistration(Request $request)
    {
        $request->validate([
            'candidate_name' => 'required|string|max:255',
            'spmb_unit_id' => 'required|exists:spmb_units,id',
            'spmb_grade_id' => 'required|exists:spmb_grades,id',
            'spmb_type_id' => 'required|exists:spmb_types,id',
            'spmb_wave_id' => 'required|exists:spmb_waves,id',
            'spmb_period_id' => 'nullable|exists:spmb_periods,id',
            'spmb_class_program_id' => 'nullable|exists:spmb_class_programs,id',
        ]);
        
        $selectedUnit = SpmbUnit::find($request->spmb_unit_id);
        $periodId = $request->spmb_period_id;
        if (!$periodId) {
            $activePeriod = $selectedUnit ? $selectedUnit->activePeriods()->first() : null;
            if (!$activePeriod) {
                $activePeriod = \App\Models\SpmbPeriod::where('is_active', true)->first();
            }
            $periodId = $activePeriod?->id;
        }

        $classProgramId = $request->spmb_class_program_id;
        if (!$classProgramId) {
            $defaultProgram = \App\Models\SpmbClassProgram::where('is_active', true)->where('name', 'like', '%Reguler%')->first();
            $classProgramId = $defaultProgram?->id;
        }

        $grade = \App\Models\SpmbGrade::find($request->spmb_grade_id);

        $registration = Registration::create([
            'user_id' => auth()->id(),
            'candidate_name' => $request->candidate_name,
            'spmb_unit_id' => $request->spmb_unit_id,
            'spmb_grade_id' => $request->spmb_grade_id,
            'admission_level' => $grade ? $grade->name : null,
            'spmb_period_id' => $periodId,
            'spmb_class_program_id' => $classProgramId,
            'spmb_wave_id' => $request->spmb_wave_id,
            'spmb_type_id' => $request->spmb_type_id,
            'registration_status' => 'draft',
            'payment_status' => 'unpaid'
        ]);

        // Handle TPA extra service (Daycare) attachment
        $includeTpa = $request->boolean('include_tpa');
        $isTpa1Guru = $grade && ($grade->id == 13 || (str_contains(strtolower($grade->name), 'tpa') && (str_contains(strtolower($grade->name), 'guru') || str_contains(strtolower($grade->name), 'karyawan'))));
        
        $tpaService = \App\Models\SpmbExtraService::where('spmb_unit_id', $request->spmb_unit_id)
            ->where(function($q) {
                $q->where('name', 'like', '%TPA%')
                  ->orWhere('name', 'like', '%Daycare%')
                  ->orWhere('name', 'like', '%Penitipan%')
                  ->orWhere('code', 'like', '%TPA%')
                  ->orWhere('code', 'like', '%Daycare%');
            })->first();

        if ($includeTpa) {
            if ($tpaService) {
                $registration->extraServices()->syncWithoutDetaching([$tpaService->id]);
            }
        } elseif (!$isTpa1Guru && $tpaService) {
            $registration->extraServices()->detach($tpaService->id);
        }

        session(['active_candidate_id' => $registration->id]);
        
        if ($isTpa1Guru) {
            return redirect()->route('dashboard.payment', $registration->id)->with('info', 'Pendaftaran jalur Khusus Putra/Putri Guru & Karyawan YPAS berhasil dibuat. Silakan hubungi Admin SPMB Unit terkait untuk konfirmasi & aktivasi formulir pendaftaran.');
        }

        return redirect()->route('dashboard.payment', $registration->id);
    }

    public function deleteDraftRegistration($id)
    {
        $registration = Registration::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('registration_status', 'draft')
            ->whereDoesntHave('payments', function($q) {
                $q->where('payment_type', 'registration_fee')->where('status', 'success');
            })
            ->firstOrFail();

        // Cancel pending payments
        $registration->payments()->where('status', 'pending')->update(['status' => 'cancelled']);

        $candidateName = $registration->candidate_name ?? 'Calon Murid';
        $registration->delete();

        return redirect()->route('dashboard')->with('success', 'Draf pendaftaran untuk "' . $candidateName . '" berhasil dibatalkan.');
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
                $isSaved = !empty($registration->additional_info['step_' . $step->id . '_saved'])
                    || ($step->fields->contains('field_name', 'info_source') && !empty($registration->additional_info['info_source']))
                    || ($step->fields->contains('field_name', 'guardian_name') && !empty($registration->guardian_name));

                if ($step->fields->contains('field_name', 'info_source')) {
                    $infoSrc = $registration->getFieldValue('info_source');
                    if ($infoSrc === 'Rekomendasi Wali Murid (Referral)') {
                        $refName = $registration->getFieldValue('referral_student_name');
                        $refClass = $registration->getFieldValue('referral_student_class');
                        if (empty($refName) || empty($refClass)) {
                            $isSaved = false;
                        }
                    }
                }

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
        session(['active_candidate_id' => $registration->id]);
        
        $formPaid = $registration->payments()->where('payment_type', 'registration_fee')->where('status', 'success')->exists();
        $status = $registration->registration_status;

        // Load active payment based on phase
        $activePayment = null;
        if (in_array($status, ['agreement_signed', 'completed'])) {
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

        $formPayment = $registration->payments()->where('payment_type', 'registration_fee')->where('status', 'success')->first();
        $isFormDispensation = $formPayment && ($formPayment->payment_method === 'DISPENSATION' || !empty($formPayment->payment_info['dispensation']));

        // Build 7-step timeline
        $timeline = [
            'registration_fee' => [
                'label' => 'Biaya Pendaftaran',
                'description' => $isFormDispensation
                    ? 'Dispensasi / Pembebasan Biaya Pendaftaran (' . ($formPayment->payment_info['dispensation_reason'] ?? 'Disetujui Panitia') . ')'
                    : 'Membayar biaya seleksi pendaftaran Rp ' . number_format($feeAmount, 0, ',', '.'),
                'status' => $formPaid ? 'completed' : 'in_progress',
            ],
            'form_fill' => [
                'label' => 'Pengisian Formulir',
                'description' => 'Mengisi data lengkap calon murid, orang tua, & dokumen.',
                'status' => ($status !== 'draft') ? 'completed' : ($formPaid ? 'in_progress' : 'not_started'),
            ],
            'verification' => [
                'label' => 'Verifikasi Berkas',
                'description' => 'Pemeriksaan berkas persyaratan oleh panitia SPMB.',
                'status' => in_array($status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']) ? 'completed' : ($status === 'failed' ? 'failed' : ($status === 'submitted' ? 'in_progress' : 'not_started')),
            ],
            'observation' => [
                'label' => 'Assessment / Ta\'aruf',
                'description' => 'Sesi tes kesiapan belajar dan wawancara pendaftar.',
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
                'label' => 'Selesai',
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
        session(['active_candidate_id' => $registration->id]);

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
            $isRevision = ($registration->registration_status === 'failed');

            $registration->update([
                'registration_status' => 'submitted',
                'committee_notes' => $isRevision 
                    ? 'Formulir pendaftaran berhasil dikirim kembali. Berkas perbaikan ananda sedang dalam proses verifikasi ulang oleh panitia SPMB.'
                    : 'Formulir & berkas pendaftaran berhasil dikirim. Berkas pendaftaran ananda sedang dalam proses verifikasi oleh panitia SPMB.'
            ]);

            // Trigger notification to relevant unit admins & super admins
            try {
                $admins = \App\Models\User::getAdminsForUnit($registration->spmb_unit_id);
                $title = $isRevision ? 'Perbaikan Formulir Dikirim' : 'Formulir Pendaftaran Baru';
                $message = $isRevision
                    ? 'Calon murid "' . $registration->candidate_name . '" (' . ($registration->unit->name ?? 'Unit') . ') telah mengirimkan perbaikan formulir & berkas untuk diverifikasi ulang.'
                    : 'Calon murid "' . $registration->candidate_name . '" (' . ($registration->unit->name ?? 'Unit') . ') baru saja mengirimkan formulir pendaftaran baru untuk diverifikasi.';

                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                    'title' => $title,
                    'message' => $message,
                    'url' => route('admin.verification') . '?search=' . urlencode($registration->candidate_name),
                    'type' => $isRevision ? 'warning' : 'info',
                    'spmb_unit_id' => $registration->spmb_unit_id,
                    'registration_id' => $registration->id,
                ]));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send form submission notification', ['error' => $e->getMessage()]);
            }

            $successMsg = $isRevision 
                ? 'Formulir perbaikan berhasil dikirim kembali! Silakan menunggu verifikasi ulang berkas dari panitia.'
                : 'Formulir pendaftaran berhasil dikirim! Silakan menunggu verifikasi berkas dari panitia.';

            session()->flash('success', $successMsg);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'redirect' => route('dashboard.detail', $id)
                ]);
            }

            return redirect()->route('dashboard.detail', $id)->with('success', $successMsg);
        }

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'redirect' => route('dashboard.detail', $id)
            ]);
        }

        return redirect()->route('dashboard.detail', $id);
    }

    public function payment($id)
    {
        $registration = $this->getRegistration($id);
        $gate = $this->checkAccessGate($registration, 'payment');
        if ($gate) return $gate;
        session(['active_candidate_id' => $registration->id]);

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
                if (($itemNet - $itemPaid) <= 0) {
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
                    $itemDiscount = $registration->getItemDiscountAmount($item['name'], $item['id'] ?? null);
                    $itemNet = max(0, $itemGross - $itemDiscount);
                    $itemPaid = $isGlobalInstallment ? 0 : $registration->getItemPaidAmount($item['name']);
                    $itemRemaining = max(0, $itemNet - $itemPaid);
                    $minItemInstallment = min($itemRemaining, (float) ($registration->min_installment_amount ?: 500000));

                    $item['paid_amount'] = $itemPaid;
                    $item['discount_amount'] = $itemDiscount;
                    $item['net_amount'] = $itemNet;
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
            $currentPaymentType = 'final_fee';
        } else {
            $currentPaymentType = 'registration_fee';
            $activePayment = $registration->activeRegistrationPayment;
            $feeDetails = method_exists($registration, 'getRegistrationFeeDetails')
                ? $registration->getRegistrationFeeDetails()
                : [
                    'items' => [],
                    'total' => 300000,
                    'name' => 'Formulir Pendaftaran',
                    'gateways' => ['winpay']
                ];

            $feeAmount = $activePayment ? (float)$activePayment->amount : (float)($feeDetails['total'] ?? 300000);
            $feeGateways = $feeDetails['gateways'] ?? ['winpay'];
            $feeName = $feeDetails['name'] ?? 'Formulir Pendaftaran';
            $grossFee = $feeAmount;
            $discountAmount = 0;
            $discountNotes = null;
            $netFee = $feeAmount;
            $totalPaid = 0;
            $remainingBalance = $feeAmount;
            $installmentMode = 'none';
            $minPaymentRequired = $feeAmount;

            if (isset($feeDetails['items']) && count($feeDetails['items']) <= 1) {
                $feeDetails = null;
            }
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
            ->forPaymentType($currentPaymentType)
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
        session(['active_candidate_id' => $registration->id]);
        
        $committeeMessage = $this->getCommitteeMessage($registration);
        
        $documentFields = SpmbFormField::where('type', 'file')
            ->orderBy('order', 'asc')
            ->get();
        
        return view('web.verification', compact('registration', 'committeeMessage', 'documentFields'));
    }

    public function observation($id)
    {
        $registration = $this->getRegistration($id);
        $gate = $this->checkAccessGate($registration, 'observation');
        if ($gate) return $gate;
        session(['active_candidate_id' => $registration->id]);
        
        // Fetch dynamic agreement letter template for the candidate's unit
        $agreementTemplate = \App\Models\SpmbAgreementTemplate::where('spmb_unit_id', $registration->spmb_unit_id)->first();
        
        if ($agreementTemplate) {
            $replacements = [
                '{{nama_calon_murid}}' => $registration->candidate_name ?? '',
                '{{nama_calon_siswa}}' => $registration->candidate_name ?? '',
                '{{nama_murid}}' => $registration->candidate_name ?? '',
                '{{nama_siswa}}' => $registration->candidate_name ?? '',
                '{{nama_wali}}' => $registration->signature_name ?: ($registration->father_name ?: ($registration->mother_name ?: '')),
                '{{nama_unit}}' => $registration->unit?->name ?? '',
                '{{nama_kelas}}' => $registration->grade?->name ?? '',
                '{{tahun_ajaran}}' => $registration->period?->year ?? '2026-2027',
            ];
            
            $agreementTemplate->title = str_replace(array_keys($replacements), array_values($replacements), $agreementTemplate->title);
            $agreementTemplate->content = str_replace(array_keys($replacements), array_values($replacements), $agreementTemplate->content);
            
            // Align colons in the metadata block using custom vanilla CSS grid rows
            $metadataPattern = '/<p>(?:<[^>]+>)*(Nama Murid|Nama Calon Murid|Nama Calon Siswa|Nama Orangtua\/Wali|Nama Orang\s*Tua\s*\/\s*Wali|Tahun Ajaran|Layanan Pendidikan|Unit & Program)(?:<[^>]+>)*\s*:\s*(.*?)<\/p>/i';
            $metadataReplacement = '<div class="metadata-row text-slate-750 dark:text-slate-300"><div>$1</div><div>:</div><div class="font-bold">$2</div></div>';
            $agreementTemplate->content = preg_replace($metadataPattern, $metadataReplacement, $agreementTemplate->content);
        }

        $observationDetails = null;
        if (in_array($registration->registration_status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed'])) {
            $isPaud = stripos($registration->unit?->code ?? '', 'PAUD') !== false || stripos($registration->unit?->name ?? '', 'PAUD') !== false || stripos($registration->unit?->name ?? '', 'TK') !== false || stripos($registration->unit?->name ?? '', 'KB') !== false;
            $fallbackAddress = $isPaud 
                ? 'Jl. Candi Panggung Indah No. 1-3, Mojolangu, Kecamatan Lowokwaru, Kota Malang, Jawa Timur' 
                : 'Jl. Arumba No.31, Tunggulwulung, Kec. Lowokwaru, Kota Malang, Jawa Timur';
            $defaultAddress = $registration->unit?->taaruf_default_address ?: $fallbackAddress;

            $observationDetails = [
                'title' => $registration->unit?->taaruf_title ?? 'Jadwal dan Sesi Assessment / Ta\'aruf',
                'location' => $registration->observation_location ?: ($registration->unit?->taaruf_default_location ?: ($registration->unit?->name ?? 'Sekolah Dasar Anak Saleh')),
                'room' => $registration->observation_room ?: ($registration->unit?->taaruf_default_room ?? ''),
                'address' => $registration->observation_address ?: $defaultAddress,
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

        // Trigger notification to relevant unit admins & super admins
        try {
            $admins = \App\Models\User::getAdminsForUnit($registration->spmb_unit_id);
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                'title' => 'Surat Pernyataan Disetujui',
                'message' => 'Surat pernyataan & rincian biaya masuk untuk calon murid "' . $registration->candidate_name . '" (' . ($registration->unit->name ?? 'Unit') . ') telah ditandatangani oleh ' . $request->signature_name . '.',
                'url' => route('admin.payments.data') . '?search=' . urlencode($registration->candidate_name),
                'type' => 'success',
                'spmb_unit_id' => $registration->spmb_unit_id,
                'registration_id' => $registration->id,
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
        session(['active_candidate_id' => $registration->id]);
        
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
                if (($itemNet - $itemPaid) <= 0) {
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
                } elseif ($field->field_name === 'birth_date' || $field->type === 'date') {
                    $rules[$field->field_name] .= '|date|before_or_equal:today|after:2000-01-01';
                }
            }
        }

        // Conditional required validation for referral when Rekomendasi is chosen
        if ($request->input('info_source') === 'Rekomendasi Wali Murid (Referral)') {
            $rules['referral_student_name'] = 'required|string|min:2|max:255';
            $rules['referral_student_class'] = 'required|string|min:2|max:255';
            $rules['referral_parent_phone'] = 'nullable|string|max:50';
        }

        $customMessages = [
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melebihi tanggal hari ini / tahun berjalan.',
            'birth_date.after' => 'Tahun kelahiran tidak valid (harus di atas tahun 2000).',
            'birth_date.date' => 'Format tanggal lahir tidak valid.',
            'referral_student_name.required' => 'Nama lengkap murid yang mereferensikan wajib diisi jika memilih opsi Rekomendasi Wali Murid.',
            'referral_student_name.min' => 'Nama lengkap murid yang mereferensikan minimal 2 karakter.',
            'referral_student_class.required' => 'Kelas & unit murid saat ini (TA 2026/2027) wajib diisi jika memilih opsi Rekomendasi Wali Murid.',
            'referral_student_class.min' => 'Kelas & unit murid saat ini minimal 2 karakter.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $customMessages);

        // Age limits validation against selected grade & active academic period
        if ($step->fields->contains('field_name', 'birth_date') && $request->filled('birth_date')) {
            $grade = $registration->grade;
            if (!$grade && $registration->spmb_grade_id) {
                $grade = \App\Models\SpmbGrade::find($registration->spmb_grade_id);
            }
            if ($grade && ($grade->min_age_years !== null || $grade->max_age_years !== null)) {
                $periodYear = $registration->period ? $registration->period->year : null;
                $cutoffDate = \App\Models\SpmbGrade::resolveCutoffDate($periodYear);
                $ageCheck = $grade->validateAge($request->birth_date, $cutoffDate);
                if (!$ageCheck['valid']) {
                    $validator->after(function ($validator) use ($ageCheck) {
                        $validator->errors()->add('birth_date', $ageCheck['message']);
                    });
                }
            }
        }

        $validated = $validator->validate();

        // 2. Save fields dynamically
        $physicalColumns = [
            'candidate_name', 'nickname', 'nik', 'family_card_no', 'gender', 'birth_place', 
            'birth_date', 'religion', 'previous_school', 'admission_level',
            'address', 'house_number', 'rt', 'rw', 'kelurahan', 'kecamatan', 'city', 'province',
            'father_name', 'father_nik', 'father_job', 'father_address', 'father_phone',
            'mother_name', 'mother_nik', 'mother_job', 'mother_address', 'mother_phone',
            'guardian_name', 'guardian_nik', 'guardian_job', 'guardian_address', 'guardian_phone', 'parent_phone',
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
                $val = (is_string($val) && trim($val) === '') ? null : $val;

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
            $services = (array)$request->input('extra_services', []);

            // Restrict to extra services that are active for this unit and match eligibility criteria
            $eligibleServiceIds = \App\Models\SpmbExtraService::forUnit($registration->spmb_unit_id)->get()->filter(function($s) use ($registration) {
                return $s->matchesEligibility(
                    $registration->spmb_type_id,
                    $registration->spmb_class_program_id,
                    $registration->spmb_wave_id,
                    $registration->spmb_period_id,
                    $registration->spmb_grade_id
                );
            })->pluck('id')->toArray();

            $services = array_intersect($services, $eligibleServiceIds);
            $registration->extraServices()->sync(array_values(array_filter($services)));
        }

        // Capture referral & custom info source fields if present
        if ($request->has('referral_student_name')) {
            $additionalInfo['referral_student_name'] = $request->input('referral_student_name');
            $additionalInfo['referral_student_class'] = $request->input('referral_student_class');
            $additionalInfo['referral_parent_phone'] = $request->input('referral_parent_phone');
        }
        if ($request->has('info_source_custom')) {
            $additionalInfo['info_source_custom'] = $request->input('info_source_custom');
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
            
        $isLastStep = ($allSteps->last() && $allSteps->last()->id == $stepId);
        $allCompleted = true;
        foreach ($allSteps as $s) {
            $hasReq = false;
            foreach ($s->fields as $f) {
                if ($f->is_required) {
                    $hasReq = true;
                    $val = $registration->getFieldValue($f->field_name);
                    if (empty($val)) {
                        $allCompleted = false;
                        break 2;
                    }
                }
            }
            if (!$hasReq) {
                $isSaved = !empty($registration->additional_info['step_' . $s->id . '_saved'])
                    || ($s->fields->contains('field_name', 'info_source') && !empty($registration->additional_info['info_source']))
                    || ($s->fields->contains('field_name', 'guardian_name') && !empty($registration->guardian_name));

                if ($s->fields->contains('field_name', 'info_source')) {
                    $infoSrc = $registration->getFieldValue('info_source');
                    if ($infoSrc === 'Rekomendasi Wali Murid (Referral)') {
                        $refName = $registration->getFieldValue('referral_student_name');
                        $refClass = $registration->getFieldValue('referral_student_class');
                        if (empty($refName) || empty($refClass)) {
                            $isSaved = false;
                        }
                    }
                }

                if (!$isSaved) {
                    $allCompleted = false;
                    break;
                }
            }
        }

        $shouldSubmit = ($isLastStep && $allCompleted);

        if ($shouldSubmit && in_array($registration->registration_status, ['draft', 'failed'])) {
            $isRevision = ($registration->registration_status === 'failed');

            $registration->update([
                'registration_status' => 'submitted',
                'committee_notes' => $isRevision 
                    ? 'Formulir pendaftaran berhasil dikirim kembali. Berkas perbaikan ananda sedang dalam proses verifikasi ulang oleh panitia SPMB.'
                    : 'Formulir & berkas pendaftaran berhasil dikirim. Berkas pendaftaran ananda sedang dalam proses verifikasi oleh panitia SPMB.'
            ]);

            // Trigger notification to relevant unit admins & super admins
            try {
                $admins = \App\Models\User::getAdminsForUnit($registration->spmb_unit_id);
                $title = $isRevision ? 'Perbaikan Formulir Dikirim' : 'Formulir Pendaftaran Baru';
                $message = $isRevision
                    ? 'Calon murid "' . $registration->candidate_name . '" (' . ($registration->unit->name ?? 'Unit') . ') telah mengirimkan perbaikan formulir & berkas untuk diverifikasi ulang.'
                    : 'Calon murid "' . $registration->candidate_name . '" (' . ($registration->unit->name ?? 'Unit') . ') baru saja mengirimkan formulir pendaftaran baru untuk diverifikasi.';

                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                    'title' => $title,
                    'message' => $message,
                    'url' => route('admin.verification') . '?search=' . urlencode($registration->candidate_name),
                    'type' => $isRevision ? 'warning' : 'info',
                    'spmb_unit_id' => $registration->spmb_unit_id,
                    'registration_id' => $registration->id,
                ]));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send form submission notification', ['error' => $e->getMessage()]);
            }
        }

        $stepSuccessMsg = $shouldSubmit 
            ? ($isRevision ?? false ? 'Formulir perbaikan berhasil dikirim kembali! Silakan menunggu verifikasi ulang berkas dari panitia.' : 'Formulir pendaftaran berhasil dikirim! Silakan menunggu verifikasi berkas dari panitia.')
            : 'Langkah "' . $step->title . '" berhasil disimpan.';

        session()->flash('success', $stepSuccessMsg);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $stepSuccessMsg,
                'allCompleted' => $shouldSubmit,
                'redirect' => $shouldSubmit ? route('dashboard.detail', $id) : null
            ]);
        }

        if ($shouldSubmit) {
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
            if (in_array($status, ['agreement_signed', 'completed'])) {
                $paymentType = 'final_fee';
                $feeDetails = $this->getFinalFeeDetails($registration);



                // Filter out already fully paid items
                $fullyPaidItemNames = [];
                foreach ($feeDetails['items'] as $item) {
                    $itemGross = (float) ($item['amount'] ?? 0);
                    $itemDiscount = $registration->getItemDiscountAmount($item['name'], $item['id'] ?? null);
                    $itemNet = max(0, $itemGross - $itemDiscount);
                    $itemPaid = $registration->getItemPaidAmount($item['name'], $item['id'] ?? null);
                    if (($itemNet - $itemPaid) <= 0) {
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
                    $itemDiscount = $registration->getItemDiscountAmount($item['name'], $item['id'] ?? null);
                    $itemNet = max(0, $itemGross - $itemDiscount);
                    $itemPaid = $isGlobalInstallment ? 0 : $registration->getItemPaidAmount($item['name']);
                    $itemRemaining = max(0, $itemNet - $itemPaid);

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
                $feeDetails = method_exists($registration, 'getRegistrationFeeDetails')
                    ? $registration->getRegistrationFeeDetails()
                    : null;
                $amount = $feeDetails ? (float)$feeDetails['total'] : 300000;
                $gateways = $feeDetails['gateways'] ?? ['winpay'];
            } else {
                return redirect()->back()->with('error', 'Tidak ada tagihan pembayaran aktif pada tahapan ini.');
            }

            // Resolve active gateway based on the user's selected payment_method
            $activeChannel = \App\Models\SpmbPaymentChannel::where('code', $request->payment_method)
                ->where('is_active', true)
                ->forPaymentType($paymentType)
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

            // Calculate dynamic admin fee based on active channel configuration & gateway
            if ($activeChannel) {
                $adminFee = $activeChannel->calculateFee($amount);
            } elseif ($gateway === 'bni') {
                $feeBniVa = floatval(\App\Models\Setting::get('fee_bni_va', 1500));
                $feeBniQris = floatval(\App\Models\Setting::get('fee_bni_qris', 0.7)) / 100;
                $adminFee = (str_contains(strtolower($request->payment_method), 'qr')) ? round($amount * $feeBniQris) : $feeBniVa;
            } else {
                $feeWinpayVa = floatval(\App\Models\Setting::get('fee_winpay_va', 4500));
                $feeWinpayRetail = floatval(\App\Models\Setting::get('fee_winpay_retail', 4500));
                $feeWinpayQris = floatval(\App\Models\Setting::get('fee_winpay_qris', 0.7)) / 100;
                $feeWinpayEwallet = floatval(\App\Models\Setting::get('fee_winpay_ewallet', 2.0)) / 100;

                $methodUpper = strtoupper($request->payment_method);
                if (str_contains($methodUpper, 'QRIS')) {
                    $adminFee = round($amount * $feeWinpayQris);
                } elseif (str_contains($methodUpper, 'DANA') || str_contains($methodUpper, 'SHOPEE') || str_contains($methodUpper, 'OVO') || str_contains($methodUpper, 'LINKAJA')) {
                    $adminFee = round($amount * $feeWinpayEwallet);
                } elseif (str_contains($methodUpper, 'INDOMARET') || str_contains($methodUpper, 'ALFAMART')) {
                    $adminFee = $feeWinpayRetail;
                } else {
                    $adminFee = $feeWinpayVa;
                }
            }

            $totalAmount = $amount + $adminFee;

            // Generate cryptographically unique invoice number (anti-collision)
            $randomHex = strtoupper(bin2hex(random_bytes(3)));
            $invoiceBase = 'INV-SPMB-' . date('Ymd') . '-' . $registration->id . '-' . $randomHex;

            // Step 1: Create local pending payment first (Anti-Orphan Architecture)
            DB::beginTransaction();
            try {
                $initialPaymentInfo = ['gateway' => $gateway];
                if ($paymentType === 'final_fee' || !empty($feeDetails['items'])) {
                    $initialPaymentInfo['selected_items'] = $feeDetails['items'] ?? [];
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

                $itemsToStore = !empty($feeDetails['items']) ? $feeDetails['items'] : [
                    [
                        'id' => $feeDetails['id'] ?? null,
                        'name' => $feeDetails['name'] ?? 'Formulir Pendaftaran',
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
                $candidateName = $registration->candidate_name ?: ($registration->student_name ?: ($registration->name ?: 'Calon Murid'));

                // Susun nama transaksi: {KODE_UNIT} {JENIS_BIAYA} {NAMA_MURID} (Maksimal 24 Karakter SNAP BI)
                $rawUnit = $registration->unit?->code ?: ($registration->unit?->name ?? 'SPMB');
                $cleanUnit = preg_replace('/[^a-zA-Z0-9]/', '', $rawUnit);
                $unitCode = strtoupper(substr($cleanUnit ?: 'SPMB', 0, 4));

                $feeShort = ($paymentType === 'final_fee') ? 'Adm' : 'Form';

                $cleanStudent = preg_replace('/[^a-zA-Z0-9 ]/', ' ', $candidateName);
                $cleanStudent = preg_replace('/\s+/', ' ', trim($cleanStudent));

                $prefix = "{$unitCode} {$feeShort} ";
                $maxStudentLen = max(5, 24 - strlen($prefix));
                $shortStudent = substr($cleanStudent, 0, $maxStudentLen);
                $studentPaymentName = trim("{$prefix}{$shortStudent}");

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

                return redirect()->back()->with('error', $response['message']);
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

    /**
     * Cek status pembayaran secara real-time ke Payment Gateway (Winpay / BNI SNAP BI)
     */
    public function checkPaymentStatus($id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Transaksi pembayaran tidak ditemukan.'], 404);
            }
            return redirect()->back()->with('error', 'Transaksi pembayaran tidak ditemukan.');
        }

        // Verify ownership
        $registration = Registration::where('id', $payment->registration_id)
            ->where('user_id', auth()->id())
            ->first();
        if (!$registration) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $redirectUrl = ($payment->payment_type === 'final_fee' || in_array($registration->registration_status, ['agreement_signed', 'completed']))
            ? route('dashboard.result', $registration->id)
            : route('dashboard.form', $registration->id);

        // Jika transaksi di database kita memang sudah lunas
        if ($payment->status === 'success') {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'is_paid' => true,
                    'status' => 'PAID',
                    'message' => 'Alhamdulillah! Pembayaran Anda sudah lunas terkonfirmasi.',
                    'redirect' => $redirectUrl
                ]);
            }
            return redirect()->to($redirectUrl)->with('success', 'Pembayaran sudah lunas terkonfirmasi.');
        }

        $gatewayCode = $payment->payment_info['gateway'] ?? 'winpay';

        try {
            $gatewayService = \App\Services\PaymentGatewayFactory::make($gatewayCode ?: 'winpay');
            $statusResult = method_exists($gatewayService, 'checkPaymentStatus')
                ? $gatewayService->checkPaymentStatus($payment->invoice_number, $payment->payment_info ?: [])
                : ['success' => false, 'is_paid' => false, 'status' => 'UNKNOWN', 'message' => 'Fitur pengecekan status tidak didukung oleh gateway ini.'];

            if (!empty($statusResult['is_paid'])) {
                // Settle payment atomically
                \App\Services\PaymentSettlementService::settlePayment($payment, $statusResult['data'] ?? [], 'user_inquiry_check');

                $successMsg = ($payment->payment_type === 'final_fee')
                    ? 'Alhamdulillah! Pembayaran biaya administrasi akhir sebesar Rp ' . number_format($payment->amount, 0, ',', '.') . ' berhasil diverifikasi lunas.'
                    : 'Alhamdulillah! Pembayaran biaya pendaftaran berhasil diverifikasi lunas. Silakan lengkapi formulir pendaftaran.';

                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => true,
                        'is_paid' => true,
                        'status' => 'PAID',
                        'message' => $successMsg,
                        'redirect' => $redirectUrl
                    ]);
                }
                return redirect()->to($redirectUrl)->with('success', $successMsg);
            }

            if (($statusResult['status'] ?? '') === 'EXPIRED') {
                \App\Services\PaymentSettlementService::expirePayment($payment, 'gateway_inquiry_expired');

                $expireMsg = 'Batas waktu pembayaran tagihan ini telah kedaluwarsa. Silakan pilih kembali metode pembayaran Anda.';
                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => true,
                        'is_paid' => false,
                        'status' => 'EXPIRED',
                        'message' => $expireMsg,
                        'reload' => true
                    ]);
                }
                return redirect()->route('dashboard.payment', $registration->id)->with('warning', $expireMsg);
            }

            // Status masih pending / unpaid
            $pendingMsg = 'Pembayaran belum terdeteksi oleh sistem perbankan. Jika Anda baru saja menyelesaikan transfer, mohon tunggu 1-2 menit lalu klik tombol ini kembali.';
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'is_paid' => false,
                    'status' => 'PENDING',
                    'message' => $pendingMsg
                ]);
            }
            return redirect()->back()->with('info', $pendingMsg);

        } catch (\Throwable $e) {
            Log::error('checkPaymentStatus controller exception', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            $errMsg = 'Terjadi kendala saat memeriksa status ke bank: ' . $e->getMessage();
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'is_paid' => false,
                    'status' => 'ERROR',
                    'message' => $errMsg
                ], 500);
            }
            return redirect()->back()->with('error', $errMsg);
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
            if ($payment->payment_type === 'final_fee') {
                $hasSuccess = $registration->payments()->where('status', 'success')->where('payment_type', 'final_fee')->exists();
                $registration->update([
                    'payment_status' => $hasSuccess ? 'partially_paid' : 'unpaid'
                ]);
            } else {
                $hasSuccess = $registration->payments()->where('status', 'success')->where('payment_type', 'registration_fee')->exists();
                $registration->update([
                    'payment_status' => $hasSuccess ? 'paid' : 'unpaid'
                ]);
            }

            // Preserve selected items index and custom installment amount query parameters upon redirection
            $itemsQuery = '';
            $selectedItemAmounts = [];
            if (isset($payment->payment_info['selected_items']) && is_array($payment->payment_info['selected_items'])) {
                foreach ($payment->payment_info['selected_items'] as $si) {
                    if (!empty($si['name'])) {
                        $selectedItemAmounts[$si['name']] = $si['amount'] ?? null;
                    }
                }
            }
            if (empty($selectedItemAmounts) && $payment->items()->count() > 0) {
                foreach ($payment->items as $pi) {
                    $selectedItemAmounts[$pi->fee_name] = $pi->amount;
                }
            }

            if (!empty($selectedItemAmounts)) {
                $feeDetails = $this->getFinalFeeDetails($registration);
                if (isset($feeDetails['items']) && is_array($feeDetails['items'])) {
                    // Filter out already fully paid items
                    $fullyPaidItemNames = [];
                    foreach ($feeDetails['items'] as $item) {
                        $itemGross = (float) ($item['amount'] ?? 0);
                        $itemDiscount = $registration->getItemDiscountAmount($item['name'], $item['id'] ?? null);
                        $itemNet = max(0, $itemGross - $itemDiscount);
                        $itemPaid = $registration->getItemPaidAmount($item['name'], $item['id'] ?? null);
                        if (($itemNet - $itemPaid) <= 0) {
                            $fullyPaidItemNames[] = $item['name'];
                        }
                    }

                    $unpaidItems = [];
                    foreach ($feeDetails['items'] as $item) {
                        if (!in_array($item['name'], $fullyPaidItemNames)) {
                            $unpaidItems[] = $item;
                        }
                    }

                    // Resolve indices with custom installment amounts preserved
                    $paramPairs = [];
                    foreach ($unpaidItems as $idx => $item) {
                        if (array_key_exists($item['name'], $selectedItemAmounts)) {
                            $customAmt = $selectedItemAmounts[$item['name']];
                            if ($customAmt !== null && $customAmt > 0) {
                                $paramPairs[] = $idx . ':' . (int) $customAmt;
                            } else {
                                $paramPairs[] = $idx;
                            }
                        }
                    }
                    if (!empty($paramPairs)) {
                        $itemsQuery = '?items=' . implode(',', $paramPairs);
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
            ? 'Bukti-Pelunasan-SPMB-' . $candidateSlug . ($filterItemName ? '-' . \Illuminate\Support\Str::slug($filterItemName) : '') . '.pdf'
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
        $user = auth()->user();
        $isAdmin = ($user->role === 'admin' || $user->role === 'super_admin' || $user->role === 'superadmin');

        $registration = \App\Models\Registration::where('id', $id)
            ->when(!$isAdmin, function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->firstOrFail();
            
        if ($registration->registration_status !== 'completed') {
            return redirect()->back()->with('error', 'Surat kelulusan hanya tersedia jika status pendaftaran sudah lengkap/lunas.');
        }
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('web.admission-letter-pdf', compact('registration'));
        
        $response = response()->make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="SKP-' . ($registration->id_label ?: ('SANS-' . $registration->id)) . '.pdf"',
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

    /**
     * Update/Track WhatsApp Group join status for registration.
     */
    public function joinWaGroup(Request $request, $id)
    {
        $registration = $this->getRegistration($id);
        $additionalInfo = $registration->additional_info ?? [];

        $joined = $request->has('status') ? filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN) : true;
        
        $additionalInfo['wa_group_joined'] = $joined;
        if ($joined) {
            $additionalInfo['wa_group_joined_at'] = now()->toIso8601String();
        } else {
            unset($additionalInfo['wa_group_joined_at']);
        }

        $registration->additional_info = $additionalInfo;
        $registration->save();

        $groupUrl = $registration->unit?->spmb_group_url;

        return response()->json([
            'success' => true,
            'joined' => $joined,
            'group_url' => $groupUrl,
            'joined_at' => $joined ? now()->translatedFormat('d M Y, H:i') : null,
            'message' => $joined ? 'Status berhasil diperbarui: Anda telah bergabung ke Group WhatsApp SPMB.' : 'Status bergabung dibatalkan.'
        ]);
    }

    /**
     * Confirm/Update attendance for Ta'aruf / Observation session.
     */
    public function confirmAttendance(Request $request, $id)
    {
        $registration = $this->getRegistration($id);

        $request->validate([
            'attendance_status' => 'required|string|in:confirmed_present,reschedule_requested,cancelled',
            'attendance_notes' => 'nullable|string|max:1000',
        ]);

        $status = $request->input('attendance_status');
        $notes = $request->input('attendance_notes');

        $registration->update([
            'observation_attendance_status' => $status,
            'observation_attendance_notes' => $notes,
            'observation_attendance_confirmed_at' => now(),
        ]);

        // Trigger notification to relevant unit admins & super admins
        try {
            $admins = \App\Models\User::getAdminsForUnit($registration->spmb_unit_id);
            $isConfirmed = ($status === 'confirmed_present');
            $title = $isConfirmed ? 'Konfirmasi Kehadiran Ta\'aruf' : 'Permohonan Reschedule Ta\'aruf';
            $notifMessage = $isConfirmed
                ? 'Calon murid "' . $registration->candidate_name . '" (' . ($registration->unit->name ?? 'Unit') . ') telah mengonfirmasi SIAP HADIR untuk sesi Ta\'aruf pada tanggal ' . ($registration->observation_date ? $registration->observation_date->translatedFormat('d F Y') : '-') . '.'
                : 'Calon murid "' . $registration->candidate_name . '" (' . ($registration->unit->name ?? 'Unit') . ') mengajukan PERMOHONAN RESCHEDULE Ta\'aruf' . ($notes ? ': "' . $notes . '"' : '.');

            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                'title' => $title,
                'message' => $notifMessage,
                'url' => route('admin.taaruf') . '?unit_id=' . $registration->spmb_unit_id . '&search=' . urlencode($registration->candidate_name),
                'type' => $isConfirmed ? 'success' : 'warning',
                'spmb_unit_id' => $registration->spmb_unit_id,
                'registration_id' => $registration->id,
            ]));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send taaruf attendance notification to admin', ['error' => $e->getMessage()]);
        }

        $message = $status === 'confirmed_present' 
            ? 'Alhamdulillah, konfirmasi kehadiran Anda berhasil disimpan. Kami tunggu kehadiran ananda sesuai jadwal.' 
            : ($status === 'reschedule_requested' 
                ? 'Permohonan penjadwalan ulang (reschedule) berhasil diajukan. Panitia SPMB akan meninjau dan memperbarui jadwal ananda.' 
                : 'Status konfirmasi berhasil diperbarui.');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $status,
                'notes' => $notes,
                'confirmed_at' => now()->translatedFormat('d M Y, H:i'),
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Download observation result file for a candidate registration.
     */
    public function downloadObservationResult($id)
    {
        $registration = $this->getRegistration($id);

        if (empty($registration->observation_result_path) || !Storage::disk('public')->exists($registration->observation_result_path)) {
            return redirect()->back()->with('error', "Berkas hasil observasi tidak ditemukan.");
        }

        $filePath = Storage::disk('public')->path($registration->observation_result_path);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $safeName = 'Hasil-Observasi-' . \Illuminate\Support\Str::slug($registration->candidate_name ?: 'Calon-Murid') . '.' . $extension;

        return response()->download($filePath, $safeName);
    }
}
