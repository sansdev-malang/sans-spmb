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

    public function show(Request $request)
    {
        $registration = $this->getRegistration($request);
        return response()->json([
            'registration' => $registration
        ]);
    }

    public function updateCandidateInfo(Request $request)
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
            'class_program' => 'required|string',
        ]);

        $registration = $this->getRegistration($request);
        $program = SpmbClassProgram::where('name', $request->class_program)->first();
        $registration->update(array_merge(
            $request->only([
                'candidate_name', 'nickname', 'nik', 'gender',
                'birth_place', 'birth_date', 'religion',
                'previous_school', 'admission_level'
            ]),
            ['spmb_class_program_id' => $program ? $program->id : null]
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

        // Save files to public disk (easy access for developer testing)
        if ($request->hasFile('birth_certificate')) {
            $birthCertPath = $request->file('birth_certificate')->store('documents', 'public');
            $registration->birth_certificate_path = $birthCertPath;
        }

        if ($request->hasFile('family_card')) {
            $familyCardPath = $request->file('family_card')->store('documents', 'public');
            $registration->family_card_path = $familyCardPath;
        }

        // Auto transition registration status to submitted since step 3 is completed
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

        // Build dashboard timeline & details according to Google Stitch layout guidelines
        $timeline = [
            'registration' => [
                'label' => 'Pendaftaran & Pengisian Berkas',
                'description' => 'Mengisi formulir data diri, orang tua, dan mengunggah berkas.',
                'status' => $registration->registration_status === 'draft' ? 'in_progress' : 'completed',
            ],
            'payment' => [
                'label' => 'Biaya Seleksi (Pembayaran)',
                'description' => 'Melakukan pembayaran formulir pendaftaran sebesar Rp 350.000.',
                'status' => 'not_started',
            ],
            'verification' => [
                'label' => 'Verifikasi Berkas',
                'description' => 'Pemeriksaan berkas pendaftaran oleh panitia SPMB.',
                'status' => 'not_started',
            ],
            'observation' => [
                'label' => 'Tes Observasi',
                'description' => 'Mengikuti tes kesiapan belajar secara daring.',
                'status' => 'not_started',
            ],
            'announcement' => [
                'label' => 'Pengumuman Hasil',
                'description' => 'Pengumuman kelulusan akhir dan daftar ulang.',
                'status' => 'not_started',
            ],
        ];

        // 1. Payment status evaluation
        if ($registration->registration_status !== 'draft') {
            if ($registration->payment_status === 'paid') {
                $timeline['payment']['status'] = 'completed';
            } elseif ($registration->payment_status === 'pending' && $activePayment) {
                $timeline['payment']['status'] = 'in_progress';
            } else {
                $timeline['payment']['status'] = 'in_progress'; // waiting for charge initiation
            }
        }

        // 2. Verification status evaluation
        if ($registration->payment_status === 'paid') {
            if ($registration->registration_status === 'verified') {
                $timeline['verification']['status'] = 'completed';
            } elseif ($registration->registration_status === 'failed') {
                $timeline['verification']['status'] = 'failed';
            } else {
                $timeline['verification']['status'] = 'in_progress';
            }
        }

        // 3. Observation status evaluation
        $observationDetails = null;
        if ($registration->registration_status === 'verified') {
            $timeline['observation']['status'] = 'in_progress';
            
            // Provide Mock/Dynamic schedule data for Observation test as shown in Stitch Dashboard Layout
            $observationDetails = [
                'title' => 'Tes Observasi secara daring',
                'datetime' => 'Sabtu, 26 Okt 2024. 08:00 - 10:00 WIB',
                'zoom_link' => 'https://zoom.us/j/9876543210',
                'guide_link' => 'https://sekolah-anak-saleh.sch.id/panduan-observasi.pdf'
            ];
        }

        // 4. Announcement evaluation
        if ($registration->registration_status === 'verified' && $registration->payment_status === 'paid') {
            // Can be extended once final test results are added, currently waiting
            $timeline['announcement']['status'] = 'not_started'; 
        }

        // Panitia message based on active step or custom notes from admin
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
                $committeeMessage = 'Alhamdulillah, berkas ananda ' . ($registration->candidate_name ?? 'Ahmad Raihan') . ' telah kami terima dan diverifikasi. Silakan persiapkan ananda untuk mengikuti Tes Observasi secara daring. Mohon cek detail jadwal dan tautan di bawah ini.';
            } elseif ($registration->registration_status === 'failed') {
                $committeeMessage = 'Mohon maaf, berkas pendaftaran Anda tidak lolos verifikasi. Silakan hubungi admin panitia SPMB.';
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
