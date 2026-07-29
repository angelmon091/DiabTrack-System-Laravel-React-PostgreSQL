<?php

namespace Tests\Feature;

use App\Models\PatientLink;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CaregiverLinkPatientTest extends TestCase
{
    use RefreshDatabase;

    private function caregiver(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach(Role::firstOrCreate(['name' => 'cuidador']));
        return $user;
    }

    public function test_caregiver_link_screen_is_rendered(): void
    {
        $this->withoutVite();
        $this->actingAs($this->caregiver())->get('/caregiver/link')->assertInertia(fn (Assert $page) => $page
            ->component('Caregiver/LinkPatient')->url('/caregiver/link')->where('storeUrl', '/caregiver/link')->where('dashboardUrl', '/caregiver')->has('relationships', 6));
    }

    public function test_caregiver_can_link_patient_with_valid_invitation(): void
    {
        $caregiver = $this->caregiver();
        $patient = User::factory()->create();
        PatientLink::create(['patient_id' => $patient->id, 'role' => 'cuidador', 'invite_code' => 'ABC123', 'status' => 'pending', 'expires_at' => now()->addHour()]);

        $this->actingAs($caregiver)->post('/caregiver/link', ['invite_code' => 'abc123', 'relationship' => 'Pareja'])->assertRedirect(route('caregiver.dashboard'));
        $this->assertDatabaseHas('patient_links', ['patient_id' => $patient->id, 'linked_user_id' => $caregiver->id, 'status' => 'active', 'relationship' => 'Pareja']);
        $this->assertDatabaseHas('patient_notifications', ['user_id' => $patient->id, 'title' => 'Nuevo cuidador vinculado']);
    }

    public function test_caregiver_cannot_use_expired_invitation(): void
    {
        $patient = User::factory()->create();
        PatientLink::create(['patient_id' => $patient->id, 'role' => 'cuidador', 'invite_code' => 'OLD123', 'status' => 'pending', 'expires_at' => now()->subMinute()]);
        $this->actingAs($this->caregiver())->from('/caregiver/link')->post('/caregiver/link', ['invite_code' => 'OLD123', 'relationship' => 'Otro'])->assertSessionHasErrors('invite_code');
    }
}
