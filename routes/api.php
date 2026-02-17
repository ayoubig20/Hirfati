<?php

use App\Http\Controllers\Api\ArtisanController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\TrustController;
use Illuminate\Support\Facades\Route;

// ─── Authentication ─────────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth');

// ─── Public endpoints ───────────────────────────────────────────
Route::get('/artisans/search', [ArtisanController::class, 'search']);
Route::get('/artisans/featured', [ArtisanController::class, 'featured']);
Route::get('/artisans/categories', [ArtisanController::class, 'categories']);
Route::get('/artisans/{id}', [ArtisanController::class, 'show']);
Route::get('/artisans/{artisanId}/reviews', [ReviewController::class, 'artisanReviews']);
Route::get('/artisans/{artisanId}/orders', [OrderController::class, 'artisanOrders']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/trust/stats/{artisan_id}', [TrustController::class, 'stats']);

// ─── Admin-only endpoints ───────────────────────────────────────
Route::middleware(['auth', 'admin'])->group(function () {
    // Artisan management
    Route::post('/artisans', [ArtisanController::class, 'store']);
    Route::put('/artisans/{id}', [ArtisanController::class, 'update']);

    // Order management
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    // Review management
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);

    // Trust management
    Route::post('/trust/calculate/{artisan_id}', [TrustController::class, 'calculate']);
    Route::get('/trust/fraud-alerts', [TrustController::class, 'fraudAlerts']);
});
