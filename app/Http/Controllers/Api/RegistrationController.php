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
        $feeCategory = \App\Models\SpmbFeeCategory::where('name', 'Formulir Pendaftaran')->first();
        if ($feeCategory) {
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

    private function getFinalFeeDetails($registration)
    {
        $unitId = $registration->spmb_unit_id;
        $unitName = $registration->unit->name ?? '';
        $gradeName = $registration->grade->name ?? '';

        $category = \App\Models\SpmbFeeCategory::where('name', 'Biaya Adminstrasi')
            ->orWhere('name', 'Biaya Administrasi')
            ->first();

        $fees = null;
        if ($category && $unitId) {
            $fees = \App\Models\SpmbFee::where('spmb_fee_category_id', $category->id)
                ->where('spmb_unit_id', $unitId)
                ->where('is_active', true)
                ->get();
        }

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
                    'amount' => $fee->amount
                ];
            }

            $details['total'] = array_sum(array_map(function($item) {
                return $item['amount'];
            }, $details['items']));

            return $details;
        }

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

        $details['items'] = [
            ['name' => 'Uang Gedung', 'amount' => $details['uang_gedung']],
            ['name' => 'Biaya Seragam', 'amount' => $details['seragam']],
            ['name' => 'SPP Bulanan', 'amount' => $details['spp']],
            ['name' => 'Uang Kegiatan', 'amount' => $details['kegiatan']],
        ];

        $details['total'] = $details['uang_gedung'] + $details['seragam'] + $details['spp'] + $details['kegiatan'];
        return $details;
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
