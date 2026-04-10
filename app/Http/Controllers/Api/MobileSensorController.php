<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MobileSensorController extends Controller
{
    /**
     * GET /api/mobile/sensor/latest
     * Returns the single most recent sensor reading.
     */
    public function latest()
    {
        $reading = SensorReading::orderBy('id', 'desc')->first();

        if (!$reading) {
            return response()->json([
                'status'  => false,
                'message' => 'Belum ada data sensor.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'OK',
            'data'    => $reading,
        ]);
    }

    /**
     * GET /api/mobile/sensor/history?limit=50&from_date=...&to_date=...&status=...
     * Returns paginated historical sensor data with optional filters.
     */
    public function history(Request $request)
    {
        $query = SensorReading::orderBy('id', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $limit = min((int) $request->get('limit', 50), 500); // Max 500 per request
        $data  = $query->paginate($limit);

        return response()->json([
            'status'  => true,
            'message' => 'OK',
            'data'    => $data->items(),
            'meta'    => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
        ]);
    }

    /**
     * GET /api/mobile/sensor/alerts
     * Returns the last 20 warning/danger events.
     */
    public function alerts()
    {
        $alerts = SensorReading::whereIn('status', ['warning', 'danger'])
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'OK',
            'data'    => $alerts,
        ]);
    }

    /**
     * GET /api/mobile/sensor/daily-stats
     * Returns today's summary statistics.
     */
    public function dailyStats()
    {
        $today = Carbon::today();

        return response()->json([
            'status'  => true,
            'message' => 'OK',
            'data'    => [
                'date'    => $today->toDateString(),
                'total'   => SensorReading::whereDate('created_at', $today)->count(),
                'warning' => SensorReading::whereDate('created_at', $today)->where('status', 'warning')->count(),
                'danger'  => SensorReading::whereDate('created_at', $today)->where('status', 'danger')->count(),
                'avg_cr'  => round(
                    SensorReading::whereDate('created_at', $today)->whereNotNull('cr_estimated')->avg('cr_estimated') ?? 0,
                    2
                ),
            ],
        ]);
    }
}
