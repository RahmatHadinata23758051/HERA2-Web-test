<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SensorReading;

class DashboardController extends Controller
{
    public function index()
    {
        // Get newest 30 and reverse so chronological order for chart
        $initialData = SensorReading::orderBy('id', 'desc')->take(30)->get()->reverse()->values();
        return view('dashboard', compact('initialData'));
    }
}
