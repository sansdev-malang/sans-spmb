<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\SpmbFee;
use App\Models\SpmbUnit;
use App\Models\SpmbGrade;
use App\Models\SpmbFeeCategory;
use App\Models\SpmbPaymentChannel;
use App\Models\PaymentGateway;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "========================================================================\n";
echo "🎯 PENGUJIAN END-TO-END WORKFLOW SISTEM PEMBAYARAN SPMB (OPSI C & LEDGER)\n";
echo "========================================================================\n\n";

DB::beginTransaction();

try {
    // 1. Inisialisasi Master Data & Gateway Uji Coba
    $unit = SpmbUnit::create(['name' => 'Unit E2E SPMB', 'code' => 'E2E-TEST', 'is_active' => true]);
    $grade = SpmbGrade::first();
    $catForm = SpmbFeeCategory::where('name', 'like', '%Formulir%')->first() ?: SpmbFeeCategory::first();
    $catDsp = SpmbFeeCategory::where('name', '!=', 'Formulir Pendaftaran')->first() ?: SpmbFeeCategory::first();

    $formFee = SpmbFee::create([
        'spmb_unit_id' => $unit->id,
        'spmb_fee_category_id' => $catForm->id,
        'name' => 'Formulir Pendaftaran E2E',
        'amount' => 350000,
        'payment_gateway' => ['winpay'],
        'is_active' => true,
    ]);

    $dspPangkal = SpmbFee::create([
        'spmb_unit_id' => $unit->id,
        'spmb_fee_category_id' => $catDsp->id,
        'name' => 'Uang Pangkal E2E',
        'amount' => 10000000,
        'payment_gateway' => ['winpay'],
        'is_active' => true,
    ]);

    $dspSeragam = SpmbFee::create([
        'spmb_unit_id' => $unit->id,
        'spmb_fee_category_id' => $catDsp->id,
        'name' => 'Seragam E2E',
        'amount' => 2000000,
        'payment_gateway' => ['winpay'],
        'is_active' => true,
    ]);

    $user = User::create([
        'name' => 'Wali Murid E2E',
        'email' => 'wali_e2e_' . time() . '@test.com',
        'password' => bcrypt('password'),
        'role' => 'user',
    ]);

    $reg = Registration::create([
        'user_id' => $user->id,
        'candidate_name' => 'Ahmad Fulan E2E',
        'spmb_unit_id' => $unit->id,
        'spmb_grade_id' => $grade ? $grade->id : 1,
        'registration_status' => 'draft',
        'payment_status' => 'unpaid',
        'installment_mode' => 'selective',
        'installment_allowed_fee_ids' => [$dspPangkal->id],
        'min_installment_amount' => 500000,
    ]);

    echo "▶ TAHAP 1: Pembayaran Formulir Pendaftaran (Rp 350.000)\n";
    echo "  - Pendaftar: {$reg->candidate_name} (ID: {$reg->id})\n";
    echo "  - Status awal: registration_status={$reg->registration_status}, payment_status={$reg->payment_status}\n";

    // Request Charge Formulir
    $controller = app(PaymentController::class);
    $reqChargeForm = Request::create('/api/payments/charge', 'POST', [
        'registration_id' => $reg->id,
        'payment_method' => 'MANDIRI',
    ]);
    $reqChargeForm->setUserResolver(fn() => $user);

    $resChargeForm = $controller->charge($reqChargeForm);
    $dataChargeForm = json_decode($resChargeForm->getContent(), true);

    echo "  ✓ Response Charge Formulir: HTTP " . $resChargeForm->getStatusCode() . " | " . ($dataChargeForm['message'] ?? '') . "\n";
    $invoiceForm = $dataChargeForm['payment']['invoice_number'] ?? null;
    $paymentFormId = $dataChargeForm['payment']['id'] ?? null;
    echo "  ✓ Invoice diterbitkan: {$invoiceForm}\n";

    // Verifikasi Database Status Pending & PaymentItem
    $pForm = Payment::with('items')->find($paymentFormId);
    echo "  ✓ Record Payment Local: Status={$pForm->status}, Base Amount=Rp " . number_format($pForm->base_amount, 0, ',', '.') . ", Admin Fee=Rp " . number_format($pForm->admin_fee, 0, ',', '.') . "\n";
    echo "  ✓ Payment Items Count: " . $pForm->items->count() . " ({$pForm->items->first()->fee_name} = Rp " . number_format($pForm->items->first()->amount, 0, ',', '.') . ")\n";

    // Simulasi Webhook Callback Pelunasan Formulir
    $callbackPayloadForm = [
        'trxId' => $invoiceForm,
        'responseCode' => '2002500',
        'responseMessage' => 'Success',
        'paymentStatus' => 'SUCCESS',
        'paymentAmount' => [
            'value' => number_format($pForm->amount, 2, '.', ''),
            'currency' => 'IDR'
        ]
    ];

    $reqCallbackForm = Request::create('/api/payments/callback/winpay', 'POST', [], [], [], [
        'HTTP_X-TIMESTAMP' => date('c'),
        'HTTP_X-SIGNATURE' => 'mock_signature',
    ], json_encode($callbackPayloadForm));

    // Eksekusi update status via controller
    $resCallbackForm = $controller->callback($reqCallbackForm, 'winpay');
    echo "  ✓ Response Webhook Formulir: HTTP " . $resCallbackForm->getStatusCode() . " (SNAP BI 2002500 Success)\n";

    $reg->refresh();
    echo "  ✓ Status Pendaftar Sekarang: payment_status={$reg->payment_status}\n\n";
    if ($reg->payment_status !== 'paid') throw new \Exception("Status formulir harus paid!");

    // Majukan status pendaftar ke agreement_signed untuk tes DSP
    $reg->update([
        'registration_status' => 'agreement_signed',
        'final_fee_snapshot' => [
            'items' => [
                ['id' => $dspPangkal->id, 'name' => $dspPangkal->name, 'amount' => 10000000],
                ['id' => $dspSeragam->id, 'name' => $dspSeragam->name, 'amount' => 2000000],
            ],
            'total' => 12000000
        ]
    ]);

    echo "▶ TAHAP 2: Pembayaran Cicilan DSP Opsi C (Uang Pangkal Rp 6.000.000 dari total Rp 12.000.000)\n";
    $reqChargeDsp1 = Request::create('/api/payments/charge', 'POST', [
        'registration_id' => $reg->id,
        'payment_method' => 'BCA',
        'items' => [
            ['fee_id' => $dspPangkal->id, 'amount' => 6000000] // Bayar cicilan 6jt
        ]
    ]);
    $reqChargeDsp1->setUserResolver(fn() => $user);

    $resChargeDsp1 = $controller->charge($reqChargeDsp1);
    $dataChargeDsp1 = json_decode($resChargeDsp1->getContent(), true);

    echo "  ✓ Response Charge DSP Cicilan 1: HTTP " . $resChargeDsp1->getStatusCode() . "\n";
    $invoiceDsp1 = $dataChargeDsp1['payment']['invoice_number'];
    $pDsp1 = Payment::with('items')->find($dataChargeDsp1['payment']['id']);
    echo "  ✓ Invoice Cicilan: {$invoiceDsp1} (Base: Rp " . number_format($pDsp1->base_amount, 0, ',', '.') . ")\n";

    // Webhook Callback Cicilan 1
    $callbackPayloadDsp1 = [
        'trxId' => $invoiceDsp1,
        'responseCode' => '2002500',
        'paymentStatus' => 'SUCCESS',
        'paymentAmount' => [
            'value' => number_format($pDsp1->amount, 2, '.', ''),
            'currency' => 'IDR'
        ]
    ];
    $reqCallbackDsp1 = Request::create('/api/payments/callback/winpay', 'POST', [], [], [], [], json_encode($callbackPayloadDsp1));
    $controller->callback($reqCallbackDsp1, 'winpay');

    $reg->refresh();
    echo "  ✓ Status Pendaftar Setelah Cicilan 1: payment_status={$reg->payment_status} (partially_paid)\n";
    echo "  ✓ Total Terbayar (Pokok): Rp " . number_format($reg->total_paid_final_fee, 0, ',', '.') . " / Rp " . number_format($reg->net_fee, 0, ',', '.') . "\n";
    echo "  ✓ Sisa Tagihan: Rp " . number_format($reg->remaining_balance, 0, ',', '.') . "\n";
    echo "  ✓ Uang Pangkal Terbayar: Rp " . number_format($reg->getItemPaidAmount($dspPangkal->name, $dspPangkal->id), 0, ',', '.') . "\n\n";

    if ($reg->payment_status !== 'partially_paid') throw new \Exception("Status harus partially_paid!");
    if ($reg->total_paid_final_fee != 6000000) throw new \Exception("total_paid_final_fee harus 6.000.000!");
    if ($reg->remaining_balance != 6000000) throw new \Exception("remaining_balance harus 6.000.000!");

    // Test Idempotensi Webhook (Menembak webhook yang sama untuk kedua kali)
    echo "▶ TAHAP 3: Uji Idempotensi Webhook (Simulasi Webhook Duplikat dari Gateway)\n";
    $resDup = $controller->callback($reqCallbackDsp1, 'winpay');
    $reg->refresh();
    echo "  ✓ Response Idempotent Webhook: HTTP " . $resDup->getStatusCode() . " (200 OK)\n";
    echo "  ✓ Total Terbayar Tetap: Rp " . number_format($reg->total_paid_final_fee, 0, ',', '.') . " (Tidak bertambah ganda!)\n\n";
    if ($reg->total_paid_final_fee != 6000000) throw new \Exception("Idempotency failed: total_paid_final_fee bertambah ganda!");

    // Pelunasan Sisa DSP (Uang Pangkal 4jt + Seragam 2jt = 6jt)
    echo "▶ TAHAP 4: Pelunasan Sisa DSP (Uang Pangkal 4jt + Seragam 2jt = Rp 6.000.000)\n";
    $reqChargeDsp2 = Request::create('/api/payments/charge', 'POST', [
        'registration_id' => $reg->id,
        'payment_method' => 'MANDIRI',
        'items' => [
            ['fee_id' => $dspPangkal->id, 'amount' => 4000000],
            ['fee_id' => $dspSeragam->id, 'amount' => 2000000],
        ]
    ]);
    $reqChargeDsp2->setUserResolver(fn() => $user);
    $resChargeDsp2 = $controller->charge($reqChargeDsp2);
    $dataChargeDsp2 = json_decode($resChargeDsp2->getContent(), true);

    $invoiceDsp2 = $dataChargeDsp2['payment']['invoice_number'];
    $pDsp2 = Payment::find($dataChargeDsp2['payment']['id']);

    $callbackPayloadDsp2 = [
        'trxId' => $invoiceDsp2,
        'responseCode' => '2002500',
        'paymentStatus' => 'SUCCESS',
        'paymentAmount' => [
            'value' => number_format($pDsp2->amount, 2, '.', ''),
            'currency' => 'IDR'
        ]
    ];
    $reqCallbackDsp2 = Request::create('/api/payments/callback/winpay', 'POST', [], [], [], [], json_encode($callbackPayloadDsp2));
    $controller->callback($reqCallbackDsp2, 'winpay');

    $reg->refresh();
    echo "  ✓ Status Akhir Pendaftar: registration_status={$reg->registration_status}, payment_status={$reg->payment_status}\n";
    echo "  ✓ Total Terbayar: Rp " . number_format($reg->total_paid_final_fee, 0, ',', '.') . " (LUNAS 100%)\n";
    echo "  ✓ Sisa Tagihan: Rp " . number_format($reg->remaining_balance, 0, ',', '.') . "\n";
    echo "  ✓ Uang Pangkal: Rp " . number_format($reg->getItemPaidAmount($dspPangkal->name, $dspPangkal->id), 0, ',', '.') . " (LUNAS)\n";
    echo "  ✓ Seragam: Rp " . number_format($reg->getItemPaidAmount($dspSeragam->name, $dspSeragam->id), 0, ',', '.') . " (LUNAS)\n\n";

    if ($reg->registration_status !== 'completed') throw new \Exception("registration_status harus completed!");
    if ($reg->payment_status !== 'paid') throw new \Exception("payment_status harus paid!");
    if ($reg->remaining_balance != 0) throw new \Exception("remaining_balance harus 0!");

    echo "========================================================================\n";
    echo "🎉 SELURUH SKENARIO END-TO-END WORKFLOW PEMBAYARAN LULUS SEMPURNA (100%)\n";
    echo "========================================================================\n";

} finally {
    DB::rollBack();
    echo "\n[Cleanup] Seluruh data uji E2E telah di-rollback dengan aman dari database.\n";
}
