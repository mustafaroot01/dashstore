<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Flutter App
|--------------------------------------------------------------------------
| Prefix: /api
*/

// ── Auth (OTP) ──────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('send-otp',           [Api\AuthController::class, 'sendOtp']);
    Route::post('verify-otp',         [Api\AuthController::class, 'verifyOtp']);
    Route::post('register',           [Api\AuthController::class, 'register']);
    Route::post('forgot-password',    [Api\AuthController::class, 'forgotPassword']);
    Route::post('verify-reset-code',  [Api\AuthController::class, 'verifyResetCode']);
    Route::post('reset-password',     [Api\AuthController::class, 'resetPassword']);
});

// ── Public Content ───────────────────────────────────────────────
Route::get('sliders',           [Api\ContentController::class, 'sliders']);
Route::get('categories',        [Api\ContentController::class, 'categories']);
Route::get('products',          [Api\ContentController::class, 'products']);
Route::get('products/{id}',     [Api\ContentController::class, 'product']);
Route::get('districts',         [Api\ContentController::class, 'districts']);
Route::get('privacy-policy',    [Api\ContentController::class, 'privacyPolicy']);

// ── Protected (Bearer Token) ─────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Profile
    Route::get('user/profile',  [Api\UserProfileController::class, 'show']);
    Route::put('user/profile',  [Api\UserProfileController::class, 'update']);
    Route::delete('auth/delete-account', [Api\AuthController::class, 'deleteAccount']);

    // Orders
    Route::post('coupons/validate', [Api\OrderController::class, 'validateCoupon']);
    Route::post('orders',           [Api\OrderController::class, 'store']);
    Route::get('orders',            [Api\OrderController::class, 'index']);
    Route::get('orders/{id}',       [Api\OrderController::class, 'show']);
});
