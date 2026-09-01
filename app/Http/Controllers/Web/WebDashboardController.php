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

    private function getRegistrationFee($registration)
    {
        $feeCategory = \App\Models\SpmbFeeCategory::where('name', 'Formulir Pendaftaran')->first();
        if ($feeCategory) {
            // 1. Try to find fee by spmb_unit_id and fee category
            if (!empty($registration->spmb_unit_id)) {
                $fee = \App\Models\SpmbFee::where('spmb_fee_category_id', $feeCategory->id)
                    ->where('spmb_unit_id', $registration->spmb_unit_id)
                    ->where('is_active', true)
                    ->first();
                if (!$fee) {
                    $fee = \App\Models\SpmbFee::where('spmb_fee_category_id', $feeCategory->id)
                        ->where('spmb_unit_id', $registration->spmb_unit_id)
                        ->first();
                }
                if ($fee) return $fee;
            }

            // 2. Fallback to admission_level mapping if present
            $admissionLevel = $registration->admission_level ?? '';
            $fee = \App\Models\SpmbFee::where('spmb_fee_category_id', $feeCategory->id)
                ->where(function($q) use ($admissionLevel) {
                    if ($admissionLevel) {
                        $q->where('name', 'like', '%' . $admissionLevel . '%')
                          ->orWhere('name', 'Formulir Pendaftaran');
                    } else {
                        $q->where('name', 'Formulir Pendaftaran');
                    }
                })->first();
            
            if (!$fee) {
                $fee = \App\Models\SpmbFee::where('spmb_fee_category_id', $feeCategory->id)->first();
            }
            return $fee;
        }
        return null;
    }

    public function getFinalFeeDetails($registration)
    {
        $unitId = $registration->spmb_unit_id;
        $unitName = $registration->unit->name ?? '';
        $gradeName = $registration->grade->name ?? '';

        // Get category "Biaya Administrasi" (handling typo in fc2 "Biaya Adminstrasi" or correct "Biaya Administrasi")
        $category = \App\Models\SpmbFeeCategory::where('name', 'Biaya Adminstrasi')
            ->orWhere('name', 'Biaya Administrasi')
            ->first();

        // Fetch active fees for this unit & category
        $fees = null;
        if ($category && $unitId) {
            $fees = \App\Models\SpmbFee::where('spmb_fee_category_id', $category->id)
                ->where('spmb_unit_id', $unitId)
                ->where('is_active', true)
                ->get();
        }

        // Initialize details with 0
        $details = [
            'uang_gedung' => 0,
            'seragam' => 0,
            'spp' => 0,
            'kegiatan' => 0,
            'items' => [],
        ];

        if ($fees && $fees->count() > 0) {
            foreach ($fees as $fee) {
                $feeNameUpper = strtoupper($fee->name);
                $gradeNameUpper = strtoupper($gradeName);

                // Skip if fee name contains a specific grade that does not match candidate's grade
                $gradeKeywords = ['TK A', 'TK B', 'KB', 'TPA', 'PLAY GROUP', 'PLAYGROUP', 'KELAS 1', 'KELAS 7'];
                $hasKeyword = false;
                foreach ($gradeKeywords as $kw) {
                    if (strpos($feeNameUpper, $kw) !== false) {
                        $hasKeyword = true;
                        if (strpos($gradeNameUpper, $kw) !== false || ($kw === 'PLAY GROUP' && strpos($gradeNameUpper, 'KB') !== false) || ($kw === 'KB' && strpos($gradeNameUpper, 'PLAY GROUP') !== false)) {
                            $hasKeyword = false;
                            break;
                        }
                    }
                }

                if ($hasKeyword) {
                    continue;
                }

                // Map to corresponding keys for legacy rendering if applicable
                if (strpos($feeNameUpper, 'GEDUNG') !== false || strpos($feeNameUpper, 'MUSA\'ADAH') !== false || strpos($feeNameUpper, 'MUSAADAH') !== false) {
                    $details['uang_gedung'] = $fee->amount;
                } elseif (strpos($feeNameUpper, 'SERAGAM') !== false) {
                    $details['seragam'] = $fee->amount;
                } elseif (strpos($feeNameUpper, 'SPP') !== false) {
                    $details['spp'] = $fee->amount;
                } elseif (strpos($feeNameUpper, 'KEGIATAN') !== false) {
                    $details['kegiatan'] = $fee->amount;
                } else {
                    $details[strtolower(str_replace(' ', '_', $fee->name))] = $fee->amount;
                }

                $details['items'][] = [
                    'name' => $fee->name,
                    'amount' => $fee->amount,
                    'gateways' => is_array($fee->payment_gateway) ? $fee->payment_gateway : [$fee->payment_gateway]
                ];
            }

            $details['total'] = array_sum(array_map(function($item) {
                return $item['amount'];
            }, $details['items']));

            return $details;
        }

        // Fallback: Hardcoded details for backward compatibility with seeder defaults
        if (stripos($unitName, 'PAUD') !== false || stripos($gradeName, 'KB') !== false || stripos($gradeName, 'TK') !== false || stripos($gradeName, 'TPA') !== false) {
            if (stripos($gradeName, 'KB Saja') !== false) {
                $details = ['uang_gedung' => 3000000, 'seragam' => 1000000, 'spp' => 250000, 'kegiatan' => 750000];
            } elseif (stripos($gradeName, 'TK A') !== false || stripos($gradeName, 'TK B') !== false) {
                $details = ['uang_gedung' => 3500000, 'seragam' => 1200000, 'spp' => 300000, 'kegiatan' => 800000];
            } elseif (stripos($gradeName, 'TPA Saja') !== false) {
                $details = ['uang_gedung' => 2500000, 'seragam' => 800000, 'spp' => 200000, 'kegiatan' => 500000];
            } elseif (stripos($gradeName, 'KB + TPA') !== false) {
                $details = ['uang_gedung' => 4500000, 'seragam' => 1500000, 'spp' => 400000, 'kegiatan' => 1100000];
            } elseif (stripos($gradeName, 'TK + TPA') !== false) {
                $details = ['uang_gedung' => 5000000, 'seragam' => 1600000, 'spp' => 450000, 'kegiatan' => 1150000];
            } else {
                $details = ['uang_gedung' => 3200000, 'seragam' => 1100000, 'spp' => 280000, 'kegiatan' => 780000];
            }
        } elseif (stripos($unitName, 'SMP') !== false) {
            if (stripos($gradeName, 'Pindahan') !== false || stripos($gradeName, 'Mutasi') !== false) {
                $details = ['uang_gedung' => 6000000, 'seragam' => 2000000, 'spp' => 600000, 'kegiatan' => 1100000];
            } else {
                $details = ['uang_gedung' => 8500000, 'seragam' => 2000000, 'spp' => 600000, 'kegiatan' => 1400000];
            }
        } else {
            if (stripos($gradeName, 'Pindahan') !== false || stripos($gradeName, 'Mutasi') !== false) {
                $details = ['uang_gedung' => 5000000, 'seragam' => 1800000, 'spp' => 500000, 'kegiatan' => 1000000];
            } else {
                $details = ['uang_gedung' => 7000000, 'seragam' => 1800000, 'spp' => 500000, 'kegiatan' => 1200000];
            }
        }

        // Add to items list for fallback too
        $details['items'] = [
            ['name' => 'Uang Gedung', 'amount' => $details['uang_gedung'], 'gateways' => ['winpay']],
            ['name' => 'Biaya Seragam', 'amount' => $details['seragam'], 'gateways' => ['winpay']],
            ['name' => 'SPP Bulanan', 'amount' => $details['spp'], 'gateways' => ['winpay']],
            ['name' => 'Uang Kegiatan', 'amount' => $details['kegiatan'], 'gateways' => ['winpay']],
        ];

        $details['total'] = $details['uang_gedung'] + $details['seragam'] + $details['spp'] + $details['kegiatan'];
        return $details;
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
        $registrations = Registration::with(['unit', 'grade'])
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

        foreach ($steps as $step) {
            $isCompleted = true;
            foreach ($step->fields as $field) {
                if ($field->is_required) {
                    $val = $registration->getFieldValue($field->field_name);
                    if (empty($val)) {
                        $isCompleted = false;
                        break;
                    }
                }
            }
            $step->is_completed = $isCompleted;
            if ($isCompleted) {
                $stepsCompleted++;
            } else {
                $allStepsCompleted = false;
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


        // 2. Pindahkan field extra_services ke Step 1 dan ubah labelnya menjadi "Layanan Non-Formal"
        \Illuminate\Support\Facades\DB::table('spmb_form_fields')
            ->where('field_name', 'extra_services')
            ->update([
                'form_step_id' => 1, 
                'label' => 'Layanan Non-Formal',
                'order' => 5
            ]);

        // 3. Hapus field "Tingkat Pendaftaran" (admission_level) dari form wizard karena sudah diisi otomatis di awal
        \Illuminate\Support\Facades\DB::table('spmb_form_fields')
            ->where('field_name', 'admission_level')
            ->delete();

        // 4. Backfill data admission_level untuk pendaftaran lama yang masih kosong
        \App\Models\Registration::where(function($q) {
            $q->whereNull('admission_level')->orWhere('admission_level', '');
        })->chunkById(100, function($registrations) {
            foreach ($registrations as $reg) {
                if ($reg->grade) {
                    $reg->update([
                        'admission_level' => $reg->grade->name === 'KB' ? 'Play Group' : $reg->grade->name
                    ]);
                }
            }
        });

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
            $feeDetails = $registration->final_fee_snapshot ?? $this->getFinalFeeDetails($registration);

            // Filter out already paid items
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

            // Exclude already paid items from original feeDetails items list
            $unpaidItems = [];
            foreach ($feeDetails['items'] as &$item) {
                if (!in_array($item['name'], $paidItemNames)) {
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
                $feeDetails['items'] = $unpaidItems;

                // Apply manual checked items filter if passed in query string
                $selectedIndices = request()->query('items');
                if ($selectedIndices !== null && $selectedIndices !== '') {
                    $indices = explode(',', $selectedIndices);
                    $filteredItems = [];
                    foreach ($indices as $index) {
                        if (isset($unpaidItems[$index])) {
                            $filteredItems[] = $unpaidItems[$index];
                        }
                    }
                    if (!empty($filteredItems)) {
                        $feeDetails['items'] = $filteredItems;
                    }
                }

                // Calculate final total based on unpaid/filtered items
                $feeDetails['total'] = array_sum(array_map(function($item) {
                    return $item['amount'];
                }, $feeDetails['items']));

                $feeAmount = ($activePayment && $activePayment->status === 'pending') ? $activePayment->amount : $feeDetails['total'];
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
        }

        $channels = SpmbPaymentChannel::where('is_active', true)
            ->whereHas('gateway', function($q) use ($feeGateways) {
                $q->whereIn('code', $feeGateways);
            })
            ->orderBy('type')
            ->orderBy('name')
            ->get();
        $feeGateway = reset($feeGateways) ?: 'winpay';

        return view('web.payment', compact('registration', 'activePayment', 'channels', 'feeAmount', 'feeGateway', 'feeDetails', 'feeName'));
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
                'title' => 'Sesi Ta\'aruf Tatap Muka',
                'location' => $registration->unit?->name ?? 'Sekolah Anak Saleh',
                'notes' => 'Undangan resmi kehadiran offline akan dikirimkan panitia melalui WhatsApp ke nomor ' . $registration->parent_phone
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
        
        $feeDetails = $registration->final_fee_snapshot ?? $this->getFinalFeeDetails($registration);
        
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
        
        // Calculate paid item names from successful payments
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
        
        return view('web.result', compact('registration', 'feeDetails', 'paidItemNames'));
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
            'candidate_name', 'nickname', 'nik', 'gender', 'birth_place', 
            'birth_date', 'religion', 'previous_school', 'admission_level', 'father_name', 
            'mother_name', 'parent_phone', 'birth_certificate_path', 'family_card_path',
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

        if ($allCompleted && $registration->registration_status === 'draft') {
            $registration->update([
                'registration_status' => 'submitted'
            ]);
            return redirect()->route('dashboard.detail', $id)->with('success', 'Pendaftaran Anda berhasil dikirim! Silakan selesaikan pembayaran seleksi.');
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
        $status = $registration->registration_status;

        // Determine payment type
        if ($status === 'agreement_signed') {
            $paymentType = 'final_fee';
            $feeDetails = $registration->final_fee_snapshot ?? $this->getFinalFeeDetails($registration);

            // Filter out already paid items
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

            // Exclude already paid items from original feeDetails items list
            $unpaidItems = [];
            foreach ($feeDetails['items'] as &$item) {
                if (!in_array($item['name'], $paidItemNames)) {
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
            $feeDetails['items'] = $unpaidItems;

            // Apply manual checked items filter if passed in query string or POST input
            $selectedIndices = $request->input('items') ?? request()->query('items');
            if ($selectedIndices !== null && $selectedIndices !== '') {
                $indices = explode(',', $selectedIndices);
                $filteredItems = [];
                foreach ($indices as $index) {
                    if (isset($unpaidItems[$index])) {
                        $filteredItems[] = $unpaidItems[$index];
                    }
                }
                if (!empty($filteredItems)) {
                    $feeDetails['items'] = $filteredItems;
                }
            }

            // Calculate final total based on unpaid/filtered items
            $feeDetails['total'] = array_sum(array_map(function($item) {
                return $item['amount'];
            }, $feeDetails['items']));

            $amount = $feeDetails['total'];
            
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
            $gateways = !empty($commonGateways) ? array_values($commonGateways) : ['winpay'];
        } elseif ($status === 'draft') {
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
        $invoiceBase = 'INV-SPMB-' . date('Ymd') . '-' . $registration->id . '-' . rand(100, 999);

        try {
            $studentName = $registration->student_name ?? $registration->name ?? null;
            $studentPhone = $registration->parent_phone ?? $registration->phone ?? null;
            $gatewayService = \App\Services\PaymentGatewayFactory::make($gateway);
            $response = $gatewayService->createPayment($totalAmount, $invoiceBase, $request->payment_method, $studentName, $studentPhone);
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        if (!$response['success']) {
            return redirect()->back()->with('error', 'Failed to initiate payment: ' . $response['message']);
        }

        $paymentData = $response['data'];
        $invoiceNo = $paymentData['trxId'] ?? $paymentData['partnerReferenceNo'] ?? null;
        $refId = $paymentData['referenceId'] ?? null;

        DB::beginTransaction();
        try {
            Payment::create([
                'registration_id' => $registration->id,
                'invoice_number' => $invoiceNo ?? 'INV-' . time(),
                'amount' => $totalAmount,
                'base_amount' => $amount,
                'admin_fee' => $adminFee,
                'payment_method' => $request->payment_method,
                'reference_id' => $refId,
                'payment_info' => array_merge(is_array($paymentData) ? $paymentData : [], $paymentType === 'final_fee' ? ['selected_items' => $feeDetails['items']] : []),
                'status' => 'pending',
                'payment_type' => $paymentType
            ]);

            $registration->update([
                'payment_status' => 'pending'
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Invoice pembayaran berhasil diterbitkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan data pembayaran: ' . $e->getMessage());
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
            // URL Callback Lokal/Eksternal
            $callbackUrl = url('/api/payments/callback');
            
            // Format Payload asli SNAP BI
            $payload = [
                'trxId' => $payment->invoice_number,
                'paymentStatus' => 'SUCCESS',
                'paymentAmount' => [
                    'value' => number_format($payment->amount, 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'additionalInfo' => [
                    'invoiceNumber' => $payment->invoice_number
                ]
            ];

            // Kirim request HTTP POST riil ke API callback kita
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-TIMESTAMP' => date('c'),
                'X-SIGNATURE' => 'SIMULATED_SIGNATURE',
                'X-Developer-Simulator' => 'true'
            ])->post($callbackUrl, $payload);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'Callback Berhasil! Response: ' . $response->body());
            } else {
                Log::error('Simulate callback failed', ['status' => $response->status(), 'body' => $response->body()]);
                return redirect()->back()->with('error', 'Gagal memicu callback: (HTTP ' . $response->status() . ') ' . $response->body());
            }

        } catch (\Exception $e) {
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

        DB::beginTransaction();
        try {
            $payment->update([
                'status' => 'expired'
            ]);

            $registration = Registration::find($payment->registration_id);
            $registration->update([
                'payment_status' => 'unpaid'
            ]);

            // Preserve selected items index query parameters upon redirection
            $itemsQuery = '';
            if (isset($payment->payment_info['selected_items']) && is_array($payment->payment_info['selected_items'])) {
                $selectedNames = array_column($payment->payment_info['selected_items'], 'name');
                $feeDetails = $registration->final_fee_snapshot ?? $this->getFinalFeeDetails($registration);
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('web.payment-receipt-pdf', compact('payment', 'registration'));
        
        $response = response()->make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Bukti-Bayar-SPMB-' . $payment->invoice_number . '.pdf"',
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
