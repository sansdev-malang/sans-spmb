<?php

use Illuminate\Support\Facades\Route;
use App\Models\SpmbUnit;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\WebDashboardController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminCandidateController;
use App\Http\Controllers\Web\AdminPaymentController;
use App\Http\Controllers\Web\AdminNotificationController;
use App\Http\Controllers\Web\AdminLogsController;
use App\Http\Controllers\Web\SpmbSettingsController;
use App\Http\Controllers\Web\SpmbFeesController;
use App\Http\Controllers\Web\SpmbRegistrationSettingsController;
use App\Http\Controllers\Web\SpmbFormSettingsController;
use App\Http\Controllers\Web\SpmbAgreementsController;
use App\Http\Controllers\Web\PaymentGatewayController;
use App\Http\Controllers\Web\PaymentChannelController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/unit/{code}', function ($code) {
    $unit = SpmbUnit::where('code', strtoupper($code))->where('is_active', true)->firstOrFail();
    return view('unit-detail', compact('unit'));
})->name('unit.detail');

Route::post('/quick-register', [UserController::class, 'quickRegister'])->name('quick-register');

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
        Route::post('/admin/spmb-settings/change-period', [AdminDashboardController::class, 'changePeriod'])->name('admin.change-period');

        // Admin Candidate Management Pages
        Route::get('/admin/candidates', [AdminCandidateController::class, 'index'])->name('admin.candidates');
        Route::post('/admin/candidates/{id}/installment-settings', [AdminDashboardController::class, 'updateInstallmentSettings'])->name('admin.candidates.installment-settings');
        Route::get('/admin/history', [AdminCandidateController::class, 'history'])->name('admin.history');

        // Admin Billing & Payment Transaction Pages
        Route::get('/admin/payments/data', [AdminPaymentController::class, 'data'])->name('admin.payments.data');
        Route::get('/admin/payments', [AdminPaymentController::class, 'index'])->name('admin.payments');
        Route::get('/admin/payments/receipt/{id}', [WebDashboardController::class, 'downloadReceipt'])->name('admin.payments.receipt');

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
        Route::get('/admin/spmb-settings/agreements', [SpmbAgreementsController::class, 'index'])->name('admin.spmb-settings.agreements');
        Route::post('/admin/spmb-settings/agreements/{id}', [SpmbAgreementsController::class, 'update'])->name('admin.spmb-settings.agreements.update');
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
            Route::get('/admin/logs', [AdminLogsController::class, 'index'])->name('admin.logs');
            Route::post('/admin/logs/clear', [AdminLogsController::class, 'clear'])->name('admin.logs.clear');
        });
    });
});

require __DIR__.'/auth.php';
