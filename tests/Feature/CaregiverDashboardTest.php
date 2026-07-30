<?php

namespace Tests\Feature;

use App\Models\PatientLink;
use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CaregiverDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_serializes_linked_patient_metrics_without_generating_ai(): void
    {
        $caregiver = User::factory()->create();
        $patient = User::factory()->create();
        $caregiver->roles()->attach(Role::firstOrCreate(['name' => 'cuidador']));
        $patient->roles()->attach(Role::firstOrCreate(['name' => 'paciente']));
        PatientProfile::create(['user_id' => $patient->id, 'birth_date' => '1990-01-01', 'gender' => 'Femenino', 'diabetes_type' => 'Tipo 2', 'weight' => 70, 'height' => 165]);
        PatientLink::create(['patient_id' => $patient->id, 'linked_user_id' => $caregiver->id, 'role' => 'caregiver', 'relationship' => 'Pareja', 'invite_code' => 'CDQA01', 'status' => 'active']);
        VitalSign::create(['user_id' => $patient->id, 'glucose_level' => 125, 'measurement_moment' => 'Ayunas']);

        $this->withoutVite();
        $this->actingAs($caregiver)->get(route('caregiver.dashboard', ['patient_id' => $patient->id]))
            ->assertInertia(fn (Assert $page) => $page->component('Caregiver/Dashboard')
                ->has('patients', 1)->where('patients.0.relationship', 'Pareja')
                ->where('selectedPatient.name', $patient->name)
                ->where('metrics.latestGlucose', 125)
                ->has('metrics.glucoseLabels', 7)->has('recentLogs', 1));
    }

    public function test_dashboard_has_empty_state_without_linked_patients(): void
    {
        $caregiver = User::factory()->create();
        $caregiver->roles()->attach(Role::firstOrCreate(['name' => 'cuidador']));
        $this->withoutVite();
        $this->actingAs($caregiver)->get(route('caregiver.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->component('Caregiver/Dashboard')->has('patients', 0)->where('selectedPatient', null));
    }
}
