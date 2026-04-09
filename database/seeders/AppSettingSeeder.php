<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'nama_aplikasi', 'value' => 'HERA 2.0'],
            ['key' => 'nama_instansi', 'value' => 'Universitas Hasanuddin'],
            ['key' => 'deskripsi', 'value' => 'Hexavalent Chromium Real-time Analytics'],
            ['key' => 'versi', 'value' => '2.0.0'],
            ['key' => 'copyright', 'value' => 'Universitas Hasanuddin'],
            ['key' => 'tahun', 'value' => date('Y')],
            ['key' => 'logo_path', 'value' => ''], 
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
