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

    /**
     * @OA\Get(
     *     path="/api/mobile/sensor/latest",
     *     summary="Data sensor terbaru",
     *     description="Mengembalikan satu pembacaan sensor IoT paling terakhir dari InfluxDB. Data mencakup TDS, pH, EC, suhu air, suhu lingkungan, kelembapan, tegangan, dan status kualitas air.",
     *     tags={"Sensor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Data sensor terbaru berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OK"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="location", type="string", example="citarum_node_alpha"),
     *                 @OA\Property(property="tds", type="number", example=450.5),
     *                 @OA\Property(property="ph", type="number", example=7.2),
     *                 @OA\Property(property="ec", type="number", example=680.3),
     *                 @OA\Property(property="suhu_air", type="number", example=28.5),
     *                 @OA\Property(property="suhu_lingkungan", type="number", example=31.0),
     *                 @OA\Property(property="kelembapan", type="number", example=74.2),
     *                 @OA\Property(property="tegangan", type="number", example=3.78),
     *                 @OA\Property(property="cr_estimated", type="number", example=0.0214),
     *                 @OA\Property(property="status", type="string", example="normal", description="Nilai: normal, warning, danger"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2026-04-16T06:30:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Belum ada data sensor"),
     *     @OA\Response(response=401, description="Token tidak valid")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/mobile/sensor/history",
     *     summary="Riwayat pembacaan sensor",
     *     description="Mengambil riwayat pembacaan sensor dari InfluxDB dengan filter tanggal dan status kualitas air. Mendukung offset pagination.",
     *     tags={"Sensor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="from_date",
     *         in="query",
     *         description="Tanggal awal filter (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2026-04-01")
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="query",
     *         description="Tanggal akhir filter (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2026-04-16")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter status kualitas air",
     *         required=false,
     *         @OA\Schema(type="string", enum={"normal","warning","danger"}, example="danger")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Jumlah item per halaman (default: 50, maks: 500)",
     *         required=false,
     *         @OA\Schema(type="integer", example=50)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Nomor halaman",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Riwayat sensor berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OK"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=50),
     *                 @OA\Property(property="total", type="integer", example=243)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token tidak valid")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/mobile/sensor/alerts",
     *     summary="Daftar alert sensor",
     *     description="Mengembalikan maksimal 20 pembacaan sensor terkini yang berstatus `warning` atau `danger`. Berguna untuk notifikasi kondisi air berbahaya di aplikasi mobile.",
     *     tags={"Sensor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar alert berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OK"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="status", type="string", example="danger"),
     *                     @OA\Property(property="cr_estimated", type="number", example=0.156),
     *                     @OA\Property(property="timestamp", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token tidak valid")
     * )
     */
    public function alerts()
    {
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

    /**
     * @OA\Get(
     *     path="/api/mobile/sensor/daily-stats",
     *     summary="Statistik sensor harian",
     *     description="Mengembalikan ringkasan statistik pembacaan sensor hari ini: jumlah total pembacaan, jumlah status warning, jumlah status danger, dan rata-rata estimasi kadar Chromium.",
     *     tags={"Sensor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistik harian berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OK"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="date", type="string", format="date", example="2026-04-16"),
     *                 @OA\Property(property="total", type="integer", example=48),
     *                 @OA\Property(property="warning", type="integer", example=5),
     *                 @OA\Property(property="danger", type="integer", example=2),
     *                 @OA\Property(property="avg_cr", type="number", example=0.0213, description="Rata-rata estimasi kadar Chromium hari ini (mg/L)")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token tidak valid")
     * )
     */
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
