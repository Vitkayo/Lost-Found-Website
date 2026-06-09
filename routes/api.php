<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClaimController;
use App\Http\Controllers\Api\ItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/items', [ItemController::class, 'index']);
Route::post('/items', [ItemController::class, 'store']);
Route::get('/items/{id}', [ItemController::class, 'show']);

Route::get('/claims', [ClaimController::class, 'index']);
Route::post('/claims', [ClaimController::class, 'store']);
Route::get('/claims/{id}', [ClaimController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);
    Route::patch('/claims/{id}/status', [ClaimController::class, 'updateStatus']);
    Route::delete('/claims/{id}', [ClaimController::class, 'destroy']);
});
