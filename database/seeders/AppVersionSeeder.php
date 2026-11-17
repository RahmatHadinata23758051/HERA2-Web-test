<?php

namespace Database\Seeders;

use App\Models\AppVersion;
use Illuminate\Database\Seeder;

class AppVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AppVersion::firstOrCreate(
            ['build_number' => 1],
            [
                'version' => '1.0.0',
                'download_url' => 'https://github.com/RahmatHadinata23758051/HERA2-Web-test/releases',
                'changelog' => "- Rilis Perdana Aplikasi HERA Mobile\n- Monitoring real-time Chromium Heksavalent : Cr(VI) dan Nikel : Ni (µg/L)\n- Pengujian Lapangan & Sinkronisasi Otomatis",
                'file_size' => '25.0 MB',
                'is_force_update' => false,
                'is_active' => true,
            ]
        );
    }
}
