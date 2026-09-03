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
        ]);

        $user = $request->user();
        
        $registration = Registration::where('user_id', $user->id)->first();

        if (!$registration) {
            return response()->json([
                'message' => 'Registration record not found.'
            ], 404);
        }

        // Gunakan atomic lock per registrasi untuk mencegah race condition / double submit
        $lockKey = 'charge_lock_reg_' . $registration->id;
        $lock = Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            return response()->json([
                'message' => 'Sedang memproses permintaan pembayaran sebelumnya. Silakan tunggu beberapa saat.'
            ], 429);
        }

        try {
            $status = $registration->registration_status;

            if ($status === 'agreement_signed') {
                $paymentType = 'final_fee';
                $feeDetails = $registration->final_fee_snapshot ?? $this->getFinalFeeDetails($registration);
                $amount = $feeDetails['total'];
                
                $finalFee = \App\Models\SpmbFee::where('spmb_unit_id', $registration->spmb_unit_id)
                    ->where('spmb_fee_category_id', 2)
                    ->first();
                $gateways = $finalFee ? (is_array($finalFee->payment_gateway) ? $finalFee->payment_gateway : [$finalFee->payment_gateway]) : ['winpay'];
            } else {
                if ($registration->payment_status === 'paid') {
                    return response()->json([
                        'message' => 'You have already paid your registration fee.'
                    ], 422);
                }
                $paymentType = 'registration_fee';
                $fee = $this->getRegistrationFee($registration);
                $amount = $fee ? $fee->amount : 350000;
                $gateways = $fee ? (is_array($fee->payment_gateway) ? $fee->payment_gateway : [$fee->payment_gateway]) : ['winpay'];
            }

            // Resolve which gateway should process this transaction based on the user's selected payment_method
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
                    'payment' => $existingPayment
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

            try {
                $studentName = $registration->student_name ?? $registration->candidate_name ?? $registration->name ?? null;
                $studentPhone = $registration->parent_phone ?? $registration->phone ?? null;
                $gatewayService = \App\Services\PaymentGatewayFactory::make($gateway);
                $response = $gatewayService->createPayment($totalAmount, $invoiceNo, $request->payment_method, $studentName, $studentPhone);
            } catch (\Throwable $e) {
                $response = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }

            if (!$response['success']) {
                return response()->json([
                    'message' => 'Failed to create payment transaction with ' . ucfirst($gateway) . '.',
                    'error' => $response['message']
                ], 500);
            }

            $paymentData = $response['data'];
            $invoiceNoFromGateway = $paymentData['trxId'] ?? $paymentData['partnerReferenceNo'] ?? $invoiceNo;
            $refId = $paymentData['referenceId'] ?? $paymentData['partnerReferenceNo'] ?? null;

            DB::beginTransaction();
            try {
                $payment = Payment::create([
                    'registration_id' => $registration->id,
                    'invoice_number' => $invoiceNoFromGateway,
                    'amount' => $totalAmount,
                    'base_amount' => $amount,
                    'admin_fee' => $adminFee,
                    'payment_method' => $request->payment_method,
                    'reference_id' => $refId,
                    'payment_info' => $paymentData,
                    'status' => 'pending',
                    'payment_type' => $paymentType
                ]);

                $registration->update([
                    'payment_status' => 'pending'
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Payment transaction initiated successfully.',
                    'payment' => $payment
                ], 201);

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Failed to save payment transaction', ['error' => $e->getMessage()]);
                return response()->json([
                    'message' => 'Internal server error while saving payment transaction.'
                ], 500);
            }
        } finally {
            $lock->release();
        }
    }

    /**
     * Webhook Callback Handler untuk Payment Gateway (Winpay SNAP BI / BNI SNAP)
     * Mengikuti pipeline: Signature Verification -> Payload Validation -> Idempotency -> DB Transaction -> Decoupled Notification
     */
    public function callback(Request $request)
    {
        $headers = $request->headers->all();
        $body = $request->json()->all();

        Log::info('Payment Webhook received', ['headers' => $headers, 'body' => $body]);

        // Resolusi gateway yang mengirim callback
        $gatewayCode = 'winpay';
        if ($request->header('X-CLIENT-KEY') && !$request->header('X-PARTNER-ID')) {
            $gatewayCode = 'bni';
        }

        // =========================================================================================
        // [1] VERIFIKASI DIGITAL SIGNATURE & KEASLIAN WEBHOOK DI AWAL (FAIL-CLOSED)
        // =========================================================================================
        try {
            $gatewayService = \App\Services\PaymentGatewayFactory::make($gatewayCode);
            $verified = method_exists($gatewayService, 'verifyCallback')
                ? $gatewayService->verifyCallback($headers, $body)
                : false;
        } catch (\Throwable $e) {
            Log::error('Callback signature verification error', ['gateway' => $gatewayCode, 'error' => $e->getMessage()]);
            $verified = false;
        }

        if (!$verified) {
            Log::warning('Payment Webhook rejected: Unauthorized digital signature', ['gateway' => $gatewayCode]);
            return response()->json([
                'responseCode' => '4012700',
                'responseMessage' => 'Unauthorized signature'
            ], 401);
        }

        // =========================================================================================
        // [2] EKSTRAKSI & VALIDASI IDENTITAS TRANSAKSI & STATUS SNAP BI
        // =========================================================================================
        $invoiceNo = $body['trxId'] 
            ?? $body['partnerReferenceNo'] 
            ?? ($body['additionalInfo']['invoiceNumber'] ?? null)
            ?? ($body['referenceNo'] ?? null);

        if (!$invoiceNo) {
            return response()->json([
                'responseCode' => '4002700',
                'responseMessage' => 'Missing transaction ID'
            ], 400);
        }

        $responseCode = $body['responseCode'] ?? null;
        $rawStatus = $body['paymentStatus'] ?? $body['latestTransactionStatus'] ?? $body['latestStatus'] ?? null;

        $isSuccess = false;
        $isFailed = false;

        // Evaluasi status pembayaran berdasarkan responseCode & status resmi SNAP BI
        if (in_array($responseCode, ['2002500', '2002600', '2002700', '2000000'])) {
            $isSuccess = true;
        } elseif (is_string($rawStatus) && in_array(strtoupper($rawStatus), ['SUCCESS', 'PAID', 'SETTLED', '00', '0000'])) {
            $isSuccess = true;
        } elseif (is_string($rawStatus) && in_array(strtoupper($rawStatus), ['FAILED', 'EXPIRED', 'CANCELLED', 'REJECTED'])) {
            $isFailed = true;
        } elseif ($responseCode && (str_starts_with((string)$responseCode, '40') || str_starts_with((string)$responseCode, '50'))) {
            $isFailed = true;
        }

        // =========================================================================================
        // [3] PENCARIAN RECORD TRANSAKSI & IDEMPOTENCY CHECK
        // =========================================================================================
        $payment = Payment::where('invoice_number', $invoiceNo)->first();

        if (!$payment) {
            Log::warning('Payment callback transaction not found', ['invoice' => $invoiceNo]);
            return response()->json([
                'responseCode' => '4042700',
                'responseMessage' => 'Payment transaction not found'
            ], 404);
        }

        // Idempotency: Jika invoice sudah sukses diproses sebelumnya, kembalikan 200 OK tanpa memproses ulang
        if ($payment->status === 'success') {
            Log::info('Payment callback idempotent response: already success', ['invoice' => $invoiceNo]);
            return response()->json([
                'responseCode' => '2002500',
                'responseMessage' => 'Success'
            ]);
        }

        // =========================================================================================
        // [4] VALIDASI NOMINAL TRANSAKSI (AMOUNT MATCHING)
        // =========================================================================================
        $callbackAmount = isset($body['paymentAmount']['value']) 
            ? floatval($body['paymentAmount']['value']) 
            : (isset($body['amount']['value']) ? floatval($body['amount']['value']) : (isset($body['paidAmount']['value']) ? floatval($body['paidAmount']['value']) : null));

        if ($callbackAmount !== null && $callbackAmount > 0) {
            $expectedAmount = floatval($payment->amount);
            $expectedBaseAmount = floatval($payment->base_amount);
            if (round($callbackAmount) !== round($expectedAmount) && round($callbackAmount) !== round($expectedBaseAmount)) {
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
            DB::beginTransaction();
            try {
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

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Failed to process payment success callback atomically', ['error' => $e->getMessage()]);
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
                            'url' => route('admin.payments.data') . '?search=' . urlencode($reg->candidate_name),
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

            return response()->json([
                'responseCode' => '2002500',
                'responseMessage' => 'Success'
            ]);
        }

        if ($isFailed) {
            DB::beginTransaction();
            try {
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
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Failed to update payment status to failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'responseCode' => '2002500',
            'responseMessage' => 'Success'
        ]);
    }
}
