<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Repositories\InfluxSensorRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class MobileSensorController extends Controller
{
    protected $influxRepo;

    public function __construct(InfluxSensorRepository $influxRepo)
    {
        $this->influxRepo = $influxRepo;
    }

    public function latest()
    {
        $readings = $this->influxRepo->getReportData(null, null, 'Semua');
        $reading = $readings->first();

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

    public function history(Request $request)
    {
        $from = $request->filled('from_date') ? $request->from_date . ' 00:00:00' : null;
        $to = $request->filled('to_date') ? $request->to_date . ' 23:59:59' : null;
        $status = $request->filled('status') && $request->status !== 'all' ? $request->status : 'Semua';

        $data = $this->influxRepo->getReportData($from, $to, $status);

        $limit = min((int) $request->get('limit', 50), 500);
        $currentPage = Paginator::resolveCurrentPage();
        
        $items = array_slice($data->toArray(), ($currentPage - 1) * $limit, $limit);
        $paginated = new LengthAwarePaginator($items, $data->count(), $limit, $currentPage);

        return response()->json([
            'status'  => true,
            'message' => 'OK',
            'data'    => $paginated->items(),
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function alerts()
    {
        // Fetch 100 recent data, filter warning/danger, take 20.
        $recentData = $this->influxRepo->getReportData(null, null, 'Semua');
        
        $alerts = $recentData->filter(function($item) {
            return in_array($item->status, ['warning', 'danger']);
        })->take(20)->values();

        return response()->json([
            'status'  => true,
            'message' => 'OK',
            'data'    => $alerts,
        ]);
    }

    public function dailyStats()
    {
        $today = Carbon::today();
        $stats = $this->influxRepo->getDailyStats();

        return response()->json([
            'status'  => true,
            'message' => 'OK',
            'data'    => [
                'date'    => $today->toDateString(),
                'total'   => $stats['total'],
                'warning' => $stats['warning'],
                'danger'  => $stats['danger'],
                'avg_cr'  => $stats['avg_cr'],
            ],
        ]);
    }
}
