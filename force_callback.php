<?php
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invoice = 'INV-SPMB-20260829-4-350';
$payment = \App\Models\Payment::where('invoice_number', $invoice)->first();
if (!$payment) {
    echo "Payment not found\n";
    exit;
}

$body = [
    'trxId' => $invoice,
    'paymentRequestId' => '215584',
    'virtualAccountNo' => '888988255485255',
    'paidAmount' => [
        'value' => '354500.00',
        'currency' => 'IDR'
    ],
    'trxDateTime' => '2026-08-29T16:16:49+07:00'
];

$request = Request::create('/api/payments/callback/v1.0/transfer-va/payment', 'POST', [], [], [], [], json_encode($body));
$request->headers->set('Content-Type', 'application/json');
$request->headers->set('X-Developer-Simulator', 'true'); // bypass signature

$controller = app(\App\Http\Controllers\Api\PaymentController::class);
$response = $controller->callback($request);

echo "Status code: " . $response->getStatusCode() . "\n";
echo "Response: " . $response->getContent() . "\n";
