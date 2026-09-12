<?php

namespace App\Services;

use App\Models\ApiClient;
use App\Models\ApiIntegrationLog;
use App\Models\Registration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApiIntegrationService
{
    /**
     * Serialisasi data pendaftar dinamis sesuai checklist izin data (allowed_fields) milik client
     *
     * @param Registration $reg
     * @param ApiClient $client
     * @return array
     */
    public function serializeCandidate(Registration $reg, ApiClient $client): array
    {
        $reg->loadMissing(['user', 'unit', 'period', 'wave', 'classProgram', 'payments']);

        $candidateInfo = is_array($reg->candidate_info) ? $reg->candidate_info : (json_decode($reg->candidate_info ?: '[]', true) ?: []);
        $parentInfo = is_array($reg->parent_info) ? $reg->parent_info : (json_decode($reg->parent_info ?: '[]', true) ?: []);
        $schoolOrigin = is_array($reg->school_origin) ? $reg->school_origin : (json_decode($reg->school_origin ?: '[]', true) ?: []);
        $documents = is_array($reg->documents) ? $reg->documents : (json_decode($reg->documents ?: '[]', true) ?: []);

        // Base Envelope (Identitas Pendaftaran)
        $regNumber = $reg->registration_no ?: ($reg->id_label ?: 'SPMB-' . str_pad($reg->id, 5, '0', STR_PAD_LEFT));
        $periodName = $reg->period->name ?? ($reg->period->year ?? (date('Y') . '/' . (date('Y') + 1)));

        $data = [
            'id' => $reg->id,
            'registration_number' => $regNumber,
            'registration_no' => $regNumber,
            'unit' => [
                'code' => $reg->unit->code ?? null,
                'name' => $reg->unit->name ?? null,
            ],
            'period' => $periodName,
            'wave' => $reg->wave->name ?? null,
            'class_program' => $reg->classProgram->name ?? null,
            'registration_status' => $reg->registration_status,
            'payment_status' => $reg->payment_status,
            'verified_at' => $reg->verified_at ? $reg->verified_at->toIso8601String() : null,
            'created_at' => $reg->created_at->toIso8601String(),
            'updated_at' => $reg->updated_at->toIso8601String(),
        ];

        // 1. Kelompok Data: Biodata Calon Siswa (bio)
        if ($client->canAccessField('bio')) {
            $data['student_bio'] = [
                'full_name' => $reg->candidate_name ?? ($candidateInfo['full_name'] ?? ($reg->user->name ?? '')),
                'nickname' => $reg->nickname ?? ($candidateInfo['nickname'] ?? ''),
                'nisn' => $reg->nisn ?? ($candidateInfo['nisn'] ?? ''),
                'nik' => $reg->nik ?? ($candidateInfo['nik'] ?? ''),
                'gender' => $reg->gender ?? ($candidateInfo['gender'] ?? ($candidateInfo['jenis_kelamin'] ?? '')),
                'birth_place' => $reg->birth_place ?? ($candidateInfo['birth_place'] ?? ($candidateInfo['tempat_lahir'] ?? '')),
                'birth_date' => $reg->birth_date ? (is_string($reg->birth_date) ? $reg->birth_date : $reg->birth_date->format('Y-m-d')) : ($candidateInfo['birth_date'] ?? ($candidateInfo['tanggal_lahir'] ?? '')),
                'religion' => $reg->religion ?? ($candidateInfo['religion'] ?? ($candidateInfo['agama'] ?? 'Islam')),
                'photo_url' => $reg->student_photo_path ? (str_starts_with($reg->student_photo_path, 'http') ? $reg->student_photo_path : url(asset('storage/' . ltrim($reg->student_photo_path, '/')))) : null,
                'address' => [
                    'street' => $reg->address ?? ($candidateInfo['address'] ?? ($candidateInfo['alamat'] ?? '')),
                    'house_number' => $reg->house_number ?? ($candidateInfo['house_number'] ?? ''),
                    'rt' => $reg->rt ?? ($candidateInfo['rt'] ?? ''),
                    'rw' => $reg->rw ?? ($candidateInfo['rw'] ?? ''),
                    'village' => $reg->kelurahan ?? ($candidateInfo['kelurahan'] ?? ($candidateInfo['desa'] ?? '')),
                    'district' => $reg->kecamatan ?? ($candidateInfo['kecamatan'] ?? ''),
                    'city' => $reg->city ?? ($candidateInfo['city'] ?? ($candidateInfo['kota'] ?? '')),
                    'province' => $reg->province ?? ($candidateInfo['province'] ?? ($candidateInfo['provinsi'] ?? '')),
                    'full_address' => $reg->getFullCandidateAddress(),
                ]
            ];
        }

        // 2. Kelompok Data: Data Orang Tua / Wali (parents)
        if ($client->canAccessField('parents')) {
            $data['parent_info'] = [
                'father' => [
                    'name' => $reg->father_name ?? ($parentInfo['father_name'] ?? ($parentInfo['nama_ayah'] ?? '')),
                    'nik' => $reg->father_nik ?? ($parentInfo['father_nik'] ?? ($parentInfo['nik_ayah'] ?? '')),
                    'phone' => $reg->father_phone ?? ($parentInfo['father_phone'] ?? ($parentInfo['no_hp_ayah'] ?? '')),
                    'job' => $reg->father_job ?? ($parentInfo['father_job'] ?? ($parentInfo['pekerjaan_ayah'] ?? '')),
                    'education' => $reg->father_education ?? ($parentInfo['father_education'] ?? ($parentInfo['pendidikan_ayah'] ?? '')),
                    'income' => $reg->father_income ?? ($parentInfo['father_income'] ?? ($parentInfo['penghasilan_ayah'] ?? '')),
                ],
                'mother' => [
                    'name' => $reg->mother_name ?? ($parentInfo['mother_name'] ?? ($parentInfo['nama_ibu'] ?? '')),
                    'nik' => $reg->mother_nik ?? ($parentInfo['mother_nik'] ?? ($parentInfo['nik_ibu'] ?? '')),
                    'phone' => $reg->mother_phone ?? ($parentInfo['mother_phone'] ?? ($parentInfo['no_hp_ibu'] ?? '')),
                    'job' => $reg->mother_job ?? ($parentInfo['mother_job'] ?? ($parentInfo['pekerjaan_ibu'] ?? '')),
                    'education' => $reg->mother_education ?? ($parentInfo['mother_education'] ?? ($parentInfo['pendidikan_ibu'] ?? '')),
                    'income' => $reg->mother_income ?? ($parentInfo['mother_income'] ?? ($parentInfo['penghasilan_ibu'] ?? '')),
                ],
                'guardian' => [
                    'name' => $reg->guardian_name ?? ($parentInfo['guardian_name'] ?? ($parentInfo['nama_wali'] ?? '')),
                    'phone' => $reg->guardian_phone ?? ($parentInfo['guardian_phone'] ?? ($parentInfo['no_hp_wali'] ?? '')),
                    'relation' => $reg->guardian_relation ?? ($parentInfo['guardian_relation'] ?? ($parentInfo['hubungan_wali'] ?? '')),
                ],
                'primary_contact' => [
                    'whatsapp' => $reg->parent_phone ?? ($parentInfo['primary_whatsapp'] ?? ($reg->user->phone ?? ($reg->father_phone ?? ($reg->mother_phone ?? '')))),
                    'email' => $reg->user->email ?? '',
                ]
            ];
        }

        // 3. Kelompok Data: Sekolah Asal & Prestasi (school_origin)
        if ($client->canAccessField('school_origin')) {
            $data['school_origin'] = [
                'previous_school' => $reg->previous_school ?? ($schoolOrigin['school_name'] ?? ($schoolOrigin['nama_sekolah'] ?? '')),
                'npsn' => $reg->npsn ?? ($schoolOrigin['npsn'] ?? ''),
                'school_address' => $reg->school_address ?? ($schoolOrigin['school_address'] ?? ''),
            ];
        }

        // 4. Kelompok Data: Berkas & Lampiran Dokumen (documents)
        if ($client->canAccessField('documents')) {
            $docList = [];
            $unitId = $reg->spmb_unit_id;

            // 1. Ambil form fields untuk dokumen / berkas yang relevan dengan unit atau global
            $docFormFields = \App\Models\SpmbFormField::where(function($q) {
                    $q->where('form_step_id', 6)->orWhere('type', 'file');
                })
                ->where(function($q) use ($unitId) {
                    if ($unitId) {
                        $q->whereDoesntHave('units')
                          ->orWhereHas('units', function($u) use ($unitId) {
                              $u->where('spmb_units.id', $unitId);
                          });
                    }
                })
                ->orderBy('order', 'asc')
                ->get();

            $processedKeys = [];

            foreach ($docFormFields as $field) {
                $path = $reg->getFieldValue($field->field_name);
                if (!empty($path)) {
                    $docList[] = [
                        'key' => $field->field_name,
                        'name' => $field->label,
                        'url' => str_starts_with($path, 'http') ? $path : url(asset('storage/' . ltrim($path, '/'))),
                    ];
                    $processedKeys[] = $field->field_name;
                }
            }

            // 2. Kolom fisik dokumen bawaan standar jika ada yang terisi dan belum masuk form fields
            $standardFileColumns = [
                'student_photo_path' => 'Pas Foto Calon Murid (Foto Formal)',
                'birth_certificate_path' => 'Akta Kelahiran',
                'family_card_path' => 'Kartu Keluarga (KK)',
                'diploma_certificate_path' => 'Ijazah / Surat Keterangan Aktif Sekolah',
                'student_card_path' => 'NISN / KIA / Kartu Pelajar (Opsional)',
                'special_needs_assessment_path' => 'Asesmen Kebutuhan Khusus (Jika Ada)',
                'payment_receipt_path' => 'Bukti Pembayaran Pendaftaran',
            ];

            foreach ($standardFileColumns as $col => $label) {
                if (!in_array($col, $processedKeys)) {
                    $path = $reg->getFieldValue($col);
                    if (!empty($path)) {
                        $docList[] = [
                            'key' => $col,
                            'name' => $label,
                            'url' => str_starts_with($path, 'http') ? $path : url(asset('storage/' . ltrim($path, '/'))),
                        ];
                        $processedKeys[] = $col;
                    }
                }
            }

            // 3. Tambahkan lampiran dinamis dari additional_info jika ada
            if (is_array($reg->additional_info)) {
                foreach ($reg->additional_info as $infoKey => $infoVal) {
                    if (!in_array($infoKey, $processedKeys) && is_string($infoVal) && !empty($infoVal)) {
                        $ext = strtolower(pathinfo($infoVal, PATHINFO_EXTENSION));
                        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'webp']) || str_starts_with($infoVal, 'documents/')) {
                            $customLabel = \App\Models\SpmbFormField::where('field_name', $infoKey)->first()?->label 
                                ?? ucwords(str_replace(['_', '-'], ' ', $infoKey));
                            $docList[] = [
                                'key' => $infoKey,
                                'name' => $customLabel,
                                'url' => str_starts_with($infoVal, 'http') ? $infoVal : url(asset('storage/' . ltrim($infoVal, '/'))),
                            ];
                        }
                    }
                }
            }

            $data['documents'] = $docList;
        }

        // 5. Kelompok Data: Riwayat Pembayaran (payments) - Default restricted
        if ($client->canAccessField('payments')) {
            $data['payments'] = $reg->payments->map(function ($p) {
                return [
                    'invoice_number' => $p->invoice_number,
                    'amount' => (float) $p->amount,
                    'status' => $p->status,
                    'payment_type' => $p->payment_type,
                    'payment_method' => $p->payment_info['channel'] ?? ($p->payment_info['bankName'] ?? 'WINPAY'),
                    'paid_at' => $p->paid_at ? $p->paid_at->toIso8601String() : null,
                ];
            });
        }

        // 6. Kelompok Data Tambahan / Dinamis (Custom Form Steps)
        try {
            $customSteps = \App\Models\SpmbFormStep::with('fields')->whereNotIn('id', [1, 2, 3, 4, 5, 6])->get();
            foreach ($customSteps as $cStep) {
                $cKey = 'step_' . $cStep->id;
                if ($client->canAccessField($cKey) || $client->canAccessField('*')) {
                    $stepFields = [];
                    foreach ($cStep->fields as $f) {
                        $stepFields[$f->field_name] = $reg->getFieldValue($f->field_name);
                    }
                    $data[\Illuminate\Support\Str::slug($cStep->title, '_')] = $stepFields;
                }
            }
        } catch (\Throwable $e) {
            // Ignore if DB error
        }

        return $data;
    }

    /**
     * Kirim Webhook ke seluruh API Client yang berlangganan event terkait
     *
     * @param string $event Event name (misal: 'candidate.verified', 'payment.success')
     * @param Registration $registration
     * @return void
     */
    public function dispatchWebhook(string $event, Registration $registration): void
    {
        $unitCode = $registration->unit->code ?? 'all';

        // Cari seluruh client aktif yang terdaftar ke unit dan event ini
        $clients = ApiClient::where('is_active', true)
            ->whereNotNull('webhook_url')
            ->get()
            ->filter(function ($client) use ($unitCode, $event) {
                return $client->canAccessUnit($unitCode) && $client->isSubscribedToEvent($event);
            });

        if ($clients->isEmpty()) {
            return;
        }

        foreach ($clients as $client) {
            $this->sendWebhookToClient($client, $event, $registration);
        }
    }

    /**
     * Eksekusi pengiriman HTTP POST Webhook ke satu Client
     */
    public function sendWebhookToClient(ApiClient $client, string $event, Registration $registration): array
    {
        $startTime = microtime(true);
        $deliveryId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $payload = [
            'event' => $event,
            'delivery_id' => $deliveryId,
            'timestamp' => $timestamp,
            'data' => $this->serializeCandidate($registration, $client),
        ];

        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', $payloadJson, $client->webhook_secret ?: 'spmb_secret');

        $statusCode = null;
        $responseBody = null;
        $errorMessage = null;

        try {
            $response = Http::timeout(10)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'SANS-SPMB-Webhook/1.0',
                'X-Spmb-Event' => $event,
                'X-Spmb-Delivery-Id' => $deliveryId,
                'X-Spmb-Timestamp' => $timestamp,
                'X-Spmb-Signature' => $signature,
            ])->withBody($payloadJson, 'application/json')->post($client->webhook_url);

            $statusCode = $response->status();
            $responseBody = $response->json() ?? ['raw' => substr($response->body(), 0, 500)];
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            Log::warning("Webhook delivery failed for client [{$client->name}] URL [{$client->webhook_url}]: " . $e->getMessage());
        }

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        // Catat ke log
        ApiIntegrationLog::create([
            'api_client_id' => $client->id,
            'client_name' => $client->name,
            'type' => 'outbound_webhook',
            'endpoint_or_url' => $client->webhook_url,
            'method' => 'POST',
            'status_code' => $statusCode,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'request_payload' => $payload,
            'response_payload' => $responseBody,
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs,
        ]);

        return [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'status_code' => $statusCode,
            'duration_ms' => $durationMs,
            'error' => $errorMessage,
        ];
    }

    /**
     * Kirim Test Ping Webhook untuk memverifikasi endpoint tujuan
     */
    public function testWebhookPing(ApiClient $client): array
    {
        if (empty($client->webhook_url)) {
            return [
                'success' => false,
                'message' => 'URL Webhook belum diatur pada client ini.',
            ];
        }

        $startTime = microtime(true);
        $deliveryId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $payload = [
            'event' => 'ping',
            'delivery_id' => $deliveryId,
            'timestamp' => $timestamp,
            'data' => [
                'message' => 'Test Ping Connection from SANS SPMB Pusat.',
                'client_name' => $client->name,
                'allowed_units' => $client->allowed_units,
                'allowed_fields' => $client->allowed_fields,
            ]
        ];

        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', $payloadJson, $client->webhook_secret ?: 'spmb_secret');

        $statusCode = null;
        $responseBody = null;
        $errorMessage = null;

        try {
            $response = Http::timeout(10)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'SANS-SPMB-Webhook/1.0',
                'X-Spmb-Event' => 'ping',
                'X-Spmb-Delivery-Id' => $deliveryId,
                'X-Spmb-Timestamp' => $timestamp,
                'X-Spmb-Signature' => $signature,
            ])->post($client->webhook_url, $payload);

            $statusCode = $response->status();
            $responseBody = $response->json() ?? ['raw' => substr($response->body(), 0, 500)];
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
        }

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        // Catat ke log
        ApiIntegrationLog::create([
            'api_client_id' => $client->id,
            'client_name' => $client->name,
            'type' => 'test_ping',
            'endpoint_or_url' => $client->webhook_url,
            'method' => 'POST',
            'status_code' => $statusCode,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'request_payload' => $payload,
            'response_payload' => $responseBody,
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs,
        ]);

        $isOk = $statusCode >= 200 && $statusCode < 300;

        return [
            'success' => $isOk,
            'status_code' => $statusCode,
            'duration_ms' => $durationMs,
            'message' => $isOk ? "Ping berhasil! Server tujuan merespon status HTTP {$statusCode}." : "Ping gagal atau server merespon status HTTP {$statusCode} (" . ($errorMessage ?: 'Non-200 Response') . ")",
            'response' => $responseBody,
        ];
    }
}
