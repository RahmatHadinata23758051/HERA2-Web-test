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
            ['build_number' => 3],
            [
                'version' => '2.2.0',
                'download_url' => 'https://github.com/RahmatHadinata23758051/Mobile-Apps-Design/releases/download/v2.2.0/app-release.apk',
                'changelog' => "- Redesain Modern Menyeluruh Seluruh Halaman Mobile (Login, Register, Home, Telemetri, Uji Lapangan, Riwayat, Profil)\n- Penyelarasan Tema Emerald HERA (#006948) konsisten dengan Dashboard Web\n- Kartu Telemetri Logam Berat Cr(VI) & Ni (µg/L) lebih rapi, presisi & interaktif\n- Tampilan Koordinat GPS Lapangan & Riwayat Pengujian dengan Desain Micro-Card",
                'file_size' => '53.8 MB',
                'is_force_update' => false,
                'is_active' => true,
            ]
        );
    }
}
