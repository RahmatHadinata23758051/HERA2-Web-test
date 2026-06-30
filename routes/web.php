<?php
@copy('C:\Users\user\Nata\Tools\favicon.png', __DIR__ . '/../public/favicon.ico');

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

    
    // Laporan & Export
    Route::get('/laporan', [App\Http\Controllers\ReportController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/pengujian', [App\Http\Controllers\WebFieldTestController::class, 'index'])->name('laporan.pengujian.index');
    Route::get('/laporan/pengujian/export/excel', [App\Http\Controllers\WebFieldTestController::class, 'exportExcel'])->name('laporan.pengujian.export.excel');
    Route::get('/laporan/pengujian/export/pdf', [App\Http\Controllers\WebFieldTestController::class, 'exportPdf'])->name('laporan.pengujian.export.pdf');
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

        // Threshold Chromium
        Route::get('/pengaturan/threshold', [\App\Http\Controllers\ThresholdController::class, 'index'])->name('settings.threshold');
        Route::put('/pengaturan/threshold', [\App\Http\Controllers\ThresholdController::class, 'update'])->name('settings.threshold.update');

        // Manajemen Perangkat IoT
        Route::resource('iot-devices', \App\Http\Controllers\IotDeviceController::class)->except(['show']);

        // Log Aktivitas (halaman terpisah)
        Route::get('/log-aktivitas', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-log.index');
    });

    // =========================================================
    // Analisis Data Excel (RQ Risk Quotient) — semua auth user
    // =========================================================
    Route::prefix('analisis')->name('analisis.')->group(function () {
        // Rute Batch Analisis
        Route::get('/batch', [\App\Http\Controllers\RqBatchController::class, 'index'])->name('index');
        Route::post('/batch', [\App\Http\Controllers\RqBatchController::class, 'store'])->name('batch.store');
        Route::delete('/batch/{id}', [\App\Http\Controllers\RqBatchController::class, 'destroy'])->name('batch.destroy');
        Route::get('/batch/{id}/{pollutant?}', [\App\Http\Controllers\RqBatchController::class, 'show'])->name('batch.show');
        Route::post('/batch/{id}/import', [\App\Http\Controllers\RqBatchController::class, 'import'])->name('batch.import');
        Route::post('/batch/{id}/store-manual', [\App\Http\Controllers\RqBatchController::class, 'storeManual'])->name('batch.store_manual');

        // Rute Record Responden Individu
        Route::get('/record/{id}/edit', [\App\Http\Controllers\RqBatchController::class, 'editRecord'])->name('record.edit');
        Route::put('/record/{id}', [\App\Http\Controllers\RqBatchController::class, 'updateRecord'])->name('record.update');
        Route::delete('/record/{id}', [\App\Http\Controllers\RqBatchController::class, 'destroyRecord'])->name('record.destroy');
    });
});
