<?php

use App\Http\Controllers\Api\ArtisanController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\TrustController;
use Illuminate\Support\Facades\Route;

// Artisan endpoints
Route::get('/artisans/search', [ArtisanController::class, 'search']);
Route::get('/artisans/featured', [ArtisanController::class, 'featured']);
Route::get('/artisans/categories', [ArtisanController::class, 'categories']);
Route::get('/artisans/{id}', [ArtisanController::class, 'show']);
Route::post('/artisans', [ArtisanController::class, 'store']);
Route::put('/artisans/{id}', [ArtisanController::class, 'update']);

// Order endpoints
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
Route::get('/artisans/{artisanId}/orders', [OrderController::class, 'artisanOrders']);

// Review endpoints
Route::post('/reviews', [ReviewController::class, 'store']);
Route::get('/artisans/{artisanId}/reviews', [ReviewController::class, 'artisanReviews']);
Route::put('/reviews/{id}', [ReviewController::class, 'update']);

// Trust endpoints
Route::post('/trust/calculate/{artisan_id}', [TrustController::class, 'calculate']);
Route::get('/trust/stats/{artisan_id}', [TrustController::class, 'stats']);
Route::get('/trust/fraud-alerts', [TrustController::class, 'fraudAlerts']);
