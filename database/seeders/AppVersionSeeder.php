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
            ['build_number' => 4],
            [
                'version' => '2.3.0',
                'download_url' => 'https://github.com/RahmatHadinata23758051/Mobile-Apps-Design/releases/download/v2.3.0/app-release.apk',
                'changelog' => "- Perbaikan Flow Pengujian Lapangan (GPS): bypass toleransi mode pengujian dan dialog sukses interaktif estimasi Cr(VI) & Ni\n- Penyelarasan Nama Menu Mobile 1:1 dengan Web (Dashboard, Monitoring Telemetri, Pengujian Lapangan, Laporan Historis, Data Pengujian Lapangan, Profil Akun)\n- Diferensiasi Visual Tegas antara Log Sensor Otomatis IoT [NODE IOT] dan Hasil Uji GPS Petugas [UJI GPS]\n- Penyempurnaan Responsivitas UI pada Header Tabel Riwayat & Isolasi Error Auto-Refresh",
                'file_size' => '54.2 MB',
                'is_force_update' => false,
                'is_active' => true,
            ]
        );
    }
}
