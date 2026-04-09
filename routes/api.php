<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\SensorController;

// Protect API routes using web sessions since they are consumed by the SPA dashboard
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/health-check', function () {
        try {
            $response = Http::timeout(2)->get('http://localhost:8001/health');
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // fail silently towards dashboard
        }
        return response()->json(['status' => 'error', 'model_loaded' => false], 500);
    });

    Route::get('/sensor/latest', [SensorController::class, 'latest']);
    Route::get('/sensor/alerts', [SensorController::class, 'alerts']);
    Route::get('/sensor/history', [SensorController::class, 'history']);
});
