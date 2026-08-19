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

    public function charge(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string|in:MANDIRI,BRI,BNI,BCA,QRIS',
        ]);

        $user = $request->user();
        
        // Find user registration
        $registration = Registration::where('user_id', $user->id)->first();

        if (!$registration) {
            return response()->json([
                'message' => 'Registration record not found.'
            ], 404);
        }

        // Validate that they have completed all registration steps first
        if ($registration->registration_status === 'draft') {
            return response()->json([
                'message' => 'Please complete your registration form and upload all documents before making a payment.'
            ], 422);
        }

        if ($registration->payment_status === 'paid') {
            return response()->json([
                'message' => 'You have already paid your registration fee.'
            ], 422);
        }

        // Check if there is already an active pending payment for the same method
        $existingPayment = Payment::where('registration_id', $registration->id)
            ->where('payment_method', $request->payment_method)
            ->where('status', 'pending')
            ->first();

        if ($existingPayment) {
            return response()->json([
                'message' => 'Pending payment transaction found.',
                'payment' => $existingPayment
            ]);
        }

        // Cost is Rp 350.000 for SPMB Form Registration Fee
        $amount = 350000;
        $invoiceNo = 'INV-SPMB-' . date('Ymd') . '-' . $registration->id . '-' . rand(100, 999);

        // Call Winpay service to create payment
        $response = $this->winpayService->createPayment($amount, $invoiceNo, $request->payment_method);

        if (!$response['success']) {
            return response()->json([
                'message' => 'Failed to create payment transaction with Winpay.',
                'error' => $response['message']
            ], 500);
        }

        // Save transaction to database
        $paymentData = $response['data'];
        $refId = $paymentData['referenceId'] ?? null;

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'registration_id' => $registration->id,
                'invoice_number' => $invoiceNo,
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'reference_id' => $refId,
                'payment_info' => $paymentData,
                'status' => 'pending'
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

        Log::info('Winpay Webhook received', ['headers' => $headers, 'body' => $body]);

        // Verify signature
        if (!$this->winpayService->verifyCallback($headers, $body)) {
            Log::warning('Winpay Webhook signature verification failed');
            return response()->json([
                'responseCode' => '4012700',
                'responseMessage' => 'Unauthorized signature'
            ], 401);
        }

        // Parse standard SNAP callback fields
        $invoiceNo = $body['trxId'] ?? $body['partnerReferenceNo'] ?? ($body['additionalInfo']['invoiceNumber'] ?? null);
        $status = $body['paymentStatus'] ?? $body['latestStatus'] ?? null;

        if (!$invoiceNo) {
            return response()->json([
                'responseCode' => '4002700',
                'responseMessage' => 'Missing transaction ID'
            ], 400);
        }

        // Find the payment record
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

        if (strtoupper($status) === 'SUCCESS' || $status === '00') {
            DB::beginTransaction();
            try {
                $payment->update([
                    'status' => 'success'
                ]);

                Registration::where('id', $payment->registration_id)->update([
                    'payment_status' => 'paid'
                ]);

                DB::commit();
                Log::info('Payment success processed', ['invoice' => $invoiceNo]);

                // SNAP Standard Success Response
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

        // If transaction failed or expired
        if (in_array(strtoupper($status), ['FAILED', 'EXPIRED'])) {
            $payment->update([
                'status' => strtolower($status)
            ]);

            Registration::where('id', $payment->registration_id)->update([
                'payment_status' => strtolower($status)
            ]);
        }

        return response()->json([
            'responseCode' => '2002500',
            'responseMessage' => 'Success'
        ]);
    }
}
