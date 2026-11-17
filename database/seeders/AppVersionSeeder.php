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
            ['build_number' => 2],
            [
                'version' => '2.1.0',
                'download_url' => 'https://github.com/RahmatHadinata23758051/Mobile-Apps-Design/releases/download/v2.1.0/app-release.apk',
                'changelog' => "- Tampilan Desain Modern Baru (Quick Actions Bento Grid di Beranda)\n- Redesain Kartu Pengguna dengan Status Online Indicator\n- Peningkatan responsivitas dan stabilitas sistem telemetri sensor",
                'file_size' => '53.8 MB',
                'is_force_update' => false,
                'is_active' => true,
            ]
        );
    }
}
