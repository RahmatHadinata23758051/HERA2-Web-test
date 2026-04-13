<?php

namespace App\Repositories;

use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;

class InfluxSensorRepository
{
    protected $client;
    protected $org;
    protected $bucket;

    public function __construct()
    {
        $this->client = new Client([
            "url" => env('INFLUXDB_URL'),
            "token" => env('INFLUXDB_TOKEN'),
        ]);
        $this->org = env('INFLUXDB_ORG');
        $this->bucket = env('INFLUXDB_BUCKET');
    }

    /**
     * Menyimpan data sensor ke dalam InfluxDB
     */
    public function writeSensorData(array $data)
    {
        // Membuka session Write API
        $writeApi = $this->client->createWriteApi();
        
        // Membentuk struktur data model (Point Data)
        $point = Point::measurement('sensor_reading')
            ->addTag('location', $data['location'] ?? 'main_node')
            ->addField('ec', (float) ($data['ec'] ?? 0))
            ->addField('tds', (float) ($data['tds'] ?? 0))
            ->addField('ph', (float) ($data['ph'] ?? 0))
            ->addField('suhu_air', (float) ($data['suhu_air'] ?? 0))
            ->addField('suhu_lingkungan', (float) ($data['suhu_lingkungan'] ?? 0))
            ->addField('kelembapan', (float) ($data['kelembapan'] ?? 0))
            ->addField('tegangan', (float) ($data['tegangan'] ?? 0))
            ->addField('cr_estimated', (float) ($data['cr_estimated'] ?? 0))
            ->addField('status', $data['status'] ?? 'normal');

        // Eksekusi Tulis Data
        $writeApi->write($point, WritePrecision::S, $this->bucket, $this->org);
        
        // Tutup API (Best practice)
        $writeApi->close();
    }

    /**
     * Mengambil riwayat data terbaru
     */
    public function getLatestReadings($limit = 10)
    {
        $queryApi = $this->client->createQueryApi();
        
        // Format Flux Query Murni
        $query = "from(bucket: \"{$this->bucket}\")
                    |> range(start: -24h)
                    |> filter(fn: (r) => r._measurement == \"sensor_reading\")
                    |> pivot(rowKey:[\"_time\"], columnKey: [\"_field\"], valueColumn: \"_value\")
                    |> sort(columns: [\"_time\"], desc: true)
                    |> limit(n: {$limit})";

        return $queryApi->query($query, $this->org);
    }

    public function getReportData($fromDate = null, $toDate = null, $status = 'Semua')
    {
        $start = $fromDate ? \Carbon\Carbon::parse($fromDate)->setTimezone('UTC')->toIso8601String() : '-30d';
        $stop = $toDate ? \Carbon\Carbon::parse($toDate)->setTimezone('UTC')->toIso8601String() : 'now()';

        $query = "from(bucket: \"{$this->bucket}\")
                    |> range(start: {$start}, stop: {$stop})
                    |> filter(fn: (r) => r._measurement == \"sensor_reading\")
                    |> pivot(rowKey:[\"_time\"], columnKey: [\"_field\"], valueColumn: \"_value\")";

        if ($status && $status !== 'Semua') {
            $query .= "\n                    |> filter(fn: (r) => r.status == \"{$status}\")";
        }

        $query .= "\n                    |> sort(columns: [\"_time\"], desc: true)";

        $records = $this->client->createQueryApi()->query($query, $this->org);
        
        $formatted = [];
        foreach ($records as $table) {
            foreach ($table->records as $r) {
                // Return explicitly as an object to mock Eloquent model properties
                $obj = new \stdClass();
                $obj->created_at = \Carbon\Carbon::parse($r->getTime())->setTimezone(config('app.timezone')); // Local Time
                $obj->location = $r['location'];
                $obj->ec = (float) $r['ec'];
                $obj->tds = (float) $r['tds'];
                $obj->ph = (float) $r['ph'];
                $obj->suhu_air = (float) $r['suhu_air'];
                $obj->suhu_lingkungan = $r['suhu_lingkungan'] !== null ? (float) $r['suhu_lingkungan'] : 0;
                $obj->kelembapan = $r['kelembapan'] !== null ? (float) $r['kelembapan'] : 0;
                $obj->tegangan = $r['tegangan'] !== null ? (float) $r['tegangan'] : 0;
                $obj->cr_estimated = $r['cr_estimated'] !== null ? (float) $r['cr_estimated'] : 0;
                $obj->status = $r['status'] ?? 'normal';
                
                $formatted[] = $obj;
            }
        }
        return collect($formatted);
    }

    public function getDailyStats()
    {
        $start = \Carbon\Carbon::today()->setTimezone('UTC')->toIso8601String();
        
        $query = "from(bucket: \"{$this->bucket}\")
                    |> range(start: {$start})
                    |> filter(fn: (r) => r._measurement == \"sensor_reading\")
                    |> pivot(rowKey:[\"_time\"], columnKey: [\"_field\"], valueColumn: \"_value\")";
        
        $records = $this->client->createQueryApi()->query($query, $this->org);
        
        $total = 0;
        $warning = 0;
        $danger = 0;
        $sumCr = 0;
        $countCr = 0;

        foreach ($records as $table) {
            foreach ($table->records as $r) {
                $total++;
                $status = $r['status'];
                if ($status === 'warning') $warning++;
                if ($status === 'danger') $danger++;
                
                $cr = $r['cr_estimated'];
                if (is_numeric($cr)) {
                    $sumCr += $cr;
                    $countCr++;
                }
            }
        }
        
        return [
            'total' => $total,
            'warning' => $warning,
            'danger' => $danger,
            'avg_cr' => $countCr > 0 ? round($sumCr / $countCr, 2) : 0,
        ];
    }
}
