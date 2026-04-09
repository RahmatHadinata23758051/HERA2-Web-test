<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SensorReading;

class SensorController extends Controller
{
    public function latest()
    {
        return response()->json(
            SensorReading::orderBy('id', 'desc')->take(30)->get()->reverse()->values()
        );
    }

    public function alerts()
    {
        return response()->json(
            SensorReading::whereIn('status', ['warning', 'danger'])
                ->orderBy('id', 'desc')
                ->take(10)
                ->get()
        );
    }

    public function history()
    {
        return response()->json(
            SensorReading::orderBy('id', 'desc')->paginate(50)
        );
    }
}
