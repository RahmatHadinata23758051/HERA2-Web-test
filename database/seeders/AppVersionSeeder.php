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
        AppVersion::updateOrCreate(
            ['build_number' => 1],
            [
                'version' => '2.0.0',
                'download_url' => 'https://github.com/RahmatHadinata23758051/Mobile-Apps-Design/releases/download/v2.0.0/app-release.apk',
                'changelog' => "- Rilis Perdana Aplikasi HERA Mobile v2.0.0\n- Sistem In-App OTA Auto Update tanpa Play Store\n- Monitoring real-time Chromium Heksavalent : Cr(VI) dan Nikel : Ni (µg/L)\n- Pengujian Lapangan & Telemetri Sinkronisasi Otomatis",
                'file_size' => '53.7 MB',
                'is_force_update' => false,
                'is_active' => true,
            ]
        );
    }
}
