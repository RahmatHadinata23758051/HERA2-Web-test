<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SensorReadingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $cr = rand(10, 150);
            $status = 'normal';
            if ($cr >= 50 && $cr <= 100) {
                $status = 'warning';
            } elseif ($cr > 100) {
                $status = 'danger';
            }

            \App\Models\SensorReading::create([
                'ec' => rand(100, 1500),
                'tds' => rand(64, 960),
                'ph' => rand(50, 85) / 10,
                'suhu_air' => rand(240, 320) / 10,
                'suhu_lingkungan' => rand(250, 350) / 10,
                'kelembapan' => rand(600, 900) / 10,
                'tegangan' => rand(35, 42) / 10,
                'cr_estimated' => $cr,
                'status' => $status
            ]);
        }
    }
}
