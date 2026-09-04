<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SpmbPaymentChannel;
use App\Models\Setting;

echo "=== PAYMENT CHANNELS & FEE CALCULATIONS TEST ===\n";

$channels = SpmbPaymentChannel::all();
echo "Total channels found: " . $channels->count() . "\n";

foreach ($channels as $c) {
    $fee350k = $c->calculateFee(350000);
    $fee5500 = $c->calculateFee(5500);
    echo sprintf(
        "[%s] %-25s | Type: %-8s | Fee: %-16s | Trx 350k: Rp %-6s | Trx 5.5k: Rp %-4s\n",
        $c->code,
        substr($c->name, 0, 25),
        $c->type,
        $c->fee_label,
        number_format($fee350k, 0, ',', '.'),
        number_format($fee5500, 0, ',', '.')
    );
}

echo "\n=== SETTINGS DEFAULT KEYS TEST ===\n";
echo "fee_winpay_va: " . Setting::get('fee_winpay_va') . "\n";
echo "fee_winpay_retail: " . Setting::get('fee_winpay_retail') . "\n";
echo "fee_winpay_qris: " . Setting::get('fee_winpay_qris') . "\n";
echo "fee_winpay_ewallet: " . Setting::get('fee_winpay_ewallet') . "\n";
echo "fee_bni_va: " . Setting::get('fee_bni_va') . "\n";
echo "fee_bni_qris: " . Setting::get('fee_bni_qris') . "\n";
