<?php

namespace Tests\Feature\Tracking;

use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VitalSignTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $patientRole = Role::firstOrCreate(['name' => 'paciente']);
        $this->patient = User::factory()->create();
        $this->patient->roles()->attach($patientRole->id);
        PatientProfile::create([
            'user_id' => $this->patient->id,
            'birth_date' => '1995-05-15',
            'gender' => 'Masculino',
            'diabetes_type' => 'Tipo 2',
            'weight' => 80,
            'height' => 180,
            'target_glucose_min' => 70,
            'target_glucose_max' => 130,
        ]);
    }

    public function test_patient_can_view_create_vital_signs_form(): void
    {
        $this->withoutVite();
        $this->actingAs($this->patient)->get(route('tracking.vital.create'))->assertInertia(fn (Assert $page) => $page
            ->component('Tracking/Vitals/Create')->url('/tracking/vitals')
            ->where('storeUrl', '/tracking/vitals')->has('measurementMoments', 4)
            ->has('stressLevels', 3)->has('trackingNavigation', 4));
    }

    public function test_patient_can_store_valid_vital_signs_without_calling_ai(): void
    {
        Http::fake();
        $data = [
            'glucose_level' => 110,
            'systolic' => 120,
            'diastolic' => 80,
            'heart_rate' => 72,
            'weight' => 80.5,
            'hba1c' => 5.8,
            'measurement_moment' => 'Ayunas',
            'stress_level' => 'Bajo',
            'notes' => 'Medición rutinaria matutina.',
        ];
        $this->actingAs($this->patient)->from(route('tracking.vital.create'))->post(route('tracking.vital.store'), $data)
            ->assertRedirect(route('dashboard'))->assertSessionHas('status', __('Registro de salud guardado con éxito.'));
        $this->assertDatabaseHas('vital_signs', ['user_id' => $this->patient->id, ...$data]);
        Http::assertNothingSent();
    }

    public function test_store_vital_signs_validation_fails_for_invalid_glucose(): void
    {
        $this->actingAs($this->patient)->from(route('tracking.vital.create'))->post(route('tracking.vital.store'), [
            'glucose_level' => 15,
            'measurement_moment' => 'Ayunas',
        ])->assertRedirect(route('tracking.vital.create'))->assertSessionHasErrors('glucose_level');
    }

    public function test_all_vital_ranges_are_enforced_by_backend(): void
    {
        $this->actingAs($this->patient)->from(route('tracking.vital.create'))->post(route('tracking.vital.store'), [
            'glucose_level' => 601,
            'systolic' => 251,
            'diastolic' => 151,
            'heart_rate' => 221,
            'weight' => 351,
            'hba1c' => 16,
            'measurement_moment' => 'Momento inválido',
            'stress_level' => str_repeat('x', 256),
            'notes' => str_repeat('x', 1001),
        ])->assertRedirect(route('tracking.vital.create'))->assertSessionHasErrors([
            'glucose_level', 'systolic', 'diastolic', 'heart_rate', 'weight',
            'hba1c', 'measurement_moment', 'stress_level', 'notes',
        ]);
        $this->assertDatabaseCount('vital_signs', 0);
    }

    public function test_inertia_store_uses_full_page_location_for_blade_dashboard(): void
    {
        $this->actingAs($this->patient)
            ->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('tracking.vital.store'), [
            'glucose_level' => 120,
            'measurement_moment' => 'Ayunas',
        ])->assertStatus(409)->assertHeader('X-Inertia-Location', route('dashboard'));
    }

    public function test_storing_vital_sign_invalidates_dashboard_cache(): void
    {
        $cacheKey = DashboardMetricsService::cacheKey($this->patient->id);
        Cache::put($cacheKey, ['dummy_data' => true], 300);
        $this->assertTrue(Cache::has($cacheKey));
        $this->actingAs($this->patient)->post(route('tracking.vital.store'), [
            'glucose_level' => 120,
            'measurement_moment' => 'Después de Comer',
        ]);
        $this->assertFalse(Cache::has($cacheKey));
    }
}
