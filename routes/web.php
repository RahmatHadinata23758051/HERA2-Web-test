<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;

// Guest only routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', fn() => redirect('/dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/monitoring', [App\Http\Controllers\MonitoringController::class, 'index'])->name('monitoring');
    
    // Laporan & Export
    Route::get('/laporan', [App\Http\Controllers\ReportController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export/excel', [App\Http\Controllers\ReportController::class, 'exportExcel'])->name('laporan.export.excel');
    Route::get('/laporan/export/pdf', [App\Http\Controllers\ReportController::class, 'exportPdf'])->name('laporan.export.pdf');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Admin — Direksi only
    Route::middleware('role:direksi')->group(function () {
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('users', UserController::class)->except(['show']);
        });

        // App Settings
        Route::get('/pengaturan', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::put('/pengaturan', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    });
});
