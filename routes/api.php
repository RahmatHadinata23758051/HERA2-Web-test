<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileSensorController;
use App\Http\Controllers\Api\MobileTestController;

// ────────────────────────────────────────────────────────────────
// Internal SPA Routes (Web Dashboard — session/cookie auth)
// ────────────────────────────────────────────────────────────────
Route::post('/v1/telemetry/ingest', [\App\Http\Controllers\Api\SensorController::class, 'ingest'])
    ->middleware(\App\Http\Middleware\IotTokenAuth::class)
    ->name('api.sensor.ingest');

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/health-check', function () {
        try {
            $aiUrl = env('AI_SERVICE_URL', 'http://localhost:8001');
            $response = Http::timeout(2)->get($aiUrl . '/health');
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // fail silently towards dashboard
        }
        return response()->json(['status' => 'error', 'model_loaded' => false], 500);
    });

    Route::get('/sensor/latest',  [SensorController::class, 'latest']);
    Route::get('/sensor/alerts',  [SensorController::class, 'alerts']);
    Route::get('/sensor/history', [SensorController::class, 'history']);
});

// ────────────────────────────────────────────────────────────────
// Mobile API Routes (Sanctum token-based auth)
// ────────────────────────────────────────────────────────────────

// Public — No auth required
Route::prefix('mobile')->name('mobile.')->group(function () {
    Route::post('/login', [MobileAuthController::class, 'login'])->name('login');
});

// Protected — Requires valid Bearer token
Route::prefix('mobile')->name('mobile.')->middleware('auth:sanctum')->group(function () {
    // Auth management
    Route::post('/logout',  [MobileAuthController::class, 'logout'])->name('logout');
    Route::get('/profile',  [MobileAuthController::class, 'profile'])->name('profile');

    // Sensor data
    Route::get('/sensor/latest',      [MobileSensorController::class, 'latest'])->name('sensor.latest');
    Route::get('/sensor/history',     [MobileSensorController::class, 'history'])->name('sensor.history');
    Route::get('/sensor/alerts',      [MobileSensorController::class, 'alerts'])->name('sensor.alerts');
    Route::get('/sensor/daily-stats', [MobileSensorController::class, 'dailyStats'])->name('sensor.daily-stats');

    // Field Testing Data (Smartphone Trigger)
    Route::post('/testing/location',  [MobileTestController::class, 'store'])->name('testing.location');
    Route::get('/testing/history',    [MobileTestController::class, 'history'])->name('testing.history');
});

