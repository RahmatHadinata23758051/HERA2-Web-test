<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Threshold;

class MonitoringController extends Controller
{
    public function index()
    {
        $thresholds = Threshold::getCrThresholds();
        return view('monitoring.index', compact('thresholds'));
    }
}
