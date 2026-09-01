<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Registration;
use App\Models\SpmbClassProgram;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    private function getRegistration(Request $request)
    {
        return Registration::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'registration_status' => 'draft',
                'payment_status' => 'unpaid'
            ]
        );
    }

    private function getRegistrationFee($registration)
    {
        return app(\App\Http\Controllers\Web\WebDashboardController::class)->getRegistrationFee($registration);
    }

    private function getFinalFeeDetails($registration)
    {
        return app(\App\Http\Controllers\Web\WebDashboardController::class)->getFinalFeeDetails($registration);
    }

    public function show(Request $request)
    {
        $registration = $this->getRegistration($request);
        return response()->json([
            'registration' => $registration
        ]);
    }

    public function updateCandidateInfo(Request $request)
    {
        $registration = $this->getRegistration($request);
        $program = SpmbClassProgram::where('name', $request->class_program)->first();

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

        // Dynamically resolve Unit and Grade based on admission_level
        $unit = $registration->unit;
        $level = $request->admission_level;
        $gradeName = $level === 'Play Group' ? 'KB' : $level;
        
        $grade = \App\Models\SpmbGrade::where('spmb_unit_id', $unit?->id)
            ->where(function($q) use ($gradeName) {
                $q->where('name', $gradeName)
                  ->orWhere('name', 'KB Saja'); // for old test data compatibility
            })
            ->first();

        $registration->update(array_merge(
            $request->only([
                'candidate_name', 'nickname', 'nik', 'gender',
                'birth_place', 'birth_date', 'religion',
                'previous_school', 'admission_level'
            ]),
            [
                'spmb_class_program_id' => $program ? $program->id : null,
                'spmb_unit_id' => $unit ? $unit->id : null,
                'spmb_grade_id' => $grade ? $grade->id : null,
            ]
        ));

        return response()->json([
            'message' => 'Candidate information updated successfully',
            'registration' => $registration
        ]);
    }

    public function updateParentInfo(Request $request)
    {
        $request->validate([
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'parent_phone' => 'required|string|min:10|max:15',
        ]);

        $registration = $this->getRegistration($request);
        $registration->update($request->only([
            'father_name', 'mother_name', 'parent_phone'
        ]));

        return response()->json([
            'message' => 'Parent information updated successfully',
            'registration' => $registration
        ]);
    }

    public function uploadDocuments(Request $request)
    {
        $request->validate([
            'birth_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'family_card' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $registration = $this->getRegistration($request);

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

        return response()->json([
            'message' => 'Documents uploaded and registration submitted successfully',
            'registration' => $registration
        ]);
    }

    public function dashboard(Request $request)
    {
        $registration = $this->getRegistration($request);
        $activePayment = $registration->activePayment;
        $status = $registration->registration_status;
        $formPaid = $registration->payments()->where('payment_type', 'registration_fee')->where('status', 'success')->exists();

        $feeDb = $this->getRegistrationFee($registration);
        $feeAmount = $feeDb ? $feeDb->amount : 350000;
        
        $finalFees = $this->getFinalFeeDetails($registration);

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
                'title' => 'Sesi Ta\'aruf Tatap Muka',
                'location' => $registration->unit?->name ?? 'Sekolah Anak Saleh',
                'notes' => 'Undangan resmi kehadiran offline akan dikirimkan panitia melalui WhatsApp ke nomor ' . $registration->parent_phone
            ];
        }

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
                    $committeeMessage = 'Selamat datang! Silakan lakukan pembayaran biaya pendaftaran formulir sebesar Rp ' . number_format($feeAmount, 0, ',', '.') . ' untuk membuka formulir.';
                } else {
                    $committeeMessage = 'Pembayaran formulir terkonfirmasi. Silakan lengkapi formulir pendaftaran Anda pada menu di atas.';
                }
            } elseif ($status === 'submitted') {
                $committeeMessage = 'Formulir pendaftaran berhasil dikirim. Berkas pendaftaran ananda sedang dalam proses verifikasi oleh panitia SPMB.';
            } elseif ($status === 'verified') {
                $committeeMessage = 'Alhamdulillah, berkas pendaftaran ananda ' . ($registration->candidate_name ?? 'Ananda') . ' telah kami terima dan diverifikasi. Silakan persiapkan untuk mengikuti sesi Ta\'aruf tatap muka di unit sekolah.';
            } elseif ($status === 'taaruf_completed') {
                $committeeMessage = 'Sesi Ta\'aruf offline selesai dilakukan. Silakan mengisi dan menyetujui Formulir Pernyataan Kesanggupan untuk memproses biaya administrasi akhir.';
            } elseif ($status === 'agreement_signed') {
                $committeeMessage = 'Pernyataan kesanggupan disetujui. Silakan selesaikan pembayaran biaya administrasi akhir sebesar Rp ' . number_format($finalFees['total'], 0, ',', '.') . ' untuk menyelesaikan pendaftaran.';
            } elseif ($status === 'completed') {
                $committeeMessage = 'Selamat! Pendaftaran ananda ' . ($registration->candidate_name ?? 'Ananda') . ' dinyatakan selesai dan resmi diterima di Sekolah Anak Saleh. Selamat bergabung!';
            } elseif ($status === 'failed') {
                $committeeMessage = 'Mohon maaf, berkas pendaftaran Anda tidak lolos verifikasi. Silakan hubungi admin panitia.';
            }
        }

        return response()->json([
            'candidate_name' => $registration->candidate_name,
            'registration_id' => $registration->id,
            'registration_status' => $registration->registration_status,
            'payment_status' => $registration->payment_status,
            'committee_message' => $committeeMessage,
            'timeline' => $timeline,
            'active_payment' => $activePayment ? [
                'invoice_number' => $activePayment->invoice_number,
                'amount' => $activePayment->amount,
                'payment_method' => $activePayment->payment_method,
                'status' => $activePayment->status,
                'payment_info' => $activePayment->payment_info,
            ] : null,
            'observation_details' => $observationDetails
        ]);
    }
}
