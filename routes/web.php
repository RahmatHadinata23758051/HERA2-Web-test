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

    // =========================================================
    // Analisis Data Excel (RQ Risk Quotient) — semua auth user
    // =========================================================
    Route::prefix('analisis')->name('analisis.')->group(function () {
        // Sub-modul per polutan
        Route::get('/rqnitrat', [\App\Http\Controllers\AnalisisController::class, 'rqNitrat'])->name('rq.nitrat');
        Route::get('/rqpb',     [\App\Http\Controllers\AnalisisController::class, 'rqPb'])->name('rq.pb');
        Route::get('/rqcd',     [\App\Http\Controllers\AnalisisController::class, 'rqCd'])->name('rq.cd');
        Route::get('/rqph',     [\App\Http\Controllers\AnalisisController::class, 'rqPh'])->name('rq.ph');
        Route::get('/rqf',      [\App\Http\Controllers\AnalisisController::class, 'rqF'])->name('rq.f');

        // Input data manual
        Route::get('/input',    [\App\Http\Controllers\AnalisisController::class, 'inputData'])->name('input');
        Route::post('/store',   [\App\Http\Controllers\AnalisisController::class, 'store'])->name('store');

        // Import & Export Excel
        Route::post('/import',           [\App\Http\Controllers\AnalisisController::class, 'import'])->name('import');
        Route::get('/export/{type}',     [\App\Http\Controllers\AnalisisController::class, 'export'])->name('export');

        // Delete
        Route::delete('/{id}', [\App\Http\Controllers\AnalisisController::class, 'destroy'])->name('destroy');
    });
});
