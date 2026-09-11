<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class WinpayService
 * 
 * Driver integrasi Payment Gateway Winpay berstandar SNAP BI (Standar Nasional Open API Pembayaran Indonesia).
 * 
 * Service ini mendukung 3 MODE OPERASIONAL:
 * ---------------------------------------------------------------------------------------------------------
 * 1. MODE SIMULATOR (Local Offline Mock):
 *    - Diaktifkan jika `WINPAY_MODE=simulator` pada konfigurasi / .env.
 *    - Tidak memerlukan koneksi internet / tidak menembak server Winpay.
 *    - Mengembalikan data transaksi mock/dummy (VA, QRIS, E-Wallet) untuk pengujian lokal.
 *    - Callback/webhook signature otomatis di-bypass untuk kemudahan testing developer.
 * 
 * 2. MODE SANDBOX (Uji Coba Server Winpay):
 *    - Diaktifkan jika `WINPAY_MODE=sandbox`.
 *    - Base URL: `https://sandbox-snap.winpay.id`
 *    - Menggunakan kredensial sandbox (Merchant ID, Client Key, Secret, RSA Sandbox Keys).
 *    - Melakukan request HTTP nyata ke server Sandbox Winpay dengan Asymmetric Signature SHA256withRSA.
 *    - Memvalidasi webhook callback menggunakan Public Key Sandbox Winpay.
 * 
 * 3. MODE PRODUCTION (Live Server Transaksi Nyata):
 *    - Diaktifkan jika `WINPAY_MODE=production`.
 *    - Base URL: `https://snap.winpay.id`
 *    - Menggunakan kredensial production resmi hasil PKS/MOU Winpay.
 *    - Melakukan transaksi nyata dan menghasilkan nomor VA / QRIS / E-Wallet live.
 *    - Memvalidasi webhook callback menggunakan Public Key Production Winpay.
 * ---------------------------------------------------------------------------------------------------------
 */
class WinpayService implements PaymentGatewayInterface
{
    /**
     * Mode operasional aktif ('simulator', 'sandbox', atau 'production')
     * @var string
     */
    protected $mode;

    protected $merchantId;
    protected $clientKey;
    protected $privateKey;
    protected $publicKey;
    protected $baseUrl;

    public function __construct()
    {
        // Ambil mode dari Setting database (admin UI) atau fallback ke file .env (default: 'simulator')
        $this->mode = \App\Models\Setting::get('winpay_mode', env('WINPAY_MODE', 'simulator'));

        /* =========================================================================================
         * [1] KONFIGURASI MODE PRODUCTION (Live Server Winpay: https://snap.winpay.id)
         * ========================================================================================= */
        if ($this->mode === 'production') {
            $this->merchantId = \App\Models\Setting::get('winpay_production_merchant_id') 
                ?: \App\Models\Setting::get('winpay_prod_merchant_id');
            $this->clientKey = \App\Models\Setting::get('winpay_production_client_key') 
                ?: \App\Models\Setting::get('winpay_prod_client_key');
            $this->privateKey = \App\Models\Setting::get('winpay_production_private_key') 
                ?: \App\Models\Setting::get('winpay_prod_private_key');
            $this->publicKey = \App\Models\Setting::get('winpay_production_public_key') 
                ?: \App\Models\Setting::get('winpay_prod_public_key');
        } 
        /* =========================================================================================
         * [2] KONFIGURASI MODE SANDBOX (Uji Coba Server: https://sandbox-snap.winpay.id)
         * ========================================================================================= */
        elseif ($this->mode === 'sandbox') {
            $this->merchantId = \App\Models\Setting::get('winpay_sandbox_merchant_id');
            $this->clientKey = \App\Models\Setting::get('winpay_sandbox_client_key');
            $this->privateKey = \App\Models\Setting::get('winpay_sandbox_private_key');
            $this->publicKey = \App\Models\Setting::get('winpay_sandbox_public_key');
        } 
        /* =========================================================================================
         * [3] KONFIGURASI MODE SIMULATOR (Local Offline Mock - Tanpa Request Luar)
         * ========================================================================================= */
        else {
            $this->merchantId = \App\Models\Setting::get('winpay_simulator_merchant_id') 
                ?: \App\Models\Setting::get('winpay_merchant_id');
            $this->clientKey = \App\Models\Setting::get('winpay_simulator_client_key') 
                ?: \App\Models\Setting::get('winpay_client_key');
            $this->privateKey = \App\Models\Setting::get('winpay_simulator_private_key') 
                ?: \App\Models\Setting::get('winpay_private_key');
            $this->publicKey = \App\Models\Setting::get('winpay_simulator_public_key') 
                ?: \App\Models\Setting::get('winpay_public_key');
        }

        // Fallback checks ke konfigurasi .env jika database belum terisi
        if (empty($this->merchantId)) {
            $this->merchantId = env('WINPAY_MERCHANT_ID', 'MOCK_MERCHANT_ID');
        }
        if (empty($this->clientKey)) {
            $this->clientKey = env('WINPAY_CLIENT_KEY', 'MOCK_CLIENT_KEY');
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

        // Penentuan Base URL berdasarkan mode aktif
        $this->baseUrl = $this->mode === 'production' 
            ? 'https://snap.winpay.id' 
            : 'https://sandbox-snap.winpay.id';
    }

    /**
     * Parse and extract valid OpenSSL Private Key resource
     */
    protected function getPrivateKeyResource($key)
    {
        if (empty($key)) return null;

        $key = trim($key);
        $res = @openssl_pkey_get_private($key);
        if ($res) return $res;

        $clean = preg_replace('/-----BEGIN[^-]+-----/', '', $key);
        $clean = preg_replace('/-----END[^-]+-----/', '', $clean);
        $clean = str_replace(["\r", "\n", ' ', "\t"], '', $clean);

        if (!empty($clean)) {
            $pemRsa = "-----BEGIN RSA PRIVATE KEY-----\n" . chunk_split($clean, 64, "\n") . "-----END RSA PRIVATE KEY-----";
            $res = @openssl_pkey_get_private($pemRsa);
            if ($res) return $res;

            $pemPrivate = "-----BEGIN PRIVATE KEY-----\n" . chunk_split($clean, 64, "\n") . "-----END PRIVATE KEY-----";
            $res = @openssl_pkey_get_private($pemPrivate);
            if ($res) return $res;
        }

        return null;
    }

    /**
     * Parse and extract valid OpenSSL Public Key resource
     */
    protected function getPublicKeyResource($key)
    {
        if (empty($key)) return null;

        $key = trim($key);
        $res = @openssl_pkey_get_public($key);
        if ($res) return $res;

        $clean = preg_replace('/-----BEGIN[^-]+-----/', '', $key);
        $clean = preg_replace('/-----END[^-]+-----/', '', $clean);
        $clean = str_replace(["\r", "\n", ' ', "\t"], '', $clean);

        if (!empty($clean)) {
            $pemPublic = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($clean, 64, "\n") . "-----END PUBLIC KEY-----";
            $res = @openssl_pkey_get_public($pemPublic);
            if ($res) return $res;

            $pemRsa = "-----BEGIN RSA PUBLIC KEY-----\n" . chunk_split($clean, 64, "\n") . "-----END RSA PUBLIC KEY-----";
            $res = @openssl_pkey_get_public($pemRsa);
            if ($res) return $res;
        }

        return null;
    }

    /**
     * Format RSA Private Key ke string PEM valid jika belum memiliki header
     */
    protected function formatPrivateKey($key)
    {
        if (empty($key)) return '';
        $key = trim($key);
        if (str_contains($key, '-----BEGIN')) {
            return $key;
        }
        return "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
    }

    /**
     * Format RSA Public Key ke string PEM valid jika belum memiliki header
     */
    protected function formatPublicKey($key)
    {
        if (empty($key)) return '';
        $key = trim($key);
        if (str_contains($key, '-----BEGIN')) {
            return $key;
        }
        return "-----BEGIN PUBLIC KEY-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
    }

    /**
     * Generate SNAP Asymmetric Signature (SHA256withRSA) for direct transaction
     */
    public function generateAsymmetricSignature($method, $endpoint, $bodyArray, $timestamp)
    {
        if (empty($this->privateKey)) {
            Log::warning('Winpay Private Key is empty. Cannot generate asymmetric signature.');
            return '';
        }

        $privateKeyResource = $this->getPrivateKeyResource($this->privateKey);

        if (!$privateKeyResource) {
            Log::error('Invalid Winpay Private Key format. Unable to parse with OpenSSL.');
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
     * 
     * ALUR KERJA METODE:
     * - Jika mode = 'simulator'  ==> Mengembalikan Mock Response lokal (tanpa request HTTP).
     * - Jika mode = 'sandbox'    ==> Mengirim request SNAP BI ke https://sandbox-snap.winpay.id
     * - Jika mode = 'production' ==> Mengirim request SNAP BI ke https://snap.winpay.id
     * 
     * @param float $amount Nominal transaksi
     * @param string $invoiceNo Nomor referensi invoice (misal: INV-SPMB-20260902-123)
     * @param string $method Kode kanal pembayaran (MANDIRI, BRI, BNI, BCA, QRIS, DANA, SHOPEEPAY, dll)
     * @param string|null $customerName Nama murid / pembayar
     * @param string|null $customerPhone Nomor HP murid / orang tua
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function createPayment($amount, $invoiceNo, $method, $customerName = null, $customerPhone = null)
    {
        // 1. Normalisasi & mapping kode channel
        $cleanMethod = strtoupper(trim($method));
        if ($cleanMethod === 'DAN') $cleanMethod = 'DANA';
        if ($cleanMethod === 'SPA') $cleanMethod = 'SPAY';
        if ($cleanMethod === 'QRI') $cleanMethod = 'QRIS';
        if ($cleanMethod === 'ALF') $cleanMethod = 'ALFAMART';
        if ($cleanMethod === 'IND') $cleanMethod = 'INDOMARET';
        if ($cleanMethod === 'MAN') $cleanMethod = 'MANDIRI';

        $isQris = $cleanMethod === 'QRIS';
        $isEwallet = in_array($cleanMethod, ['DANA', 'SHOPEEPAY', 'SPAY', 'OVO', 'ASTRAPAY', 'ASTRA', 'SPEEDCASH', 'SC', 'GOPAY']);
        $isRetail = in_array($cleanMethod, ['ALFAMART', 'INDOMARET', 'ALF', 'IND', 'FASTPAY']);

        /* =========================================================================================
         * [A] EKSEKUSI MODE SIMULATOR LOKAL (Offline Mock - Tanpa Internet)
         * ========================================================================================= */
        if ($this->mode === 'simulator') {
            return $this->getMockPaymentResponse($amount, $invoiceNo, $cleanMethod);
        }

        /* =========================================================================================
         * [B] EKSEKUSI MODE SANDBOX / PRODUCTION (Online Real HTTP Request ke Winpay)
         * ========================================================================================= */
        $timezone = new \DateTimeZone('Asia/Jakarta');
        $now = new \DateTime('now', $timezone);
        $timestamp = $now->format('Y-m-d\TH:i:sP');

        // 1. Tentukan endpoint SNAP BI resmi Winpay berdasarkan tipe kanal
        $endpoint = '/v1.0/transfer-va/create-va';
        if ($isQris) {
            $endpoint = '/v1.0/qr/qr-mpm-generate';
        } elseif ($isEwallet) {
            $endpoint = '/v1.0/debit/payment-host-to-host';
        }

        $expiry = new \DateTime('now', $timezone);
        $expiry->modify('+24 hours');
        $expiredDate = $expiry->format('Y-m-d\TH:i:sP');

        // 2. Sanitasi nama pelanggan (Wajib Alfanumerik & Spasi, Panjang 5-24 karakter standar SNAP BI Winpay)
        $rawName = trim($customerName ?: 'Calon Murid SPMB');
        $cleanName = preg_replace('/[^a-zA-Z0-9 ]/', ' ', $rawName);
        $cleanName = preg_replace('/\s+/', ' ', $cleanName);
        $cleanName = trim($cleanName);
        if (strlen($cleanName) < 5) {
            $cleanName = str_pad($cleanName, 5, '0', STR_PAD_RIGHT);
        }
        $vaName = substr($cleanName, 0, 24);

        // Sanitasi nomor telepon pelanggan (Wajib numerik 10-15 digit)
        $phone = preg_replace('/[^0-9]/', '', $customerPhone ?: '081234567890');
        if (strlen($phone) < 10) {
            $phone = '081234567890';
        }

        // 3. Susun Request Payload sesuai spesifikasi SNAP BI masing-masing metode
        if ($isEwallet) {
            // Mapping channel E-Wallet resmi Winpay
            $ewalletChannel = $cleanMethod;
            if ($ewalletChannel === 'SHOPEEPAY') $ewalletChannel = 'SPAY';
            if ($ewalletChannel === 'ASTRAPAY') $ewalletChannel = 'ASTRA';
            if ($ewalletChannel === 'SPEEDCASH') $ewalletChannel = 'SC';

            $notifyUrl = str_replace('http://', 'https://', url('/api/payments/callback'));
            $returnUrl = str_replace('http://', 'https://', url('/dashboard'));
            
            $ewalletExpiry = new \DateTime('now', $timezone);
            $ewalletExpiry->modify('+60 minutes');
            $ewalletValidUpTo = $ewalletExpiry->format('Y-m-d\TH:i:sP');

            $body = [
                'partnerReferenceNo' => $invoiceNo,
                'amount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'urlParam' => [
                    [
                        'url' => $notifyUrl,
                        'type' => 'PAY_NOTIFY',
                        'isDeeplink' => 'N'
                    ],
                    [
                        'url' => $returnUrl,
                        'type' => 'PAY_RETURN',
                        'isDeeplink' => 'N'
                    ]
                ],
                'validUpTo' => $ewalletValidUpTo,
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
            // Closed Virtual Account (VA) & Retail
            $custNo = substr(preg_replace('/[^0-9]/', '', $invoiceNo . rand(1000, 9999)), -8);

            $body = [
                'customerNo' => $custNo,
                'virtualAccountName' => $vaName,
                'virtualAccountTrxType' => 'c', // 'c' = Closed amount (tagihan nominal pasti)
                'expiredDate' => $expiredDate,
                'trxId' => $invoiceNo,
                'totalAmount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'additionalInfo' => [
                    'channel' => $cleanMethod,
                    'invoiceNumber' => $invoiceNo
                ]
            ];
        }

        // 4. Generate SNAP Asymmetric Digital Signature (SHA256withRSA)
        $signature = $this->generateAsymmetricSignature('POST', $endpoint, $body, $timestamp);

        // 5. Kirim HTTP Request ke Server Winpay (Sandbox atau Production)
        $response = Http::timeout(30)->withHeaders([
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
            
            // Normalisasi respon E-Wallet
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

            // Normalisasi respon QRIS
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

            // Normalisasi respon Virtual Account (VA)
            if (isset($data['virtualAccountData']) || isset($data['virtualAccountNo'])) {
                $vaData = $data['virtualAccountData'] ?? $data;
                $vaNo = trim($vaData['virtualAccountNo'] ?? ($vaData['vaNo'] ?? ($vaData['payCode'] ?? '')));

                // Strip Sandbox aggregator prefix (988332) if Winpay Sandbox prepends it to the 16-digit BNI VA
                if (str_starts_with($vaNo, '988332988') && strlen($vaNo) > 16) {
                    $vaNo = substr($vaNo, 6); // Removes '988332', leaving '98878884...'
                } elseif (str_starts_with($vaNo, '332988') && strlen($vaNo) > 16) {
                    $vaNo = substr($vaNo, 3); // Removes '332', leaving '98878884...'
                }

                $normalizedData = [
                    'trxId' => $vaData['trxId'] ?? $invoiceNo,
                    'referenceId' => $vaData['additionalInfo']['contractId'] ?? ($data['referenceId'] ?? null),
                    'virtualAccountNo' => $vaNo,
                    'customerNo' => $custNo ?? ($vaData['customerNo'] ?? null),
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

        Log::error('Winpay createPayment failed', [
            'endpoint' => $endpoint,
            'request' => $body,
            'status' => $response->status(),
            'response' => $response->body()
        ]);

        $rawMsg = $response->json('message') ?? ($response->json('responseMessage') ?? '');
        $respCode = (string)($response->json('responseCode') ?? '');

        if (strtolower($rawMsg) === 'bad request' || str_contains($rawMsg, '400') || empty($rawMsg) || str_starts_with($respCode, '400')) {
            if ($isRetail) {
                $userMsg = "Kanal Gerai Ritel ({$cleanMethod}) saat ini belum aktif atau belum dapat memproses nominal tagihan ini (batas minimal kasir minimarket adalah Rp 10.000). Silakan gunakan QRIS atau DANA.";
            } elseif ($isEwallet) {
                $userMsg = "Kanal E-Wallet ({$cleanMethod}) saat ini belum aktif atau belum dapat memproses transaksi. Silakan gunakan metode pembayaran DANA atau QRIS.";
            } elseif ($isQris) {
                $userMsg = "Kanal QRIS saat ini belum dapat memproses transaksi. Silakan gunakan metode DANA atau Virtual Account.";
            } else {
                $userMsg = "Metode Virtual Account ({$cleanMethod}) belum dapat memproses nominal tagihan ini (batas minimal Virtual Account Bank adalah Rp 10.000). Silakan gunakan QRIS atau DANA.";
            }
        } else {
            $userMsg = $rawMsg;
        }

        return [
            'success' => false,
            'message' => $userMsg
        ];
    }

    /**
     * Validate incoming Webhook/Callback from Winpay
     * 
     * - Pada MODE SIMULATOR: Otomatis mengembalikan `true` (bypass signature).
     * - Pada MODE SANDBOX / PRODUCTION: Memverifikasi Asymmetric Signature menggunakan Public Key Winpay.
     */
    public function verifyCallback($headers, $body, $rawContent = null, $requestUri = null)
    {
        /* =========================================================================================
         * [A] BYPASS UNTUK MODE SIMULATOR LOKAL & DEVELOPER TEST
         * ========================================================================================= */
        if ($this->mode === 'simulator' || (app()->environment('local', 'testing') && (isset($headers['x-developer-simulator']) || isset($headers['X-Developer-Simulator']) || isset($headers['X-DEVELOPER-SIMULATOR'])))) {
            return true;
        }

        $signature = $headers['x-signature'][0] ?? $headers['X-SIGNATURE'][0] ?? $headers['x-signature'] ?? $headers['X-SIGNATURE'] ?? '';
        if (is_array($signature)) $signature = $signature[0] ?? '';
        $timestamp = $headers['x-timestamp'][0] ?? $headers['X-TIMESTAMP'][0] ?? $headers['x-timestamp'] ?? $headers['X-TIMESTAMP'] ?? '';
        if (is_array($timestamp)) $timestamp = $timestamp[0] ?? '';

        if (empty($signature) || empty($timestamp)) {
            Log::warning('Winpay Callback rejected: Missing X-SIGNATURE or X-TIMESTAMP header.');
            return false;
        }
        
        /* =========================================================================================
         * [B] VALIDASI DIGITAL SIGNATURE DENGAN PUBLIC KEY WINPAY (SANDBOX / PRODUCTION)
         * ========================================================================================= */
        if (empty($this->publicKey)) {
            Log::error('Winpay Callback rejected (Fail-Closed): Winpay Public Key is not configured on live mode.');
            return false; // Security: FAIL-CLOSED! Never allow unverified webhook in live environments.
        }

        $publicKeyResource = $this->getPublicKeyResource($this->publicKey);

        if (!$publicKeyResource) {
            Log::error('Winpay Callback rejected: Invalid Winpay Public Key PEM format.');
            return false;
        }

        $signatureBinary = base64_decode($signature);
        if ($signatureBinary === false) {
            Log::error('Winpay Callback rejected: Corrupt base64 signature.');
            return false;
        }

        // Kumpulkan semua kandidat path endpoint yang mungkin ditandatangani oleh Winpay SNAP BI
        $endpointCandidates = [
            '/api/payments/callback',
            'api/payments/callback',
            '/v1.0/transfer-va/payment',
            'v1.0/transfer-va/payment',
            '/v1.0/qr/qr-mpm-notify',
            'v1.0/qr/qr-mpm-notify',
            '/v1.0/debit/notify',
            'v1.0/debit/notify',
            '/v1.0/debit/payment-host-to-host',
            'v1.0/debit/payment-host-to-host',
            '/api/payments/callback/v1.0/transfer-va/payment',
            'api/payments/callback/v1.0/transfer-va/payment',
            '/api/payments/callback/v1.0/qr/qr-mpm-notify',
            'api/payments/callback/v1.0/qr/qr-mpm-notify',
            '/api/payments/callback/v1.0/debit/notify',
            'api/payments/callback/v1.0/debit/notify',
            '/api/payments/callback/winpay',
            'api/payments/callback/winpay',
        ];

        if (!empty($requestUri)) {
            $parsedPath = parse_url($requestUri, PHP_URL_PATH);
            if ($parsedPath) {
                $endpointCandidates[] = $parsedPath;
                $endpointCandidates[] = ltrim($parsedPath, '/');
                $endpointCandidates[] = '/' . ltrim($parsedPath, '/');

                // Ekstrak suffix SNAP BI jika ada (misal dari /api/payments/callback/v1.0/transfer-va/payment -> /v1.0/transfer-va/payment)
                if (preg_match('#(/v1\.0/.*)#', $parsedPath, $matches)) {
                    $endpointCandidates[] = $matches[1];
                    $endpointCandidates[] = ltrim($matches[1], '/');
                }
            }
            $endpointCandidates[] = $requestUri;
        }
        $endpointCandidates = array_unique(array_filter($endpointCandidates));

        // Kumpulkan kandidat payload body hash yang mungkin di-hash oleh Winpay
        $bodyCandidates = [];
        
        // 1. Raw body persis seperti diterima lewat HTTP wire (solusi paling akurat untuk SNAP BI)
        if (!empty($rawContent)) {
            $bodyCandidates[] = $rawContent;
            
            // 1b. Minified raw content (menghilangkan whitespace tanpa merusak format angka float)
            $decoded = json_decode($rawContent, true);
            if ($decoded !== null) {
                $bodyCandidates[] = json_encode($decoded, JSON_UNESCAPED_SLASHES);
                $bodyCandidates[] = json_encode($decoded);
            }
        }

        // 2. Encoded body array (fallback)
        if (!empty($body)) {
            $bodyCandidates[] = json_encode($body, JSON_UNESCAPED_SLASHES);
            $bodyCandidates[] = json_encode($body);
        }

        $bodyCandidates = array_unique(array_filter($bodyCandidates));

        // Lakukan pengujian verifikasi signature pada semua kandidat
        foreach ($endpointCandidates as $ep) {
            foreach ($bodyCandidates as $bContent) {
                $hashedBody = strtolower(bin2hex(hash('sha256', $bContent, true)));
                $stringToSign = implode(':', [
                    'POST',
                    $ep,
                    $hashedBody,
                    $timestamp
                ]);

                $verified = openssl_verify($stringToSign, $signatureBinary, $publicKeyResource, OPENSSL_ALGO_SHA256);
                if ($verified === 1) {
                    Log::info('Winpay Callback signature verified successfully', [
                        'endpoint' => $ep,
                        'timestamp' => $timestamp
                    ]);
                    return true;
                }
            }
        }

        Log::warning('Winpay Callback signature mismatch', [
            'timestamp' => $timestamp,
            'signature_snippet' => substr($signature, 0, 30) . '...',
            'endpoints_tested' => $endpointCandidates,
            'body_length' => strlen($rawContent ?: ''),
        ]);

        return false;
    }

    /**
     * Delete / Cancel Transaksi di Payment Gateway Winpay (Standar SNAP BI)
     * - QRIS: POST /v1.0/qr/qr-mpm-cancel (Service Code: 77)
     * - Virtual Account (VA) & Retail: DELETE /v1.0/transfer-va/delete-va (Service Code: 31)
     * - E-Wallet: Sesi dibatalkan secara lokal/otomatis kedaluwarsa
     *
     * @param string $invoiceNo Nomor referensi invoice (Merchant Ref / partnerReferenceNo / trxId)
     * @param array $paymentInfo Data payment_info transaksi yang tersimpan
     * @return array ['success' => bool, 'message' => string, 'data' => array|null]
     */
    public function cancelPayment($invoiceNo, $paymentInfo = [])
    {
        // 1. Pada mode simulator lokal, langsung return success
        if ($this->mode === 'simulator') {
            Log::info('Winpay cancelPayment bypassed (Simulator mode)', ['invoice' => $invoiceNo]);
            return [
                'success' => true,
                'message' => 'Transaksi pembayaran berhasil dibatalkan (Simulator)',
                'data' => null
            ];
        }

        // 2. Identifikasi tipe kanal pembayaran
        $channel = strtoupper(trim(
            $paymentInfo['channel'] 
            ?? ($paymentInfo['bankName'] 
            ?? ($paymentInfo['additionalInfo']['channel'] 
            ?? ($paymentInfo['payment_method'] ?? '')))
        ));

        $isQris = ($channel === 'QRIS') 
            || !empty($paymentInfo['qrUrl']) 
            || !empty($paymentInfo['qrContent'])
            || (isset($paymentInfo['payment_method']) && strtoupper($paymentInfo['payment_method']) === 'QRIS');

        $isEwallet = in_array($channel, ['DANA', 'SHOPEEPAY', 'SPAY', 'OVO', 'ASTRAPAY', 'ASTRA', 'SPEEDCASH', 'SC', 'GOPAY', 'LINKAJA'])
            || !empty($paymentInfo['webRedirectUrl'])
            || !empty($paymentInfo['appRedirectUrl']);

        $timezone = new \DateTimeZone('Asia/Jakarta');
        $now = new \DateTime('now', $timezone);
        $timestamp = $now->format('Y-m-d\TH:i:sP');

        $contractId = $paymentInfo['referenceId'] 
            ?? ($paymentInfo['contractId'] 
            ?? ($paymentInfo['additionalInfo']['contractId'] 
            ?? ''));

        $isRetail = in_array($channel, ['ALFAMART', 'INDOMARET', 'RETAIL', 'MODERN_RETAIL', 'ALFA', 'INDO']);

        // 3. Tangani Pembatalan E-Wallet (POST /v1.0/debit/cancel - Service Code: 57)
        if ($isEwallet) {
            $endpoint = '/v1.0/debit/cancel';
            $httpMethod = 'POST';

            $ewalletChannel = $channel;
            if ($ewalletChannel === 'SHOPEEPAY') $ewalletChannel = 'SPAY';
            if ($ewalletChannel === 'ASTRAPAY') $ewalletChannel = 'ASTRA';
            if ($ewalletChannel === 'SPEEDCASH') $ewalletChannel = 'SC';

            $body = [
                'originalPartnerReferenceNo' => $invoiceNo,
                'reason' => 'Dibatalkan oleh pendaftar / Ganti channel pembayaran',
                'additionalInfo' => array_filter([
                    'contractId' => (string) $contractId,
                    'channel' => $ewalletChannel
                ])
            ];
        } elseif ($isQris) {
            // 4. Tangani Pembatalan QRIS (POST /v1.0/qr/qr-mpm-cancel - Service Code: 77)
            $endpoint = '/v1.0/qr/qr-mpm-cancel';
            $httpMethod = 'POST';

            $body = [
                'originalPartnerReferenceNo' => $invoiceNo,
                'reason' => 'Dibatalkan oleh pendaftar / Ganti channel pembayaran',
                'additionalInfo' => array_filter([
                    'contractId' => (string) $contractId
                ])
            ];
        } else {
            // 5. Tangani Pembatalan Virtual Account (VA) & Modern Retail (DELETE /v1.0/transfer-va/delete-va - Service Code: 31)
            $endpoint = '/v1.0/transfer-va/delete-va';
            $httpMethod = 'DELETE';

            $vaNo = trim(
                $paymentInfo['virtualAccountNo'] 
                ?? ($paymentInfo['virtualAccount'] 
                ?? ($paymentInfo['vaNo'] 
                ?? ($paymentInfo['payCode'] ?? '')))
            );

            $channelCode = $channel ?: 'MANDIRI';
            if ($channelCode === 'ALFA') $channelCode = 'ALFAMART';
            if ($channelCode === 'INDO') $channelCode = 'INDOMARET';

            $body = [
                'virtualAccountNo' => $vaNo,
                'trxId' => $invoiceNo,
                'additionalInfo' => array_filter([
                    'contractId' => (string) $contractId,
                    'channel' => $channelCode
                ])
            ];
        }

        // Generate SNAP Asymmetric Digital Signature (SHA256withRSA)
        $signature = $this->generateAsymmetricSignature($httpMethod, $endpoint, $body, $timestamp);

        try {
            // Penting: Gunakan send() dengan opsi ['json' => $body] agar HTTP DELETE mengirimkan JSON request body (bukan query string)
            $response = Http::timeout(20)->withHeaders([
                'X-SIGNATURE' => $signature,
                'X-TIMESTAMP' => $timestamp,
                'X-PARTNER-ID' => $this->clientKey,
                'X-EXTERNAL-ID' => $invoiceNo,
                'CHANNEL-ID' => 'WEB',
                'Content-Type' => 'application/json',
            ])->send($httpMethod, $this->baseUrl . $endpoint, [
                'json' => $body
            ]);

            if ($response->successful()) {
                Log::info("Winpay {$httpMethod} {$endpoint} SUCCESS", [
                    'invoice' => $invoiceNo,
                    'request' => $body,
                    'response' => $response->json()
                ]);
                return [
                    'success' => true,
                    'message' => $response->json('responseMessage') ?? 'Tagihan berhasil dibatalkan di Winpay.',
                    'data' => $response->json()
                ];
            }

            Log::warning("Winpay {$httpMethod} {$endpoint} non-success response", [
                'invoice' => $invoiceNo,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => $response->json('responseMessage') ?? 'Gagal membatalkan tagihan di Winpay.',
                'data' => $response->json()
            ];
        } catch (\Throwable $e) {
            Log::error("Winpay {$httpMethod} {$endpoint} exception", [
                'invoice' => $invoiceNo,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Pengecekan / Inquiry Status Pembayaran Real-time ke Winpay SNAP BI
     *
     * Mendukung:
     * - QRIS: POST /v1.0/qr/qr-mpm-query (Service Code: 51)
     * - Virtual Account & Retail: POST /v1.0/transfer-va/inquiry-status (Service Code: 28)
     * - E-Wallet / Debit: POST /v1.0/debit/status (Service Code: 55)
     *
     * @param string $invoiceNo
     * @param array $paymentInfo
     * @return array ['success' => bool, 'is_paid' => bool, 'status' => string, 'message' => string, 'data' => array|null]
     */
    public function checkPaymentStatus($invoiceNo, $paymentInfo = [])
    {
        // 1. Tangani Mode Simulator
        if ($this->mode === 'simulator') {
            Log::info('Winpay checkPaymentStatus bypassed (Simulator mode)', ['invoice' => $invoiceNo]);
            return [
                'success' => true,
                'is_paid' => false,
                'status' => 'PENDING',
                'message' => 'Status transaksi (Simulator): Menunggu pembayaran.',
                'data' => [
                    'latestTransactionStatus' => '01',
                    'transactionStatusDesc' => 'Pending'
                ]
            ];
        }

        // 2. Identifikasi channel & tipe pembayaran
        $channel = strtoupper(trim(
            $paymentInfo['channel'] 
            ?? ($paymentInfo['bankName'] 
            ?? ($paymentInfo['additionalInfo']['channel'] 
            ?? ($paymentInfo['payment_method'] ?? '')))
        ));

        $isQris = ($channel === 'QRIS') 
            || !empty($paymentInfo['qrUrl']) 
            || !empty($paymentInfo['qrContent'])
            || (isset($paymentInfo['payment_method']) && strtoupper($paymentInfo['payment_method']) === 'QRIS');

        $isEwallet = in_array($channel, ['DANA', 'SHOPEEPAY', 'SPAY', 'OVO', 'ASTRAPAY', 'ASTRA', 'SPEEDCASH', 'SC', 'GOPAY', 'LINKAJA'])
            || !empty($paymentInfo['webRedirectUrl'])
            || !empty($paymentInfo['appRedirectUrl']);

        $timezone = new \DateTimeZone('Asia/Jakarta');
        $now = new \DateTime('now', $timezone);
        $timestamp = $now->format('Y-m-d\TH:i:sP');

        $contractId = $paymentInfo['referenceId'] 
            ?? ($paymentInfo['contractId'] 
            ?? ($paymentInfo['additionalInfo']['contractId'] 
            ?? ''));

        $httpMethod = 'POST';

        if ($isQris) {
            // [A] QRIS Query Status (POST /v1.0/qr/qr-mpm-query - Service Code: 51, serviceCode payload: "47")
            $endpoint = '/v1.0/qr/qr-mpm-query';
            $body = [
                'originalPartnerReferenceNo' => $invoiceNo,
                'serviceCode' => '47',
                'additionalInfo' => array_filter([
                    'contractId' => (string) $contractId,
                ])
            ];
            if (!empty($paymentInfo['referenceId'])) {
                $body['originalReferenceNo'] = (string) $paymentInfo['referenceId'];
            }
        } elseif ($isEwallet) {
            // [B] E-Wallet / Debit Query Status (POST /v1.0/debit/status - Service Code: 55)
            $endpoint = '/v1.0/debit/status';
            $ewalletChannel = $channel ?: 'DANA';
            if ($ewalletChannel === 'SHOPEEPAY') $ewalletChannel = 'SPAY';
            if ($ewalletChannel === 'ASTRAPAY') $ewalletChannel = 'ASTRA';
            if ($ewalletChannel === 'SPEEDCASH') $ewalletChannel = 'SC';

            $body = [
                'originalPartnerReferenceNo' => $invoiceNo,
                'additionalInfo' => array_filter([
                    'contractId' => (string) $contractId,
                    'channel' => $ewalletChannel,
                ])
            ];
            if (!empty($paymentInfo['referenceId'])) {
                $body['originalReferenceNo'] = (string) $paymentInfo['referenceId'];
            }
        } else {
            // [C] Virtual Account & Modern Retail Inquiry Status (POST /v1.0/transfer-va/status - Service Code: 26)
            $endpoint = '/v1.0/transfer-va/status';
            $vaNo = trim(
                $paymentInfo['virtualAccountNo'] 
                ?? ($paymentInfo['virtualAccount'] 
                ?? ($paymentInfo['vaNo'] 
                ?? ($paymentInfo['payCode'] ?? '')))
            );

            $channelCode = $channel ?: 'MANDIRI';
            if ($channelCode === 'ALFA') $channelCode = 'ALFAMART';
            if ($channelCode === 'INDO') $channelCode = 'INDOMARET';

            $body = [
                'virtualAccountNo' => $vaNo,
                'trxId' => $invoiceNo,
                'additionalInfo' => array_filter([
                    'contractId' => (string) $contractId,
                    'channel' => $channelCode,
                    'trxId' => $invoiceNo,
                ])
            ];
        }

        // Generate SNAP Asymmetric Digital Signature (SHA256withRSA)
        $signature = $this->generateAsymmetricSignature($httpMethod, $endpoint, $body, $timestamp);

        try {
            $response = Http::timeout(20)->withHeaders([
                'X-SIGNATURE' => $signature,
                'X-TIMESTAMP' => $timestamp,
                'X-PARTNER-ID' => $this->clientKey,
                'X-EXTERNAL-ID' => $invoiceNo,
                'CHANNEL-ID' => 'WEB',
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . $endpoint, $body);

            $respData = $response->json();
            $httpOk = $response->successful();

            Log::info("Winpay checkPaymentStatus {$endpoint} response", [
                'invoice' => $invoiceNo,
                'status_code' => $response->status(),
                'response' => $respData
            ]);

            if ($httpOk && is_array($respData)) {
                $rawStatus = $respData['latestTransactionStatus'] 
                    ?? ($respData['virtualAccountData']['paymentFlagStatus'] 
                    ?? ($respData['paymentStatus'] 
                    ?? ($respData['status'] ?? null)));

                $isPaid = false;
                $statusNormalized = 'PENDING';

                if (in_array((string)$rawStatus, ['00', 'SUCCESS', 'PAID', 'SETTLED', 'SUCCESSFUL'])) {
                    $isPaid = true;
                    $statusNormalized = 'PAID';
                } elseif (in_array((string)$rawStatus, ['02', 'EXPIRED', 'KADALUWARSA'])) {
                    $statusNormalized = 'EXPIRED';
                } elseif (in_array((string)$rawStatus, ['03', 'FAILED', 'CANCELLED', 'BATAL'])) {
                    $statusNormalized = 'CANCELLED';
                }

                $msg = $respData['responseMessage'] 
                    ?? ($respData['transactionStatusDesc'] 
                    ?? ($isPaid ? 'Pembayaran berhasil dikonfirmasi lunas.' : 'Pembayaran belum terdeteksi.'));

                return [
                    'success' => true,
                    'is_paid' => $isPaid,
                    'status' => $statusNormalized,
                    'message' => $msg,
                    'data' => $respData
                ];
            }

            return [
                'success' => false,
                'is_paid' => false,
                'status' => 'UNKNOWN',
                'message' => $respData['responseMessage'] ?? 'Gagal memeriksa status pembayaran di Winpay.',
                'data' => $respData
            ];
        } catch (\Throwable $e) {
            Log::error("Winpay checkPaymentStatus exception", [
                'invoice' => $invoiceNo,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'is_paid' => false,
                'status' => 'ERROR',
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }


    /**
     * =============================================================================================
     * [KHUSUS MODE SIMULATOR LOKAL] Return Mock Response untuk Pengujian Offline Tanpa Koneksi Internet
     * =============================================================================================
     * Method ini TIDAK PERNAH dipanggil saat mode 'sandbox' atau 'production' aktif.
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

    /**
     * Daftar Master Channel Pembayaran Resmi yang Didukung Winpay
     * Digunakan oleh fitur "Sinkronisasi Channel" di Admin Panel.
     */
    public function getPaymentMethods()
    {
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
            ['code' => 'DANA', 'name' => 'DANA', 'type' => 'E-Wallet'],
            ['code' => 'SHOPEEPAY', 'name' => 'ShopeePay', 'type' => 'E-Wallet'],
            ['code' => 'OVO', 'name' => 'OVO', 'type' => 'E-Wallet'],
            ['code' => 'ASTRAPAY', 'name' => 'AstraPay', 'type' => 'E-Wallet'],
            ['code' => 'SPEEDCASH', 'name' => 'SpeedCash', 'type' => 'E-Wallet'],
        ];
    }
}
