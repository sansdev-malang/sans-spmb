<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use App\Notifications\SpmbNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SettlePaymentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spmb:settle-payment {invoice : Nomor Invoice (contoh: INV-SPMB-20260908-22-6493AB), ID Transaksi, atau Reference ID} {--force : Paksa update meskipun status sudah success}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi dan pelunasan manual transaksi SPMB berdasarkan Invoice atau Reference ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoiceInput = trim($this->argument('invoice'));
        $force = $this->option('force');

        $this->info("Mencari data transaksi untuk: {$invoiceInput}...");

        $payment = Payment::with(['registration.unit', 'items'])->where(function ($q) use ($invoiceInput) {
            $q->where('invoice_number', $invoiceInput)
              ->orWhere('reference_id', $invoiceInput);
            if (is_numeric($invoiceInput)) {
                $q->orWhere('id', (int)$invoiceInput);
            }
        })->first();

        if (!$payment) {
            $this->error("Transaksi pembayaran dengan invoice/ID '{$invoiceInput}' tidak ditemukan.");
            return 1;
        }

        $reg = $payment->registration;
        $candidateName = $reg ? ($reg->candidate_name ?: 'Calon Murid') : '-';

        $this->table(
            ['ID', 'Invoice Number', 'Metode', 'Nominal', 'Status Saat Ini', 'Jenis Biaya', 'Nama Murid'],
            [[
                $payment->id,
                $payment->invoice_number,
                $payment->payment_method,
                'Rp ' . number_format($payment->amount, 0, ',', '.'),
                $payment->status,
                $payment->payment_type,
                $candidateName,
            ]]
        );

        if ($payment->status === 'success' && !$force) {
            $this->warn("Transaksi ini sudah berstatus 'success'. Gunakan opsi --force jika ingin memproses ulang.");
            return 0;
        }

        DB::beginTransaction();
        try {
            $currentInfo = is_array($payment->payment_info) ? $payment->payment_info : [];
            $newInfo = array_merge($currentInfo, [
                'settled_at' => now()->toIso8601String(),
                'manual_sync_by' => 'cli_artisan_settle',
            ]);

            $payment->update([
                'status' => 'success',
                'payment_info' => $newInfo,
            ]);

            if ($reg) {
                if ($payment->payment_type === 'final_fee') {
                    $totalRequired = $reg->net_fee;
                    $totalPaid = $reg->total_paid_final_fee;

                    if ($totalPaid >= $totalRequired) {
                        $reg->update([
                            'payment_status' => 'paid',
                            'registration_status' => 'completed',
                            'committee_notes' => 'Alhamdulillah, seluruh rangkaian pendaftaran dan pembayaran administrasi akhir ananda ' . ($reg->candidate_name ?? 'Ananda') . ' telah lunas diverifikasi. Selamat bergabung di Sekolah Anak Saleh!',
                        ]);
                    } else {
                        $reg->update([
                            'payment_status' => 'partially_paid',
                            'committee_notes' => 'Pembayaran administrasi akhir sebagian berhasil diterima. Silakan selesaikan sisa tanggungan pembiayaan Anda.',
                        ]);
                    }
                } else {
                    $reg->update([
                        'payment_status' => 'paid',
                        'committee_notes' => 'Pembayaran formulir pendaftaran berhasil diterima. Silakan isi dan lengkapi formulir pendaftaran Anda.',
                    ]);
                }
            }

            DB::commit();
            $this->info("✓ Transaksi '{$payment->invoice_number}' BERHASIL dilunaskan!");
            $this->info("  - Status Payment: " . $payment->fresh()->status);
            $this->info("  - Status Pembayaran Murid: " . ($reg ? $reg->fresh()->payment_status : '-'));
            $this->info("  - Status Pendaftaran Murid: " . ($reg ? $reg->fresh()->registration_status : '-'));

            // Notifikasi
            if ($reg) {
                try {
                    $admins = User::getAdminsForUnit($reg->spmb_unit_id ?? null);
                    if ($admins->isNotEmpty()) {
                        Notification::send($admins, new SpmbNotification([
                            'title' => 'Pembayaran Berhasil Diverifikasi',
                            'message' => 'Pembayaran ' . ($payment->payment_type === 'final_fee' ? 'DSP/Administrasi Akhir' : 'Formulir') . ' untuk calon murid "' . $reg->candidate_name . '" telah berhasil diverifikasi (Invoice: ' . $payment->invoice_number . ').',
                            'url' => route('admin.payments.data') . '?search=' . urlencode($reg->candidate_name),
                            'type' => 'success',
                            'spmb_unit_id' => $reg->spmb_unit_id,
                            'registration_id' => $reg->id,
                        ]));
                    }
                } catch (\Throwable $notifEx) {
                    $this->warn("Catatan: Notifikasi sistem gagal dikirim: " . $notifEx->getMessage());
                }
            }

            return 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Gagal memproses pelunasan transaksi: " . $e->getMessage());
            return 1;
        }
    }
}
