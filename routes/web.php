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

Route::middleware('auth')->group(function () {
    // Candidate Dashboard
    Route::get('/dashboard', [WebDashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/registration/create', [WebDashboardController::class, 'createRegistration'])->name('dashboard.registration.create');
    
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

        // Route to change selected academic period session
        Route::post('/admin/spmb-settings/change-period', function(\Illuminate\Http\Request $request) {
            $request->validate([
                'selected_period_id' => 'required|exists:spmb_periods,id'
            ]);
            session(['selected_period_id' => $request->selected_period_id]);
            return redirect()->back()->with('success', 'Tahun ajaran berhasil diubah.');
        })->name('admin.change-period');

        // Admin Management Pages
        Route::get('/admin/candidates', function () {
            $selectedPeriodId = session('selected_period_id', function() {
                return \App\Models\SpmbPeriod::where('is_active', true)->value('id') 
                    ?? \App\Models\SpmbPeriod::value('id');
            });
            $candidates = \App\Models\Registration::scopedByAdmin()
                ->with(['user', 'period', 'wave', 'type'])
                ->where('spmb_period_id', $selectedPeriodId)
                ->whereNotNull('candidate_name')
                ->latest()
                ->paginate(10);
            return view('admin.candidates', compact('candidates'));
        })->name('admin.candidates');

        Route::get('/admin/payments', function () {
            $selectedPeriodId = session('selected_period_id', function() {
                return \App\Models\SpmbPeriod::where('is_active', true)->value('id') 
                    ?? \App\Models\SpmbPeriod::value('id');
            });
            $payments = \App\Models\Payment::scopedByAdmin()
                ->with('registration')
                ->whereHas('registration', function($q) use ($selectedPeriodId) {
                    $q->where('spmb_period_id', $selectedPeriodId);
                })
                ->latest()
                ->paginate(10);
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

        // Super Admin Restricted Routes
        Route::middleware('super_admin')->group(function () {
            Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings');
            Route::post('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
            Route::post('/admin/settings/channels/{id}/toggle', [SettingsController::class, 'toggleChannel'])->name('admin.settings.channels.toggle');
            Route::post('/admin/settings/channels/sync', [SettingsController::class, 'syncChannels'])->name('admin.settings.channels.sync');

            // Payment Gateways CRUD & Settings
            Route::get('/admin/payment-gateways', [PaymentGatewayController::class, 'index'])->name('admin.payment-gateways.index');
            Route::post('/admin/payment-gateways', [PaymentGatewayController::class, 'store'])->name('admin.payment-gateways.store');
            Route::post('/admin/payment-gateways/{id}/update', [PaymentGatewayController::class, 'update'])->name('admin.payment-gateways.update');
            Route::delete('/admin/payment-gateways/{id}', [PaymentGatewayController::class, 'destroy'])->name('admin.payment-gateways.destroy');
            Route::get('/admin/payment-gateways/{code}/settings', [PaymentGatewayController::class, 'settings'])->name('admin.payment-gateways.settings');
            Route::post('/admin/payment-gateways/{code}/settings', [PaymentGatewayController::class, 'saveSettings'])->name('admin.payment-gateways.settings.save');

            // New Config Pages
            Route::get('/admin/ui-settings', [SettingsController::class, 'uiSettings'])->name('admin.ui-settings');
            Route::post('/admin/ui-settings', [SettingsController::class, 'saveUiSettings'])->name('admin.ui-settings.save');

            Route::get('/admin/api-integrations', function () {
                return view('admin.settings-api-integrations');
            })->name('admin.api-integrations');

            Route::get('/admin/spmb-settings', [SpmbSettingsController::class, 'index'])->name('admin.spmb-settings');
            Route::get('/admin/spmb-settings/units-grades', [SpmbSettingsController::class, 'unitsGrades'])->name('admin.spmb-settings.units-grades');
            Route::get('/admin/spmb-settings/qrcode', [SpmbSettingsController::class, 'qrcode'])->name('admin.spmb-settings.qrcode');
            
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

            // User CRUD endpoints
            Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
            Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
            Route::post('/admin/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
            Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
            Route::post('/admin/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
        });
    });
});

require __DIR__.'/auth.php';
