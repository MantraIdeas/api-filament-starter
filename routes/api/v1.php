<?php

use App\Http\Controllers\Api\V1\AuthenticationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/register', [AuthenticationController::class, 'register']);
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::post('/forgot-password', [AuthenticationController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthenticationController::class, 'resetPassword']);
    Route::post('/resend-otp', [AuthenticationController::class, 'resendOtp']);

    Route::post('/verify-otp', [AuthenticationController::class, 'verifyOtp']);

    Route::group(['middleware' => ['auth:sanctum', 'verified']], function (): void {
        Route::post('/change-password', [AuthenticationController::class, 'changePassword']);
    });

});
