<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AppVersion;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AppVersionTest extends TestCase
{
    use DatabaseTransactions;

    protected function getDireksiUser(): User
    {
        $user = User::where('role', 'direksi')->first();
        if ($user) {
            return $user;
        }

        return User::factory()->create([
            'name' => 'Direksi Tester',
            'email' => 'direksi_tester@hera.test',
            'role' => 'direksi',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_get_latest_app_version_api_returns_success(): void
    {
        AppVersion::create([
            'version' => '1.0.1',
            'build_number' => 2,
            'download_url' => 'https://example.com/v1.0.1.apk',
            'changelog' => 'Perbaikan bug dan optimasi.',
            'is_force_update' => false,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/mobile/app-version/latest?version=1.0.0&build_number=1');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'update_available' => true,
                     'latest_version' => '1.0.1',
                     'build_number' => 2,
                     'download_url' => 'https://example.com/v1.0.1.apk',
                     'is_force_update' => false,
                 ]);
    }

    public function test_check_app_version_reports_no_update_when_same_or_newer_build(): void
    {
        AppVersion::create([
            'version' => '1.0.0',
            'build_number' => 1,
            'download_url' => 'https://example.com/v1.0.0.apk',
            'changelog' => 'Rilis awal.',
            'is_force_update' => false,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/mobile/app-version/latest?version=1.0.0&build_number=1');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'update_available' => false,
                     'latest_version' => '1.0.0',
                     'build_number' => 1,
                 ]);
    }

    public function test_direksi_can_view_and_create_app_version_in_web_admin(): void
    {
        $user = $this->getDireksiUser();

        $viewResponse = $this->actingAs($user)->get(route('settings.app-version'));
        $viewResponse->assertStatus(200);

        $storeResponse = $this->actingAs($user)->post(route('settings.app-version.store'), [
            'version' => '2.0.0',
            'build_number' => 10,
            'download_url' => 'https://github.com/RahmatHadinata23758051/HERA2-Web-test/releases/download/v2.0.0/app-release.apk',
            'changelog' => "Fitur auto-update dan perbaikan antarmuka.\nSensor terkalibrasi.",
            'file_size' => '25 MB',
            'is_force_update' => '1',
            'is_active' => '1',
        ]);

        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('app_versions', [
            'version' => '2.0.0',
            'build_number' => 10,
            'is_force_update' => true,
            'is_active' => true,
        ]);
    }
}
