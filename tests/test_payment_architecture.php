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
use Illuminate\Support\Facades\DB;

echo "=== MEMULAI TEST ARSITEKTUR PEMBAYARAN (OPSI C & LEDGER) ===\n\n";

DB::beginTransaction();

try {
    // 1. Setup Data Uji dengan Unit Terisolasi
    $user = User::first() ?: User::factory()->create();
    $unit = SpmbUnit::create(['name' => 'Unit Uji Coba Ledger', 'code' => 'TEST-UNIT', 'is_active' => true]);
    $grade = SpmbGrade::first();
    $category = SpmbFeeCategory::where('name', '!=', 'Formulir Pendaftaran')->first() ?: SpmbFeeCategory::first();

    // Buat master SpmbFee untuk unit uji coba
    $feePangkal = SpmbFee::create([
        'spmb_unit_id' => $unit->id,
        'spmb_fee_category_id' => $category->id,
        'name' => 'Uang Pangkal Test',
        'amount' => 15000000,
        'is_active' => true,
    ]);

    $feeSeragam = SpmbFee::create([
        'spmb_unit_id' => $unit->id,
        'spmb_fee_category_id' => $category->id,
        'name' => 'Seragam Test',
        'amount' => 2000000,
        'is_active' => true,
    ]);

    $feeKegiatan = SpmbFee::create([
        'spmb_unit_id' => $unit->id,
        'spmb_fee_category_id' => $category->id,
        'name' => 'Kegiatan Test',
        'amount' => 3000000,
        'is_active' => true,
    ]);

    echo "1. Membuat data pendaftaran uji...\n";
    $reg = Registration::create([
        'user_id' => $user->id,
        'candidate_name' => 'Calon Siswa Test Ledger',
        'spmb_unit_id' => $unit->id,
        'spmb_grade_id' => $grade ? $grade->id : 1,
        'registration_status' => 'agreement_signed',
        'payment_status' => 'unpaid',
        'final_fee_snapshot' => [
            'items' => [
                ['id' => $feePangkal->id, 'name' => $feePangkal->name, 'amount' => 15000000],
                ['id' => $feeSeragam->id, 'name' => $feeSeragam->name, 'amount' => 2000000],
                ['id' => $feeKegiatan->id, 'name' => $feeKegiatan->name, 'amount' => 3000000],
            ],
            'total' => 20000000
        ],
        'discount_mode' => 'none',
        'discount_amount' => 0,
        'installment_mode' => 'selective',
        'installment_allowed_fee_ids' => [$feePangkal->id, $feeKegiatan->id], // Uang pangkal & kegiatan boleh dicicil
        'min_installment_amount' => 500000,
    ]);

    echo "   Registration ID: {$reg->id}, Total Tagihan: Rp " . number_format($reg->net_fee, 0, ',', '.') . "\n\n";

    // 2. Simulasi Pembayaran Cicilan 1 (Opsi C: Uang Pangkal 7jt + Kegiatan 1jt)
    echo "2. Simulasi Pembayaran Cicilan 1 (Uang Pangkal 7jt + Kegiatan 1jt)...\n";
    $p1 = Payment::create([
        'registration_id' => $reg->id,
        'invoice_number' => 'INV-TEST-001',
        'amount' => 8004500, // Total transfer termasuk admin fee 4500
        'base_amount' => 8000000,
        'admin_fee' => 4500,
        'payment_method' => 'MANDIRI',
        'payment_type' => 'final_fee',
        'status' => 'success',
    ]);

    PaymentItem::create([
        'payment_id' => $p1->id,
        'spmb_fee_id' => $feePangkal->id,
        'fee_name' => $feePangkal->name,
        'amount' => 7000000,
    ]);

    PaymentItem::create([
        'payment_id' => $p1->id,
        'spmb_fee_id' => $feeKegiatan->id,
        'fee_name' => $feeKegiatan->name,
        'amount' => 1000000,
    ]);

    // Verifikasi Akuntansi Cicilan 1
    $reg->refresh();
    $totalPaid = $reg->total_paid_final_fee;
    $remaining = $reg->remaining_balance;
    $paidUangPangkal = $reg->getItemPaidAmount($feePangkal->name, $feePangkal->id);
    $paidKegiatan = $reg->getItemPaidAmount($feeKegiatan->name, $feeKegiatan->id);
    $paidSeragam = $reg->getItemPaidAmount($feeSeragam->name, $feeSeragam->id);

    echo "   ✓ Total Terbayar (Pokok): Rp " . number_format($totalPaid, 0, ',', '.') . " (Expected: 8.000.000, Admin fee tidak masuk pokok!)\n";
    echo "   ✓ Sisa Tagihan: Rp " . number_format($remaining, 0, ',', '.') . " (Expected: 12.000.000)\n";
    echo "   ✓ Terbayar Uang Pangkal: Rp " . number_format($paidUangPangkal, 0, ',', '.') . " (Expected: 7.000.000)\n";
    echo "   ✓ Terbayar Kegiatan: Rp " . number_format($paidKegiatan, 0, ',', '.') . " (Expected: 1.000.000)\n";
    echo "   ✓ Terbayar Seragam: Rp " . number_format($paidSeragam, 0, ',', '.') . " (Expected: 0)\n\n";

    if ($totalPaid != 8000000) throw new \Exception("total_paid_final_fee mismatch: {$totalPaid}");
    if ($remaining != 12000000) throw new \Exception("remaining_balance mismatch: {$remaining}");
    if ($paidUangPangkal != 7000000) throw new \Exception("paidUangPangkal mismatch: {$paidUangPangkal}");
    if ($paidKegiatan != 1000000) throw new \Exception("paidKegiatan mismatch: {$paidKegiatan}");
    if ($paidSeragam != 0) throw new \Exception("paidSeragam mismatch: {$paidSeragam}");

    // 3. Simulasi Pembayaran Cicilan 2 (Pelunasan Sisa)
    echo "3. Simulasi Pembayaran Cicilan 2 (Pelunasan Uang Pangkal 8jt, Seragam 2jt, Kegiatan 2jt)...\n";
    $p2 = Payment::create([
        'registration_id' => $reg->id,
        'invoice_number' => 'INV-TEST-002',
        'amount' => 12004500,
        'base_amount' => 12000000,
        'admin_fee' => 4500,
        'payment_method' => 'BCA',
        'payment_type' => 'final_fee',
        'status' => 'success',
    ]);

    PaymentItem::create(['payment_id' => $p2->id, 'spmb_fee_id' => $feePangkal->id, 'fee_name' => $feePangkal->name, 'amount' => 8000000]);
    PaymentItem::create(['payment_id' => $p2->id, 'spmb_fee_id' => $feeSeragam->id, 'fee_name' => $feeSeragam->name, 'amount' => 2000000]);
    PaymentItem::create(['payment_id' => $p2->id, 'spmb_fee_id' => $feeKegiatan->id, 'fee_name' => $feeKegiatan->name, 'amount' => 2000000]);

    $reg->refresh();
    $totalPaidAfter = $reg->total_paid_final_fee;
    $remainingAfter = $reg->remaining_balance;
    $paidUangPangkalAfter = $reg->getItemPaidAmount($feePangkal->name, $feePangkal->id);
    $paidKegiatanAfter = $reg->getItemPaidAmount($feeKegiatan->name, $feeKegiatan->id);
    $paidSeragamAfter = $reg->getItemPaidAmount($feeSeragam->name, $feeSeragam->id);

    echo "   ✓ Total Terbayar Setelah Pelunasan: Rp " . number_format($totalPaidAfter, 0, ',', '.') . " (Expected: 20.000.000)\n";
    echo "   ✓ Sisa Tagihan: Rp " . number_format($remainingAfter, 0, ',', '.') . " (Expected: 0 - LUNAS)\n";
    echo "   ✓ Uang Pangkal Lunas: Rp " . number_format($paidUangPangkalAfter, 0, ',', '.') . " (Expected: 15.000.000)\n";
    echo "   ✓ Seragam Lunas: Rp " . number_format($paidSeragamAfter, 0, ',', '.') . " (Expected: 2.000.000)\n";
    echo "   ✓ Kegiatan Lunas: Rp " . number_format($paidKegiatanAfter, 0, ',', '.') . " (Expected: 3.000.000)\n\n";

    if ($totalPaidAfter != 20000000) throw new \Exception("totalPaidAfter mismatch: {$totalPaidAfter}");
    if ($remainingAfter != 0) throw new \Exception("remainingAfter mismatch: {$remainingAfter}");
    if ($paidUangPangkalAfter != 15000000) throw new \Exception("paidUangPangkalAfter mismatch");
    if ($paidSeragamAfter != 2000000) throw new \Exception("paidSeragamAfter mismatch");
    if ($paidKegiatanAfter != 3000000) throw new \Exception("paidKegiatanAfter mismatch");

    // 4. Verifikasi Backward Compatibility dengan Data Legacy (Tanpa payment_items)
    echo "4. Verifikasi Kompatibilitas Data Legacy (Transaksi lama dengan payment_info JSON)...\n";
    $pLegacy = Payment::create([
        'registration_id' => $reg->id,
        'invoice_number' => 'INV-LEGACY-001',
        'amount' => 5004500,
        'base_amount' => 5000000,
        'admin_fee' => 4500,
        'payment_method' => 'BRI',
        'payment_type' => 'final_fee',
        'payment_info' => [
            'selected_items' => [
                ['name' => 'Item Legacy Khusus', 'amount' => 5000000]
            ]
        ],
        'status' => 'success',
    ]);

    $paidLegacy = $reg->getItemPaidAmount('Item Legacy Khusus');
    echo "   ✓ Terbayar Item Legacy: Rp " . number_format($paidLegacy, 0, ',', '.') . " (Expected: 5.000.000)\n\n";
    if ($paidLegacy != 5000000) throw new \Exception("paidLegacy mismatch: {$paidLegacy}");

    echo "🎉 SEMUA PENGUJIAN AKUNTANSI & RELASI LEDGER PEMBAYARAN BERHASIL 100% TANPA KESALAHAN!\n";

} finally {
    DB::rollBack(); // Rollback transaksi pengujian agar database bersih
    echo "\n[Cleanup] Transaksi pengujian telah di-rollback dengan aman. Database tetap bersih.\n";
}
