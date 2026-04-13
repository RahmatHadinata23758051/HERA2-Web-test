<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\InfluxSensorRepository;
use Carbon\Carbon;

class SensorController extends Controller
{
    protected $influxRepo;

    public function __construct(InfluxSensorRepository $influxRepo)
    {
        $this->influxRepo = $influxRepo;
    }

    public function latest()
    {
        $records = $this->influxRepo->getLatestReadings(30);
        $formatted = [];
        
        foreach ($records as $table) {
            foreach ($table->records as $r) {
                $formatted[] = [
                    'created_at' => $r->getTime(),
                    'cr_estimated' => $r['cr_estimated'],
                    'ec' => $r['ec'],
                    'tds' => $r['tds'],
                    'ph' => $r['ph'],
                    'suhu_air' => $r['suhu_air'],
                    'suhu_lingkungan' => $r['suhu_lingkungan'],
                    'kelembapan' => $r['kelembapan'],
                    'tegangan' => $r['tegangan'],
                    'status' => $r['status'],
                ];
            }
        }
        
        // Reverse array agar waktu berurutan ASCENDING untuk chart
        return response()->json(array_reverse($formatted));
    }

    public function alerts()
    {
        // Ambil 50 data terakhir, filter yang danger/warning
        $records = $this->influxRepo->getLatestReadings(50);
        $alerts = [];
        
        foreach ($records as $table) {
            foreach ($table->records as $r) {
                $status = $r['status'];
                if (in_array($status, ['warning', 'danger'])) {
                    $alerts[] = [
                        'created_at' => $r->getTime(),
                        'cr_estimated' => $r['cr_estimated'],
                        'status' => $status,
                    ];
                }
            }
        }
        
        return response()->json(array_slice($alerts, 0, 10));
    }

    public function history(Request $request)
    {
        $limit = $request->input('limit', 1000);
        
        $from = $request->has('from') ? \Carbon\Carbon::parse($request->from)->format('Y-m-d H:i:s') : null;
        $to = $request->has('to') ? \Carbon\Carbon::parse($request->to)->format('Y-m-d H:i:s') : null;

        $data = $this->influxRepo->getReportData($from, $to, 'Semua');
        
        $sliced = array_slice($data->toArray(), 0, $limit);

        return response()->json(array_reverse($sliced));
    }
}
