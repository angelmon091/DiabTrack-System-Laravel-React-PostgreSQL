<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $this->withoutVite();

        $response = $this->actingAs($user)->get('/onboarding');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Onboarding/RoleSelection')
                ->url('/onboarding')
                ->where('patientUrl', '/onboarding/patient')
                ->where('caregiverUrl', '/onboarding/caregiver')
                ->where('doctorUrl', '/onboarding/doctor'));
    }

    public function test_user_can_submit_personal_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/onboarding/patient', [
            'birth_day' => '15',
            'birth_month' => 'Marzo',
            'birth_year' => '1990',
            'diabetes_type' => 'Diabetes Mellitus Tipo 2',
            'weight' => '80.5',
            'height' => '175',
            'gender' => 'Masculino',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('patient_profiles', [
            'user_id' => $user->id,
            'birth_date' => '1990-03-15',
            'weight' => 80.5,
        ]);
    }

    public function test_patient_data_screen_is_rendered_with_backend_options(): void
    {
        $user = User::factory()->create();
        $this->withoutVite();

        $this->actingAs($user)->get('/onboarding/patient')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Onboarding/PatientData')
                ->url('/onboarding/patient')
                ->where('storeUrl', '/onboarding/patient')
                ->where('backUrl', '/onboarding')
                ->has('months', 12)
                ->has('glycemicConditions'));
    }

    public function test_user_can_select_prediabetes_as_glycemic_condition(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/onboarding/patient', [
            'birth_day' => '10',
            'birth_month' => 'Julio',
            'birth_year' => '1985',
            'diabetes_type' => 'Prediabetes',
            'weight' => '72',
            'height' => '168',
            'gender' => 'Femenino',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('patient_profiles', [
            'user_id' => $user->id,
            'diabetes_type' => 'Prediabetes',
        ]);
    }

    public function test_unknown_glycemic_condition_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/onboarding/patient')->post('/onboarding/patient', [
            'birth_day' => '10',
            'birth_month' => 'Julio',
            'birth_year' => '1985',
            'diabetes_type' => 'Condición inventada',
            'weight' => '72',
            'height' => '168',
            'gender' => 'Femenino',
        ]);

        $response->assertRedirect('/onboarding/patient');
        $response->assertSessionHasErrors('diabetes_type');
        $this->assertDatabaseMissing('patient_profiles', ['user_id' => $user->id]);
    }

    public function test_patient_must_be_at_least_eighteen_years_old(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/onboarding/patient')->post('/onboarding/patient', [
            'birth_day' => now()->day,
            'birth_month' => ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'][now()->month - 1],
            'birth_year' => now()->subYears(17)->year,
            'diabetes_type' => 'Prediabetes',
            'weight' => 80,
            'height' => 175,
            'gender' => 'Masculino',
        ]);

        $response->assertRedirect('/onboarding/patient');
        $response->assertSessionHasErrors('birth_year');
        $this->assertDatabaseMissing('patient_profiles', ['user_id' => $user->id]);
    }

    public function test_invalid_calendar_date_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/onboarding/patient')->post('/onboarding/patient', [
            'birth_day' => 31,
            'birth_month' => 'Febrero',
            'birth_year' => 1990,
            'diabetes_type' => 'Prediabetes',
            'weight' => 80,
            'height' => 175,
            'gender' => 'Masculino',
        ]);

        $response->assertRedirect('/onboarding/patient');
        $response->assertSessionHasErrors('birth_date');
        $this->assertDatabaseMissing('patient_profiles', ['user_id' => $user->id]);
    }
}
