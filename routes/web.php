<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/board', [BoardController::class, 'index'])->name('board.index');
Route::get('/claims', [ClaimController::class, 'index'])->name('claims.index');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/report', [ReportController::class, 'create'])->name('report.create');
    Route::post('/report', [ReportController::class, 'store'])->name('report.store');
    Route::get('/reports/{item}/edit', [ReportController::class, 'edit'])->name('report.edit');
    Route::put('/reports/{item}', [ReportController::class, 'update'])->name('report.update');
    Route::delete('/reports/{item}', [ReportController::class, 'destroy'])->name('report.destroy');
    Route::post('/claims', [ClaimController::class, 'store'])->name('claims.store');
    Route::patch('/claims/{claim}/review', [ClaimController::class, 'review'])->name('claims.review');
});

Route::get('/admin', function () {
    if (session('is_admin') === true) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('admin.login');
})->name('admin');

Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'store'])->name('admin.login.store');

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::delete('/items/{id}', [AdminDashboardController::class, 'destroy'])->name('items.destroy');
    Route::delete('/claims/{id}', [AdminDashboardController::class, 'destroyClaim'])->name('claims.destroy');
    Route::patch('/claims/{claim}/review', [AdminDashboardController::class, 'reviewClaim'])->name('claims.review');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
});
