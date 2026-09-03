<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AdminRegistrationController;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Webhook Callback Routes (Multi-gateway support)
Route::post('/payments/callback', [PaymentController::class, 'callback']);
Route::post('/payments/callback/winpay', fn (Request $request, PaymentController $c) => $c->callback($request, 'winpay'));
Route::post('/payments/callback/bni', fn (Request $request, PaymentController $c) => $c->callback($request, 'bni'));
Route::post('/payments/callback/v1.0/transfer-va/payment', [PaymentController::class, 'callback']);
Route::post('/payments/callback/v1.0/qr/qr-mpm-notify', [PaymentController::class, 'callback']);

// Protected Routes (Requires Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });

    // Registration APIs
    Route::get('/registration', [RegistrationController::class, 'show']);
    Route::post('/registration/candidate-info', [RegistrationController::class, 'updateCandidateInfo']);
    Route::post('/registration/parent-info', [RegistrationController::class, 'updateParentInfo']);
    Route::post('/registration/documents', [RegistrationController::class, 'uploadDocuments']);
    
    // Dashboard & Timeline
    Route::get('/dashboard', [RegistrationController::class, 'dashboard']);

    // Payment APIs
    Route::post('/payments/charge', [PaymentController::class, 'charge']);

    // Admin APIs
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/registrations', [AdminRegistrationController::class, 'index']);
        Route::get('/registrations/{id}', [AdminRegistrationController::class, 'show']);
        Route::post('/registrations/{id}/verify', [AdminRegistrationController::class, 'verify']);
        Route::post('/registrations/{id}/reject', [AdminRegistrationController::class, 'reject']);
    });
});
