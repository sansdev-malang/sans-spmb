<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\WinpayService;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "=== TEST VERIFIKASI SIGNATURE & CALLBACK WINPAY VA ===\n\n";

// 1. Generate RSA key pair for testing
$config = [
    'config' => 'C:/Program Files/Herd/resources/app.asar.unpacked/resources/bin/openssl.cnf',
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA
];
$res = openssl_pkey_new($config);
if (!$res) {
    // Fallback if Herd config path differs
    $res = openssl_pkey_new();
}
openssl_pkey_export($res, $privKey, null, $config);
$pubDetails = openssl_pkey_get_details($res);
$pubKey = $pubDetails['key'] ?? '';

Setting::set('winpay_mode', 'production');
Setting::set('winpay_production_public_key', $pubKey);

$service = new WinpayService();

$body = [
    'partnerServiceId' => '98888',
    'customerNo' => '12345678',
    'virtualAccountNo' => '9888812345678',
    'virtualAccountName' => 'SPMB Siswa Test',
    'trxId' => 'INV-SPMB-20260908-22-6493AE',
    'paidAmount' => ['value' => '500000.00', 'currency' => 'IDR'],
    'totalAmount' => ['value' => '500000.00', 'currency' => 'IDR'],
    'trxDateTime' => '2026-09-08T09:24:26+07:00',
    'referenceNo' => '533353465',
    'additionalInfo' => ['channel' => 'BRI']
];

$rawContent = json_encode($body);
$timestamp = '2026-09-08T09:24:26+07:00';
$endpoint = '/v1.0/transfer-va/payment'; // Standard SNAP BI endpoint signed by Winpay
$hashedBody = strtolower(bin2hex(hash('sha256', $rawContent, true)));
$stringToSign = "POST:{$endpoint}:{$hashedBody}:{$timestamp}";
openssl_sign($stringToSign, $sig, $privKey, OPENSSL_ALGO_SHA256);
$sigB64 = base64_encode($sig);

$headers = [
    'x-signature' => [$sigB64],
    'x-timestamp' => [$timestamp],
    'x-partner-id' => ['WINPAY-CLIENT-KEY']
];

// Request received at /api/payments/callback/v1.0/transfer-va/payment (from screenshot)
$requestUri = '/api/payments/callback/v1.0/transfer-va/payment';
$verified = $service->verifyCallback($headers, $body, $rawContent, $requestUri);

echo "1. Validasi Asymmetric Signature SNAP BI: " . ($verified ? "BERHASIL (TRUE)" : "GAGAL (FALSE)") . "\n";

// 2. Test Callback Controller Response
DB::beginTransaction();
try {
    $user = User::first() ?: User::factory()->create();
    $reg = Registration::create([
        'user_id' => $user->id,
        'candidate_name' => 'Calon Siswa Winpay Test',
        'registration_status' => 'draft',
        'payment_status' => 'pending',
    ]);

    $payment = Payment::create([
        'registration_id' => $reg->id,
        'invoice_number' => 'INV-SPMB-20260908-22-6493AE',
        'amount' => 500000,
        'base_amount' => 495500,
        'admin_fee' => 4500,
        'payment_method' => 'BRI',
        'status' => 'pending',
        'payment_type' => 'registration_fee',
        'payment_info' => [
            'virtualAccountNo' => '9888812345678',
            'bankName' => 'BRI'
        ]
    ]);

    $controller = new PaymentController($service);
    $req = Request::create($requestUri, 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_SIGNATURE' => $sigB64,
        'HTTP_X_TIMESTAMP' => $timestamp,
        'HTTP_X_PARTNER_ID' => 'WINPAY-CLIENT-KEY',
    ], $rawContent);

    $response = $controller->callback($req);
    $respData = json_decode($response->getContent(), true);

    echo "2. Response Status Code: " . $response->getStatusCode() . "\n";
    echo "3. Response Body:\n" . json_encode($respData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    
    $isAckCorrect = ($respData['responseCode'] ?? '') === '2002500' && isset($respData['virtualAccountData']);
    echo "\n4. Verifikasi Response Format SNAP BI: " . ($isAckCorrect ? "BERHASIL (2002500 + virtualAccountData)" : "GAGAL") . "\n";

    $payment->refresh();
    $reg->refresh();

    echo "5. Verifikasi Status di Database SPMB:\n";
    echo "   - Payment Status: {$payment->status} (Expected: success)\n";
    echo "   - Registration Payment Status: {$reg->payment_status} (Expected: paid)\n";
    echo "   - Settled At: " . ($payment->payment_info['settled_at'] ?? 'None') . "\n";

    if ($payment->status === 'success' && $reg->payment_status === 'paid') {
        echo "   ✓ DATABASE SPMB BERHASIL TERUPDATE MENJADI LUNAS!\n";
    } else {
        echo "   ✗ DATABASE SPMB GAGAL TERUPDATE!\n";
    }

    DB::rollBack();
} catch (\Throwable $e) {
    DB::rollBack();
    echo "Error during test: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
