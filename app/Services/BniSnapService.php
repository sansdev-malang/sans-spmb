<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BniSnapService implements PaymentGatewayInterface
{
    protected $gatewayCode;
    protected $mode;
    protected $merchantId;
    protected $terminalId;
    protected $clientId;
    protected $clientSecret;
    protected $privateKey;
    protected $baseUrl;

    public function __construct($gatewayCode = 'bni_va')
    {
        $this->gatewayCode = $gatewayCode;
        $this->mode = Setting::get($gatewayCode . '_mode', 'simulator');

        $this->merchantId = Setting::get("{$gatewayCode}_{$this->mode}_merchant_id");
        $this->terminalId = Setting::get("{$gatewayCode}_{$this->mode}_terminal_id");
        $this->clientId = Setting::get("{$gatewayCode}_{$this->mode}_client_id");
        $this->clientSecret = Setting::get("{$gatewayCode}_{$this->mode}_client_secret");
        $this->privateKey = Setting::get("{$gatewayCode}_{$this->mode}_private_key");

        // Fallbacks for older keys
        if (empty($this->clientId)) {
            $this->clientId = Setting::get("bni_{$this->mode}_client_id");
        }
        if (empty($this->clientSecret)) {
            $this->clientSecret = Setting::get("bni_{$this->mode}_client_secret");
        }

        // Endpoint URL
        $this->baseUrl = $this->mode === 'production' 
            ? 'https://api-snap.bni.co.id' 
            : 'https://sandbox.bni.co.id';
    }

    /**
     * Generate SNAP Asymmetric Signature (SHA256withRSA)
     */
    public function generateAsymmetricSignature($timestamp)
    {
        $stringToSign = $this->clientId . '|' . $timestamp;
        
        if (empty($this->privateKey)) {
            Log::warning('BNI Private Key is empty. Using fallback dummy signature.');
            return base64_encode(hash_hmac('sha256', $stringToSign, $this->clientSecret));
        }

        $privateKeyResource = openssl_pkey_get_private($this->privateKey);
        if (!$privateKeyResource) {
            Log::error('Invalid BNI Private Key format.');
            return '';
        }

        openssl_sign($stringToSign, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    /**
     * Generate SNAP Symmetric Signature (HMAC-SHA512)
     */
    public function generateSymmetricSignature($method, $endpoint, $accessToken, $bodyArray, $timestamp)
    {
        $minifiedBody = json_encode($bodyArray, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $hashedBody = strtolower(hash('sha256', $minifiedBody));
        
        $stringToSign = strtoupper($method) . ':' . $endpoint . ':' . $accessToken . ':' . $hashedBody . ':' . $timestamp;
        
        return base64_encode(hash_hmac('sha512', $stringToSign, $this->clientSecret, true));
    }

    /**
     * Request OAuth B2B Access Token from BNI
     */
    public function getAccessToken()
    {
        if ($this->mode === 'simulator') {
            return 'mock_bni_access_token_' . time();
        }

        $timestamp = date('c'); // ISO 8601
        $signature = $this->generateAsymmetricSignature($timestamp);

        $response = Http::withHeaders([
            'X-SIGNATURE' => $signature,
            'X-TIMESTAMP' => $timestamp,
            'X-CLIENT-KEY' => $this->clientId,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/api/v1.0/access-token/b2b', [
            'grantType' => 'client_credentials'
        ]);

        if ($response->successful()) {
            return $response->json('accessToken');
        }

        Log::error('BNI SNAP getAccessToken failed', ['response' => $response->body()]);
        return null;
    }

    /**
     * Create BNI SNAP transaction (VA or QRIS).
     */
    public function createPayment($amount, $invoiceNo, $method, $customerName = null, $customerPhone = null)
    {
        if ($this->mode === 'simulator') {
            return [
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'trxId' => $invoiceNo,
                    'referenceId' => 'BNI-MOCK-' . time(),
                    'virtualAccount' => '8001' . rand(10000000, 99999999),
                    'paymentUrl' => 'https://sandbox.bni.co.id/mock-payment/' . $invoiceNo,
                    'qrisString' => str_contains(strtoupper($method), 'QRIS') ? '00020101021226670014ID.CO.BNI.WWW0118936000091503300589...' : null
                ]
            ];
        }

        // Validate basic credentials
        if (empty($this->clientId) || empty($this->clientSecret)) {
            return [
                'success' => false,
                'message' => 'Kredensial BNI ' . ucfirst($this->mode) . ' (Client ID / Secret) belum dikonfigurasi di Admin Panel.'
            ];
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Gagal mendapatkan Access Token dari BNI SNAP API.'
            ];
        }

        $timestamp = date('c');
        $endpoint = '/api/v1.0/transfer-va/create-va';
        if (str_contains(strtoupper($method), 'QRIS')) {
            $endpoint = '/api/v1.0/qr/qr-mpm-generate';
        }

        // Standard SNAP request body
        $body = [
            'partnerServiceId' => ' ' . $this->merchantId, // leading space is sometimes required in SNAP
            'customerNo' => '12345678', // customer reference
            'virtualAccountNo' => $this->merchantId . rand(100000, 999999),
            'virtualAccountName' => 'SPMB Candidate',
            'trxId' => $invoiceNo,
            'totalAmount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => 'IDR'
            ],
            'additionalInfo' => [
                'invoiceNumber' => $invoiceNo
            ]
        ];

        if (str_contains(strtoupper($method), 'QRIS')) {
            $body = [
                'partnerReferenceNo' => $invoiceNo,
                'amount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'merchantId' => $this->merchantId,
                'terminalId' => $this->terminalId ?: 'T001'
            ];
        }

        $signature = $this->generateSymmetricSignature('POST', $endpoint, $accessToken, $body, $timestamp);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-SIGNATURE' => $signature,
            'X-TIMESTAMP' => $timestamp,
            'X-PARTNER-ID' => $this->merchantId,
            'X-EXTERNAL-ID' => rand(100000, 999999) . time(),
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . $endpoint, $body);

        if ($response->successful()) {
            $responseData = $response->json();
            return [
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'trxId' => $invoiceNo,
                    'referenceId' => $responseData['referenceNo'] ?? $responseData['partnerReferenceNo'] ?? 'BNI-' . time(),
                    'virtualAccount' => $responseData['virtualAccountNo'] ?? null,
                    'paymentUrl' => $responseData['paymentUrl'] ?? null,
                    'qrisString' => $responseData['qrContent'] ?? null
                ]
            ];
        }

        $errorMsg = $response->json('responseMessage') ?? $response->json('message') ?? 'Unknown Error';
        return [
            'success' => false,
            'message' => $errorMsg . ' (Status: ' . $response->status() . ')'
        ];
    }

    /**
     * Validate incoming Webhook/Callback from BNI.
     */
    public function verifyCallback($headers, $body)
    {
        if ($this->mode === 'simulator') {
            return true;
        }

        $signature = $headers['x-signature'] ?? $headers['X-SIGNATURE'] ?? '';
        $timestamp = $headers['x-timestamp'] ?? $headers['X-TIMESTAMP'] ?? '';
        $authorization = $headers['authorization'] ?? $headers['AUTHORIZATION'] ?? '';
        
        $accessToken = str_replace('Bearer ', '', $authorization);
        
        $endpoint = '/api/payments/callback'; 

        $calculatedSignature = $this->generateSymmetricSignature('POST', $endpoint, $accessToken, $body, $timestamp);

        return hash_equals($calculatedSignature, $signature);
    }
}
