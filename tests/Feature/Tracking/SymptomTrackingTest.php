<?php

namespace Tests\Feature\Tracking;

use App\Models\Role;
use App\Models\User;
use App\Models\Symptom;
use App\Models\PatientProfile;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SymptomTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $patient;
    private Symptom $symptom1;
    private Symptom $symptom2;

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
            'weight' => 70,
            'height' => 175,
        ]);

        // Crear síntomas en base de datos
        $this->symptom1 = Symptom::create(['name' => 'Dolor de cabeza', 'category' => 'physical']);
        $this->symptom2 = Symptom::create(['name' => 'Sudoracion nocturna', 'category' => 'nocturnal']);
    }

    /**
     * Test: Acceso al formulario de síntomas clasificados por categorías.
     */
    public function test_patient_can_view_create_symptoms_form(): void
    {
        $response = $this->actingAs($this->patient)->get(route('tracking.symptom.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tracking.symptom.create');
        $response->assertViewHas('symptoms');
    }

    /**
     * Test: Registro correcto de uno o múltiples síntomas en la tabla pivot.
     */
    public function test_patient_can_store_multiple_symptoms(): void
    {
        $data = [
            'symptoms' => [
                $this->symptom1->id,
                $this->symptom2->id
            ]
        ];

        $response = $this->actingAs($this->patient)
            ->from(route('tracking.symptom.create'))
            ->post(route('tracking.symptom.store'), $data);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status', __('Registro de síntomas guardado con éxito.'));

        // Validar asociación en la tabla pivot
        $this->assertDatabaseHas('symptom_user', [
            'user_id' => $this->patient->id,
            'symptom_id' => $this->symptom1->id,
        ]);

        $this->assertDatabaseHas('symptom_user', [
            'user_id' => $this->patient->id,
            'symptom_id' => $this->symptom2->id,
        ]);
    }

    /**
     * Test: Guardar síntomas mediante peticiones AJAX.
     */
    public function test_patient_can_store_symptoms_via_ajax(): void
    {
        $data = [
            'symptoms' => [$this->symptom1->id]
        ];

        $response = $this->actingAs($this->patient)
            ->postJson(route('tracking.symptom.store'), $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Registro de síntomas guardado con éxito.'
        ]);
    }

    /**
     * Test: Form Request falla ante selección de síntomas vacía.
     */
    public function test_store_symptoms_fails_when_no_symptom_selected(): void
    {
        $data = [
            'symptoms' => [] // Debe seleccionar al menos uno
        ];

        $response = $this->actingAs($this->patient)
            ->from(route('tracking.symptom.create'))
            ->post(route('tracking.symptom.store'), $data);

        $response->assertRedirect(route('tracking.symptom.create'));
        $response->assertSessionHasErrors('symptoms');
    }

    /**
     * Test: Guardar síntomas destruye el caché de métricas.
     */
    public function test_storing_symptoms_invalidates_dashboard_cache(): void
    {
        $cacheKey = DashboardMetricsService::cacheKey($this->patient->id);
        Cache::put($cacheKey, ['dummy_symptoms' => true], 300);

        $this->assertTrue(Cache::has($cacheKey));

        $data = [
            'symptoms' => [$this->symptom1->id]
        ];

        $this->actingAs($this->patient)->post(route('tracking.symptom.store'), $data);

        $this->assertFalse(Cache::has($cacheKey));
    }
}
