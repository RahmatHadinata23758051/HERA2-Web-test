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

    public function history(Request $request)
    {
        $query = SensorReading::query();

        if ($request->has('from') && $request->has('to')) {
            $from = \Carbon\Carbon::parse($request->from)->setTimezone(config('app.timezone', 'UTC'))->toDateTimeString();
            $to = \Carbon\Carbon::parse($request->to)->setTimezone(config('app.timezone', 'UTC'))->toDateTimeString();
            $query->whereBetween('created_at', [$from, $to]);
        }

        // Limit data if a custom limit is passed, else max 1000 to prevent crashing the browser
        $limit = $request->input('limit', 1000);

        return response()->json(
            $query->orderBy('id', 'desc')->take($limit)->get()->reverse()->values()
        );
    }
}
