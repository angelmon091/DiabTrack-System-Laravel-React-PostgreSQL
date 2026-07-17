<?php

namespace Tests\Feature\Search;

use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFeatureTest extends TestCase
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
            'weight' => 70,
            'height' => 175,
        ]);
    }

    /**
     * Prueba: una consulta vacía o menor de dos caracteres devuelve colecciones vacías.
     */
    public function test_search_returns_empty_results_for_short_query(): void
    {
        $response = $this->actingAs($this->patient)
            ->getJson(route('search', ['q' => 'a']));

        $response->assertStatus(200);
        $response->assertJson([
            'sections' => [],
            'records' => [],
        ]);
    }

    /**
     * Prueba: la búsqueda de secciones por palabra clave coincide con las rutas correctas.
     */
    public function test_search_can_find_application_sections(): void
    {
        $response = $this->actingAs($this->patient)
            ->getJson(route('search', ['q' => 'glucosa']));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'label' => 'Registrar signo vital',
            'url' => route('tracking.vital.create'),
        ]);
    }

    /**
     * Prueba: el buscador clínico local encuentra registros históricos del paciente.
     */
    public function test_search_can_locate_vital_sign_records(): void
    {
        // Crear un registro de signo vital
        VitalSign::create([
            'user_id' => $this->patient->id,
            'glucose_level' => 185,
            'measurement_moment' => 'Después de Comer',
            'notes' => 'Comi pastel de chocolate',
        ]);

        $response = $this->actingAs($this->patient)
            ->getJson(route('search', ['q' => 'pastel']));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'type' => 'Signo vital',
            'title' => 'Glucosa 185 mg/dL',
            'subtitle' => 'Después de Comer · '.now()->format('d/m/Y'),
        ]);
    }
}
