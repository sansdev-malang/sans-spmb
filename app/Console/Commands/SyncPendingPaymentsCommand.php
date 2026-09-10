<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Services\PaymentGatewayFactory;
use App\Services\PaymentSettlementService;
use Illuminate\Support\Facades\Log;

class SyncPendingPaymentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spmb:sync-pending-payments 
                            {--limit=25 : Maksimal transaksi yang diproses per eksekusi} 
                            {--hours=24 : Rentang usia transaksi pending yang diperiksa dalam jam}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi status transaksi PENDING secara otomatis ke Payment Gateway (Winpay / BNI SNAP BI)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $hours = (int) $this->option('hours');

        $this->info("Memulai sinkronisasi transaksi PENDING (rentang {$hours} jam terakhir, limit: {$limit})...");

        // 1. Ambil transaksi PENDING aktif dalam window waktu
        $pendingPayments = Payment::with('registration')
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($pendingPayments->isEmpty()) {
            $this->line("Tidak ada transaksi PENDING aktif yang perlu disinkronkan.");
            return 0;
        }

        $this->info("Ditemukan {$pendingPayments->count()} transaksi PENDING. Memeriksa ke Payment Gateway...");

        $settledCount = 0;
        $expiredCount = 0;
        $stillPendingCount = 0;
        $errorCount = 0;

        foreach ($pendingPayments as $payment) {
            $gatewayCode = $payment->payment_info['gateway'] ?? 'winpay';
            $reg = $payment->registration;
            $candidateName = $reg ? ($reg->candidate_name ?: 'Calon Murid') : '-';

            $this->line("-> Memeriksa Invoice: [{$payment->invoice_number}] ({$payment->payment_method} - {$candidateName})...");

            try {
                $gatewayService = PaymentGatewayFactory::make($gatewayCode ?: 'winpay');
                $result = method_exists($gatewayService, 'checkPaymentStatus')
                    ? $gatewayService->checkPaymentStatus($payment->invoice_number, $payment->payment_info ?: [])
                    : ['success' => false, 'is_paid' => false, 'status' => 'UNKNOWN'];

                if (!empty($result['is_paid'])) {
                    // Settle payment
                    PaymentSettlementService::settlePayment($payment, $result['data'] ?? [], 'cron_sync');
                    $settledCount++;
                    $this->info("   ✓ BERHASIL LUNAS! Transaksi '{$payment->invoice_number}' telah diselesaikan.");
                } elseif (($result['status'] ?? '') === 'EXPIRED') {
                    // Mark expired
                    PaymentSettlementService::expirePayment($payment, 'cron_gateway_expired');
                    $expiredCount++;
                    $this->warn("   ⚠️ KEDALUWARSA! Transaksi '{$payment->invoice_number}' ditandai expired.");
                } else {
                    $stillPendingCount++;
                    $this->line("   ⏳ Masih PENDING / Belum terbayar.");
                }
            } catch (\Throwable $e) {
                $errorCount++;
                $this->error("   ❌ Error memeriksa '{$payment->invoice_number}': " . $e->getMessage());
                Log::error('SyncPendingPaymentsCommand exception', [
                    'payment_id' => $payment->id,
                    'invoice' => $payment->invoice_number,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->newLine();
        $this->info("=== Ringkasan Sinkronisasi Transaksi ===");
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total Diperiksa', $pendingPayments->count()],
                ['Berhasil Dilunaskan (PAID)', $settledCount],
                ['Kedaluwarsa (EXPIRED)', $expiredCount],
                ['Tetap Menunggu (PENDING)', $stillPendingCount],
                ['Gagal / Error', $errorCount],
            ]
        );

        return 0;
    }
}
