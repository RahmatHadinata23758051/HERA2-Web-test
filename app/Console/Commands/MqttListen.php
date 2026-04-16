<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Jobs\ProcessSensorIngestion;
use Illuminate\Support\Facades\Log;

class MqttListen extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Mendengarkan telemetri dari Mosquitto (MQTT) dan meneruskannya ke antrean Redis.';

    public function handle()
    {
        $server   = env('MQTT_HOST', '127.0.0.1');
        $port     = env('MQTT_PORT', 1883);
        $clientId = 'HERA_Backend_Worker_' . rand(1000, 9999);
        $topic    = 'hera/telemetry/+';
        
        $this->info("🔄 Menghubungkan ke MQTT Broker $server:$port dengan ID: $clientId");

        try {
            $mqtt = new MqttClient($server, $port, $clientId);
            
            $settings = (new ConnectionSettings)
                ->setKeepAliveInterval(60);

            // Connect using anonymous settings
            $mqtt->connect($settings, true);
            $this->info("✅ Berhasil! Mengunci telinga pada Topik: $topic\n");

            $mqtt->subscribe($topic, function (string $topic, string $message) {
                $this->info("[" . now()->format('H:i:s') . "] 📥 Paket tertangkap di $topic");
                
                $data = json_decode($message, true);
                if (is_array($data)) {
                    // Cek kelengkapan parameter sekilas
                    if (isset($data['ec'], $data['tds'], $data['ph'])) {
                        // Tembakkan langsung ke Redis Queue!
                        // Ini akan memicu AI di latar belakang dan nyambung ke WebSocket
                        ProcessSensorIngestion::dispatch($data)->onQueue('default');
                        $this->line("   ↳ ✅ Di-dispatch ke Redis Queue (ProcessSensorIngestion)");
                    } else {
                        $this->error("   ↳ ❌ Drop: Struktur JSON tidak valid. Data = " . json_encode($data));
                    }
                } else {
                    $this->error("   ↳ ❌ Drop: Gagal parse JSON. " . json_last_error_msg());
                }
            }, 0);

            $mqtt->loop(true);
            $mqtt->disconnect();
            
        } catch (\Exception $e) {
            $this->error("🔥 Gagal Mencegat MQTT: " . $e->getMessage());
            Log::error("MQTT Listen Exception: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
