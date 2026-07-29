<?php

namespace Tests\Feature;

use App\Models\DoctorProfile;
use App\Models\PatientLink;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DoctorLinkPatientTest extends TestCase
{
    use RefreshDatabase;

    private function doctor(string $status = DoctorProfile::STATUS_APPROVED): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach(Role::firstOrCreate(['name' => 'médico']));
        DoctorProfile::create(['user_id' => $user->id, 'gender' => 'Masculino', 'license_number' => 'QA12345', 'specialty' => 'Medicina General', 'approval_status' => $status]);
        return $user;
    }

    public function test_approved_doctor_link_screen_is_rendered(): void
    {
        $this->withoutVite();
        $this->actingAs($this->doctor())->get('/doctor/link')->assertInertia(fn (Assert $page) => $page->component('Doctor/LinkPatient')->url('/doctor/link')->where('storeUrl', '/doctor/link')->where('dashboardUrl', '/doctor'));
    }

    public function test_pending_doctor_cannot_open_link_screen(): void
    {
        $this->actingAs($this->doctor(DoctorProfile::STATUS_PENDING))->get('/doctor/link')->assertRedirect(route('doctor.dashboard'));
    }

    public function test_approved_doctor_can_link_patient(): void
    {
        $doctor = $this->doctor();
        $patient = User::factory()->create();
        PatientLink::create(['patient_id' => $patient->id, 'role' => 'médico', 'invite_code' => 'DOC123', 'status' => 'pending', 'expires_at' => now()->addHour()]);
        $this->actingAs($doctor)->post('/doctor/link', ['invite_code' => 'doc123'])->assertRedirect(route('doctor.dashboard'));
        $this->assertDatabaseHas('patient_links', ['patient_id' => $patient->id, 'linked_user_id' => $doctor->id, 'status' => 'active']);
        $this->assertDatabaseHas('patient_notifications', ['user_id' => $patient->id, 'title' => 'Nuevo médico vinculado']);
    }

    public function test_used_invitation_is_rejected(): void
    {
        $patient = User::factory()->create();
        PatientLink::create(['patient_id' => $patient->id, 'role' => 'médico', 'invite_code' => 'USEDOC', 'status' => 'active', 'expires_at' => now()->addHour()]);
        $this->actingAs($this->doctor())->from('/doctor/link')->post('/doctor/link', ['invite_code' => 'USEDOC'])->assertSessionHasErrors('invite_code');
    }
}
