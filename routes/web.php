<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\Web\WebDashboardController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\SpmbSettingsController;
use App\Http\Controllers\Web\SpmbFeesController;
use App\Http\Controllers\Web\SpmbRegistrationSettingsController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\SpmbFormSettingsController;

Route::middleware('auth')->group(function () {
    // Candidate Dashboard
    Route::get('/dashboard', [WebDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/form', [WebDashboardController::class, 'form'])->name('dashboard.form');
    Route::get('/dashboard/payment', [WebDashboardController::class, 'payment'])->name('dashboard.payment');
    Route::get('/dashboard/verification', [WebDashboardController::class, 'verification'])->name('dashboard.verification');
    Route::get('/dashboard/observation', [WebDashboardController::class, 'observation'])->name('dashboard.observation');
    Route::get('/dashboard/result', [WebDashboardController::class, 'result'])->name('dashboard.result');
    Route::post('/dashboard/step/{stepId}/save', [WebDashboardController::class, 'saveStep'])->name('dashboard.step.save');
    Route::post('/dashboard/form/submit', [WebDashboardController::class, 'submitForm'])->name('dashboard.form.submit');
    Route::post('/dashboard/candidate-info', [WebDashboardController::class, 'updateCandidateInfo'])->name('dashboard.candidate');
    Route::post('/dashboard/parent-info', [WebDashboardController::class, 'updateParentInfo'])->name('dashboard.parent');
    Route::post('/dashboard/documents', [WebDashboardController::class, 'uploadDocuments'])->name('dashboard.documents');
    
    // Payments
    Route::post('/dashboard/payments/charge', [WebDashboardController::class, 'chargePayment'])->name('dashboard.charge');
    Route::post('/dashboard/payments/{id}/simulate', [WebDashboardController::class, 'simulatePaymentCallback'])->name('dashboard.simulate-payment');
    Route::post('/dashboard/payments/{id}/cancel', [WebDashboardController::class, 'cancelPayment'])->name('dashboard.cancel-payment');

    // Profile Management (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Group
    Route::middleware('admin')->group(function () {
        Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings');
        Route::post('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
        Route::post('/admin/settings/channels/{id}/toggle', [SettingsController::class, 'toggleChannel'])->name('admin.settings.channels.toggle');
        Route::post('/admin/settings/channels/sync', [SettingsController::class, 'syncChannels'])->name('admin.settings.channels.sync');

        Route::get('/admin/dashboard', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin/peninjauan', [AdminDashboardController::class, 'index'])->name('admin.peninjauan');
        Route::post('/admin/registrations/{id}/verify', [AdminDashboardController::class, 'verify'])->name('admin.registrations.verify');
        Route::post('/admin/registrations/{id}/reject', [AdminDashboardController::class, 'reject'])->name('admin.registrations.reject');

        // Admin Management Pages
        Route::get('/admin/candidates', function () {
            $candidates = \App\Models\Registration::with(['user', 'period', 'wave', 'type'])->whereNotNull('candidate_name')->latest()->paginate(10);
            return view('admin.pendaftar', compact('candidates'));
        })->name('admin.candidates');

        Route::get('/admin/payments', function () {
            $payments = \App\Models\Payment::with('registration')->latest()->paginate(10);
            return view('admin.mock-payments', compact('payments'));
        })->name('admin.payments');

        // New Config Pages
        Route::get('/admin/ui-settings', function () {
            return view('admin.settings-ui');
        })->name('admin.ui-settings');

        Route::get('/admin/api-integrations', function () {
            return view('admin.settings-api-integrations');
        })->name('admin.api-integrations');

        Route::get('/admin/spmb-settings', [SpmbSettingsController::class, 'index'])->name('admin.spmb-settings');
        
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

        // Setting Pendaftaran (Activation Config Panel)
        Route::get('/admin/spmb-settings/registration', [SpmbRegistrationSettingsController::class, 'index'])->name('admin.spmb-settings.registration');
        Route::post('/admin/spmb-settings/registration', [SpmbRegistrationSettingsController::class, 'update'])->name('admin.spmb-settings.registration.update');

        // Setting Biaya
        Route::get('/admin/spmb-settings/fees', [SpmbFeesController::class, 'index'])->name('admin.spmb-settings.fees');
        Route::post('/admin/spmb-settings/fees/categories', [SpmbFeesController::class, 'storeCategory'])->name('admin.spmb-settings.fees.categories.store');
        Route::post('/admin/spmb-settings/fees/categories/{id}', [SpmbFeesController::class, 'updateCategory'])->name('admin.spmb-settings.fees.categories.update');
        Route::delete('/admin/spmb-settings/fees/categories/{id}', [SpmbFeesController::class, 'destroyCategory'])->name('admin.spmb-settings.fees.categories.delete');
        Route::post('/admin/spmb-settings/fees/admin-fees', [SpmbFeesController::class, 'storeFee'])->name('admin.spmb-settings.fees.admin-fees.store');
        Route::post('/admin/spmb-settings/fees/admin-fees/{id}', [SpmbFeesController::class, 'updateFee'])->name('admin.spmb-settings.fees.admin-fees.update');
        Route::delete('/admin/spmb-settings/fees/admin-fees/{id}', [SpmbFeesController::class, 'destroyFee'])->name('admin.spmb-settings.fees.admin-fees.delete');

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

require __DIR__.'/auth.php';
