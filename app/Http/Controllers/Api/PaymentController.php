<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Registration;
use App\Models\Payment;
use App\Services\WinpayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $existingPayment = Payment::where('registration_id', $registration->id)
            ->where('payment_method', $request->payment_method)
            ->where('payment_type', $paymentType)
            ->where('status', 'pending')
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
        $invoiceNo = 'INV-SPMB-' . date('Ymd') . '-' . $registration->id . '-' . rand(100, 999);

        try {
            $studentName = $registration->student_name ?? $registration->name ?? null;
            $studentPhone = $registration->parent_phone ?? $registration->phone ?? null;
            $gatewayService = \App\Services\PaymentGatewayFactory::make($gateway);
            $response = $gatewayService->createPayment($totalAmount, $invoiceNo, $request->payment_method, $studentName, $studentPhone);
        } catch (\Exception $e) {
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

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save payment transaction', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Internal server error while saving payment transaction.'
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        $headers = $request->headers->all();
        $body = $request->json()->all();

        Log::info('Winpay/BNI Webhook received', ['headers' => $headers, 'body' => $body]);

        $invoiceNo = $body['trxId'] ?? $body['partnerReferenceNo'] ?? ($body['additionalInfo']['invoiceNumber'] ?? null);
        $status = $body['paymentStatus'] ?? $body['latestStatus'] ?? null;

        // Standard SNAP BI VA / QRIS payment notification implies SUCCESS status
        if (is_null($status) && ($request->is('*transfer-va/payment*') || $request->is('*qr-mpm-notify*'))) {
            $status = 'SUCCESS';
        }

        if (!$invoiceNo) {
            return response()->json([
                'responseCode' => '4002700',
                'responseMessage' => 'Missing transaction ID'
            ], 400);
        }

        $payment = Payment::where('invoice_number', $invoiceNo)->first();

        if (!$payment) {
            return response()->json([
                'responseCode' => '4042700',
                'responseMessage' => 'Payment transaction not found'
            ], 404);
        }

        if ($payment->status === 'success') {
            return response()->json([
                'responseCode' => '2002500',
                'responseMessage' => 'Success'
            ]);
        }

        $registration = $payment->registration;
        if ($payment->payment_type === 'final_fee') {
            $finalFee = \App\Models\SpmbFee::where('spmb_unit_id', $registration->spmb_unit_id)
                ->where('spmb_fee_category_id', 2)
                ->first();
            $gateways = $finalFee ? (is_array($finalFee->payment_gateway) ? $finalFee->payment_gateway : [$finalFee->payment_gateway]) : ['winpay'];
        } else {
            $fee = $this->getRegistrationFee($registration);
            $gateways = $fee ? (is_array($fee->payment_gateway) ? $fee->payment_gateway : [$fee->payment_gateway]) : ['winpay'];
        }

        // Resolve gateway from payment channel first, or fallback to first allowed gateway
        $activeChannel = \App\Models\SpmbPaymentChannel::where('code', $payment->payment_method)
            ->where('is_active', true)
            ->first();
        
        $gatewayCode = 'winpay';
        if ($activeChannel && $activeChannel->gateway) {
            $gatewayCode = $activeChannel->gateway->code;
        } else {
            $gatewayCode = reset($gateways) ?: 'winpay';
        }

        try {
            $gatewayService = \App\Services\PaymentGatewayFactory::make($gatewayCode);
            if (method_exists($gatewayService, 'verifyCallback')) {
                $verified = $gatewayService->verifyCallback($headers, $body);
            } else {
                $verified = false;
            }
        } catch (\Exception $e) {
            Log::error('Callback gateway resolution error', ['error' => $e->getMessage()]);
            $verified = false;
        }

        if (!$verified) {
            Log::warning('Webhook signature verification failed', ['gateway' => $gatewayCode]);
            return response()->json([
                'responseCode' => '4012700',
                'responseMessage' => 'Unauthorized signature'
            ], 401);
        }

        // Verify payment amount matches tagihan asli (base_amount atau total amount)
        $callbackAmount = isset($body['paymentAmount']['value']) ? floatval($body['paymentAmount']['value']) : null;
        if ($callbackAmount) {
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

        if (strtoupper($status) === 'SUCCESS' || $status === '00') {
            DB::beginTransaction();
            try {
                $currentInfo = $payment->payment_info ?? [];
                if (!is_array($currentInfo)) {
                    $currentInfo = [];
                }
                $newInfo = array_merge($currentInfo, [
                    'callback_payload' => $body
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

                        // Trigger notification to all admins for full DSP payment
                        try {
                            $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
                            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                                'title' => 'Pembayaran DSP Lunas',
                                'message' => 'Pembayaran Uang Pangkal (DSP) calon siswa "' . $registration->candidate_name . '" telah lunas (Total: Rp ' . number_format($totalPaid, 0, ',', '.') . ').',
                                'url' => route('admin.payments.data') . '?search=' . urlencode($registration->candidate_name),
                                'type' => 'success',
                            ]));
                        } catch (\Exception $e) {
                            Log::error('Failed to send DSP success notification', ['error' => $e->getMessage()]);
                        }

                        // Trigger notification to candidate
                        try {
                            $registration->user->notify(new \App\Notifications\SpmbNotification([
                                'title' => 'Pembayaran DSP Lunas',
                                'message' => 'Alhamdulillah, Uang Pangkal (DSP) untuk ananda "' . $registration->candidate_name . '" telah lunas diverifikasi. Selamat bergabung di Sekolah Anak Saleh!',
                                'url' => route('dashboard.result', $registration->id),
                                'type' => 'success',
                            ]));
                        } catch (\Exception $e) {
                            Log::error('Failed to send candidate DSP success notification', ['error' => $e->getMessage()]);
                        }
                    } else {
                        $registration->update([
                            'payment_status' => 'partially_paid',
                            'committee_notes' => 'Pembayaran administrasi akhir sebagian berhasil diterima. Silakan selesaikan sisa tanggungan pembiayaan Anda.'
                        ]);

                        // Trigger notification to all admins for partial DSP payment
                        try {
                            $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
                            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                                'title' => 'Pembayaran DSP Sebagian',
                                'message' => 'Diterima pembayaran DSP sebagian untuk calon siswa "' . $registration->candidate_name . '" sebesar Rp ' . number_format($payment->amount, 0, ',', '.') . ' (Masuk: Rp ' . number_format($totalPaid, 0, ',', '.') . ' / ' . number_format($totalRequired, 0, ',', '.') . ').',
                                'url' => route('admin.payments.data') . '?search=' . urlencode($registration->candidate_name),
                                'type' => 'info',
                            ]));
                        } catch (\Exception $e) {
                            Log::error('Failed to send DSP partial success notification', ['error' => $e->getMessage()]);
                        }

                        // Trigger notification to candidate
                        try {
                            $registration->user->notify(new \App\Notifications\SpmbNotification([
                                'title' => 'Pembayaran DSP Sebagian',
                                'message' => 'Pembayaran DSP sebagian untuk ananda "' . $registration->candidate_name . '" sebesar Rp ' . number_format($payment->amount, 0, ',', '.') . ' telah diverifikasi. Silakan selesaikan sisa tanggungan pembiayaan Anda.',
                                'url' => route('dashboard.result', $registration->id),
                                'type' => 'info',
                            ]));
                        } catch (\Exception $e) {
                            Log::error('Failed to send candidate DSP partial success notification', ['error' => $e->getMessage()]);
                        }
                    }
                } else {
                    $registration->update([
                        'payment_status' => 'paid',
                        'committee_notes' => 'Pembayaran formulir pendaftaran berhasil diterima. Silakan isi dan lengkapi formulir pendaftaran Anda.'
                    ]);

                    // Trigger notification to all admins for form payment
                    try {
                        $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
                        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SpmbNotification([
                            'title' => 'Pembayaran Formulir Sukses',
                            'message' => 'Pembayaran formulir untuk calon siswa "' . $registration->candidate_name . '" sebesar Rp ' . number_format($payment->amount, 0, ',', '.') . ' telah lunas.',
                            'url' => route('admin.payments.data') . '?search=' . urlencode($registration->candidate_name),
                            'type' => 'success',
                        ]));
                    } catch (\Exception $e) {
                        Log::error('Failed to send payment success notification', ['error' => $e->getMessage()]);
                    }

                    // Trigger notification to candidate
                    try {
                        $registration->user->notify(new \App\Notifications\SpmbNotification([
                            'title' => 'Pembayaran Formulir Sukses',
                            'message' => 'Alhamdulillah, pembayaran formulir pendaftaran untuk ananda "' . $registration->candidate_name . '" telah sukses diverifikasi. Silakan isi dan lengkapi formulir pendaftaran Anda.',
                            'url' => route('dashboard.form', $registration->id),
                            'type' => 'success',
                        ]));
                    } catch (\Exception $e) {
                        Log::error('Failed to send candidate payment success notification', ['error' => $e->getMessage()]);
                    }
                }

                DB::commit();
                Log::info('Payment success processed', ['invoice' => $invoiceNo]);

                return response()->json([
                    'responseCode' => '2002500',
                    'responseMessage' => 'Success'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to process payment success callback', ['error' => $e->getMessage()]);
                return response()->json([
                    'responseCode' => '5002700',
                    'responseMessage' => 'Internal server error'
                ], 500);
            }
        }

        if (in_array(strtoupper($status), ['FAILED', 'EXPIRED'])) {
            DB::beginTransaction();
            try {
                $currentInfo = $payment->payment_info ?? [];
                if (!is_array($currentInfo)) {
                    $currentInfo = [];
                }
                $newInfo = array_merge($currentInfo, [
                    'callback_payload' => $body
                ]);

                $payment->update([
                    'status' => strtolower($status),
                    'payment_info' => $newInfo
                ]);

                $registration->update([
                    'payment_status' => strtolower($status)
                ]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }

        return response()->json([
            'responseCode' => '2002500',
            'responseMessage' => 'Success'
        ]);
    }
}
