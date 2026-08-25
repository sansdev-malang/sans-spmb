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
                    'amount' => $fee->amount,
                    'gateways' => is_array($fee->payment_gateway) ? $fee->payment_gateway : [$fee->payment_gateway]
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
            ['name' => 'Uang Gedung', 'amount' => $details['uang_gedung'], 'gateways' => ['winpay']],
            ['name' => 'Biaya Seragam', 'amount' => $details['seragam'], 'gateways' => ['winpay']],
            ['name' => 'SPP Bulanan', 'amount' => $details['spp'], 'gateways' => ['winpay']],
            ['name' => 'Uang Kegiatan', 'amount' => $details['kegiatan'], 'gateways' => ['winpay']],
        ];

        $details['total'] = $details['uang_gedung'] + $details['seragam'] + $details['spp'] + $details['kegiatan'];
        return $details;
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
            $gatewayService = \App\Services\PaymentGatewayFactory::make($gateway);
            $response = $gatewayService->createPayment($totalAmount, $invoiceNo, $request->payment_method);
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
                $payment->update([
                    'status' => 'success'
                ]);

                if ($payment->payment_type === 'final_fee') {
                    $feeDetails = $registration->final_fee_snapshot ?? $this->getFinalFeeDetails($registration);
                    $totalRequired = $feeDetails['total'] ?? 0;
                    
                    $totalPaid = $registration->payments()
                        ->where('status', 'success')
                        ->where('payment_type', 'final_fee')
                        ->sum('base_amount');
                    
                    if ($totalPaid >= $totalRequired) {
                        $registration->update([
                            'payment_status' => 'paid',
                            'registration_status' => 'completed',
                            'committee_notes' => 'Alhamdulillah, seluruh rangkaian pendaftaran dan pembayaran administrasi akhir ananda ' . ($registration->candidate_name ?? 'Ananda') . ' telah lunas diverifikasi. Selamat bergabung di Sekolah Anak Saleh!'
                        ]);
                    } else {
                        $registration->update([
                            'payment_status' => 'partially_paid',
                            'committee_notes' => 'Pembayaran administrasi akhir sebagian berhasil diterima. Silakan selesaikan sisa tanggungan pembiayaan Anda.'
                        ]);
                    }
                } else {
                    $registration->update([
                        'payment_status' => 'paid',
                        'committee_notes' => 'Pembayaran formulir pendaftaran berhasil diterima. Silakan isi dan lengkapi formulir pendaftaran Anda.'
                    ]);
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
                $payment->update([
                    'status' => strtolower($status)
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
