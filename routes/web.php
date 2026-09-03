<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/unit/{code}', function ($code) {
    $unit = \App\Models\SpmbUnit::where('code', strtoupper($code))->where('is_active', true)->firstOrFail();
    return view('unit-detail', compact('unit'));
})->name('unit.detail');

Route::post('/quick-register', [UserController::class, 'quickRegister'])->name('quick-register');

use App\Http\Controllers\Web\WebDashboardController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\SpmbSettingsController;
use App\Http\Controllers\Web\SpmbFeesController;
use App\Http\Controllers\Web\SpmbRegistrationSettingsController;
use App\Http\Controllers\Web\SpmbFormSettingsController;
use App\Http\Controllers\Web\PaymentGatewayController;
use App\Http\Controllers\Web\PaymentChannelController;
use App\Http\Controllers\Web\AdminNotificationController;

Route::middleware('auth')->group(function () {
    // Candidate Dashboard
    Route::get('/dashboard', [WebDashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/registration/create', [WebDashboardController::class, 'createRegistration'])->name('dashboard.registration.create');
    
    // Candidate In-App Notifications Routes
    Route::get('/dashboard/notifications/unread-count', [AdminNotificationController::class, 'unreadCount'])->name('dashboard.notifications.count');
    Route::get('/dashboard/notifications/dropdown', [AdminNotificationController::class, 'dropdownList'])->name('dashboard.notifications.dropdown');
    Route::post('/dashboard/notifications/mark-all-read', [AdminNotificationController::class, 'markAllRead'])->name('dashboard.notifications.mark-all-read');
    Route::get('/dashboard/notifications/{id}/read', [AdminNotificationController::class, 'markAsReadAndRedirect'])->name('dashboard.notifications.read-redirect');
    
    // Single Registration Details
    Route::get('/dashboard/registration/{id}/detail', [WebDashboardController::class, 'detail'])->name('dashboard.detail');
    Route::get('/dashboard/registration/{id}/form', [WebDashboardController::class, 'form'])->name('dashboard.form');
    Route::get('/dashboard/registration/{id}/payment', [WebDashboardController::class, 'payment'])->name('dashboard.payment');
    Route::get('/dashboard/registration/{id}/verification', [WebDashboardController::class, 'verification'])->name('dashboard.verification');
    Route::get('/dashboard/registration/{id}/observation', [WebDashboardController::class, 'observation'])->name('dashboard.observation');
    Route::get('/dashboard/registration/{id}/result', [WebDashboardController::class, 'result'])->name('dashboard.result');
    Route::post('/dashboard/registration/{id}/step/{stepId}/save', [WebDashboardController::class, 'saveStep'])->name('dashboard.step.save');
    Route::post('/dashboard/registration/{id}/form/submit', [WebDashboardController::class, 'submitForm'])->name('dashboard.form.submit');
    Route::post('/dashboard/registration/{id}/candidate-info', [WebDashboardController::class, 'updateCandidateInfo'])->name('dashboard.candidate');
    Route::post('/dashboard/registration/{id}/parent-info', [WebDashboardController::class, 'updateParentInfo'])->name('dashboard.parent');
    Route::post('/dashboard/registration/{id}/documents', [WebDashboardController::class, 'uploadDocuments'])->name('dashboard.documents');
    Route::post('/dashboard/registration/{id}/agreement/submit', [WebDashboardController::class, 'submitAgreement'])->name('dashboard.agreement.submit');
    
    // Payments
    Route::post('/dashboard/registration/{id}/payments/charge', [WebDashboardController::class, 'chargePayment'])->name('dashboard.charge');
    Route::post('/dashboard/payments/{id}/simulate', [WebDashboardController::class, 'simulatePaymentCallback'])->name('dashboard.simulate-payment');
    Route::post('/dashboard/payments/{id}/cancel', [WebDashboardController::class, 'cancelPayment'])->name('dashboard.cancel-payment');
    Route::get('/dashboard/payments/{id}/receipt', [WebDashboardController::class, 'downloadReceipt'])->name('dashboard.payment.receipt');
    Route::get('/dashboard/registration/{id}/admission-letter', [WebDashboardController::class, 'downloadAdmissionLetter'])->name('dashboard.admission-letter.download');

    // Profile Management (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Group
    Route::middleware('admin')->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin/verification', [AdminDashboardController::class, 'index'])->name('admin.verification');
        Route::post('/admin/registrations/{id}/verify', [AdminDashboardController::class, 'verify'])->name('admin.registrations.verify');
        Route::post('/admin/registrations/{id}/reject', [AdminDashboardController::class, 'reject'])->name('admin.registrations.reject');
        Route::post('/admin/registrations/{id}/complete-taaruf', [AdminDashboardController::class, 'completeTaaruf'])->name('admin.registrations.complete-taaruf');

        // Admin In-App Notifications Routes
        Route::get('/admin/notifications/unread-count', [AdminNotificationController::class, 'unreadCount'])->name('admin.notifications.count');
        Route::get('/admin/notifications/dropdown', [AdminNotificationController::class, 'dropdownList'])->name('admin.notifications.dropdown');
        Route::post('/admin/notifications/mark-all-read', [AdminNotificationController::class, 'markAllRead'])->name('admin.notifications.mark-all-read');
        Route::get('/admin/notifications/{id}/read', [AdminNotificationController::class, 'markAsReadAndRedirect'])->name('admin.notifications.read-redirect');

        // Route to change selected academic period session
        Route::post('/admin/spmb-settings/change-period', function(\Illuminate\Http\Request $request) {
            $request->validate([
                'selected_period_id' => 'required|exists:spmb_periods,id'
            ]);
            session(['selected_period_id' => $request->selected_period_id]);
            return redirect()->back()->with('success', 'Tahun ajaran berhasil diubah.');
        })->name('admin.change-period');

        // Admin Management Pages
        Route::get('/admin/candidates', function (Illuminate\Http\Request $request) {
            $selectedPeriodId = session('selected_period_id', function() {
                return \App\Models\SpmbPeriod::where('is_active', true)->value('id') 
                    ?? \App\Models\SpmbPeriod::value('id');
            });
            
            $query = \App\Models\Registration::scopedByAdmin()
                ->with(['user', 'period', 'wave', 'type', 'payments'])
                ->where('spmb_period_id', $selectedPeriodId)
                ->whereNotNull('candidate_name')
                ->whereHas('payments', function($q) {
                    $q->where('payment_type', 'registration_fee')
                      ->where('status', 'success');
                });

            // Calculate Stats for Active Candidates
            $baseStatsQuery = \App\Models\Registration::scopedByAdmin()
                ->where('spmb_period_id', $selectedPeriodId)
                ->whereNotNull('candidate_name')
                ->whereHas('payments', function($q) {
                    $q->where('payment_type', 'registration_fee')
                      ->where('status', 'success');
                });

            if ($request->filled('unit_id')) {
                $baseStatsQuery->where('spmb_unit_id', $request->unit_id);
            }

            $totalCount = (clone $baseStatsQuery)->count();
            $maleCount = (clone $baseStatsQuery)->where('gender', 'L')->count();
            $femaleCount = (clone $baseStatsQuery)->where('gender', 'P')->count();
            $verifiedCount = (clone $baseStatsQuery)->whereIn('registration_status', ['verified', 'taaruf_completed', 'agreement_signed', 'completed'])->count();
            $pendingCount = (clone $baseStatsQuery)->where('registration_status', 'submitted')->count();

            $stats = [
                'total' => $totalCount,
                'male' => $maleCount,
                'female' => $femaleCount,
                'verified' => $verifiedCount,
                'pending' => $pendingCount,
            ];

            // Calculate Wave Stats
            $waveStats = \App\Models\SpmbWave::all()->map(function($w) use ($baseStatsQuery) {
                return [
                    'name' => $w->name,
                    'count' => (clone $baseStatsQuery)->where('spmb_wave_id', $w->id)->count()
                ];
            })->filter(function($item) {
                return $item['count'] > 0;
            });

            // Calculate Jalur (Type) Stats
            $typeStats = \App\Models\SpmbType::all()->map(function($t) use ($baseStatsQuery) {
                return [
                    'name' => $t->name,
                    'count' => (clone $baseStatsQuery)->where('spmb_type_id', $t->id)->count()
                ];
            })->filter(function($item) {
                return $item['count'] > 0;
            });

            // Calculate Program Kelas Stats
            $classProgramStats = \App\Models\SpmbClassProgram::all()->map(function($cp) use ($baseStatsQuery) {
                return [
                    'name' => $cp->name,
                    'count' => (clone $baseStatsQuery)->where('spmb_class_program_id', $cp->id)->count()
                ];
            })->filter(function($item) {
                return $item['count'] > 0;
            });

            // Calculate Stage Counts for Pills
            $stageCounts = [
                'all' => (clone $baseStatsQuery)->count(),
                'draft' => (clone $baseStatsQuery)->whereIn('registration_status', ['draft', 'failed'])->count(),
                'submitted' => (clone $baseStatsQuery)->where('registration_status', 'submitted')->count(),
                'verified' => (clone $baseStatsQuery)->where('registration_status', 'verified')->count(),
                'taaruf_completed' => (clone $baseStatsQuery)->where('registration_status', 'taaruf_completed')->count(),
                'agreement_signed' => (clone $baseStatsQuery)->where('registration_status', 'agreement_signed')->count(),
                'completed' => (clone $baseStatsQuery)->where('registration_status', 'completed')->count(),
            ];

            // Search by Name, WhatsApp, or NIK
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('candidate_name', 'like', "%{$search}%")
                      ->orWhere('parent_phone', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                });
            }

            // Filter by Stage / Status Pill
            if ($request->filled('stage') && $request->stage !== 'all') {
                if ($request->stage === 'draft') {
                    $query->whereIn('registration_status', ['draft', 'failed']);
                } else {
                    $query->where('registration_status', $request->stage);
                }
            }

            // Filter by Unit/Jenjang School
            if ($request->filled('unit_id')) {
                $query->where('spmb_unit_id', $request->unit_id);
            }

            // Filter by Registration Date Range
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // Filter by Gender
            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            // Filter by Wave
            if ($request->filled('wave_id')) {
                $query->where('spmb_wave_id', $request->wave_id);
            }

            // Filter by Registration Type
            if ($request->filled('type_id')) {
                $query->where('spmb_type_id', $request->type_id);
            }

            // Filter by Class Program
            if ($request->filled('class_program_id')) {
                $query->where('spmb_class_program_id', $request->class_program_id);
            }

            // Filter by Document Upload Status
            if ($request->filled('doc_status')) {
                if ($request->doc_status === 'complete') {
                    $query->whereNotNull('birth_certificate_path')
                          ->whereNotNull('family_card_path');
                } elseif ($request->doc_status === 'incomplete') {
                    $query->where(function($q) {
                        $q->whereNull('birth_certificate_path')
                          ->orWhereNull('family_card_path');
                    });
                }
            }

            // Per page limit
            $perPage = intval($request->get('per_page', 10));
            if (!in_array($perPage, [10, 25, 50, 100])) {
                $perPage = 10;
            }

            $candidates = $query->latest()->paginate($perPage)->withQueryString();

            return view('admin.candidates', compact('candidates', 'stats', 'waveStats', 'typeStats', 'classProgramStats', 'stageCounts'));
        })->name('admin.candidates');

        Route::post('/admin/candidates/{id}/installment-settings', [AdminDashboardController::class, 'updateInstallmentSettings'])->name('admin.candidates.installment-settings');

        Route::get('/admin/history', function (Illuminate\Http\Request $request) {
            $selectedPeriodId = session('selected_period_id', function() {
                return \App\Models\SpmbPeriod::where('is_active', true)->value('id') 
                    ?? \App\Models\SpmbPeriod::value('id');
            });

            $query = \App\Models\Registration::scopedByAdmin()
                ->with(['user', 'period', 'wave', 'type', 'payments'])
                ->where('spmb_period_id', $selectedPeriodId)
                ->whereNotNull('candidate_name');

            // Search by Name, WhatsApp, or NIK
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('candidate_name', 'like', "%{$search}%")
                      ->orWhere('parent_phone', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                });
            }

            // Filter by Unit/Jenjang School
            if ($request->filled('unit_id')) {
                $query->where('spmb_unit_id', $request->unit_id);
            }

            // Filter by SPMB Process Stage
            if ($request->filled('status')) {
                $query->where('registration_status', $request->status);
            }

            // Filter by Registration Date Range
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // Filter by Gender
            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            // Filter by Wave
            if ($request->filled('wave_id')) {
                $query->where('spmb_wave_id', $request->wave_id);
            }

            // Filter by Registration Type
            if ($request->filled('type_id')) {
                $query->where('spmb_type_id', $request->type_id);
            }

            // Filter by Class Program
            if ($request->filled('class_program_id')) {
                $query->where('spmb_class_program_id', $request->class_program_id);
            }

            // Filter by Document Upload Status
            if ($request->filled('doc_status')) {
                if ($request->doc_status === 'complete') {
                    $query->whereNotNull('birth_certificate_path')
                          ->whereNotNull('family_card_path');
                } elseif ($request->doc_status === 'incomplete') {
                    $query->where(function($q) {
                        $q->whereNull('birth_certificate_path')
                          ->orWhereNull('family_card_path');
                    });
                }
            }

            // Per page limit
            $perPage = intval($request->get('per_page', 10));
            if (!in_array($perPage, [10, 25, 50, 100])) {
                $perPage = 10;
            }

            $candidates = $query->latest()->paginate($perPage)->withQueryString();

            return view('admin.history', compact('candidates'));
        })->name('admin.history');

        Route::get('/admin/payments/data', function (Illuminate\Http\Request $request) {
            $selectedPeriodId = session('selected_period_id', function() {
                return \App\Models\SpmbPeriod::where('is_active', true)->value('id') 
                    ?? \App\Models\SpmbPeriod::value('id');
            });
            
            // Base query for candidate billing (Khusus calon siswa yang telah lolos seleksi / masuk tahap daftar ulang DSP)
            $query = \App\Models\Registration::scopedByAdmin()
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
        })->name('admin.payments.data');

        Route::get('/admin/payments', function (Illuminate\Http\Request $request) {
            $selectedPeriodId = session('selected_period_id', function() {
                return \App\Models\SpmbPeriod::where('is_active', true)->value('id') 
                    ?? \App\Models\SpmbPeriod::value('id');
            });
            
            $query = \App\Models\Payment::scopedByAdmin()
                ->with('registration')
                ->whereHas('registration', function($q) use ($selectedPeriodId) {
                    $q->where('spmb_period_id', $selectedPeriodId);
                });

            // Search by Invoice, Reference ID, Candidate Name, or Gateway Info
            if ($request->filled('search')) {
                $search = $request->search;
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
                $category = \App\Models\SpmbFeeCategory::find($request->category_id);
                if ($category) {
                    $isFormulir = str_contains(strtolower($category->name), 'formulir') || str_contains(strtolower($category->name), 'pendaftaran');
                    $feeNames = \App\Models\SpmbFee::where('spmb_fee_category_id', $category->id)->pluck('name');
                    
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
                $targetFee = \App\Models\SpmbFee::find($request->fee_id);
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
        })->name('admin.payments');

        // Setting Biaya (Accessible to both Super Admin and Unit Admin)
        Route::get('/admin/spmb-settings/fees', [SpmbFeesController::class, 'index'])->name('admin.spmb-settings.fees');
        Route::post('/admin/spmb-settings/fees/categories', [SpmbFeesController::class, 'storeCategory'])->name('admin.spmb-settings.fees.categories.store');
        Route::post('/admin/spmb-settings/fees/categories/{id}', [SpmbFeesController::class, 'updateCategory'])->name('admin.spmb-settings.fees.categories.update');
        Route::delete('/admin/spmb-settings/fees/categories/{id}', [SpmbFeesController::class, 'destroyCategory'])->name('admin.spmb-settings.fees.categories.delete');
        Route::post('/admin/spmb-settings/fees/admin-fees', [SpmbFeesController::class, 'storeFee'])->name('admin.spmb-settings.fees.admin-fees.store');
        Route::post('/admin/spmb-settings/fees/admin-fees/{id}', [SpmbFeesController::class, 'updateFee'])->name('admin.spmb-settings.fees.admin-fees.update');
        Route::delete('/admin/spmb-settings/fees/admin-fees/{id}', [SpmbFeesController::class, 'destroyFee'])->name('admin.spmb-settings.fees.admin-fees.delete');

        // Routes accessible to both Super Admin and Unit Admin (with internal scoping)
        Route::get('/admin/spmb-settings/qrcode', [SpmbSettingsController::class, 'qrcode'])->name('admin.spmb-settings.qrcode');
        Route::get('/admin/spmb-settings/agreements', [\App\Http\Controllers\Web\SpmbAgreementsController::class, 'index'])->name('admin.spmb-settings.agreements');
        Route::post('/admin/spmb-settings/agreements/{id}', [\App\Http\Controllers\Web\SpmbAgreementsController::class, 'update'])->name('admin.spmb-settings.agreements.update');
        Route::get('/admin/spmb-settings/instructions', [SettingsController::class, 'reRegistrationInstructions'])->name('admin.spmb-settings.instructions');
        Route::post('/admin/spmb-settings/instructions', [SettingsController::class, 'saveReRegistrationInstructions'])->name('admin.spmb-settings.instructions.save');
        Route::get('/admin/spmb-settings/customer-service', [SpmbSettingsController::class, 'customerService'])->name('admin.spmb-settings.cs');
        Route::post('/admin/spmb-settings/customer-service', [SpmbSettingsController::class, 'saveCustomerService'])->name('admin.spmb-settings.cs.save');
        Route::get('/admin/spmb-settings/brochures', [SpmbSettingsController::class, 'brochures'])->name('admin.spmb-settings.brochures');
        Route::post('/admin/spmb-settings/brochures', [SpmbSettingsController::class, 'saveBrochures'])->name('admin.spmb-settings.brochures.save');
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::post('/admin/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/admin/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');

        // Admin Profile & Password Management
        Route::get('/admin/profile', [ProfileController::class, 'editAdminProfile'])->name('admin.profile.edit');
        Route::post('/admin/profile', [ProfileController::class, 'updateAdminProfile'])->name('admin.profile.update');
        Route::get('/admin/profile/password', [ProfileController::class, 'editAdminPassword'])->name('admin.profile.password');
        Route::post('/admin/profile/password', [ProfileController::class, 'updateAdminPassword'])->name('admin.profile.password.update');

        // Super Admin Restricted Routes
        Route::middleware('super_admin')->group(function () {
            Route::get('/admin/activity-logs', [AdminDashboardController::class, 'activityLogs'])->name('admin.activity-logs');
            Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings');
            Route::post('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');

            // Payment Gateways CRUD & Settings
            Route::get('/admin/payment-gateways', [PaymentGatewayController::class, 'index'])->name('admin.payment-gateways.index');
            Route::post('/admin/payment-gateways', [PaymentGatewayController::class, 'store'])->name('admin.payment-gateways.store');
            Route::post('/admin/payment-gateways/{id}/update', [PaymentGatewayController::class, 'update'])->name('admin.payment-gateways.update');
            Route::delete('/admin/payment-gateways/{id}', [PaymentGatewayController::class, 'destroy'])->name('admin.payment-gateways.destroy');
            Route::get('/admin/payment-gateways/{code}/settings', [PaymentGatewayController::class, 'settings'])->name('admin.payment-gateways.settings');
            Route::post('/admin/payment-gateways/{code}/settings', [PaymentGatewayController::class, 'saveSettings'])->name('admin.payment-gateways.settings.save');

            // Payment Channels CRUD
            Route::get('/admin/payment-channels', [PaymentChannelController::class, 'index'])->name('admin.payment-channels.index');
            Route::post('/admin/payment-channels', [PaymentChannelController::class, 'store'])->name('admin.payment-channels.store');
            Route::post('/admin/payment-channels/{id}/update', [PaymentChannelController::class, 'update'])->name('admin.payment-channels.update');
            Route::delete('/admin/payment-channels/{id}', [PaymentChannelController::class, 'destroy'])->name('admin.payment-channels.destroy');
            Route::post('/admin/payment-channels/{id}/toggle', [PaymentChannelController::class, 'toggle'])->name('admin.payment-channels.toggle');
            Route::post('/admin/payment-channels/sync', [PaymentChannelController::class, 'sync'])->name('admin.payment-channels.sync');

            // New Config Pages
            Route::get('/admin/ui-settings', [SettingsController::class, 'uiSettings'])->name('admin.ui-settings');
            Route::post('/admin/ui-settings', [SettingsController::class, 'saveUiSettings'])->name('admin.ui-settings.save');

            Route::get('/admin/api-integrations', function () {
                return view('admin.settings-api-integrations');
            })->name('admin.api-integrations');

            Route::get('/admin/spmb-settings', [SpmbSettingsController::class, 'index'])->name('admin.spmb-settings');
            Route::get('/admin/spmb-settings/units-grades', [SpmbSettingsController::class, 'unitsGrades'])->name('admin.spmb-settings.units-grades');
            Route::post('/admin/spmb-settings/qrcode', [SpmbSettingsController::class, 'saveQrcode'])->name('admin.spmb-settings.qrcode.save');
            
            // Period CRUD
            Route::post('/admin/spmb-settings/periods', [SpmbSettingsController::class, 'storePeriod'])->name('admin.spmb-settings.periods.store');
            Route::post('/admin/spmb-settings/periods/{id}', [SpmbSettingsController::class, 'updatePeriod'])->name('admin.spmb-settings.periods.update');
            Route::delete('/admin/spmb-settings/periods/{id}', [SpmbSettingsController::class, 'destroyPeriod'])->name('admin.spmb-settings.periods.delete');

            // Wave CRUD
            Route::post('/admin/spmb-settings/waves', [SpmbSettingsController::class, 'storeWave'])->name('admin.spmb-settings.waves.store');
            Route::post('/admin/spmb-settings/waves/{id}', [SpmbSettingsController::class, 'updateWave'])->name('admin.spmb-settings.waves.update');
            Route::delete('/admin/spmb-settings/waves/{id}', [SpmbSettingsController::class, 'destroyWave'])->name('admin.spmb-settings.waves.delete');

            // Type CRUD
            Route::post('/admin/spmb-settings/types', [SpmbSettingsController::class, 'storeType'])->name('admin.spmb-settings.types.store');
            Route::post('/admin/spmb-settings/types/{id}', [SpmbSettingsController::class, 'updateType'])->name('admin.spmb-settings.types.update');
            Route::delete('/admin/spmb-settings/types/{id}', [SpmbSettingsController::class, 'destroyType'])->name('admin.spmb-settings.types.delete');

            // Class Program CRUD
            Route::post('/admin/spmb-settings/class-programs', [SpmbSettingsController::class, 'storeClassProgram'])->name('admin.spmb-settings.class-programs.store');
            Route::post('/admin/spmb-settings/class-programs/{id}', [SpmbSettingsController::class, 'updateClassProgram'])->name('admin.spmb-settings.class-programs.update');
            Route::delete('/admin/spmb-settings/class-programs/{id}', [SpmbSettingsController::class, 'destroyClassProgram'])->name('admin.spmb-settings.class-programs.delete');

            // Unit CRUD
            Route::post('/admin/spmb-settings/units', [SpmbSettingsController::class, 'storeUnit'])->name('admin.spmb-settings.units.store');
            Route::match(['POST', 'PUT'], '/admin/spmb-settings/units/{id}', [SpmbSettingsController::class, 'updateUnit'])->name('admin.spmb-settings.units.update');
            Route::delete('/admin/spmb-settings/units/{id}', [SpmbSettingsController::class, 'destroyUnit'])->name('admin.spmb-settings.units.delete');

            // Grade CRUD
            Route::post('/admin/spmb-settings/grades', [SpmbSettingsController::class, 'storeGrade'])->name('admin.spmb-settings.grades.store');
            Route::match(['POST', 'PUT'], '/admin/spmb-settings/grades/{id}', [SpmbSettingsController::class, 'updateGrade'])->name('admin.spmb-settings.grades.update');
            Route::delete('/admin/spmb-settings/grades/{id}', [SpmbSettingsController::class, 'destroyGrade'])->name('admin.spmb-settings.grades.delete');

            // Extra Services (Layanan Non-Formal) CRUD
            Route::post('/admin/spmb-settings/extra-services', [SpmbSettingsController::class, 'storeExtraService'])->name('admin.spmb-settings.extra-services.store');
            Route::match(['POST', 'PUT'], '/admin/spmb-settings/extra-services/{id}', [SpmbSettingsController::class, 'updateExtraService'])->name('admin.spmb-settings.extra-services.update');
            Route::delete('/admin/spmb-settings/extra-services/{id}', [SpmbSettingsController::class, 'destroyExtraService'])->name('admin.spmb-settings.extra-services.delete');

            // Setting Pendaftaran (Activation Config Panel)
            Route::get('/admin/spmb-settings/registration', [SpmbRegistrationSettingsController::class, 'index'])->name('admin.spmb-settings.registration');
            Route::post('/admin/spmb-settings/registration', [SpmbRegistrationSettingsController::class, 'update'])->name('admin.spmb-settings.registration.update');



            // Setting Formulir CRUD
            Route::get('/admin/spmb-settings/form', [SpmbFormSettingsController::class, 'index'])->name('admin.spmb-settings.form');
            Route::post('/admin/spmb-settings/form/steps', [SpmbFormSettingsController::class, 'storeStep'])->name('admin.spmb-settings.form.steps.store');
            Route::post('/admin/spmb-settings/form/steps/{id}', [SpmbFormSettingsController::class, 'updateStep'])->name('admin.spmb-settings.form.steps.update');
            Route::delete('/admin/spmb-settings/form/steps/{id}', [SpmbFormSettingsController::class, 'destroyStep'])->name('admin.spmb-settings.form.steps.delete');
            
            Route::post('/admin/spmb-settings/form/fields', [SpmbFormSettingsController::class, 'storeField'])->name('admin.spmb-settings.form.fields.store');
            Route::post('/admin/spmb-settings/form/fields/{id}', [SpmbFormSettingsController::class, 'updateField'])->name('admin.spmb-settings.form.fields.update');
            Route::delete('/admin/spmb-settings/form/fields/{id}', [SpmbFormSettingsController::class, 'destroyField'])->name('admin.spmb-settings.form.fields.delete');

            // System Logs Viewer
            Route::get('/admin/logs', [\App\Http\Controllers\Web\AdminLogsController::class, 'index'])->name('admin.logs');
            Route::post('/admin/logs/clear', [\App\Http\Controllers\Web\AdminLogsController::class, 'clear'])->name('admin.logs.clear');
        });
    });
});

require __DIR__.'/auth.php';
