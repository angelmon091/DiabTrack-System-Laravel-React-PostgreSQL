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

class ActivityTrackingTest extends TestCase
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
            'weight' => 75,
            'height' => 178,
        ]);
    }

    public function test_patient_can_view_create_activity_form(): void
    {
        $this->withoutVite();
        $this->actingAs($this->patient)->get(route('tracking.activity.create'))->assertInertia(fn (Assert $page) => $page
            ->component('Tracking/Activity/Create')->url('/tracking/activity')
            ->where('storeUrl', '/tracking/activity')->has('activityTypes', 9)
            ->has('intensities', 3)->has('energyLevels', 5)->has('trackingNavigation', 4));
    }

    /**
     * Prueba: Registro exitoso de actividades deportivas.
     */
    public function test_patient_can_store_valid_activity_log(): void
    {
        Http::fake();
        $data = [
            'activity_type' => 'caminar',
            'duration_minutes' => 45,
            'intensity' => 'media',
            'start_time' => '17:00',
            'end_time' => '17:45',
            'energy_level' => 'alta',
        ];

        $response = $this->actingAs($this->patient)
            ->from(route('tracking.activity.create'))
            ->post(route('tracking.activity.store'), $data);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status', __('Registro de actividad guardado con éxito.'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->patient->id,
            'activity_type' => 'caminar',
            'duration_minutes' => 45,
            'intensity' => 'media',
            'energy_level' => 'alta',
        ]);
        Http::assertNothingSent();
    }

    /**
     * Prueba: Validación de límites en duraciones de entrenamiento.
     */
    public function test_store_activity_validation_fails_for_excessive_duration(): void
    {
        $data = [
            'activity_type' => 'caminar',
            'duration_minutes' => 500, // Máximo permitido es 480 minutos
            'intensity' => 'baja',
        ];

        $response = $this->actingAs($this->patient)
            ->from(route('tracking.activity.create'))
            ->post(route('tracking.activity.store'), $data);

        $response->assertRedirect(route('tracking.activity.create'));
        $response->assertSessionHasErrors('duration_minutes');
    }

    public function test_all_activity_fields_are_validated_by_backend(): void
    {
        $this->actingAs($this->patient)->from(route('tracking.activity.create'))->post(route('tracking.activity.store'), [
            'activity_type' => str_repeat('x', 101),
            'duration_minutes' => 0,
            'intensity' => 'extrema',
            'start_time' => '25:00',
            'end_time' => 'no-es-hora',
            'energy_level' => 'agotada',
        ])->assertRedirect(route('tracking.activity.create'))->assertSessionHasErrors([
            'activity_type', 'duration_minutes', 'intensity', 'start_time', 'end_time', 'energy_level',
        ]);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_inertia_store_uses_full_page_location_for_blade_dashboard(): void
    {
        $this->actingAs($this->patient)
            ->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('tracking.activity.store'), [
                'activity_type' => 'caminar',
                'duration_minutes' => 30,
                'intensity' => 'media',
            ])->assertStatus(409)->assertHeader('X-Inertia-Location', route('dashboard'));
    }

    /**
     * Prueba: La creación de un registro deportivo limpia la caché del panel principal.
     */
    public function test_storing_activity_invalidates_dashboard_cache(): void
    {
        $cacheKey = DashboardMetricsService::cacheKey($this->patient->id);
        Cache::put($cacheKey, ['dummy_activity' => true], 300);

        $this->assertTrue(Cache::has($cacheKey));

        $data = [
            'activity_type' => 'caminar',
            'duration_minutes' => 30,
            'intensity' => 'baja',
        ];

        $this->actingAs($this->patient)->post(route('tracking.activity.store'), $data);

        $this->assertFalse(Cache::has($cacheKey));
    }
}
