<?php

namespace Tests\Feature;

use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWeightTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_update_does_not_clear_glucose_measurement()
    {
        // 1. Setup: Crear usuario y rol
        $user = User::factory()->create();
        $patientRole = Role::firstOrCreate(['name' => 'paciente']);
        $user->roles()->attach($patientRole);

        // Crear perfil para pasar el onboarding
        PatientProfile::create([
            'user_id' => $user->id,
            'birth_date' => '1990-01-01',
            'gender' => 'masculino',
            'diabetes_type' => 'Tipo 1',
            'weight' => 70,
            'height' => 170,
        ]);

        // 2. Crear un registro de glucosa previo
        VitalSign::create([
            'user_id' => $user->id,
            'glucose_level' => 120,
            'measurement_moment' => 'Ayunas',
            'created_at' => now()->subHour(),
        ]);

        $this->actingAs($user);

        // 3. Verificar que el dashboard muestra la glucosa
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('120');

        // 4. Registrar peso desde la alerta del dashboard
        $response = $this->post('/dashboard/weight', [
            'weight' => 85.5,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('patient_profiles', [
            'user_id' => $user->id,
            'weight' => 85.5,
        ]);
        $this->assertDatabaseHas('vital_signs', [
            'user_id' => $user->id,
            'glucose_level' => 120,
        ]);

        // La comprobación crucial es que el registro de peso no borre la glucosa.
        $this->followRedirects($response)
            ->assertStatus(200)
            ->assertSee('120');
    }
}
