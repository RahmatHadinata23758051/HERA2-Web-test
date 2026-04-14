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
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'suhu_air' => 'nullable|numeric',
            'suhu_lingkungan' => 'nullable|numeric',
            'kelembapan' => 'nullable|numeric',
            'ec' => 'nullable|numeric',
            'tds' => 'nullable|numeric',
            'ph' => 'nullable|numeric',
            'tegangan' => 'nullable|numeric',
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
                'user_id' => $request->user()->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'suhu_air' => $request->suhu_air,
                'suhu_lingkungan' => $request->suhu_lingkungan,
                'kelembapan' => $request->kelembapan,
                'ec' => $request->ec,
                'tds' => $request->tds,
                'ph' => $request->ph,
                'tegangan' => $request->tegangan,
                'cr_estimated' => $crEstimated,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data pengujian terkalibrasi berhasil terekam!',
                'data' => $fieldTest
            ], 201);

        } catch (\Exception $e) {
            Log::error("Failed saving field test from mobile: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan data uji lapangan.'
            ], 500);
        }
    }

    /**
     * Retrieve testing history for the mobile app graph/list.
     */
    public function history(Request $request)
    {
        $limit = $request->query('limit', 50);

        // Mobile apps only see their own test history or maybe everything?
        // Since it's a team effort, let's fetch all tests so they see everyone's tests.
        // It helps validation in the field.
        $tests = FieldTest::orderBy('created_at', 'desc')
                    ->with('user:id,name') // include tester name
                    ->limit($limit)
                    ->get();

        // Need to format so Dart gets expected JSON structure
        return response()->json([
            'status' => true,
            'message' => 'Success retrieve history',
            'data' => $tests
        ]);
    }
}
