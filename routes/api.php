<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClaimController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\PasswordResetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/forgot-password', [PasswordResetController::class, 'send'])->middleware('throttle:5,1');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/items', [ItemController::class, 'index']);
Route::get('/items/{id}', [ItemController::class, 'show']);

Route::get('/claims', [ClaimController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/email/verify', [EmailVerificationController::class, 'verify'])->middleware('throttle:10,1');
    Route::post('/email/verification-code', [EmailVerificationController::class, 'resend'])->middleware('throttle:3,1');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/account', [AccountController::class, 'show']);
    Route::get('/account/reports', [AccountController::class, 'reports']);
    Route::get('/account/claims', [AccountController::class, 'claims']);
    Route::post('/items', [ItemController::class, 'store']);
    Route::put('/items/{id}', [ItemController::class, 'update']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);
    Route::post('/claims', [ClaimController::class, 'store']);
    Route::get('/claims/{id}', [ClaimController::class, 'show']);
    Route::patch('/claims/{id}/status', [ClaimController::class, 'updateStatus']);
    Route::delete('/claims/{id}', [ClaimController::class, 'destroy']);
});
