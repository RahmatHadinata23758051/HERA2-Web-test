<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\SensorReading;
use App\Models\Threshold;

class DashboardController extends Controller
{
    public function index(\App\Repositories\InfluxSensorRepository $repo)
    {
        // Get newest 30 and reverse so chronological order for chart
        $initialData = array_reverse($repo->getReportData(null, null, 'Semua')->take(30)->toArray());

        // Daily summary stats
        $dailyStats = $repo->getDailyStats();

        // Threshold Chromium (dari DB atau default)
        $thresholds = Threshold::getCrThresholds();

        return view('dashboard', compact('initialData', 'dailyStats', 'thresholds'));
    }
}

