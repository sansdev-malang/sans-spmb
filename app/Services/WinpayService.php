<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WinpayService implements PaymentGatewayInterface
{
    protected $mode;
    protected $merchantId;
    protected $clientKey;
    protected $clientSecret;
    protected $privateKey;
    protected $publicKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->mode = \App\Models\Setting::get('winpay_mode', env('WINPAY_MODE', 'simulator'));

        if ($this->mode === 'production') {
            $this->merchantId = \App\Models\Setting::get('winpay_prod_merchant_id');
            $this->clientKey = \App\Models\Setting::get('winpay_prod_client_key');
            $this->clientSecret = \App\Models\Setting::get('winpay_prod_client_secret');
            $this->privateKey = \App\Models\Setting::get('winpay_prod_private_key');
            $this->publicKey = \App\Models\Setting::get('winpay_prod_public_key');
        } elseif ($this->mode === 'sandbox') {
            $this->merchantId = \App\Models\Setting::get('winpay_sandbox_merchant_id');
            $this->clientKey = \App\Models\Setting::get('winpay_sandbox_client_key');
            $this->clientSecret = \App\Models\Setting::get('winpay_sandbox_client_secret');
            $this->privateKey = \App\Models\Setting::get('winpay_sandbox_private_key');
            $this->publicKey = \App\Models\Setting::get('winpay_sandbox_public_key');
        } else {
            // Simulator
            $this->merchantId = \App\Models\Setting::get('winpay_merchant_id');
            $this->clientKey = \App\Models\Setting::get('winpay_client_key');
            $this->clientSecret = \App\Models\Setting::get('winpay_client_secret');
            $this->privateKey = \App\Models\Setting::get('winpay_private_key');
            $this->publicKey = \App\Models\Setting::get('winpay_public_key');
        }

        // Fallback checks
        if (empty($this->merchantId)) {
            $this->merchantId = env('WINPAY_MERCHANT_ID', 'MOCK_MERCHANT_ID');
        }
        if (empty($this->clientKey)) {
            $this->clientKey = env('WINPAY_CLIENT_KEY', 'MOCK_CLIENT_KEY');
        }
        if (empty($this->clientSecret)) {
            $this->clientSecret = env('WINPAY_CLIENT_SECRET', 'MOCK_CLIENT_SECRET');
        }
        
        if (empty($this->privateKey)) {
            $this->privateKey = env('WINPAY_PRIVATE_KEY');
            if (empty($this->privateKey)) {
                $privateKeyPath = env('WINPAY_PRIVATE_KEY_PATH', storage_path('app/winpay/private_key.pem'));
                if (file_exists($privateKeyPath)) {
                    $this->privateKey = file_get_contents($privateKeyPath);
                }
            }
        }

        if (empty($this->publicKey)) {
            $this->publicKey = env('WINPAY_PUBLIC_KEY');
            if (empty($this->publicKey)) {
                $publicKeyPath = env('WINPAY_PUBLIC_KEY_PATH', storage_path('app/winpay/winpay_public.pem'));
                if (file_exists($publicKeyPath)) {
                    $this->publicKey = file_get_contents($publicKeyPath);
                }
            }
        }

        // Sandbox/Production endpoint url
        $this->baseUrl = $this->mode === 'production' 
            ? 'https://snap.winpay.id' 
            : 'https://sandbox-snap.winpay.id';
    }

    /**
     * Generate SNAP Asymmetric Signature (SHA256withRSA) for direct transaction
     */
    public function generateAsymmetricSignature($method, $endpoint, $bodyArray, $timestamp)
    {
        if (empty($this->privateKey)) {
            Log::warning('Winpay Private Key is empty. Using fallback dummy signature.');
            return base64_encode(hash_hmac('sha256', 'mock_string_to_sign', $this->clientSecret));
        }

        $privateKeyResource = openssl_pkey_get_private($this->privateKey);
        if (!$privateKeyResource) {
            Log::error('Invalid Winpay Private Key format.');
            return '';
        }

        $minifiedBody = json_encode($bodyArray, JSON_UNESCAPED_SLASHES);
        $hashedBody = strtolower(bin2hex(hash('sha256', $minifiedBody, true)));

        $stringToSign = implode(':', [
            strtoupper($method),
            $endpoint,
            $hashedBody,
            $timestamp
        ]);

        openssl_sign($stringToSign, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    /**
     * Create SNAP Transaction (VA, QRIS, or E-Wallet)
     */
    public function createPayment($amount, $invoiceNo, $method, $customerName = null, $customerPhone = null)
    {
        $isQris = strtoupper($method) === 'QRIS';
        $isEwallet = in_array(strtoupper($method), ['DANA', 'SHOPEEPAY', 'SPAY', 'OVO', 'ASTRAPAY', 'ASTRA', 'SPEEDCASH', 'SC']);

        if ($this->mode === 'simulator') {
            return $this->getMockPaymentResponse($amount, $invoiceNo, $method);
        }

        $timezone = new \DateTimeZone('Asia/Jakarta');
        $now = new \DateTime('now', $timezone);
        $timestamp = $now->format('Y-m-d\TH:i:sP');

        // Select correct endpoint based on channel type
        $endpoint = '/v1.0/transfer-va/create-va';
        if ($isQris) {
            $endpoint = '/v1.0/qr/qr-mpm-generate';
        } elseif ($isEwallet) {
            $endpoint = '/v1.0/debit/payment-host-to-host';
        }

        $expiry = new \DateTime('now', $timezone);
        $expiry->modify('+24 hours');
        $expiredDate = $expiry->format('Y-m-d\TH:i:sP');

        // Sanitize name for virtualAccountName / customerName (Length 5-24, alphanumeric, spaces, dashes)
        $rawName = trim($customerName ?: 'Calon Siswa SPMB');
        $cleanName = preg_replace('/[^a-zA-Z0-9 _-]/', '', $rawName);
        if (strlen($cleanName) < 5) {
            $cleanName = str_pad($cleanName, 5, ' ');
        }
        $vaName = substr($cleanName, 0, 24);

        if ($isEwallet) {
            $ewalletChannel = strtoupper($method);
            if ($ewalletChannel === 'SHOPEEPAY') $ewalletChannel = 'SPAY';
            if ($ewalletChannel === 'ASTRAPAY') $ewalletChannel = 'ASTRA';
            if ($ewalletChannel === 'SPEEDCASH') $ewalletChannel = 'SC';

            $phone = preg_replace('/[^0-9]/', '', $customerPhone ?: '081234567890');
            if (strlen($phone) < 10) {
                $phone = '081234567890';
            }

            $body = [
                'partnerReferenceNo' => $invoiceNo,
                'amount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'urlParam' => [
                    [
                        'url' => url('/api/payments/callback'),
                        'type' => 'PAY_NOTIFY',
                        'isDeeplink' => 'N'
                    ],
                    [
                        'url' => url('/dashboard'),
                        'type' => 'PAY_RETURN',
                        'isDeeplink' => 'N'
                    ]
                ],
                'validUpTo' => $expiredDate,
                'additionalInfo' => [
                    'channel' => $ewalletChannel,
                    'customerPhone' => $phone,
                    'customerName' => $vaName
                ]
            ];
        } elseif ($isQris) {
            $body = [
                'partnerReferenceNo' => $invoiceNo,
                'amount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'validityPeriod' => $expiredDate,
                'additionalInfo' => [
                    'isStatic' => false
                ]
            ];
        } else {
            // Standard SNAP request body structure for Closed Virtual Account
            $body = [
                'virtualAccountName' => $vaName,
                'virtualAccountTrxType' => 'c', // Closed (one-off)
                'expiredDate' => $expiredDate,
                'trxId' => $invoiceNo,
                'totalAmount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'additionalInfo' => [
                    'channel' => strtoupper($method),
                    'invoiceNumber' => $invoiceNo
                ]
            ];
        }

        $signature = $this->generateAsymmetricSignature('POST', $endpoint, $body, $timestamp);

        $response = Http::withHeaders([
            'X-SIGNATURE' => $signature,
            'X-TIMESTAMP' => $timestamp,
            'X-PARTNER-ID' => $this->clientKey,
            'X-EXTERNAL-ID' => $invoiceNo,
            'CHANNEL-ID' => 'WEB',
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . $endpoint, $body);

        if ($response->successful()) {
            $data = $response->json();
            \Illuminate\Support\Facades\Log::info('Winpay createPayment SUCCESS', [
                'endpoint' => $endpoint,
                'request' => $body,
                'response' => $data
            ]);
            
            // Normalize E-Wallet response
            if ($isEwallet) {
                $normalizedData = [
                    'partnerReferenceNo' => $data['partnerReferenceNo'] ?? $invoiceNo,
                    'trxId' => $data['partnerReferenceNo'] ?? $invoiceNo,
                    'referenceId' => $data['additionalInfo']['contractId'] ?? ($data['contractId'] ?? null),
                    'webRedirectUrl' => $data['webRedirectUrl'] ?? ($data['redirectUrl'] ?? null),
                    'appRedirectUrl' => $data['appRedirectUrl'] ?? null,
                    'paymentUrl' => $data['webRedirectUrl'] ?? ($data['appRedirectUrl'] ?? null),
                    'channel' => $data['additionalInfo']['channel'] ?? $method,
                    'status' => 'PENDING',
                    'message' => $data['responseMessage'] ?? 'Success'
                ];
                return [
                    'success' => true,
                    'data' => $normalizedData
                ];
            }

            // Normalize QRIS response
            if ($isQris) {
                $qrUrl = $data['qrUrl'] ?? ($data['qrData'] ?? null);
                $qrContent = $data['qrContent'] ?? null;
                $normalizedData = [
                    'partnerReferenceNo' => $data['partnerReferenceNo'] ?? $invoiceNo,
                    'referenceId' => $data['additionalInfo']['contractId'] ?? ($data['referenceId'] ?? null),
                    'qrUrl' => $qrUrl,
                    'qrContent' => $qrContent,
                    'status' => 'PENDING',
                    'message' => $data['responseMessage'] ?? 'Success'
                ];
                return [
                    'success' => true,
                    'data' => $normalizedData
                ];
            }

            // Normalize VA response
            if (isset($data['virtualAccountData']) || isset($data['virtualAccountNo'])) {
                $vaData = $data['virtualAccountData'] ?? $data;
                $normalizedData = [
                    'trxId' => $vaData['trxId'] ?? $invoiceNo,
                    'referenceId' => $vaData['additionalInfo']['contractId'] ?? ($data['referenceId'] ?? null),
                    'virtualAccountNo' => trim($vaData['virtualAccountNo'] ?? ($vaData['vaNo'] ?? ($vaData['payCode'] ?? ''))),
                    'virtualAccountName' => $vaData['virtualAccountName'] ?? $vaName,
                    'bankName' => $vaData['additionalInfo']['channel'] ?? $method,
                    'status' => 'PENDING',
                    'message' => $data['responseMessage'] ?? 'Success'
                ];
                return [
                    'success' => true,
                    'data' => $normalizedData
                ];
            }

            return [
                'success' => true,
                'data' => $data
            ];
        }

        Log::error('Winpay createPayment failed', ['response' => $response->body()]);
        return [
            'success' => false,
            'message' => $response->json('message') ?? ($response->json('responseMessage') ?? 'Payment creation failed')
        ];
    }

    /**
     * Validate incoming Webhook/Callback from Winpay
     */
    public function verifyCallback($headers, $body)
    {
        if ($this->mode === 'simulator') {
            return true;
        }

        $signature = $headers['x-signature'][0] ?? $headers['X-SIGNATURE'][0] ?? $headers['x-signature'] ?? $headers['X-SIGNATURE'] ?? '';
        if (is_array($signature)) $signature = $signature[0] ?? '';
        $timestamp = $headers['x-timestamp'][0] ?? $headers['X-TIMESTAMP'][0] ?? $headers['x-timestamp'] ?? $headers['X-TIMESTAMP'] ?? '';
        if (is_array($timestamp)) $timestamp = $timestamp[0] ?? '';
        
        // Bypass verification if it's a simulated developer callback
        $devSim = $headers['x-developer-simulator'][0] ?? $headers['X-DEVELOPER-SIMULATOR'][0] ?? $headers['x-developer-simulator'] ?? $headers['X-DEVELOPER-SIMULATOR'] ?? '';
        if (is_array($devSim)) $devSim = $devSim[0] ?? '';
        if ($devSim === 'true') {
            Log::info('Developer simulator request detected, bypassing signature verification.');
            return true;
        }
        
        if (empty($this->publicKey)) {
            Log::warning('Winpay Public Key is empty, skipping signature verification.');
            return true; 
        }

        $publicKeyResource = openssl_pkey_get_public($this->publicKey);
        if (!$publicKeyResource) {
            Log::error('Invalid Winpay Public Key format.');
            return false;
        }

        // standard path for callback
        $endpoint = '/api/payments/callback'; 
        
        $minifiedBody = json_encode($body, JSON_UNESCAPED_SLASHES);
        $hashedBody = strtolower(bin2hex(hash('sha256', $minifiedBody, true)));

        $stringToSign = implode(':', [
            'POST',
            $endpoint,
            $hashedBody,
            $timestamp
        ]);

        $signatureBinary = base64_decode($signature);
        
        $verified = openssl_verify($stringToSign, $signatureBinary, $publicKeyResource, OPENSSL_ALGO_SHA256);
        
        return $verified === 1;
    }

    /**
     * Return Mock Response for Sandbox testing in Simulator Mode
     */
    private function getMockPaymentResponse($amount, $invoiceNo, $method)
    {
        $refId = 'MOCK-WINPAY-' . strtoupper(bin2hex(random_bytes(4)));

        if ($method === 'QRIS') {
            return [
                'success' => true,
                'data' => [
                    'partnerReferenceNo' => $invoiceNo,
                    'referenceId' => $refId,
                    'qrContent' => '00020101021226300016ID.CO.WINPAY.WWW01189360000000000000005204599953033605802ID5912Sekolah Anak Saleh6007Bandung61054011162070703A0150120123456789012345678901', // mock QRIS content
                    'amount' => [
                        'value' => number_format($amount, 2, '.', ''),
                        'currency' => 'IDR'
                    ],
                    'status' => 'PENDING',
                    'message' => 'QRIS generated successfully (Simulator)'
                ]
            ];
        }

        // Virtual Account (VA) Mock
        $bankName = in_array(strtoupper($method), ['MANDIRI', 'BRI', 'BNI', 'BCA']) ? strtoupper($method) : 'MANDIRI';
        $vaNumber = '889900' . rand(10000000, 99999999);

        return [
            'success' => true,
            'data' => [
                'trxId' => $invoiceNo,
                'referenceId' => $refId,
                'virtualAccountNo' => $vaNumber,
                'virtualAccountName' => 'SPMB ' . $invoiceNo,
                'bankName' => $bankName,
                'totalAmount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'status' => 'PENDING',
                'message' => 'Virtual Account created successfully (Simulator)'
            ]
        ];
    }

    public function getPaymentMethods()
    {
        // In a real API integration, this calls Winpay API endpoint:
        // /signature-service/v1.0/get-payment-methods
        // Here we simulate the list of channels returned by Winpay
        return [
            ['code' => 'MANDIRI', 'name' => 'Mandiri Virtual Account', 'type' => 'Virtual Account'],
            ['code' => 'BCA', 'name' => 'BCA Virtual Account', 'type' => 'Virtual Account'],
            ['code' => 'BNI', 'name' => 'BNI Virtual Account', 'type' => 'Virtual Account'],
            ['code' => 'BRI', 'name' => 'BRI Virtual Account', 'type' => 'Virtual Account'],
            ['code' => 'PERMATA', 'name' => 'Permata Virtual Account', 'type' => 'Virtual Account'],
            ['code' => 'BSI', 'name' => 'BSI Virtual Account', 'type' => 'Virtual Account'],
            ['code' => 'MUAMALAT', 'name' => 'Muamalat Virtual Account', 'type' => 'Virtual Account'],
            ['code' => 'CIMB', 'name' => 'CIMB Niaga Virtual Account', 'type' => 'Virtual Account'],
            ['code' => 'SINARMAS', 'name' => 'Sinarmas Virtual Account', 'type' => 'Virtual Account'],
            ['code' => 'BNC', 'name' => 'BNC Virtual Account', 'type' => 'Virtual Account'],
            ['code' => 'QRIS', 'name' => 'QRIS', 'type' => 'QR Code Payment'],
        ];
    }
}
