<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\FieldTest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;

class FieldTestQcTest extends TestCase
{
    use DatabaseTransactions;

    protected function getTestUser(): User
    {
        $user = User::where('role', 'direksi')->first();
        if ($user) {
            return $user;
        }

        return User::factory()->create([
            'name' => 'QC Tester',
            'email' => 'qc@hera.test',
            'role' => 'direksi',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_mobile_testing_location_stores_successfully_without_sensor_data_crash(): void
    {
        $user = $this->getTestUser();
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/mobile/testing/location', [
            'latitude'  => -6.917464,
            'longitude' => 107.619123,
            'altitude'  => 720.5,
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data pengujian terkalibrasi berhasil terekam!',
                 ]);

        $this->assertDatabaseHas('field_tests', [
            'user_id'   => $user->id,
            'latitude'  => -6.917464,
            'longitude' => 107.619123,
        ]);
    }

    public function test_mobile_testing_location_with_manual_sensors_and_ai_fallback(): void
    {
        $user = $this->getTestUser();
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/mobile/testing/location', [
            'latitude'     => -6.208812,
            'longitude'    => 106.845623,
            'ph'           => 7.25,
            'tds'          => 150.0,
            'temperature'  => 28.5,
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'status' => true,
                 ]);

        $record = FieldTest::where('user_id', $user->id)
            ->where('latitude', -6.208812)
            ->first();

        $this->assertNotNull($record);
        $this->assertEquals(-6.208812, (float) $record->latitude);
        $this->assertEquals(7.25, (float) $record->ph);
    }

    public function test_web_field_test_location_filter_query_does_not_crash_on_decimal_ilike(): void
    {
        $user = $this->getTestUser();

        $response = $this->actingAs($user)->get('/laporan/pengujian?location=107.61');
        $response->assertStatus(200);
        $response->assertSee('Cr(VI) Est. (µg/L)');
        $response->assertSee('Ni Est. (µg/L)');
    }

    public function test_web_dashboard_renders_with_chromium_and_nickel_ug_per_l(): void
    {
        $user = $this->getTestUser();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Chromium Heksavalen : Cr(VI)');
        $response->assertSee('Nikel : Ni');
        $response->assertSee('µg/L');
    }

    public function test_web_laporan_renders_with_ni_column_and_correct_units(): void
    {
        $user = $this->getTestUser();

        $response = $this->actingAs($user)->get('/laporan');
        $response->assertStatus(200);
        $response->assertSee('Cr(VI) (µg/L)');
        $response->assertSee('Ni (µg/L)');
    }

    public function test_threshold_settings_page_renders_with_ni_and_cr_units(): void
    {
        $user = $this->getTestUser();

        $response = $this->actingAs($user)->get('/pengaturan/threshold');
        $response->assertStatus(200);
        $response->assertSee('Chromium Heksavalen : Cr(VI)');
        $response->assertSee('Nikel : Ni');
        $response->assertSee('µg/L');
    }
}
