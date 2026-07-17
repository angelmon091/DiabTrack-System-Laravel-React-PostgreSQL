<?php

namespace Tests\Feature\Tracking;

use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class NutritionTrackingTest extends TestCase
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
            'diabetes_type' => 'Tipo 1',
            'weight' => 70,
            'height' => 175,
        ]);
    }

    /**
     * Prueba: Paciente puede ver el formulario nutricional.
     */
    public function test_patient_can_view_create_nutrition_form(): void
    {
        $response = $this->actingAs($this->patient)->get(route('tracking.nutrition.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tracking.nutrition.create');
    }

    /**
     * Prueba: Registro exitoso de comidas y conteo de carbohidratos en BD.
     */
    public function test_patient_can_store_valid_nutrition_log(): void
    {
        $data = [
            'meal_type' => 'desayuno',
            'carbs_grams' => 45,
            'consumed_at' => '08:30',
            'food_categories' => ['frutas', 'lacteos'],
            'medication_taken' => 'Insulina Rápida',
            'medication_dose' => '4 unidades',
        ];

        $response = $this->actingAs($this->patient)
            ->from(route('tracking.nutrition.create'))
            ->post(route('tracking.nutrition.store'), $data);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status', __('Registro de nutrición guardado con éxito.'));

        $this->assertDatabaseHas('nutrition_logs', [
            'user_id' => $this->patient->id,
            'meal_type' => 'desayuno',
            'carbs_grams' => 45,
            'medication_taken' => 'Insulina Rápida',
            'medication_dose' => '4 unidades',
        ]);
    }

    /**
     * Prueba: Guardar datos nutricionales mediante AJAX.
     */
    public function test_patient_can_store_nutrition_via_ajax(): void
    {
        $data = [
            'meal_type' => 'cena',
            'carbs_grams' => 60,
        ];

        $response = $this->actingAs($this->patient)
            ->postJson(route('tracking.nutrition.store'), $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Registro de nutrición guardado con éxito.',
        ]);
    }

    /**
     * Prueba: Validación de campos obligatorios en NutritionLogRequest.
     */
    public function test_store_nutrition_validation_fails_for_missing_required_fields(): void
    {
        $invalidData = [
            'meal_type' => 'invalida_categoria', // No está en la lista en: desayuno,almuerzo,cena,snack,correccion
            'carbs_grams' => -10, // Debe ser mayor o igual a 0
        ];

        $response = $this->actingAs($this->patient)
            ->from(route('tracking.nutrition.create'))
            ->post(route('tracking.nutrition.store'), $invalidData);

        $response->assertRedirect(route('tracking.nutrition.create'));
        $response->assertSessionHasErrors(['meal_type', 'carbs_grams']);
    }

    /**
     * Prueba: Guardar una comida destruye de manera reactiva el caché del dashboard del usuario.
     */
    public function test_storing_nutrition_log_invalidates_dashboard_cache(): void
    {
        $cacheKey = DashboardMetricsService::cacheKey($this->patient->id);
        Cache::put($cacheKey, ['dummy_nutrition' => true], 300);

        $this->assertTrue(Cache::has($cacheKey));

        $data = [
            'meal_type' => 'snack',
            'carbs_grams' => 15,
        ];

        $this->actingAs($this->patient)->post(route('tracking.nutrition.store'), $data);

        $this->assertFalse(Cache::has($cacheKey));
    }
}
