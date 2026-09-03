<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\SpmbPeriod;
use App\Models\SpmbFeeCategory;
use App\Models\SpmbFee;

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
        
        // Base query for candidate billing (Khusus calon siswa yang telah lolos seleksi / masuk tahap daftar ulang DSP)
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

        return view('admin.payment-data', compact('registrations', 'stats'));
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
        
        $query = Payment::scopedByAdmin()
            ->with('registration')
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
                  ->orWhereHas('registration', function($sq) use ($search) {
                      $sq->where('candidate_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Payment Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        // Per page limit
        $perPage = intval($request->get('per_page', 10));
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $payments = $query->latest()->paginate($perPage)->withQueryString();

        return view('admin.mock-payments', compact('payments'));
    }
}
