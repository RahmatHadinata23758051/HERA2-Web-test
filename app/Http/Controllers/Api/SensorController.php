<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\ProcessSensorIngestion;
use Illuminate\Support\Facades\Validator;

class SensorController extends Controller
{
    public function ingest(Request $request)
    {
        // 1. Validasi input kilat
        $validator = Validator::make($request->all(), [
            'ec' => 'required|numeric',
            'tds' => 'required|numeric',
            'ph' => 'required|numeric',
            'suhu_air' => 'required|numeric',
            'suhu_lingkungan' => 'required|numeric',
            'kelembapan' => 'required|numeric',
            'tegangan' => 'required|numeric',
            'location' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Tembakkan langsung antrean ke Redis Queue
        // Supaya HTTP Response balik ke Mikrokomputer dalam hitungan milidetik 
        // tanpa memblokir I/O untuk pendaftaran database/AI
        ProcessSensorIngestion::dispatch($validator->validated())->onQueue('default');

        return response()->json([
            'status' => 'success',
            'message' => 'Data received and queued for processing.'
        ], 202); // 202 Accepted
    }
}
