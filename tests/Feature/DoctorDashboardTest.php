<?php

namespace Tests\Feature;

use App\Models\DoctorProfile;
use App\Models\PatientLink;
use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DoctorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_doctor_dashboard_exposes_clinical_metrics_and_targets(): void
    {
        $doctor = User::factory()->create();
        $patient = User::factory()->create();
        $doctor->roles()->attach(Role::firstOrCreate(['name' => 'médico']));
        $patient->roles()->attach(Role::firstOrCreate(['name' => 'paciente']));
        DoctorProfile::create(['user_id' => $doctor->id, 'gender' => 'Masculino', 'license_number' => 'DOC-QA', 'specialty' => 'Endocrinología', 'approval_status' => 'approved']);
        PatientProfile::create(['user_id' => $patient->id, 'birth_date' => '1990-01-01', 'gender' => 'Femenino', 'diabetes_type' => 'Tipo 2', 'weight' => 70, 'height' => 165, 'target_glucose_min' => 75, 'target_glucose_max' => 145]);
        PatientLink::create(['patient_id' => $patient->id, 'linked_user_id' => $doctor->id, 'role' => 'doctor', 'invite_code' => 'DDQA01', 'status' => 'active']);
        VitalSign::create(['user_id' => $patient->id, 'glucose_level' => 150, 'measurement_moment' => 'Ayunas', 'systolic' => 120, 'diastolic' => 80]);
        $this->withoutVite();
        $this->actingAs($doctor)->get(route('doctor.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->component('Doctor/Dashboard')->where('approval.approved', true)->has('patients', 1)
            ->where('selectedPatient.targetMin', 75)->where('selectedPatient.targetMax', 145)
            ->where('metrics.latestGlucose', 150)->has('metrics.glucoseLabels', 7)->has('recentLogs', 1));
    }

    public function test_rejected_doctor_dashboard_preserves_review_state(): void
    {
        $doctor = User::factory()->create();
        $doctor->roles()->attach(Role::firstOrCreate(['name' => 'médico']));
        DoctorProfile::create(['user_id' => $doctor->id, 'gender' => 'Masculino', 'license_number' => 'REJECTED', 'specialty' => 'General', 'approval_status' => 'rejected', 'review_notes' => 'Corrige la cédula']);
        $this->withoutVite();
        $this->actingAs($doctor)->get(route('doctor.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->component('Doctor/Dashboard')->where('approval.approved', false)->where('approval.rejected', true)
            ->where('approval.notes', 'Corrige la cédula'));
    }

    public function test_approved_doctor_can_update_patient_targets_with_patch(): void
    {
        $doctor = User::factory()->create();
        $patient = User::factory()->create();
        $doctor->roles()->attach(Role::firstOrCreate(['name' => 'médico']));
        DoctorProfile::create(['user_id' => $doctor->id, 'gender' => 'Masculino', 'license_number' => 'PATCH-QA', 'specialty' => 'General', 'approval_status' => 'approved']);
        PatientProfile::create(['user_id' => $patient->id, 'birth_date' => '1990-01-01', 'gender' => 'Femenino', 'diabetes_type' => 'Tipo 2', 'weight' => 70, 'height' => 165]);
        PatientLink::create(['patient_id' => $patient->id, 'linked_user_id' => $doctor->id, 'role' => 'doctor', 'invite_code' => 'PATCH1', 'status' => 'active']);
        $this->actingAs($doctor)->patch(route('doctor.patient.targets.update', $patient), ['target_glucose_min' => 80, 'target_glucose_max' => 150])
            ->assertRedirect(route('doctor.patient.show', $patient))->assertSessionHas('status');
        $this->assertDatabaseHas('patient_profiles', ['user_id' => $patient->id, 'target_glucose_min' => 80, 'target_glucose_max' => 150]);
    }
}
