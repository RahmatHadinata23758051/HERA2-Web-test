<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\SensorReading;

class DashboardController extends Controller
{
    public function index()
    {
        // Get newest 30 and reverse so chronological order for chart
        $initialData = SensorReading::orderBy('id', 'desc')->take(30)->get()->reverse()->values();

        // Daily summary stats (Card 9.1)
        $today = Carbon::today();
        $dailyStats = [
            'total'   => SensorReading::whereDate('created_at', $today)->count(),
            'warning' => SensorReading::whereDate('created_at', $today)->where('status', 'warning')->count(),
            'danger'  => SensorReading::whereDate('created_at', $today)->where('status', 'danger')->count(),
            'avg_cr'  => round(SensorReading::whereDate('created_at', $today)->whereNotNull('cr_estimated')->avg('cr_estimated') ?? 0, 2),
        ];

        return view('dashboard', compact('initialData', 'dailyStats'));
    }
}

