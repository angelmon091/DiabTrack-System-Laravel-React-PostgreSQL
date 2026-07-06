<?php

namespace Tests\Feature\Middleware;

use App\Models\Role;
use App\Models\User;
use App\Models\PatientProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $patient;
    private User $doctor;
    private User $caregiver;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles
        $patientRole = Role::firstOrCreate(['name' => 'paciente']);
        $doctorRole = Role::firstOrCreate(['name' => 'médico']);
        $caregiverRole = Role::firstOrCreate(['name' => 'cuidador']);

        // Paciente
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

        // Médico
        $this->doctor = User::factory()->create();
        $this->doctor->roles()->attach($doctorRole->id);

        // Cuidador
        $this->caregiver = User::factory()->create();
        $this->caregiver->roles()->attach($caregiverRole->id);

        // Admin
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    /**
     * Test: Un paciente no puede acceder a las rutas privadas del médico.
     */
    public function test_patient_cannot_access_doctor_routes(): void
    {
        $response = $this->actingAs($this->patient)->get(route('doctor.dashboard'));

        // RoleMiddleware redirige al dashboard del propio paciente al detectar conflicto de rol
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test: Un paciente no puede acceder a las rutas privadas del cuidador.
     */
    public function test_patient_cannot_access_caregiver_routes(): void
    {
        $response = $this->actingAs($this->patient)->get(route('caregiver.dashboard'));

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test: Un cuidador intentando entrar a una ruta de paciente es redirigido a su panel de cuidador.
     */
    public function test_caregiver_is_redirected_to_caregiver_dashboard_from_patient_route(): void
    {
        $response = $this->actingAs($this->caregiver)->get(route('tracking.vital.create'));

        $response->assertRedirect(route('caregiver.dashboard'));
    }

    /**
     * Test: Un usuario no administrador intentando entrar al panel admin es redirigido a su dashboard.
     */
    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->patient)->get(route('admin.dashboard'));

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test: Un administrador real puede acceder al dashboard de TI (/admin).
     */
    public function test_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }
}
