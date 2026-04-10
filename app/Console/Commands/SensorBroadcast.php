<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SensorReading;
use App\Events\SensorDataUpdated;

class SensorBroadcast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sensor:broadcast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate continuous sensor data generation and broadcast via Pusher';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting sensor simulator...");

        $ranges = [
            'ec' => ['min' => 100, 'max' => 1500, 'step' => 50],
            'tds' => ['min' => 64, 'max' => 960, 'step' => 30],
            'ph' => ['min' => 5.0, 'max' => 8.5, 'step' => 0.5],
            'suhu_air' => ['min' => 24.0, 'max' => 32.0, 'step' => 1.0],
            'suhu_lingkungan' => ['min' => 25.0, 'max' => 35.0, 'step' => 1.0],
            'kelembapan' => ['min' => 60.0, 'max' => 90.0, 'step' => 2.0],
            'tegangan' => ['min' => 3.5, 'max' => 4.2, 'step' => 0.1],
        ];

        // Prepare init values (middle of constraints)
        $current = [];
        foreach ($ranges as $key => $config) {
            $current[$key] = $config['min'] + (($config['max'] - $config['min']) / 2);
        }

        while (true) {
            foreach ($ranges as $key => $config) {
                $delta = (rand(-100, 100) / 100) * $config['step'];
                $newVal = $current[$key] + $delta;
                
                // clamp
                if ($newVal < $config['min']) $newVal = $config['min'];
                if ($newVal > $config['max']) $newVal = $config['max'];
                
                $current[$key] = round($newVal, 2);
            }

            try {
                $response = Http::timeout(2)->post('http://localhost:8001/predict', $current);
                
                if ($response->successful()) {
                    $json = $response->json();
                    
                    $cr_estimated = $json['cr_estimated'] ?? 0;
                    $status = $json['status'] ?? 'normal';

                    $dbData = array_merge($current, [
                        'cr_estimated' => $cr_estimated,
                        'status' => $status
                    ]);
                    
                    $reading = SensorReading::create($dbData);
                    
                    broadcast(new SensorDataUpdated($reading));
                    
                    $this->info("Broadcasted CR: {$cr_estimated} ng/L ({$status})");
                } else {
                    Log::error("FastAPI Error: " . $response->body());
                    $this->error("Fast API responded with an error.");
                }
            } catch (\Exception $e) {
                Log::error("FastAPI Timeout or Exception: " . $e->getMessage());
                $this->error("Fast API Request failed.");
            }

            sleep(15);
        }
    }
}
