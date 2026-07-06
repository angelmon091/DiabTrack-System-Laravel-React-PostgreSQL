<?php

namespace Tests\Feature\Tracking;

use App\Models\Role;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\PatientProfile;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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

    /**
     * Test: Paciente puede visualizar la vista para crear actividades.
     */
    public function test_patient_can_view_create_activity_form(): void
    {
        $response = $this->actingAs($this->patient)->get(route('tracking.activity.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tracking.activity.create');
    }

    /**
     * Test: Registro exitoso de actividades deportivas.
     */
    public function test_patient_can_store_valid_activity_log(): void
    {
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
    }

    /**
     * Test: Guardar actividad deportiva mediante llamada AJAX.
     */
    public function test_patient_can_store_activity_via_ajax(): void
    {
        $data = [
            'activity_type' => 'natacion',
            'duration_minutes' => 60,
            'intensity' => 'alta',
        ];

        $response = $this->actingAs($this->patient)
            ->postJson(route('tracking.activity.store'), $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Registro de actividad guardado con éxito.'
        ]);
    }

    /**
     * Test: Validación de límites en duraciones de entrenamiento.
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

    /**
     * Test: La creación de un registro deportivo limpia la caché del panel principal.
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
