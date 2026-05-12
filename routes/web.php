<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Google OAuth
    Route::get('/google/redirect', [GoogleAuthController::class, 'redirect']);
    Route::get('/google/callback', [GoogleAuthController::class, 'callback']);
});

// Protected routes (require valid JWT)
Route::middleware('auth:api')->prefix('auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});
