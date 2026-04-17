<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Repositories\InfluxSensorRepository;
use App\Events\SensorDataUpdated;
use App\Models\Threshold;

class ProcessSensorIngestion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sensorData;

    public function __construct(array $sensorData)
    {
        $this->sensorData = $sensorData;
    }

    public function handle(InfluxSensorRepository $influxRepo): void
    {
        try {
            // 1. Minta Prediksi dari AI Service (FastAPI Docker atau Local)
            $aiUrl = env('AI_SERVICE_URL', 'http://localhost:8001') . '/predict';
            $response = Http::timeout(5)->post($aiUrl, $this->sensorData);
            
            if ($response->successful()) {
                $json = $response->json();
                
                // 2. Ambil nilai prediksi Chromium dari AI
                $cr = (float) ($json['cr_estimated'] ?? 0);

                // 3. Klasifikasi status berdasarkan threshold kustom (dari DB, di-cache)
                $status = Threshold::classifyCr($cr);

                $this->sensorData['cr_estimated'] = $cr;
                $this->sensorData['status']        = $status;

                // 3. Simpan Riwayat Tetap ke InfluxDB Container
                $influxRepo->writeSensorData($this->sensorData);
                
                // Tambahkan waktu timestamp murni untuk kebutuhan parsing Javascript Dashboard
                $broadcastData = array_merge($this->sensorData, [
                    'created_at' => now()->toIso8601String()
                ]);

                // 4. Siarkan data final via Websocket (Soketi Emulator lokal)
                broadcast(new SensorDataUpdated($broadcastData));
                
            } else {
                Log::error("Proses Ingestion AI Gagal: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Proses Ingestion Exception: " . $e->getMessage());
        }
    }
}
