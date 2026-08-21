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

    public function index()
    {
        $registrations = Registration::with(['unit', 'grade'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
            
        $units = SpmbUnit::where('is_active', true)->get();
        $grades = SpmbGrade::where('is_active', true)->get();

        return view('web.dashboard-index', compact('registrations', 'units', 'grades'));
    }
    
    public function createRegistration(Request $request)
    {
        $request->validate([
            'candidate_name' => 'required|string|max:255',
            'spmb_unit_id' => 'required|exists:spmb_units,id',
            'spmb_grade_id' => 'required|exists:spmb_grades,id',
        ]);
        
        $registration = Registration::create([
            'user_id' => auth()->id(),
            'candidate_name' => $request->candidate_name,
            'spmb_unit_id' => $request->spmb_unit_id,
            'spmb_grade_id' => $request->spmb_grade_id,
            'registration_status' => 'draft',
            'payment_status' => 'unpaid'
        ]);
        
        return redirect()->route('dashboard.detail', $registration->id)->with('success', 'Berhasil menambahkan pendaftaran anak baru. Silakan lengkapi formulir.');
    }

    private function getFormDetails($registration)
    {
        $steps = SpmbFormStep::with('fields')->orderBy('order')->get();
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
        $activePayment = $registration->activePayment;

        $formDetails = $this->getFormDetails($registration);
        $allStepsCompleted = $formDetails['allStepsCompleted'];
        $stepsCompleted = $formDetails['stepsCompleted'];
        $stepsCount = $formDetails['stepsCount'];

        // Build dashboard timeline steps
        $timeline = [
            'registration' => [
                'label' => 'Form & Documents',
                'description' => 'Mengisi data diri, data orang tua, dan mengunggah berkas.',
                'status' => $allStepsCompleted ? 'completed' : 'in_progress',
            ],
            'payment' => [
                'label' => 'Payment Fee (Winpay)',
                'description' => 'Membayar biaya seleksi pendaftaran Rp 350.000.',
                'status' => 'not_started',
            ],
            'verification' => [
                'label' => 'Verification Process',
                'description' => 'Pemeriksaan berkas pendaftaran oleh panitia.',
                'status' => 'not_started',
            ],
            'observation' => [
                'label' => 'Observation Test',
                'description' => 'Tes kesiapan belajar calon siswa secara daring.',
                'status' => 'not_started',
            ],
            'announcement' => [
                'label' => 'Final Results',
                'description' => 'Pengumuman kelulusan akhir & daftar ulang.',
                'status' => 'not_started',
            ],
        ];

        if ($registration->registration_status !== 'draft') {
            if ($registration->payment_status === 'paid') {
                $timeline['payment']['status'] = 'completed';
            } elseif ($registration->payment_status === 'pending' && $activePayment) {
                $timeline['payment']['status'] = 'in_progress';
            } else {
                $timeline['payment']['status'] = 'in_progress';
            }
        }

        if ($registration->payment_status === 'paid') {
            if ($registration->registration_status === 'verified') {
                $timeline['verification']['status'] = 'completed';
            } elseif ($registration->registration_status === 'failed') {
                $timeline['verification']['status'] = 'failed';
            } else {
                $timeline['verification']['status'] = 'in_progress';
            }
        }

        $observationDetails = null;
        if ($registration->registration_status === 'verified') {
            $timeline['observation']['status'] = 'in_progress';
            $observationDetails = [
                'title' => 'Tes Observasi secara daring',
                'datetime' => 'Sabtu, 26 Okt 2024. 08:00 - 10:00 WIB',
                'zoom_link' => 'https://zoom.us/j/9876543210',
                'guide_link' => 'https://sekolah-anak-saleh.sch.id/panduan-observasi.pdf'
            ];
        }

        $committeeMessage = $registration->committee_notes;
        if (empty($committeeMessage)) {
            $committeeMessage = 'Silakan lengkapi formulir pendaftaran Anda terlebih dahulu.';
            if ($registration->registration_status === 'submitted') {
                if ($registration->payment_status !== 'paid') {
                    $committeeMessage = 'Formulir Anda telah disimpan. Silakan lakukan pembayaran pendaftaran sebesar Rp 350.000 untuk memulai proses verifikasi berkas.';
                } else {
                    $committeeMessage = 'Pembayaran terkonfirmasi. Berkas Anda sedang diperiksa oleh Panitia SPMB. Mohon tunggu proses verifikasi selesai.';
                }
            } elseif ($registration->registration_status === 'verified') {
                $committeeMessage = 'Alhamdulillah, berkas ananda ' . ($registration->candidate_name ?? 'Ananda') . ' telah kami terima dan diverifikasi. Silakan persiapkan ananda untuk mengikuti Tes Observasi secara daring. Mohon cek detail jadwal dan tautan di bawah ini.';
            } elseif ($registration->registration_status === 'failed') {
                $committeeMessage = 'Mohon maaf, berkas pendaftaran Anda tidak lolos verifikasi. Silakan hubungi admin panitia.';
            }
        }

        return view('web.dashboard', compact('registration', 'activePayment', 'timeline', 'committeeMessage', 'observationDetails', 'stepsCompleted', 'stepsCount'));
    }

    public function form($id)
    {
        $registration = $this->getRegistration($id);
        $formDetails = $this->getFormDetails($registration);
        $steps = $formDetails['steps'];
        $allStepsCompleted = $formDetails['allStepsCompleted'];
        
        return view('web.form', compact('registration', 'steps', 'allStepsCompleted'));
    }

    public function submitForm($id)
    {
        $registration = $this->getRegistration($id);
        $formDetails = $this->getFormDetails($registration);
        
        if (!$formDetails['allStepsCompleted']) {
            return redirect()->back()->with('error', 'Silakan lengkapi seluruh tahapan formulir terlebih dahulu.');
        }

        if ($registration->registration_status === 'draft') {
            $registration->update([
                'registration_status' => 'submitted'
            ]);
            return redirect()->route('dashboard.payment', $id)->with('success', 'Formulir pendaftaran berhasil dikirim! Silakan selesaikan pembayaran biaya seleksi.');
        }

        return redirect()->route('dashboard.payment', $id);
    }

    public function payment($id)
    {
        $registration = $this->getRegistration($id);
        $activePayment = $registration->activePayment;
        $channels = SpmbPaymentChannel::where('is_active', true)->orderBy('type')->orderBy('name')->get();
        
        return view('web.payment', compact('registration', 'activePayment', 'channels'));
    }

    public function verification($id)
    {
        $registration = $this->getRegistration($id);
        
        $committeeMessage = $registration->committee_notes;
        if (empty($committeeMessage)) {
            if ($registration->registration_status === 'draft') {
                $committeeMessage = 'Formulir belum dikirim. Silakan lengkapi formulir pendaftaran terlebih dahulu.';
            } elseif ($registration->payment_status !== 'paid') {
                $committeeMessage = 'Menunggu pelunasan pembayaran pendaftaran untuk memulai proses verifikasi berkas.';
            } else {
                $committeeMessage = 'Berkas Anda sedang diperiksa oleh Panitia SPMB. Mohon tunggu proses verifikasi selesai.';
            }
        }
        
        return view('web.verification', compact('registration', 'committeeMessage'));
    }

    public function observation($id)
    {
        $registration = $this->getRegistration($id);
        
        $observationDetails = null;
        if ($registration->registration_status === 'verified') {
            $observationDetails = [
                'title' => 'Tes Observasi secara daring',
                'datetime' => 'Sabtu, 26 Okt 2024. 08:00 - 10:00 WIB',
                'zoom_link' => 'https://zoom.us/j/9876543210',
                'guide_link' => 'https://sekolah-anak-saleh.sch.id/panduan-observasi.pdf'
            ];
        }
        
        return view('web.observation', compact('registration', 'observationDetails'));
    }

    public function result($id)
    {
        $registration = $this->getRegistration($id);
        
        return view('web.result', compact('registration'));
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
            'mother_name', 'parent_phone', 'birth_certificate_path', 'family_card_path'
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
                if (in_array($fieldName, $physicalColumns)) {
                    $registration->{$fieldName} = $val;
                } else {
                    $additionalInfo[$fieldName] = $val;
                }
            }
        }

        $registration->additional_info = $additionalInfo;
        $registration->save();

        // 3. Check if all steps are completed. If yes, transition status to 'submitted'!
        $allSteps = SpmbFormStep::with('fields')->orderBy('order')->get();
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
        $request->validate([
            'candidate_name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'nik' => 'required|string|digits:16',
            'gender' => 'required|string|in:male,female',
            'birth_place' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'religion' => 'required|string|max:100',
            'previous_school' => 'nullable|string|max:255',
            'admission_level' => 'required|string|in:Play Group,TK A,TK B',
        ]);

        $registration = $this->getRegistration($id);
        $registration->update($request->only([
            'candidate_name', 'nickname', 'nik', 'gender',
            'birth_place', 'birth_date', 'religion',
            'previous_school', 'admission_level'
        ]));

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
            'payment_method' => 'required|string|in:MANDIRI,BRI,BNI,BCA,QRIS',
        ]);

        $registration = $this->getRegistration($id);

        if ($registration->registration_status === 'draft') {
            return redirect()->back()->with('error', 'Please complete steps 1-3 first.');
        }

        // Call Winpay Service to charge
        $response = $this->winpayService->createPayment(350000, 'INV-SPMB-' . date('Ymd') . '-' . $registration->id . '-' . rand(100, 999), $request->payment_method);

        if (!$response['success']) {
            return redirect()->back()->with('error', 'Failed to initiate Winpay payment: ' . $response['message']);
        }

        $paymentData = $response['data'];
        $invoiceNo = $paymentData['trxId'] ?? $paymentData['partnerReferenceNo'] ?? null;
        $refId = $paymentData['referenceId'] ?? null;

        DB::beginTransaction();
        try {
            Payment::create([
                'registration_id' => $registration->id,
                'invoice_number' => $invoiceNo ?? 'INV-' . time(),
                'amount' => 350000,
                'payment_method' => $request->payment_method,
                'reference_id' => $refId,
                'payment_info' => $paymentData,
                'status' => 'pending'
            ]);

            $registration->update([
                'payment_status' => 'pending'
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Payment invoice generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save payment info: ' . $e->getMessage());
        }
    }

    public function simulatePaymentCallback($id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return redirect()->back()->with('error', 'Payment transaction not found.');
        }

        DB::beginTransaction();
        try {
            $payment->update([
                'status' => 'success'
            ]);

            $registration = Registration::find($payment->registration_id);
            $registration->update([
                'payment_status' => 'paid'
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Simulation: Payment received! Status updated to PAID.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to simulate callback: ' . $e->getMessage());
        }
    }

    public function cancelPayment($id)
    {
        $payment = Payment::findOrFail($id);
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

            DB::commit();
            return redirect()->route('dashboard.payment', $regId)->with('success', 'Transaksi pembayaran berhasil dibatalkan. Silakan pilih kembali metode pembayaran Anda.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }
}
