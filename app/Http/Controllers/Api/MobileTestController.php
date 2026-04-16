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
     * Store new testing data submitted by mobile app (Smartphone Trigger).
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

        // Auto-predict Chromium using the existing FastAPI engine
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

        // Save data to database
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
     * Retrieve testing history for the mobile app with cursor-based pagination.
     * Mobile expects: { status: true, data: { data: [...], next_cursor: "..." } }
     */
    public function history(Request $request)
    {
        $limit  = (int) $request->query('limit', 20);
        $cursor = $request->query('cursor'); // ISO datetime string or null

        $query = FieldTest::with('user:id,name')
                    ->orderBy('created_at', 'desc');

        // Apply cursor: fetch records older than the cursor timestamp
        if ($cursor) {
            $query->where('created_at', '<', $cursor);
        }

        $tests = $query->limit($limit + 1)->get();

        // Determine next_cursor
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

