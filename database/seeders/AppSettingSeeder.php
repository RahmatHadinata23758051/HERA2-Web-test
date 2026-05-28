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
            // Legacy keys (used by PDF reports)
            ['key' => 'nama_aplikasi', 'value' => 'HERA 2.0'],
            ['key' => 'nama_instansi', 'value' => 'Instansi Terkait'],
            ['key' => 'deskripsi', 'value' => 'Hexavalent Chromium Real-time Analytics'],
            ['key' => 'versi', 'value' => '2.0.0'],
            ['key' => 'copyright', 'value' => 'Instansi Terkait'],
            ['key' => 'tahun', 'value' => date('Y')],
            ['key' => 'logo_path', 'value' => ''],

            // App keys (used by SettingsController & layout views)
            ['key' => 'app_name', 'value' => 'HERA'],
            ['key' => 'app_version', 'value' => '2.0'],
            ['key' => 'app_institution', 'value' => 'Instansi Terkait'],
            ['key' => 'app_description', 'value' => 'Real-time Hexavalent Chromium Monitoring System'],
            ['key' => 'app_copyright', 'value' => 'Instansi Terkait'],
            ['key' => 'app_year', 'value' => date('Y')],
            ['key' => 'app_logo', 'value' => ''],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
