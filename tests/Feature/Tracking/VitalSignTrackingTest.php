<?php

namespace Tests\Feature\Tracking;

use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VitalSignTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $patient;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear rol de paciente
        $patientRole = Role::firstOrCreate(['name' => 'paciente']);

        // Crear usuario paciente con su perfil clínico inicial
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

    /**
     * Prueba: Un paciente puede acceder a la interfaz de registro de signos vitales.
     */
    public function test_patient_can_view_create_vital_signs_form(): void
    {
        $response = $this->actingAs($this->patient)->get(route('tracking.vital.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tracking.vital.create');
    }

    /**
     * Prueba: Registro exitoso de signos vitales y persistencia correcta en base de datos.
     */
    public function test_patient_can_store_valid_vital_signs(): void
    {
        $data = [
            'glucose_level' => 110,
            'systolic' => 120,
            'diastolic' => 80,
            'heart_rate' => 72,
            'weight' => 80.5,
            'hba1c' => 5.8,
            'measurement_moment' => 'Ayunas',
            'stress_level' => 'bajo',
            'notes' => 'Medición rutinaria matutina.',
        ];

        $response = $this->actingAs($this->patient)
            ->from(route('tracking.vital.create'))
            ->post(route('tracking.vital.store'), $data);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status', __('Registro de salud guardado con éxito.'));

        $this->assertDatabaseHas('vital_signs', [
            'user_id' => $this->patient->id,
            'glucose_level' => 110,
            'systolic' => 120,
            'diastolic' => 80,
            'heart_rate' => 72,
            'weight' => 80.5,
            'hba1c' => 5.8,
            'measurement_moment' => 'Ayunas',
            'stress_level' => 'bajo',
            'notes' => 'Medición rutinaria matutina.',
        ]);
    }

    /**
     * Prueba: Registro de signos vitales mediante petición AJAX (espera respuesta JSON).
     */
    public function test_patient_can_store_vital_signs_via_ajax(): void
    {
        $data = [
            'glucose_level' => 95,
            'measurement_moment' => 'Antes de Comer',
        ];

        $response = $this->actingAs($this->patient)
            ->postJson(route('tracking.vital.store'), $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Registro de salud guardado con éxito.',
        ]);
    }

    /**
     * Prueba: Validaciones del Form Request (VitalSignRequest) rechazan glucosa inválida.
     */
    public function test_store_vital_signs_validation_fails_for_invalid_glucose(): void
    {
        $invalidData = [
            'glucose_level' => 15, // Mínimo es 20
            'measurement_moment' => 'Ayunas',
        ];

        $response = $this->actingAs($this->patient)
            ->from(route('tracking.vital.create'))
            ->post(route('tracking.vital.store'), $invalidData);

        $response->assertRedirect(route('tracking.vital.create'));
        $response->assertSessionHasErrors('glucose_level');
    }

    /**
     * Prueba: Guardar un nuevo signo vital destruye automáticamente el caché del dashboard.
     */
    public function test_storing_vital_sign_invalidates_dashboard_cache(): void
    {
        $cacheKey = DashboardMetricsService::cacheKey($this->patient->id);
        Cache::put($cacheKey, ['dummy_data' => true], 300);

        $this->assertTrue(Cache::has($cacheKey));

        $data = [
            'glucose_level' => 120,
            'measurement_moment' => 'Después de Comer',
        ];

        $this->actingAs($this->patient)->post(route('tracking.vital.store'), $data);

        // La caché debe haberse borrado
        $this->assertFalse(Cache::has($cacheKey));
    }
}
