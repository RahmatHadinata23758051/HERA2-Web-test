<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\SensorReading;
use App\Models\Threshold;
use App\Models\FieldTest;

class DashboardController extends Controller
{
    public function index(\App\Repositories\InfluxSensorRepository $repo)
    {
        // Get newest 30 and reverse so chronological order for chart
        $initialData = array_reverse($repo->getReportData(null, null, 'Semua')->take(30)->toArray());

        // Daily summary stats
        $dailyStats = $repo->getDailyStats();

        // Threshold Chromium & Nickel (dari DB atau default)
        $thresholds = array_merge(
            Threshold::getCrThresholds(),
            Threshold::getNiThresholds()
        );

        // Ambil data pengujian lapangan (GPS) terbaru untuk posisi perangkat terkini di peta
        $latestFieldTest = FieldTest::with('user')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->first();

        return view('dashboard', compact('initialData', 'dailyStats', 'thresholds', 'latestFieldTest'));
    }
}

