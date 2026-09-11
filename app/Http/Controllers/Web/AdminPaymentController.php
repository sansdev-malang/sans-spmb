<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\SpmbPeriod;
use App\Models\SpmbFeeCategory;
use App\Models\SpmbFee;
use App\Models\SpmbUnit;
use App\Models\SpmbWave;
use App\Models\SpmbPaymentChannel;
use App\Services\SimpleXlsxService;
use Illuminate\Support\Str;

class AdminPaymentController extends Controller
{
    /**
     * Display candidate billing and DSP fee management.
     */
    public function data(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });
        
        // Base query for candidate billing (Khusus calon murid yang telah lolos seleksi / masuk tahap daftar ulang DSP)
        $query = Registration::scopedByAdmin()
            ->with(['unit', 'grade', 'classProgram', 'wave', 'type', 'payments', 'extraServices'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed']);

        // Filter stats query
        $baseStatsQuery = (clone $query);
        if ($request->filled('unit_id')) {
            $baseStatsQuery->where('spmb_unit_id', $request->unit_id);
        }

        $allCands = (clone $baseStatsQuery)->get();
        $totalCandidates = $allCands->count();
        $totalGross = $allCands->sum(fn($c) => $c->getGrossFee());
        $totalDiscount = $allCands->sum(fn($c) => $c->total_discount);
        $totalNet = $allCands->sum(fn($c) => $c->net_fee);
        $totalPaid = $allCands->sum(fn($c) => $c->total_paid_final_fee);
        $totalRemaining = $allCands->sum(fn($c) => $c->remaining_balance);
        
        $totalLunas = $allCands->filter(fn($c) => $c->remaining_balance <= 0 && $c->net_fee > 0 && $c->total_paid_final_fee > 0)->count();
        $totalSebagian = $allCands->filter(fn($c) => $c->total_paid_final_fee > 0 && $c->remaining_balance > 0)->count();
        $totalBelumBayar = $allCands->filter(fn($c) => $c->total_paid_final_fee <= 0)->count();
        $totalDiskon = $allCands->filter(fn($c) => $c->total_discount > 0)->count();
        $totalCicilan = $allCands->filter(fn($c) => in_array($c->installment_mode, ['all', 'selective']))->count();

        $stats = [
            'candidate_count' => $totalCandidates,
            'gross_revenue' => $totalGross,
            'discount_sum' => $totalDiscount,
            'net_revenue' => $totalNet,
            'paid_sum' => $totalPaid,
            'remaining_sum' => $totalRemaining,
            'lunas_count' => $totalLunas,
            'sebagian_count' => $totalSebagian,
            'belum_bayar_count' => $totalBelumBayar,
            'diskon_count' => $totalDiskon,
            'cicilan_count' => $totalCicilan,
        ];

        // Search by Candidate Name, ID, Phone, Parent Name
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%" . ltrim(preg_replace('/[^0-9]/', '', $search), '0') . "%")
                  ->orWhere('parent_phone', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('mother_name', 'like', "%{$search}%")
                  ->orWhereHas('payments', function($sq) use ($search) {
                      $sq->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('reference_id', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Unit/Jenjang
        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        // Quick Status Tabs Filter
        if ($request->filled('status')) {
            $st = $request->status;
            if ($st === 'lunas') {
                $candIds = $allCands->filter(fn($c) => $c->remaining_balance <= 0 && $c->net_fee > 0 && $c->total_paid_final_fee > 0)->pluck('id');
                $query->whereIn('id', $candIds);
            } elseif ($st === 'sebagian') {
                $candIds = $allCands->filter(fn($c) => $c->total_paid_final_fee > 0 && $c->remaining_balance > 0)->pluck('id');
                $query->whereIn('id', $candIds);
            } elseif ($st === 'belum_bayar') {
                $candIds = $allCands->filter(fn($c) => $c->total_paid_final_fee <= 0)->pluck('id');
                $query->whereIn('id', $candIds);
            } elseif ($st === 'diskon') {
                $candIds = $allCands->filter(fn($c) => $c->total_discount > 0)->pluck('id');
                $query->whereIn('id', $candIds);
            } elseif ($st === 'cicilan') {
                $query->whereIn('installment_mode', ['all', 'selective']);
            }
        }

        // Filter by Wave
        if ($request->filled('wave_id')) {
            $baseStatsQuery->where('spmb_wave_id', $request->wave_id);
            $query->where('spmb_wave_id', $request->wave_id);
        }

        // Filter by Kebijakan Diskon
        if ($request->filled('discount_mode')) {
            $query->where('discount_mode', $request->discount_mode);
        }

        // Filter by Kebijakan Cicilan
        if ($request->filled('installment_mode')) {
            $query->where('installment_mode', $request->installment_mode);
        }

        // Per page limit
        $perPage = intval($request->get('per_page', 10));
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $registrations = $query->latest()->paginate($perPage)->withQueryString();

        $units = SpmbUnit::orderBy('id', 'asc')->get();
        if (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $units = $units->where('id', auth()->user()->spmb_unit_id);
        }
        $waves = SpmbWave::orderBy('id', 'asc')->get();

        return view('admin.payment-data', compact('registrations', 'stats', 'units', 'waves'));
    }

    /**
     * Build base query for payment history based on filters.
     */
    protected function getPaymentHistoryQuery(Request $request, $selectedPeriodId)
    {
        $query = Payment::scopedByAdmin()
            ->with(['registration.unit', 'registration.grade', 'registration.wave', 'registration.type', 'registration.classProgram', 'items'])
            ->whereHas('registration', function($q) use ($selectedPeriodId) {
                $q->where('spmb_period_id', $selectedPeriodId);
            });

        // Search by Invoice, Reference ID, Candidate Name, or Gateway Info
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('reference_id', 'like', "%{$search}%")
                  ->orWhere('payment_info->virtualAccountNo', 'like', "%{$search}%")
                  ->orWhere('payment_info->trxId', 'like', "%{$search}%")
                  ->orWhere('payment_info->callback_payload->originalReferenceNo', 'like', "%{$search}%")
                  ->orWhere('payment_info->callback_payload->referenceNo', 'like', "%{$search}%")
                  ->orWhereHas('registration', function($sq) use ($search) {
                      $sq->where('candidate_name', 'like', "%{$search}%");
                  });
            });
        }

        $isSpamView = ($request->get('view') === 'spam');

        // Filter by Status & View (Spam vs Active)
        if ($isSpamView) {
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            } else {
                $query->whereNotIn('status', ['success', 'pending']);
            }
        } else {
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            } else {
                $query->whereIn('status', ['success', 'pending']);
            }
        }

        // Filter by Unit/Jenjang
        if ($request->filled('unit_id')) {
            $query->whereHas('registration', function($q) use ($request) {
                $q->where('spmb_unit_id', $request->unit_id);
            });
        }

        // Filter by Transaction Time / Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by Payment Method
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        // Filter by Jenis Biaya (SpmbFeeCategory)
        if ($request->filled('category_id')) {
            $category = SpmbFeeCategory::find($request->category_id);
            if ($category) {
                $isFormulir = str_contains(strtolower($category->name), 'formulir') || str_contains(strtolower($category->name), 'pendaftaran');
                $feeNames = SpmbFee::where('spmb_fee_category_id', $category->id)->pluck('name');
                
                $query->where(function($q) use ($isFormulir, $feeNames) {
                    if ($isFormulir) {
                        $q->where('payment_type', 'registration_fee');
                    }
                    
                    if ($feeNames->isNotEmpty()) {
                        $q->orWhereHas('registration', function($sq) use ($feeNames) {
                            $sq->where(function($ssq) use ($feeNames) {
                                foreach ($feeNames as $name) {
                                    if (str_contains($name, 'TK A')) {
                                        $ssq->orWhere('admission_level', 'TK A');
                                    } elseif (str_contains($name, 'TK B')) {
                                        $ssq->orWhere('admission_level', 'TK B');
                                    } elseif (str_contains($name, 'SD')) {
                                        $ssq->orWhere('admission_level', 'SD');
                                    } elseif (str_contains($name, 'SMP')) {
                                        $ssq->orWhere('admission_level', 'SMP');
                                    } elseif (str_contains($name, 'SMA')) {
                                        $ssq->orWhere('admission_level', 'SMA');
                                    } else {
                                        $ssq->orWhere('admission_level', 'like', "%{$name}%");
                                    }
                                }
                            });
                        });
                    }
                });
            }
        }

        // Filter by SpmbFee (Nama Biaya)
        if ($request->filled('fee_id')) {
            $targetFee = SpmbFee::find($request->fee_id);
            if ($targetFee) {
                $feeName = $targetFee->name;
                $query->whereHas('registration', function($q) use ($feeName) {
                    if (str_contains($feeName, 'TK A')) {
                        $q->where('admission_level', 'TK A');
                    } elseif (str_contains($feeName, 'TK B')) {
                        $q->where('admission_level', 'TK B');
                    } elseif (str_contains($feeName, 'SD')) {
                        $q->where('admission_level', 'SD');
                    } elseif (str_contains($feeName, 'SMP')) {
                        $q->where('admission_level', 'SMP');
                    } elseif (str_contains($feeName, 'SMA')) {
                        $q->where('admission_level', 'SMA');
                    } else {
                        $q->where('admission_level', 'like', "%{$feeName}%");
                    }
                });
            }
        }

        // Filter by Wave
        if ($request->filled('wave_id')) {
            $query->whereHas('registration', function($q) use ($request) {
                $q->where('spmb_wave_id', $request->wave_id);
            });
        }

        return $query;
    }

    /**
     * Display payment transactions log.
     */
    public function index(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });
        
        $query = $this->getPaymentHistoryQuery($request, $selectedPeriodId);

        $isSpamView = ($request->get('view') === 'spam');

        // Per page limit
        $perPage = intval($request->get('per_page', 10));
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $payments = $query->latest()->paginate($perPage)->withQueryString();

        // Count total spam/cancelled transactions for badge
        $spamCountQuery = Payment::scopedByAdmin()
            ->whereHas('registration', function($q) use ($selectedPeriodId) {
                $q->where('spmb_period_id', $selectedPeriodId);
            })
            ->whereNotIn('status', ['success', 'pending']);
        
        if ($request->filled('unit_id')) {
            $spamCountQuery->whereHas('registration', function($q) use ($request) {
                $q->where('spmb_unit_id', $request->unit_id);
            });
        }
        $spamCount = $spamCountQuery->count();

        $units = SpmbUnit::orderBy('id', 'asc')->get();
        if (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $units = $units->where('id', auth()->user()->spmb_unit_id);
        }
        $waves = SpmbWave::orderBy('id', 'asc')->get();
        $channels = \App\Models\SpmbPaymentChannel::orderBy('id', 'asc')->get();

        return view('admin.payment-history', compact('payments', 'units', 'waves', 'channels', 'spamCount', 'isSpamView'));
    }

    /**
     * Check single payment transaction status from Admin Panel
     */
    public function checkStatus($id)
    {
        $payment = Payment::with('registration')->findOrFail($id);
        $gatewayCode = $payment->payment_info['gateway'] ?? 'winpay';

        try {
            $gatewayService = \App\Services\PaymentGatewayFactory::make($gatewayCode ?: 'winpay');
            $result = method_exists($gatewayService, 'checkPaymentStatus')
                ? $gatewayService->checkPaymentStatus($payment->invoice_number, $payment->payment_info ?: [])
                : ['success' => false, 'is_paid' => false, 'status' => 'UNKNOWN', 'message' => 'Pengecekan status tidak didukung oleh gateway ini.'];

            if (!empty($result['is_paid'])) {
                \App\Services\PaymentSettlementService::settlePayment($payment, $result['data'] ?? [], 'admin_check');
                $msg = 'Alhamdulillah! Transaksi [' . $payment->invoice_number . '] terkonfirmasi LUNAS dari bank.';
                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json(['success' => true, 'is_paid' => true, 'status' => 'PAID', 'message' => $msg]);
                }
                return redirect()->back()->with('success', $msg);
            }

            if (($result['status'] ?? '') === 'EXPIRED') {
                \App\Services\PaymentSettlementService::expirePayment($payment, 'admin_gateway_expired');
                $msg = 'Transaksi [' . $payment->invoice_number . '] terdeteksi telah KEDALUWARSA di bank.';
                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json(['success' => true, 'is_paid' => false, 'status' => 'EXPIRED', 'message' => $msg]);
                }
                return redirect()->back()->with('warning', $msg);
            }

            $msg = 'Status transaksi [' . $payment->invoice_number . ']: Menunggu Pembayaran (Belum ada mutasi masuk).';
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'is_paid' => false, 'status' => 'PENDING', 'message' => $msg]);
            }
            return redirect()->back()->with('info', $msg);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Admin checkStatus error', ['payment_id' => $id, 'error' => $e->getMessage()]);
            $msg = 'Gagal memeriksa status ke bank: ' . $e->getMessage();
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }
            return redirect()->back()->with('error', $msg);
        }
    }

    /**
     * Bulk sync all pending payments in the last 48 hours from Admin Panel
     */
    public function syncPending(Request $request)
    {
        $pendingPayments = Payment::with('registration')
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subHours(48))
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get();

        if ($pendingPayments->isEmpty()) {
            $msg = 'Tidak ada transaksi berstatus PENDING aktif saat ini.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'count' => 0, 'message' => $msg]);
            }
            return redirect()->back()->with('info', $msg);
        }

        $settled = 0;
        $expired = 0;
        $pending = 0;

        foreach ($pendingPayments as $payment) {
            $gatewayCode = $payment->payment_info['gateway'] ?? 'winpay';
            try {
                $gatewayService = \App\Services\PaymentGatewayFactory::make($gatewayCode ?: 'winpay');
                $result = method_exists($gatewayService, 'checkPaymentStatus')
                    ? $gatewayService->checkPaymentStatus($payment->invoice_number, $payment->payment_info ?: [])
                    : ['is_paid' => false, 'status' => 'UNKNOWN'];

                if (!empty($result['is_paid'])) {
                    \App\Services\PaymentSettlementService::settlePayment($payment, $result['data'] ?? [], 'admin_bulk_sync');
                    $settled++;
                } elseif (($result['status'] ?? '') === 'EXPIRED') {
                    \App\Services\PaymentSettlementService::expirePayment($payment, 'admin_bulk_expired');
                    $expired++;
                } else {
                    $pending++;
                }
            } catch (\Throwable $e) {
                // skip on error
            }
        }

        $msg = "Sinkronisasi selesai! {$settled} transaksi berhasil dilunaskan, {$expired} kedaluwarsa, {$pending} masih menunggu.";
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'settled' => $settled, 'expired' => $expired, 'pending' => $pending, 'message' => $msg]);
        }
        return redirect()->back()->with('success', $msg);
    }

    /**
     * Cancel pending payment transaction from Admin Panel
     */
    public function cancelPayment($id)
    {
        $payment = Payment::with('registration')->findOrFail($id);

        if ($payment->status !== 'pending') {
            $msg = 'Hanya transaksi berstatus PENDING yang dapat dibatalkan.';
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $result = \App\Services\PaymentSettlementService::cancelPayment($payment, 'admin_manual_cancel');

        if ($result['success']) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => $result['message']]);
            }
            return redirect()->back()->with('success', $result['message']);
        }

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => false, 'message' => $result['message']], 500);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Export filtered candidate billing and DSP fee data to Excel (.xlsx).
     */
    public function export(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });
        
        $query = Registration::scopedByAdmin()
            ->with(['unit', 'grade', 'classProgram', 'wave', 'type', 'payments', 'extraServices'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed']);

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%" . ltrim(preg_replace('/[^0-9]/', '', $search), '0') . "%")
                  ->orWhere('parent_phone', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('mother_name', 'like', "%{$search}%")
                  ->orWhereHas('payments', function($sq) use ($search) {
                      $sq->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('reference_id', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Unit
        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        // Filter Wave
        if ($request->filled('wave_id')) {
            $query->where('spmb_wave_id', $request->wave_id);
        }

        // Filter Discount Mode
        if ($request->filled('discount_mode')) {
            $query->where('discount_mode', $request->discount_mode);
        }

        // Filter Installment Mode
        if ($request->filled('installment_mode')) {
            $query->where('installment_mode', $request->installment_mode);
        }

        $allCands = (clone $query)->get();

        // Filter Status Tab
        if ($request->filled('status')) {
            $st = $request->status;
            if ($st === 'lunas') {
                $candIds = $allCands->filter(fn($c) => $c->remaining_balance <= 0 && $c->net_fee > 0 && $c->total_paid_final_fee > 0)->pluck('id');
                $query->whereIn('id', $candIds);
            } elseif ($st === 'sebagian') {
                $candIds = $allCands->filter(fn($c) => $c->total_paid_final_fee > 0 && $c->remaining_balance > 0)->pluck('id');
                $query->whereIn('id', $candIds);
            } elseif ($st === 'belum_bayar') {
                $candIds = $allCands->filter(fn($c) => $c->total_paid_final_fee <= 0)->pluck('id');
                $query->whereIn('id', $candIds);
            } elseif ($st === 'diskon') {
                $candIds = $allCands->filter(fn($c) => $c->total_discount > 0)->pluck('id');
                $query->whereIn('id', $candIds);
            } elseif ($st === 'cicilan') {
                $query->whereIn('installment_mode', ['all', 'selective']);
            }
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        $unitCode = 'ALL';
        if ($request->filled('unit_id')) {
            $u = SpmbUnit::find($request->unit_id);
            if ($u) $unitCode = strtoupper($u->code ?: Str::slug($u->name));
        } elseif (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $u = SpmbUnit::find(auth()->user()->spmb_unit_id);
            if ($u) $unitCode = strtoupper($u->code ?: Str::slug($u->name));
        }

        $period = SpmbPeriod::find($selectedPeriodId);
        $periodLabel = $period ? Str::slug($period->name ?? $period->year) : 'SPMB';
        $filename = 'Data-Rincian-Tagihan-DSP-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.xlsx';

        $tuitionFeeLabel = SpmbFeeCategory::getTuitionCategoryName();

        $columns = [
            'No.',
            'No. Registrasi',
            'Tanggal Masuk',
            'Tahun Ajaran',
            'Unit Sekolah',
            'Tingkat / Kelas',
            'Gelombang',
            'Jalur Masuk',
            'Program Kelas',
            'Layanan Tambahan',
            'Nama Lengkap Siswa',
            'Jenis Kelamin',
            'Nama Orang Tua / Wali',
            'No. WhatsApp Orang Tua',
            'Rincian Komponen Biaya DSP',
            'Total Tagihan ' . $tuitionFeeLabel . ' Bruto (Rp)',
            'Diskon / Keringanan (Rp)',
            'Keterangan / Alasan Diskon',
            'Total Tagihan ' . $tuitionFeeLabel . ' Netto (Rp)',
            'Total ' . $tuitionFeeLabel . ' Terbayar (Rp)',
            'Sisa Piutang ' . $tuitionFeeLabel . ' (Rp)',
            'Persentase Pelunasan (%)',
            'Status Pelunasan ' . $tuitionFeeLabel,
            'Kebijakan Cicilan',
            'Tahapan Registrasi',
        ];

        $rows = [];
        $i = 1;
        foreach ($registrations as $c) {
            $gross = $c->getGrossFee();
            $discount = $c->total_discount;
            $net = $c->net_fee;
            $paid = $c->total_paid_final_fee;
            $remaining = $c->remaining_balance;
            $percent = $net > 0 ? round(($paid / $net) * 100, 1) : 0;

            $statusText = 'BELUM BAYAR';
            if ($c->is_dispensation) {
                $statusText = 'DISPENSASI';
            } elseif ($remaining <= 0 && $net > 0 && $paid > 0) {
                $statusText = 'LUNAS';
            } elseif ($paid > 0 && $remaining > 0) {
                $statusText = 'SEBAGIAN / CICILAN';
            }

            $installmentPolicy = match($c->installment_mode) {
                'all' => 'Cicil Semua Komponen',
                'selective' => 'Cicil Komponen Tertentu',
                default => 'Non-Cicil (Sekaligus)'
            };

            $genderLabel = '-';
            if ($c->gender) {
                $g = strtolower($c->gender);
                if (in_array($g, ['l', 'male', 'laki-laki'])) {
                    $genderLabel = 'Laki-laki';
                } elseif (in_array($g, ['p', 'female', 'perempuan'])) {
                    $genderLabel = 'Perempuan';
                } else {
                    $genderLabel = $c->gender;
                }
            }

            $stageLabel = match($c->registration_status) {
                'completed' => 'DITERIMA',
                'agreement_signed' => 'ADMINISTRASI',
                'taaruf_completed' => 'PERSETUJUAN',
                default => strtoupper($c->registration_status ?? '-')
            };

            $parentName = $c->guardian_name ?: ($c->father_name ?: ($c->mother_name ?: '-'));
            $parentPhone = $c->parent_phone ?: ($c->father_phone ?: ($c->mother_phone ?: '-'));

            // Build consolidated fee components text
            $feeData = $c->getFinalFeeDetails();
            $feeLines = [];
            foreach ($feeData['items'] as $item) {
                $itemPaid = $c->getItemPaidAmount($item['name'], $item['id'] ?? null);
                $isItemPaid = ($itemPaid >= $item['amount'] && $item['amount'] > 0);
                
                $statusBadge = '';
                if ($isItemPaid) {
                    $statusBadge = ' (LUNAS)';
                } elseif ($itemPaid > 0) {
                    $statusBadge = ' (Dicicil: Rp ' . number_format($itemPaid, 0, ',', '.') . ')';
                }

                $feeLines[] = '• ' . $item['name'] . ': Rp ' . number_format($item['amount'], 0, ',', '.') . $statusBadge;
            }
            $feeComponentsText = !empty($feeLines) ? implode("\n", $feeLines) : '-';

            $row = [
                $i++,
                $c->id_label ?? '-',
                $c->created_at ? $c->created_at->format('d/m/Y H:i') : '-',
                $c->period->name ?? ($c->period->year ?? '-'),
                $c->unit->name ?? '-',
                $c->grade->name ?? ($c->admission_level ?? '-'),
                $c->wave->name ?? '-',
                $c->type->name ?? '-',
                $c->classProgram->name ?? ($c->class_program ?? 'Reguler'),
                $c->extraServices->pluck('name')->implode(', ') ?: '-',
                $c->candidate_name ?? '-',
                $genderLabel,
                $parentName,
                $parentPhone,
                $feeComponentsText,
                $gross,
                $discount,
                $c->discount_reason ?: '-',
                $net,
                $paid,
                $remaining,
                $percent . '%',
                $statusText,
                $installmentPolicy,
                $stageLabel,
            ];

            $rows[] = $row;
        }

        return SimpleXlsxService::download($columns, $rows, $filename, 'Tagihan & DSP Murid');
    }

    /**
     * Export / Print filtered candidate billing and DSP fee data to PDF (A4 Landscape).
     */
    public function exportPdf(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });
        
        $query = Registration::scopedByAdmin()
            ->with(['unit', 'grade', 'classProgram', 'wave', 'type', 'payments', 'extraServices'])
            ->where('spmb_period_id', $selectedPeriodId)
            ->whereIn('registration_status', ['taaruf_completed', 'agreement_signed', 'completed']);

        // Base stats query
        $baseStatsQuery = (clone $query);
        if ($request->filled('unit_id')) {
            $baseStatsQuery->where('spmb_unit_id', $request->unit_id);
        }

        $allCands = (clone $baseStatsQuery)->get();
        $totalCandidates = $allCands->count();
        $totalGross = $allCands->sum(fn($c) => $c->getGrossFee());
        $totalDiscount = $allCands->sum(fn($c) => $c->total_discount);
        $totalNet = $allCands->sum(fn($c) => $c->net_fee);
        $totalPaid = $allCands->sum(fn($c) => $c->total_paid_final_fee);
        $totalRemaining = $allCands->sum(fn($c) => $c->remaining_balance);
        
        $totalLunas = $allCands->filter(fn($c) => $c->remaining_balance <= 0 && $c->net_fee > 0 && $c->total_paid_final_fee > 0)->count();
        $totalSebagian = $allCands->filter(fn($c) => $c->total_paid_final_fee > 0 && $c->remaining_balance > 0)->count();
        $totalBelumBayar = $allCands->filter(fn($c) => $c->total_paid_final_fee <= 0)->count();
        $totalDiskon = $allCands->filter(fn($c) => $c->total_discount > 0)->count();
        $totalCicilan = $allCands->filter(fn($c) => in_array($c->installment_mode, ['all', 'selective']))->count();

        $stats = [
            'candidate_count' => $totalCandidates,
            'gross_revenue' => $totalGross,
            'discount_sum' => $totalDiscount,
            'net_revenue' => $totalNet,
            'paid_sum' => $totalPaid,
            'remaining_sum' => $totalRemaining,
            'lunas_count' => $totalLunas,
            'sebagian_count' => $totalSebagian,
            'belum_bayar_count' => $totalBelumBayar,
            'diskon_count' => $totalDiskon,
            'cicilan_count' => $totalCicilan,
        ];

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%" . ltrim(preg_replace('/[^0-9]/', '', $search), '0') . "%")
                  ->orWhere('parent_phone', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('mother_name', 'like', "%{$search}%")
                  ->orWhereHas('payments', function($sq) use ($search) {
                      $sq->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('reference_id', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Unit
        if ($request->filled('unit_id')) {
            $query->where('spmb_unit_id', $request->unit_id);
        }

        // Quick Status Tabs Filter
        if ($request->filled('status')) {
            $st = $request->status;
            if ($st === 'lunas') {
                $candIds = $allCands->filter(fn($c) => $c->remaining_balance <= 0 && $c->net_fee > 0 && $c->total_paid_final_fee > 0)->pluck('id');
                $query->whereIn('id', $candIds);
            } elseif ($st === 'sebagian') {
                $candIds = $allCands->filter(fn($c) => $c->total_paid_final_fee > 0 && $c->remaining_balance > 0)->pluck('id');
                $query->whereIn('id', $candIds);
            } elseif ($st === 'belum_bayar') {
                $candIds = $allCands->filter(fn($c) => $c->total_paid_final_fee <= 0)->pluck('id');
                $query->whereIn('id', $candIds);
            } elseif ($st === 'diskon') {
                $candIds = $allCands->filter(fn($c) => $c->total_discount > 0)->pluck('id');
                $query->whereIn('id', $candIds);
            } elseif ($st === 'cicilan') {
                $query->whereIn('installment_mode', ['all', 'selective']);
            }
        }

        // Filter Wave
        if ($request->filled('wave_id')) {
            $query->where('spmb_wave_id', $request->wave_id);
        }

        // Filter Discount Mode
        if ($request->filled('discount_mode')) {
            $query->where('discount_mode', $request->discount_mode);
        }

        // Filter Installment Mode
        if ($request->filled('installment_mode')) {
            $query->where('installment_mode', $request->installment_mode);
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        $unitCode = 'ALL';
        $unitFilterLabel = 'Semua Jenjang / Unit';
        if ($request->filled('unit_id')) {
            $u = SpmbUnit::find($request->unit_id);
            if ($u) {
                $unitCode = strtoupper($u->code ?: Str::slug($u->name));
                $unitFilterLabel = $u->name;
            }
        } elseif (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $u = SpmbUnit::find(auth()->user()->spmb_unit_id);
            if ($u) {
                $unitCode = strtoupper($u->code ?: Str::slug($u->name));
                $unitFilterLabel = $u->name;
            }
        }

        $period = SpmbPeriod::find($selectedPeriodId);
        $periodName = $period ? ($period->name ?? $period->year) : 'SPMB';
        $periodLabel = $period ? Str::slug($period->name ?? $period->year) : 'SPMB';
        $filename = 'Laporan-Tagihan-DSP-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.pdf';

        $printedAt = now()->translatedFormat('d F Y, H:i') . ' WIB';
        $printedBy = auth()->user()->name ?? 'Administrator';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.payment-data-pdf', compact(
            'registrations',
            'stats',
            'periodName',
            'unitFilterLabel',
            'printedAt',
            'printedBy'
        ))->setPaper('a4', 'landscape');

        return response()->make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export payment history transactions log to Excel (.xlsx).
     */
    public function exportHistory(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });
        
        $query = $this->getPaymentHistoryQuery($request, $selectedPeriodId);
        $payments = $query->latest()->get();

        $unitCode = 'ALL';
        if ($request->filled('unit_id')) {
            $u = SpmbUnit::find($request->unit_id);
            if ($u) $unitCode = strtoupper($u->code ?: Str::slug($u->name));
        } elseif (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $u = SpmbUnit::find(auth()->user()->spmb_unit_id);
            if ($u) $unitCode = strtoupper($u->code ?: Str::slug($u->name));
        }

        $period = SpmbPeriod::find($selectedPeriodId);
        $periodLabel = $period ? Str::slug($period->name ?? $period->year) : 'SPMB';
        $filename = 'Data-Riwayat-Transaksi-Masuk-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.xlsx';

        $columns = [
            'No.',
            'No. Invoice',
            'ID Transaksi Gateway',
            'Waktu Transaksi',
            'No. Registrasi',
            'Nama Calon Murid',
            'Unit Sekolah',
            'Tingkat / Kelas',
            'Gelombang',
            'Jenis Pembayaran',
            'Rincian Item Pembayaran',
            'Metode Pembayaran',
            'Nomor VA / Akun',
            'Nominal Bersih (Rp)',
            'Biaya Admin (Rp)',
            'Total Bayar (Rp)',
            'Status Pembayaran',
        ];

        $rows = [];
        $i = 1;
        foreach ($payments as $pay) {
            $callbackPayload = $pay->payment_info['callback_payload'] ?? [];
            $numericWinpayId = $callbackPayload['originalReferenceNo'] 
                ?? ($callbackPayload['referenceNo'] 
                    ?? ($callbackPayload['paymentRequestId'] 
                        ?? (!empty($pay->reference_id) && is_numeric($pay->reference_id) ? $pay->reference_id : null)));
            $contractId = $pay->reference_id 
                ?? ($pay->payment_info['referenceId'] 
                    ?? ($pay->payment_info['contractId'] 
                        ?? ($pay->payment_info['additionalInfo']['contractId'] ?? null)));
            $displayWinpayId = $numericWinpayId ?: ($contractId ?: '-');

            $settledTimeRaw = $pay->payment_info['settled_at'] 
                ?? ($callbackPayload['paidTime'] 
                    ?? ($callbackPayload['trxDateTime'] ?? null));

            $displayTime = null;
            if ($pay->status === 'success' && $settledTimeRaw) {
                try {
                    $displayTime = \Carbon\Carbon::parse($settledTimeRaw)->timezone('Asia/Jakarta')->format('d/m/Y H:i');
                } catch (\Throwable $e) {
                    $displayTime = null;
                }
            }
            if (!$displayTime) {
                $displayTime = $pay->created_at ? $pay->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '-';
            }

            $reg = $pay->registration;
            $candName = $reg?->candidate_name ?? 'Draft / Belum Isi';
            $regId = $reg?->id_label ?? '-';
            $unitName = $reg?->unit?->name ?? '-';
            $gradeName = $reg?->grade?->name ?? ($reg?->admission_level ?? '-');
            $waveName = $reg?->wave?->name ?? '-';

            if ($pay->payment_type === 'registration_fee') {
                $paymentTypeLabel = 'Formulir Pendaftaran';
                $fee = $reg ? $reg->getRegistrationFee() : null;
                $feeTitle = $fee ? $fee->name : 'Biaya Formulir Pendaftaran';
            } else {
                $paymentTypeLabel = 'Biaya Masuk / DSP';
                $itemNames = [];
                if ($pay->items && $pay->items->isNotEmpty()) {
                    $itemNames = $pay->items->map(fn($it) => $it->fee_name . ($it->amount > 0 ? ' (Rp ' . number_format($it->amount, 0, ',', '.') . ')' : ''))->toArray();
                } elseif (isset($pay->payment_info['selected_items']) && is_array($pay->payment_info['selected_items'])) {
                    $itemNames = array_map(fn($it) => ($it['name'] ?? 'Item') . (isset($it['amount']) ? ' (Rp ' . number_format($it['amount'], 0, ',', '.') . ')' : ''), $pay->payment_info['selected_items']);
                }
                $feeTitle = !empty($itemNames) ? implode(", ", $itemNames) : 'Pelunasan Biaya Administrasi DSP';
            }

            $vaNumber = $pay->payment_info['virtualAccountNo'] ?? '-';
            $adminFee = (float) ($pay->admin_fee ?? 0);
            $totalAmount = (float) $pay->amount;
            $netAmount = max(0, $totalAmount - $adminFee);

            $rows[] = [
                $i++,
                $pay->invoice_number,
                $displayWinpayId,
                $displayTime . ' WIB',
                $regId,
                $candName,
                $unitName,
                $gradeName,
                $waveName,
                $paymentTypeLabel,
                $feeTitle,
                $pay->payment_method ?: 'Gateway',
                $vaNumber,
                $netAmount,
                $adminFee,
                $totalAmount,
                strtoupper($pay->status ?? '-'),
            ];
        }

        return SimpleXlsxService::download($columns, $rows, $filename, 'Riwayat Transaksi');
    }

    /**
     * Export payment history transactions log to PDF (A4 Landscape).
     */
    public function exportHistoryPdf(Request $request)
    {
        $selectedPeriodId = session('selected_period_id', function() {
            return SpmbPeriod::where('is_active', true)->value('id') 
                ?? SpmbPeriod::value('id');
        });
        
        $query = $this->getPaymentHistoryQuery($request, $selectedPeriodId);
        $payments = $query->latest()->get();

        $stats = [
            'total_count' => $payments->count(),
            'success_count' => $payments->where('status', 'success')->count(),
            'success_amount' => $payments->where('status', 'success')->sum('amount'),
            'pending_count' => $payments->where('status', 'pending')->count(),
            'pending_amount' => $payments->where('status', 'pending')->sum('amount'),
            'spam_count' => $payments->whereNotIn('status', ['success', 'pending'])->count(),
            'spam_amount' => $payments->whereNotIn('status', ['success', 'pending'])->sum('amount'),
        ];

        $unitCode = 'ALL';
        $unitFilterLabel = 'Semua Jenjang / Unit';
        if ($request->filled('unit_id')) {
            $u = SpmbUnit::find($request->unit_id);
            if ($u) {
                $unitCode = strtoupper($u->code ?: Str::slug($u->name));
                $unitFilterLabel = $u->name;
            }
        } elseif (auth()->user()->isUnitAdmin() && auth()->user()->spmb_unit_id) {
            $u = SpmbUnit::find(auth()->user()->spmb_unit_id);
            if ($u) {
                $unitCode = strtoupper($u->code ?: Str::slug($u->name));
                $unitFilterLabel = $u->name;
            }
        }

        $period = SpmbPeriod::find($selectedPeriodId);
        $periodName = $period ? ($period->name ?? $period->year) : 'SPMB';
        $periodLabel = $period ? Str::slug($period->name ?? $period->year) : 'SPMB';
        $filename = 'Laporan-Riwayat-Transaksi-Masuk-' . $unitCode . '-' . $periodLabel . '-' . date('Ymd_His') . '.pdf';

        $printedAt = now()->translatedFormat('d F Y, H:i') . ' WIB';
        $printedBy = auth()->user()->name ?? 'Administrator';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.payment-history-pdf', compact(
            'payments',
            'stats',
            'periodName',
            'unitFilterLabel',
            'printedAt',
            'printedBy'
        ))->setPaper('a4', 'landscape');

        return response()->make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

