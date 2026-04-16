<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\FieldTest;

class MobileTestController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/mobile/testing/location",
     *     summary="Kirim data pengujian lapangan",
     *     description="Menyimpan data hasil pengujian lapangan dari aplikasi mobile. Data GPS (latitude, longitude, altitude) dan sensor (TDS, pH, EC, suhu, kelembapan, tegangan) disimpan ke database. Sistem secara otomatis menghitung estimasi kadar Chromium menggunakan model AI FastAPI.",
     *     tags={"Field Test"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"latitude","longitude"},
     *             @OA\Property(property="latitude", type="number", format="float", example=-6.967585, description="Koordinat lintang lokasi pengujian"),
     *             @OA\Property(property="longitude", type="number", format="float", example=107.659063, description="Koordinat bujur lokasi pengujian"),
     *             @OA\Property(property="altitude", type="number", format="float", example=768.5, description="Ketinggian lokasi (meter dpl, opsional)"),
     *             @OA\Property(property="suhu_air", type="number", format="float", example=28.5, description="Suhu air (°C)"),
     *             @OA\Property(property="suhu_lingkungan", type="number", format="float", example=30.0, description="Suhu udara lingkungan (°C)"),
     *             @OA\Property(property="kelembapan", type="number", format="float", example=75.0, description="Kelembapan udara (%)"),
     *             @OA\Property(property="ec", type="number", format="float", example=450.0, description="Electrical Conductivity (mS/cm)"),
     *             @OA\Property(property="tds", type="number", format="float", example=300.0, description="Total Dissolved Solids (ppm)"),
     *             @OA\Property(property="ph", type="number", format="float", example=7.5, description="Nilai pH air"),
     *             @OA\Property(property="tegangan", type="number", format="float", example=3.8, description="Tegangan baterai sensor (V)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Data pengujian berhasil disimpan",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data pengujian terkalibrasi berhasil terekam!"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=7),
     *                 @OA\Property(property="user_id", type="integer", example=3),
     *                 @OA\Property(property="latitude", type="number", example=-6.967585),
     *                 @OA\Property(property="longitude", type="number", example=107.659063),
     *                 @OA\Property(property="altitude", type="number", example=768.5),
     *                 @OA\Property(property="tds", type="number", example=300),
     *                 @OA\Property(property="ph", type="number", example=7.5),
     *                 @OA\Property(property="ec", type="number", example=450),
     *                 @OA\Property(property="suhu_air", type="number", example=28.5),
     *                 @OA\Property(property="suhu_lingkungan", type="number", example=30),
     *                 @OA\Property(property="kelembapan", type="number", example=75),
     *                 @OA\Property(property="tegangan", type="number", example=3.8),
     *                 @OA\Property(property="cr_estimated", type="number", example=0.01545, description="Estimasi kadar Chromium (mg/L) dari model AI"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2026-04-16T06:30:18.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token tidak valid"),
     *     @OA\Response(response=422, description="Validasi gagal — latitude/longitude wajib diisi")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'latitude'         => 'required|numeric',
            'longitude'        => 'required|numeric',
            'altitude'         => 'nullable|numeric',
            'suhu_air'         => 'nullable|numeric',
            'suhu_lingkungan'  => 'nullable|numeric',
            'kelembapan'       => 'nullable|numeric',
            'ec'               => 'nullable|numeric',
            'tds'              => 'nullable|numeric',
            'ph'               => 'nullable|numeric',
            'tegangan'         => 'nullable|numeric',
        ]);

        $sensorData = $request->only([
            'suhu_air', 'suhu_lingkungan', 'kelembapan', 'ec', 'tds', 'ph', 'tegangan'
        ]);

        $crEstimated = null;

        try {
            $aiUrl = env('AI_SERVICE_URL', 'http://localhost:8001') . '/predict';
            $response = Http::timeout(5)->post($aiUrl, $sensorData);

            if ($response->successful()) {
                $json = $response->json();
                $crEstimated = $json['cr_estimated'] ?? null;
            } else {
                Log::warning("MobileTestController: AI Predict API failed with status " . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("MobileTestController AI Exception: " . $e->getMessage());
        }

        try {
            $fieldTest = FieldTest::create([
                'user_id'          => $request->user()->id,
                'latitude'         => $request->latitude,
                'longitude'        => $request->longitude,
                'altitude'         => $request->altitude,
                'suhu_air'         => $request->suhu_air,
                'suhu_lingkungan'  => $request->suhu_lingkungan,
                'kelembapan'       => $request->kelembapan,
                'ec'               => $request->ec,
                'tds'              => $request->tds,
                'ph'               => $request->ph,
                'tegangan'         => $request->tegangan,
                'cr_estimated'     => $crEstimated,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Data pengujian terkalibrasi berhasil terekam!',
                'data'    => $fieldTest
            ], 201);

        } catch (\Exception $e) {
            Log::error("Failed saving field test from mobile: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan data uji lapangan.'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/mobile/testing/history",
     *     summary="Riwayat pengujian lapangan",
     *     description="Mengambil riwayat data pengujian lapangan semua petugas menggunakan cursor-based pagination. Untuk halaman berikutnya, kirimkan nilai `next_cursor` dari response sebelumnya sebagai parameter `cursor`.",
     *     tags={"Field Test"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Jumlah item per halaman (default: 20, maksimum: 100)",
     *         required=false,
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *     @OA\Parameter(
     *         name="cursor",
     *         in="query",
     *         description="ISO datetime dari `next_cursor` response sebelumnya. Kosongkan untuk halaman pertama.",
     *         required=false,
     *         @OA\Schema(type="string", example="2026-04-15T13:16:28+07:00")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data riwayat berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success retrieve history"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=6),
     *                         @OA\Property(property="latitude", type="string", example="-6.96758500"),
     *                         @OA\Property(property="longitude", type="string", example="107.65906300"),
     *                         @OA\Property(property="altitude", type="string", example="768.50"),
     *                         @OA\Property(property="ph", type="number", example=7.5),
     *                         @OA\Property(property="tds", type="number", example=300),
     *                         @OA\Property(property="ec", type="number", example=450),
     *                         @OA\Property(property="cr_estimated", type="number", example=0.01545),
     *                         @OA\Property(property="created_at", type="string", example="2026-04-16T06:30:18.000000Z"),
     *                         @OA\Property(property="user", type="object",
     *                             @OA\Property(property="id", type="integer", example=3),
     *                             @OA\Property(property="name", type="string", example="test123")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="next_cursor", type="string", nullable=true, example="2026-04-15T13:16:28+07:00", description="Nilai ini dikirim sebagai parameter `cursor` untuk halaman berikutnya. Null jika sudah halaman terakhir.")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token tidak valid")
     * )
     */
    public function history(Request $request)
    {
        $limit  = (int) $request->query('limit', 20);
        $cursor = $request->query('cursor');

        $query = FieldTest::with('user:id,name')
                    ->orderBy('created_at', 'desc');

        if ($cursor) {
            $query->where('created_at', '<', $cursor);
        }

        $tests = $query->limit($limit + 1)->get();

        $nextCursor = null;
        if ($tests->count() > $limit) {
            $tests = $tests->take($limit);
            $nextCursor = $tests->last()->created_at->toIso8601String();
        }

        return response()->json([
            'status'  => true,
            'message' => 'Success retrieve history',
            'data'    => [
                'data'        => $tests->values(),
                'next_cursor' => $nextCursor,
            ]
        ]);
    }
}
