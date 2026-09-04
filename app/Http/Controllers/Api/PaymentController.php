<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Registration;
use App\Models\Payment;
use App\Services\WinpayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    protected $winpayService;

    public function __construct(WinpayService $winpayService)
    {
        $this->winpayService = $winpayService;
    }

    private function getRegistrationFee($registration)
    {
        return app(\App\Http\Controllers\Web\WebDashboardController::class)->getRegistrationFee($registration);
    }

    private function getFinalFeeDetails($registration)
    {
        return app(\App\Http\Controllers\Web\WebDashboardController::class)->getFinalFeeDetails($registration);
    }

    /**
     * Inisiasi transaksi pembayaran (charge) dengan proteksi atomic lock dan invoice unik
     */
    public function charge(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'registration_id' => 'nullable|integer',
            'items' => 'nullable|array',
        ]);

        $user = $request->user();
        
        $registrationId = $request->input('registration_id');
        if ($registrationId) {
            $registration = Registration::where('id', $registrationId)->where('user_id', $user->id)->first();
        } else {
            $registration = Registration::where('user_id', $user->id)->latest()->first();
        }

        if (!$registration) {
            return response()->json([
                'message' => 'Registration record not found or unauthorized.'
            ], 404);
        }

        // Gunakan atomic lock per registrasi untuk mencegah race condition / double submit
        $lockKey = 'charge_lock_reg_' . $registration->id;
        $lock = Cache::lock($lockKey, 15);

        if (!$lock->get()) {
            return response()->json([
                'message' => 'Sedang memproses permintaan pembayaran sebelumnya. Silakan tunggu beberapa saat.'
            ], 429);
        }

        try {
            $status = $registration->registration_status;
            $processedItems = [];

            if ($status === 'agreement_signed') {
                $paymentType = 'final_fee';
                $feeDetails = $this->getFinalFeeDetails($registration);
                $allSnapshotItems = $feeDetails['items'] ?? [];

                $inputItems = $request->input('items');
                $isGlobalInstallment = ($registration->installment_mode === 'all');
                $totalCalculatedPrincipal = 0;

                if (!empty($inputItems) && is_array($inputItems)) {
                    $inputMap = [];
                    foreach ($inputItems as $it) {
                        $key = $it['fee_id'] ?? $it['id'] ?? $it['name'] ?? null;
                        if ($key !== null) {
                            $inputMap[(string)$key] = (float)($it['amount'] ?? 0);
                        }
                    }

                    foreach ($allSnapshotItems as $item) {
                        $itemId = $item['id'] ?? null;
                        $itemName = $item['name'] ?? '';
                        $keyFound = null;
                        if ($itemId !== null && isset($inputMap[(string)$itemId])) {
                            $keyFound = (string)$itemId;
                        } elseif (isset($inputMap[$itemName])) {
                            $keyFound = $itemName;
                        }

                        if ($keyFound !== null) {
                            $isInstallmentAllowed = $registration->isFeeInstallmentAllowed($itemName, $itemId);
                            $itemGross = (float) ($item['amount'] ?? 0);
                            $itemDiscount = $registration->getItemDiscountAmount($itemName, $itemId);
                            $itemNet = max(0, $itemGross - $itemDiscount);
                            $itemPaid = $registration->getItemPaidAmount($itemName, $itemId);
                            $itemRemaining = max(0, $itemNet - $itemPaid);

                            if ($itemRemaining <= 0) continue;

                            $requestedAmount = $inputMap[$keyFound];
                            $minItemInstallment = min($itemRemaining, (float) ($registration->min_installment_amount ?: 500000));

                            if ($isGlobalInstallment || $isInstallmentAllowed) {
                                $itemAmountToPay = ($requestedAmount > 0) ? $requestedAmount : $itemRemaining;
                                if ($itemAmountToPay < $minItemInstallment) {
                                    return response()->json([
                                        'message' => "Nominal cicilan untuk {$itemName} tidak boleh kurang dari batas minimal Rp " . number_format($minItemInstallment, 0, ',', '.')
                                    ], 422);
                                }
                                if ($itemAmountToPay > $itemRemaining) {
                                    $itemAmountToPay = $itemRemaining;
                                }
                            } else {
                                $itemAmountToPay = $itemRemaining;
                            }

                            $totalCalculatedPrincipal += $itemAmountToPay;
                            $processedItems[] = [
                                'id' => $itemId,
                                'name' => $itemName,
                                'amount' => $itemAmountToPay,
                                'gateways' => $item['gateways'] ?? ['winpay'],
                            ];
                        }
                    }
                } else {
                    foreach ($allSnapshotItems as $item) {
                        $itemId = $item['id'] ?? null;
                        $itemName = $item['name'] ?? '';
                        $itemGross = (float) ($item['amount'] ?? 0);
                        $itemPaid = $registration->getItemPaidAmount($itemName, $itemId);
                        $itemRemaining = max(0, $itemGross - $itemPaid);

                        if ($itemRemaining <= 0) continue;

                        $totalCalculatedPrincipal += $itemRemaining;
                        $processedItems[] = [
                            'id' => $itemId,
                            'name' => $itemName,
                            'amount' => $itemRemaining,
                            'gateways' => $item['gateways'] ?? ['winpay'],
                        ];
                    }
                }

                if ($totalCalculatedPrincipal <= 0) {
                    return response()->json([
                        'message' => 'Seluruh tagihan atau komponen yang dipilih sudah lunas.'
                    ], 422);
                }

                $amount = $totalCalculatedPrincipal;
                $gateways = ['winpay'];
                $finalFee = \App\Models\SpmbFee::where('spmb_unit_id', $registration->spmb_unit_id)
                    ->where('spmb_fee_category_id', 2)
                    ->first();
                if ($finalFee && !empty($finalFee->payment_gateway)) {
                    $gateways = is_array($finalFee->payment_gateway) ? $finalFee->payment_gateway : [$finalFee->payment_gateway];
                }
            } else {
                if ($registration->payment_status === 'paid') {
                    return response()->json([
                        'message' => 'You have already paid your registration fee.'
                    ], 422);
                }
                $paymentType = 'registration_fee';
                $fee = $registration->getRegistrationFee();
                if (!$fee) {
                    return response()->json([
                        'message' => 'Biaya pendaftaran untuk unit ini belum dikonfigurasi oleh panitia SPMB.'
                    ], 422);
                }
                $amount = (float) $fee->amount;
                $gateways = is_array($fee->payment_gateway) ? $fee->payment_gateway : [$fee->payment_gateway];
                $processedItems = [
                    [
                        'id' => $fee->id,
                        'name' => $fee->name,
                        'amount' => $amount,
                        'gateways' => $gateways,
                    ]
                ];
            }

            // Resolve which gateway should process this transaction based on the user's selected payment_method
            $activeChannel = \App\Models\SpmbPaymentChannel::where('code', $request->payment_method)
                ->where('is_active', true)
                ->whereHas('gateway', function($q) use ($gateways) {
                    $q->whereIn('code', $gateways);
                })
                ->first();
            
            if (!$activeChannel || !$activeChannel->gateway) {
                return response()->json([
                    'message' => 'Metode pembayaran ' . $request->payment_method . ' tidak tersedia atau sedang dinonaktifkan.'
                ], 422);
            }

            $gateway = $activeChannel->gateway->code;

            // Cek transaksi pending yang identik
            $existingPayment = Payment::where('registration_id', $registration->id)
                ->where('payment_method', $request->payment_method)
                ->where('payment_type', $paymentType)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($existingPayment) {
                return response()->json([
                    'message' => 'Pending payment transaction found.',
                    'payment' => $existingPayment->load('items')
                ]);
            }

            $feeBniVa = floatval(\App\Models\Setting::get('fee_bni_va', 1500));
            $feeBniQris = floatval(\App\Models\Setting::get('fee_bni_qris', 0.7)) / 100;
            $feeWinpayVa = floatval(\App\Models\Setting::get('fee_winpay_va', 4500));

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
            $invoiceNo = 'INV-SPMB-' . date('Ymd') . '-' . $registration->id . '-' . $randomHex;

            // Step 1: Create local pending payment first (Anti-Orphan Architecture)
            DB::beginTransaction();
            try {
                $initialPaymentInfo = ['gateway' => $gateway];
                if ($paymentType === 'final_fee') {
                    $initialPaymentInfo['selected_items'] = $processedItems;
                }

                $payment = Payment::create([
                    'registration_id' => $registration->id,
                    'invoice_number' => $invoiceNo,
                    'amount' => $totalAmount,
                    'base_amount' => $amount,
                    'admin_fee' => $adminFee,
                    'payment_method' => $request->payment_method,
                    'reference_id' => null,
                    'payment_info' => $initialPaymentInfo,
                    'status' => 'pending',
                    'payment_type' => $paymentType
                ]);

                foreach ($processedItems as $pIt) {
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
                Log::error('Failed to create local pending payment', ['error' => $e->getMessage()]);
                return response()->json([
                    'message' => 'Internal server error while creating payment record.'
                ], 500);
            }

            // Step 2: Request payment transaction to Gateway
            try {
                $candidateName = $registration->candidate_name ?: ($registration->student_name ?: ($registration->name ?: 'Calon Siswa'));

                // Susun nama transaksi: {KODE_UNIT} {JENIS_BIAYA} {NAMA_SISWA} (Maksimal 24 Karakter SNAP BI)
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
                $response = $gatewayService->createPayment($totalAmount, $invoiceNo, $request->payment_method, $studentPaymentName, $studentPhone);
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

                return response()->json([
                    'message' => 'Failed to create payment transaction with ' . ucfirst($gateway) . ': ' . $response['message']
                ], 502);
            }

            // Step 3: Update local payment record with gateway response
            $paymentData = $response['data'];
            $invoiceNoFromGateway = $paymentData['trxId'] ?? $paymentData['partnerReferenceNo'] ?? $invoiceNo;
            $refId = $paymentData['referenceId'] ?? $paymentData['partnerReferenceNo'] ?? null;

            $payment->update([
                'invoice_number' => $invoiceNoFromGateway,
                'reference_id' => $refId,
                'payment_info' => array_merge(is_array($paymentData) ? $paymentData : [], ['selected_items' => $processedItems]),
            ]);

            return response()->json([
                'message' => 'Payment transaction initiated successfully.',
                'payment' => $payment->fresh(['items'])
            ], 201);

        } finally {
            $lock->release();
        }
    }

    /**
     * Webhook Callback Handler untuk Payment Gateway (Winpay SNAP BI / BNI SNAP)
     * Mengikuti pipeline: Signature Verification -> Payload Validation -> Idempotency -> DB Transaction -> Decoupled Notification
     */
    public function callback(Request $request, $explicitGateway = null)
    {
        $headers = $request->headers->all();
        $body = $request->json()->all();
        $rawContent = $request->getContent();
        $requestUri = $request->getRequestUri();

        // Resolusi gateway yang mengirim callback
        $gatewayCode = $explicitGateway ?: 'winpay';
        if (!$explicitGateway) {
            if ($request->header('X-CLIENT-KEY') && !$request->header('X-PARTNER-ID')) {
                $gatewayCode = 'bni';
            }
        }

        // =========================================================================================
        // [1] VERIFIKASI DIGITAL SIGNATURE & KEASLIAN WEBHOOK DI AWAL (FAIL-CLOSED)
        // =========================================================================================
        try {
            $gatewayService = \App\Services\PaymentGatewayFactory::make($gatewayCode);
            $verified = method_exists($gatewayService, 'verifyCallback')
                ? $gatewayService->verifyCallback($headers, $body, $rawContent, $requestUri)
                : false;
        } catch (\Throwable $e) {
            Log::error('Callback signature verification error', ['gateway' => $gatewayCode, 'error' => $e->getMessage()]);
            $verified = false;
        }

        if (!$verified) {
            Log::warning('Payment Webhook rejected: Unauthorized digital signature', [
                'gateway' => $gatewayCode,
                'path' => $requestUri,
                'timestamp' => $request->header('X-TIMESTAMP') ?: $request->header('x-timestamp'),
            ]);
            return response()->json([
                'responseCode' => '4012700',
                'responseMessage' => 'Unauthorized signature'
            ], 401);
        }

        // =========================================================================================
        // [2] EKSTRAKSI & VALIDASI IDENTITAS TRANSAKSI & STATUS SNAP BI
        // =========================================================================================
        Log::info('Payment Webhook body received', [
            'gateway' => $gatewayCode,
            'path' => $requestUri,
            'body' => $body
        ]);

        // Prioritaskan invoice number SPMB format INV-... (termasuk originalPartnerReferenceNo untuk Debit/E-Wallet)
        $invoiceNo = null;
        $candidates = [
            $body['originalPartnerReferenceNo'] ?? null,
            $body['partnerReferenceNo'] ?? null,
            $body['additionalInfo']['invoiceNumber'] ?? null,
            $body['additionalInfo']['partnerReferenceNo'] ?? null,
            $body['originalReferenceNo'] ?? null,
            $body['trxId'] ?? null,
            $body['referenceNo'] ?? null,
            $body['externalId'] ?? null,
        ];

        foreach ($candidates as $cand) {
            if ($cand && str_starts_with((string)$cand, 'INV-')) {
                $invoiceNo = (string)$cand;
                break;
            }
        }

        if (!$invoiceNo) {
            $invoiceNo = $body['originalPartnerReferenceNo']
                ?? $body['partnerReferenceNo'] 
                ?? ($body['additionalInfo']['invoiceNumber'] ?? null)
                ?? ($body['additionalInfo']['partnerReferenceNo'] ?? null)
                ?? $body['originalReferenceNo']
                ?? $body['trxId'] 
                ?? ($body['referenceNo'] ?? null);
        }

        if (!$invoiceNo) {
            Log::warning('Payment Webhook rejected: Missing transaction ID in payload', ['body' => $body]);
            return response()->json([
                'responseCode' => '4002700',
                'responseMessage' => 'Missing transaction ID'
            ], 400);
        }

        Log::info('Payment Webhook processing invoice', ['gateway' => $gatewayCode, 'resolved_invoice' => $invoiceNo]);

        $responseCode = (string)($body['responseCode'] ?? '');
        $rawStatus = $body['paymentStatus'] 
            ?? $body['latestTransactionStatus'] 
            ?? $body['latestStatus'] 
            ?? $body['status'] 
            ?? $body['transactionStatus'] 
            ?? null;

        $isSuccess = false;
        $isFailed = false;

        // Evaluasi status pembayaran berdasarkan responseCode & status resmi SNAP BI (misal '00' = SUCCESS)
        if (str_starts_with($responseCode, '200') || in_array($responseCode, ['2002500', '2002600', '2002700', '2005400', '2000000'])) {
            $isSuccess = true;
        } elseif (is_string($rawStatus) && in_array(strtoupper($rawStatus), ['SUCCESS', 'SUCCESSFUL', 'PAID', 'SETTLED', '00', '0000', 'BERHASIL'])) {
            $isSuccess = true;
        } elseif (is_string($rawStatus) && in_array(strtoupper($rawStatus), ['FAILED', 'EXPIRED', 'CANCELLED', 'REJECTED', 'GAGAL'])) {
            $isFailed = true;
        } elseif ($responseCode && (str_starts_with($responseCode, '40') || str_starts_with($responseCode, '50'))) {
            $isFailed = true;
        }

        $origPartnerRef = $body['originalPartnerReferenceNo'] ?? null;
        $partnerRef = $body['partnerReferenceNo'] ?? null;
        $origRefNo = $body['originalReferenceNo'] ?? null;
        $trxId = $body['trxId'] ?? null;
        $refNo = $body['referenceNo'] ?? null;

        // Siapkan struktur ACK standar SNAP BI Winpay
        $ackResponseCode = '2002500';
        if (!empty($body['responseCode']) && str_starts_with((string)$body['responseCode'], '200')) {
            $ackResponseCode = (string)$body['responseCode'];
        } elseif (!empty($origPartnerRef) || !empty($body['additionalInfo']['contractId']) || !empty($body['additionalInfo']['channel'])) {
            // Standar Winpay SNAP BI untuk E-Wallet & Direct Debit Notification ACK adalah 2005600
            $ackResponseCode = '2005600';
        } elseif (!empty($trxId) || !empty($body['virtualAccountNo'])) {
            // Standar Winpay SNAP BI untuk Virtual Account Payment Notification ACK adalah 2002700
            $ackResponseCode = '2002700';
        }

        $ackPayload = [
            'responseCode' => $ackResponseCode,
            'responseMessage' => 'Successful'
        ];

        if (!empty($origPartnerRef)) {
            $ackPayload['originalPartnerReferenceNo'] = $origPartnerRef;
        }
        if (!empty($origRefNo)) {
            $ackPayload['originalReferenceNo'] = $origRefNo;
        }
        if (!empty($partnerRef)) {
            $ackPayload['partnerReferenceNo'] = $partnerRef;
        }

        // =========================================================================================
        // [3] PENCARIAN RECORD TRANSAKSI & IDEMPOTENCY CHECK DENGAN PESSIMISTIC LOCK
        // =========================================================================================
        DB::beginTransaction();
        try {
            $payment = Payment::where(function($q) use ($invoiceNo, $origPartnerRef, $partnerRef, $origRefNo, $trxId, $refNo) {
                $q->where('invoice_number', $invoiceNo);
                if ($origPartnerRef) $q->orWhere('invoice_number', $origPartnerRef);
                if ($partnerRef) $q->orWhere('invoice_number', $partnerRef);
                if ($origRefNo) $q->orWhere('invoice_number', $origRefNo)->orWhere('reference_id', $origRefNo);
                if ($trxId) $q->orWhere('invoice_number', $trxId)->orWhere('reference_id', $trxId);
                if ($refNo) $q->orWhere('reference_id', $refNo);
            })->lockForUpdate()->first();

            if (!$payment) {
                DB::rollBack();
                Log::warning('Payment callback transaction not found in database', [
                    'resolved_invoice' => $invoiceNo,
                    'originalPartnerReferenceNo' => $origPartnerRef,
                    'partnerReferenceNo' => $partnerRef,
                    'originalReferenceNo' => $origRefNo,
                    'trxId' => $trxId,
                    'referenceNo' => $refNo,
                ]);
                return response()->json([
                    'responseCode' => '4042700',
                    'responseMessage' => 'Payment transaction not found'
                ], 404);
            }

            // Simpan reference_id dari Winpay jika belum tersimpan
            $resolvedWinpayRef = $origRefNo ?: ($refNo ?: $trxId);
            if (empty($payment->reference_id) && !empty($resolvedWinpayRef)) {
                $payment->update(['reference_id' => $resolvedWinpayRef]);
            }

            // Idempotency: Jika invoice sudah sukses diproses sebelumnya, kembalikan 200 OK tanpa memproses ulang
            if ($payment->status === 'success') {
                DB::rollBack();
                Log::info('Payment callback idempotent response: already success', ['invoice' => $payment->invoice_number]);
                return response()->json($ackPayload, 200, ['Content-Type' => 'application/json;charset=UTF-8']);
            }

            // =========================================================================================
            // [4] VALIDASI NOMINAL TRANSAKSI (AMOUNT MATCHING)
            // =========================================================================================
            $callbackAmount = isset($body['paymentAmount']['value']) 
                ? floatval($body['paymentAmount']['value']) 
                : (isset($body['amount']['value']) 
                    ? floatval($body['amount']['value']) 
                    : (isset($body['paidAmount']['value']) 
                        ? floatval($body['paidAmount']['value']) 
                        : (isset($body['totalAmount']['value']) 
                            ? floatval($body['totalAmount']['value']) 
                            : (isset($body['amount']) && is_numeric($body['amount']) ? floatval($body['amount']) : null))));

            if ($callbackAmount !== null && $callbackAmount > 0) {
                $expectedAmount = floatval($payment->amount);
                $expectedBaseAmount = floatval($payment->base_amount);
                if (round($callbackAmount) !== round($expectedAmount) && round($callbackAmount) !== round($expectedBaseAmount)) {
                    DB::rollBack();
                    Log::warning('Payment callback amount mismatch', [
                        'invoice' => $invoiceNo,
                        'expected_total' => $expectedAmount,
                        'expected_base' => $expectedBaseAmount,
                        'received' => $callbackAmount
                    ]);
                    return response()->json([
                        'responseCode' => '4002702',
                        'responseMessage' => 'Transaction amount mismatch'
                    ], 400);
                }
            }

            $registration = $payment->registration;
            $notificationsToDispatch = [];

            // =========================================================================================
            // [5] PROSES UPDATE STATUS DALAM DB TRANSACTION ATOMIK
            // =========================================================================================
            if ($isSuccess) {
                $currentInfo = is_array($payment->payment_info) ? $payment->payment_info : [];
                $newInfo = array_merge($currentInfo, [
                    'callback_payload' => $body,
                    'settled_at' => now()->toIso8601String()
                ]);

                $payment->update([
                    'status' => 'success',
                    'payment_info' => $newInfo
                ]);

                if ($payment->payment_type === 'final_fee') {
                    $totalRequired = $registration->net_fee;
                    $totalPaid = $registration->total_paid_final_fee;
                    
                    if ($totalPaid >= $totalRequired) {
                        $registration->update([
                            'payment_status' => 'paid',
                            'registration_status' => 'completed',
                            'committee_notes' => 'Alhamdulillah, seluruh rangkaian pendaftaran dan pembayaran administrasi akhir ananda ' . ($registration->candidate_name ?? 'Ananda') . ' telah lunas diverifikasi. Selamat bergabung di Sekolah Anak Saleh!'
                        ]);

                        $notificationsToDispatch[] = [
                            'type' => 'dsp_full',
                            'registration' => $registration,
                            'totalPaid' => $totalPaid,
                        ];
                    } else {
                        $registration->update([
                            'payment_status' => 'partially_paid',
                            'committee_notes' => 'Pembayaran administrasi akhir sebagian berhasil diterima. Silakan selesaikan sisa tanggungan pembiayaan Anda.'
                        ]);

                        $notificationsToDispatch[] = [
                            'type' => 'dsp_partial',
                            'registration' => $registration,
                            'paymentAmount' => $payment->amount,
                            'totalPaid' => $totalPaid,
                            'totalRequired' => $totalRequired,
                        ];
                    }
                } else {
                    $registration->update([
                        'payment_status' => 'paid',
                        'committee_notes' => 'Pembayaran formulir pendaftaran berhasil diterima. Silakan isi dan lengkapi formulir pendaftaran Anda.'
                    ]);

                    $notificationsToDispatch[] = [
                        'type' => 'form_fee',
                        'registration' => $registration,
                        'paymentAmount' => $payment->amount,
                    ];
                }

                DB::commit();
                Log::info('Payment success processed atomically', ['invoice' => $invoiceNo]);

            } elseif ($isFailed) {
                $currentInfo = is_array($payment->payment_info) ? $payment->payment_info : [];
                $newInfo = array_merge($currentInfo, [
                    'callback_payload' => $body
                ]);

                $payment->update([
                    'status' => 'failed',
                    'payment_info' => $newInfo
                ]);

                $registration->update([
                    'payment_status' => 'unpaid'
                ]);

                DB::commit();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to process payment callback atomically', ['error' => $e->getMessage()]);
            return response()->json([
                'responseCode' => '5002700',
                'responseMessage' => 'Internal server error'
            ], 500);
        }

        // =========================================================================================
        // [6] PENGIRIMAN NOTIFIKASI DI LUAR TRANSAKSI DATABASE (DECOUPLED)
        // =========================================================================================
        foreach ($notificationsToDispatch as $nData) {
            try {
                $reg = $nData['registration'];
                $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();

                if ($nData['type'] === 'dsp_full') {
                    \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                        'title' => 'Pembayaran DSP Lunas',
                        'message' => 'Pembayaran Uang Pangkal (DSP) calon siswa "' . $reg->candidate_name . '" telah lunas (Total: Rp ' . number_format($nData['totalPaid'], 0, ',', '.') . ').',
                        'url' => route('admin.payments.data') . '?search=' . urlencode($reg->candidate_name),
                        'type' => 'success',
                    ]));

                    if ($reg->user) {
                        $reg->user->notify(new \App\Notifications\SpmbNotification([
                            'title' => 'Pembayaran DSP Lunas',
                            'message' => 'Alhamdulillah, Uang Pangkal (DSP) untuk ananda "' . $reg->candidate_name . '" telah lunas diverifikasi. Selamat bergabung di Sekolah Anak Saleh!',
                            'url' => route('dashboard.result', $reg->id),
                            'type' => 'success',
                        ]));
                    }
                } elseif ($nData['type'] === 'dsp_partial') {
                    \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                        'title' => 'Pembayaran DSP Sebagian',
                        'message' => 'Diterima pembayaran DSP sebagian untuk calon siswa "' . $reg->candidate_name . '" sebesar Rp ' . number_format($nData['paymentAmount'], 0, ',', '.') . ' (Masuk: Rp ' . number_format($nData['totalPaid'], 0, ',', '.') . ' / ' . number_format($nData['totalRequired'], 0, ',', '.') . ').',
                        'url' => route('admin.payments.data') . '?search=' . urlencode($reg->candidate_name),
                        'type' => 'info',
                    ]));

                    if ($reg->user) {
                        $reg->user->notify(new \App\Notifications\SpmbNotification([
                            'title' => 'Pembayaran DSP Sebagian',
                            'message' => 'Pembayaran DSP sebagian untuk ananda "' . $reg->candidate_name . '" sebesar Rp ' . number_format($nData['paymentAmount'], 0, ',', '.') . ' telah diverifikasi. Silakan selesaikan sisa tanggungan pembiayaan Anda.',
                            'url' => route('dashboard.result', $reg->id),
                            'type' => 'info',
                        ]));
                    }
                } elseif ($nData['type'] === 'form_fee') {
                    \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                        'title' => 'Pembayaran Formulir Sukses',
                        'message' => 'Pembayaran formulir untuk calon siswa "' . $reg->candidate_name . '" sebesar Rp ' . number_format($nData['paymentAmount'], 0, ',', '.') . ' telah lunas.',
                        'url' => route('admin.payments') . '?search=' . urlencode($reg->candidate_name),
                        'type' => 'success',
                    ]));

                    if ($reg->user) {
                        $reg->user->notify(new \App\Notifications\SpmbNotification([
                            'title' => 'Pembayaran Formulir Sukses',
                            'message' => 'Alhamdulillah, pembayaran formulir pendaftaran untuk ananda "' . $reg->candidate_name . '" telah sukses diverifikasi. Silakan isi dan lengkapi formulir pendaftaran Anda.',
                            'url' => route('dashboard.form', $reg->id),
                            'type' => 'success',
                        ]));
                    }
                }
            } catch (\Throwable $notifEx) {
                Log::error('Non-critical error sending payment notifications', ['error' => $notifEx->getMessage()]);
            }
        }

        return response()->json($ackPayload, 200, [
            'Content-Type' => 'application/json;charset=UTF-8'
        ]);
    }
}
