<?php

namespace Tests\Feature\Tracking;

use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\Symptom;
use App\Models\User;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_patient_can_view_create_symptoms_form(): void
    {
        $this->withoutVite();
        $this->actingAs($this->patient)->get(route('tracking.symptom.create'))->assertInertia(fn (Assert $page) => $page
            ->component('Tracking/Symptoms/Create')->url('/tracking/symptoms')
            ->where('storeUrl', '/tracking/symptoms')->has('trackingNavigation', 4)
            ->has('symptomGroups', 2)
            ->where('symptomGroups.0.key', 'physical')
            ->where('symptomGroups.0.symptoms.0.name', 'Dolor de cabeza'));
    }

    /**
     * Prueba: Registro correcto de uno o múltiples síntomas en la tabla pivot.
     */
    public function test_patient_can_store_multiple_symptoms(): void
    {
        $data = [
            'symptoms' => [
                $this->symptom1->id,
                $this->symptom2->id,
            ],
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
     * Prueba: Form Request falla ante selección de síntomas vacía.
     */
    public function test_store_symptoms_fails_when_no_symptom_selected(): void
    {
        $data = [
            'symptoms' => [], // Debe seleccionar al menos uno
        ];

        $response = $this->actingAs($this->patient)
            ->from(route('tracking.symptom.create'))
            ->post(route('tracking.symptom.store'), $data);

        $response->assertRedirect(route('tracking.symptom.create'));
        $response->assertSessionHasErrors('symptoms');
    }

    public function test_store_symptoms_rejects_unknown_ids(): void
    {
        $this->actingAs($this->patient)->from(route('tracking.symptom.create'))->post(route('tracking.symptom.store'), [
            'symptoms' => [PHP_INT_MAX],
        ])->assertRedirect(route('tracking.symptom.create'))->assertSessionHasErrors('symptoms.0');
        $this->assertDatabaseCount('symptom_user', 0);
    }

    public function test_inertia_store_uses_full_page_location_for_blade_dashboard(): void
    {
        $this->actingAs($this->patient)
            ->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('tracking.symptom.store'), ['symptoms' => [$this->symptom1->id]])
            ->assertStatus(409)->assertHeader('X-Inertia-Location', route('dashboard'));
    }

    /**
     * Prueba: Guardar síntomas destruye el caché de métricas.
     */
    public function test_storing_symptoms_invalidates_dashboard_cache(): void
    {
        $cacheKey = DashboardMetricsService::cacheKey($this->patient->id);
        Cache::put($cacheKey, ['dummy_symptoms' => true], 300);

        $this->assertTrue(Cache::has($cacheKey));

        $data = [
            'symptoms' => [$this->symptom1->id],
        ];

        $this->actingAs($this->patient)->post(route('tracking.symptom.store'), $data);

        $this->assertFalse(Cache::has($cacheKey));
    }
}
