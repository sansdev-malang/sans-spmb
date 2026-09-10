<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use App\Notifications\SpmbNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PaymentSettlementService
{
    /**
     * Settle a payment atomically, update registration status, and dispatch notifications.
     *
     * @param Payment $payment
     * @param array $payload
     * @param string $source
     * @return array ['success' => bool, 'message' => string, 'payment' => Payment]
     */
    public static function settlePayment(Payment $payment, array $payload = [], string $source = 'inquiry_check')
    {
        if ($payment->status === 'success') {
            return [
                'success' => true,
                'already_settled' => true,
                'message' => 'Transaksi sudah berstatus lunas sebelumnya.',
                'payment' => $payment
            ];
        }

        $notificationsToDispatch = [];
        $registration = $payment->registration;

        DB::beginTransaction();
        try {
            $currentInfo = is_array($payment->payment_info) ? $payment->payment_info : [];
            $newInfo = array_merge($currentInfo, [
                'settle_source' => $source,
                'settled_at' => now()->toIso8601String(),
            ]);
            if (!empty($payload)) {
                $newInfo['settle_payload'] = $payload;
            }

            $payment->update([
                'status' => 'success',
                'payment_info' => $newInfo
            ]);

            if ($registration) {
                if ($payment->payment_type === 'final_fee') {
                    $totalRequired = $registration->net_fee;
                    $totalPaid = $registration->total_paid_final_fee;

                    if ($totalPaid >= $totalRequired) {
                        $registration->update([
                            'payment_status' => 'paid',
                            'registration_status' => 'completed',
                            'committee_notes' => 'Alhamdulillah, seluruh rangkaian pendaftaran dan pembayaran administrasi akhir ananda ' . ($registration->candidate_name ?? 'Ananda') . ' telah lunas diverifikasi. Selamat bergabung di Sekolah Anak Saleh!'
                        ]);

                        $notificationsToDispatch[] = [
                            'type' => 'dsp_full',
                            'registration' => $registration,
                            'totalPaid' => $totalPaid,
                        ];
                    } else {
                        $registration->update([
                            'payment_status' => 'partially_paid',
                            'committee_notes' => 'Pembayaran administrasi akhir sebagian berhasil diterima. Silakan selesaikan sisa tanggungan pembiayaan Anda.'
                        ]);

                        $notificationsToDispatch[] = [
                            'type' => 'dsp_partial',
                            'registration' => $registration,
                            'paymentAmount' => $payment->amount,
                            'totalPaid' => $totalPaid,
                            'totalRequired' => $totalRequired,
                        ];
                    }
                } else {
                    $registration->update([
                        'payment_status' => 'paid',
                        'committee_notes' => 'Pembayaran formulir pendaftaran berhasil diterima. Silakan isi dan lengkapi formulir pendaftaran Anda.'
                    ]);

                    $notificationsToDispatch[] = [
                        'type' => 'form_fee',
                        'registration' => $registration,
                        'paymentAmount' => $payment->amount,
                    ];
                }
            }

            DB::commit();
            Log::info('Payment settlement completed successfully', [
                'invoice' => $payment->invoice_number,
                'source' => $source
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Payment settlement error', [
                'invoice' => $payment->invoice_number,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'already_settled' => false,
                'message' => 'Gagal memproses pelunasan transaksi: ' . $e->getMessage(),
                'payment' => $payment
            ];
        }

        // Dispatch notifications decoupled from database transaction
        foreach ($notificationsToDispatch as $nData) {
            try {
                $reg = $nData['registration'];
                $admins = User::getAdminsForUnit($reg->spmb_unit_id ?? null);

                if ($nData['type'] === 'dsp_full') {
                    if ($admins->isNotEmpty()) {
                        Notification::send($admins, new SpmbNotification([
                            'title' => 'Pembayaran DSP Lunas',
                            'message' => 'Pembayaran Uang Pangkal (DSP) calon murid "' . $reg->candidate_name . '" telah lunas (Total: Rp ' . number_format($nData['totalPaid'], 0, ',', '.') . ').',
                            'url' => route('admin.payments.data') . '?search=' . urlencode($reg->candidate_name),
                            'type' => 'success',
                            'spmb_unit_id' => $reg->spmb_unit_id,
                            'registration_id' => $reg->id,
                        ]));
                    }

                    if ($reg->user) {
                        $reg->user->notify(new SpmbNotification([
                            'title' => 'Pembayaran DSP Lunas',
                            'message' => 'Alhamdulillah, Uang Pangkal (DSP) untuk ananda "' . $reg->candidate_name . '" telah lunas diverifikasi. Selamat bergabung di Sekolah Anak Saleh!',
                            'url' => route('dashboard.result', $reg->id),
                            'type' => 'success',
                            'spmb_unit_id' => $reg->spmb_unit_id,
                            'registration_id' => $reg->id,
                        ]));
                    }
                } elseif ($nData['type'] === 'dsp_partial') {
                    if ($admins->isNotEmpty()) {
                        Notification::send($admins, new SpmbNotification([
                            'title' => 'Pembayaran DSP Sebagian',
                            'message' => 'Diterima pembayaran DSP sebagian untuk calon murid "' . $reg->candidate_name . '" sebesar Rp ' . number_format($nData['paymentAmount'], 0, ',', '.') . ' (Masuk: Rp ' . number_format($nData['totalPaid'], 0, ',', '.') . ' / ' . number_format($nData['totalRequired'], 0, ',', '.') . ').',
                            'url' => route('admin.payments.data') . '?search=' . urlencode($reg->candidate_name),
                            'type' => 'info',
                            'spmb_unit_id' => $reg->spmb_unit_id,
                            'registration_id' => $reg->id,
                        ]));
                    }

                    if ($reg->user) {
                        $reg->user->notify(new SpmbNotification([
                            'title' => 'Pembayaran DSP Sebagian',
                            'message' => 'Pembayaran DSP sebagian untuk ananda "' . $reg->candidate_name . '" sebesar Rp ' . number_format($nData['paymentAmount'], 0, ',', '.') . ' telah diverifikasi. Silakan selesaikan sisa tanggungan pembiayaan Anda.',
                            'url' => route('dashboard.result', $reg->id),
                            'type' => 'info',
                            'spmb_unit_id' => $reg->spmb_unit_id,
                            'registration_id' => $reg->id,
                        ]));
                    }
                } elseif ($nData['type'] === 'form_fee') {
                    if ($admins->isNotEmpty()) {
                        Notification::send($admins, new SpmbNotification([
                            'title' => 'Pembayaran Formulir Sukses',
                            'message' => 'Pembayaran formulir untuk calon murid "' . $reg->candidate_name . '" sebesar Rp ' . number_format($nData['paymentAmount'], 0, ',', '.') . ' telah lunas.',
                            'url' => route('admin.payments') . '?search=' . urlencode($reg->candidate_name),
                            'type' => 'success',
                            'spmb_unit_id' => $reg->spmb_unit_id,
                            'registration_id' => $reg->id,
                        ]));
                    }

                    if ($reg->user) {
                        $reg->user->notify(new SpmbNotification([
                            'title' => 'Pembayaran Formulir Sukses',
                            'message' => 'Alhamdulillah, pembayaran formulir pendaftaran untuk ananda "' . $reg->candidate_name . '" telah sukses diverifikasi. Silakan isi dan lengkapi formulir pendaftaran Anda.',
                            'url' => route('dashboard.form', $reg->id),
                            'type' => 'success',
                            'spmb_unit_id' => $reg->spmb_unit_id,
                            'registration_id' => $reg->id,
                        ]));
                    }
                }
            } catch (\Throwable $notifEx) {
                Log::error('Non-critical error sending payment settlement notifications', ['error' => $notifEx->getMessage()]);
            }
        }

        return [
            'success' => true,
            'already_settled' => false,
            'message' => 'Pembayaran berhasil dikonfirmasi dan dilunaskan.',
            'payment' => $payment->fresh(),
            'registration' => $registration ? $registration->fresh() : null
        ];
    }

    /**
     * Mark payment as expired and refresh registration payment status.
     *
     * @param Payment $payment
     * @param string $reason
     * @return bool
     */
    public static function expirePayment(Payment $payment, string $reason = 'expired')
    {
        DB::beginTransaction();
        try {
            $currentInfo = is_array($payment->payment_info) ? $payment->payment_info : [];
            $newInfo = array_merge($currentInfo, [
                'expired_at' => now()->toIso8601String(),
                'expire_reason' => $reason
            ]);

            $payment->update([
                'status' => 'expired',
                'payment_info' => $newInfo
            ]);

            $registration = $payment->registration;
            if ($registration) {
                $hasSuccess = $registration->payments()->where('status', 'success')->exists();
                $registration->update([
                    'payment_status' => $hasSuccess ? 'partially_paid' : 'unpaid'
                ]);
            }

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to expire payment', ['invoice' => $payment->invoice_number, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
