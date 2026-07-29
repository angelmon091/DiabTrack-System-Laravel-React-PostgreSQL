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
        $this->withoutVite();
        $this->actingAs($this->patient)->get(route('tracking.nutrition.create'))->assertInertia(fn (Assert $page) => $page
            ->component('Tracking/Nutrition/Create')->url('/tracking/nutrition/create')
            ->where('storeUrl', '/tracking/nutrition')->has('trackingNavigation', 4)
            ->has('mealTypes', 5)->has('foodCategories', 8));
    }

    /**
     * Prueba: Registro exitoso de comidas y conteo de carbohidratos en BD.
     */
    public function test_patient_can_store_valid_nutrition_log(): void
    {
        Http::fake();
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
        Http::assertNothingSent();
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

    public function test_all_nutrition_limits_are_enforced_by_backend(): void
    {
        $this->actingAs($this->patient)->from(route('tracking.nutrition.create'))->post(route('tracking.nutrition.store'), [
            'meal_type' => 'desayuno', 'carbs_grams' => 501, 'consumed_at' => '25:00',
            'food_categories' => [123], 'medication_taken' => str_repeat('x', 101),
            'medication_dose' => str_repeat('x', 51),
        ])->assertRedirect(route('tracking.nutrition.create'))->assertSessionHasErrors([
            'carbs_grams', 'consumed_at', 'food_categories.0', 'medication_taken', 'medication_dose',
        ]);
        $this->assertDatabaseCount('nutrition_logs', 0);
    }

    public function test_inertia_store_uses_full_page_location_for_blade_dashboard(): void
    {
        $this->actingAs($this->patient)->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('tracking.nutrition.store'), ['meal_type' => 'cena', 'carbs_grams' => 60])
            ->assertStatus(409)->assertHeader('X-Inertia-Location', route('dashboard'));
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
