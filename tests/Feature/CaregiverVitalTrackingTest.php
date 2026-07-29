<?php

namespace Tests\Feature;

use App\Models\PatientLink;
use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CaregiverVitalTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $caregiver;
    private User $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->caregiver = User::factory()->create();
        $this->patient = User::factory()->create();
        $this->caregiver->roles()->attach(Role::firstOrCreate(['name' => 'cuidador']));
        $this->patient->roles()->attach(Role::firstOrCreate(['name' => 'paciente']));
        PatientProfile::create(['user_id' => $this->patient->id, 'birth_date' => '1990-01-01', 'gender' => 'Femenino', 'diabetes_type' => 'Tipo 2', 'weight' => 70, 'height' => 165]);
        PatientLink::create(['patient_id' => $this->patient->id, 'linked_user_id' => $this->caregiver->id, 'role' => 'caregiver', 'invite_code' => 'CVQA01', 'status' => 'active']);
    }

    public function test_linked_caregiver_can_view_vital_form(): void
    {
        $this->withoutVite();
        $this->actingAs($this->caregiver)->get(route('caregiver.patient.vital.create', $this->patient))->assertInertia(fn (Assert $page) => $page
            ->component('Caregiver/Tracking/Vitals/Create')
            ->where('patient.name', $this->patient->name)->has('measurementMoments', 4)->has('stressLevels', 3));
    }

    public function test_unlinked_caregiver_cannot_view_or_store_vitals(): void
    {
        PatientLink::query()->delete();
        $this->actingAs($this->caregiver)->get(route('caregiver.patient.vital.create', $this->patient))->assertForbidden();
        $this->actingAs($this->caregiver)->post(route('caregiver.patient.vital.store', $this->patient), ['glucose_level' => 120, 'measurement_moment' => 'Ayunas'])->assertForbidden();
    }

    public function test_caregiver_can_store_valid_vitals_without_calling_ai(): void
    {
        Http::fake();
        $data = ['glucose_level' => 120, 'measurement_moment' => 'Ayunas', 'stress_level' => 'Medio', 'notes' => 'Control', 'systolic' => 118, 'diastolic' => 76, 'heart_rate' => 75, 'hba1c' => 5.9];
        $this->actingAs($this->caregiver)->post(route('caregiver.patient.vital.store', $this->patient), $data)
            ->assertRedirect(route('caregiver.dashboard', ['patient_id' => $this->patient->id]))->assertSessionHas('status');
        $this->assertDatabaseHas('vital_signs', ['user_id' => $this->patient->id, ...$data]);
        Http::assertNothingSent();
    }

    public function test_caregiver_vital_limits_are_enforced(): void
    {
        $this->actingAs($this->caregiver)->from(route('caregiver.patient.vital.create', $this->patient))->post(route('caregiver.patient.vital.store', $this->patient), [
            'glucose_level' => 601, 'measurement_moment' => 'Ayunas', 'stress_level' => str_repeat('x', 256),
            'notes' => str_repeat('x', 1001), 'systolic' => 251, 'diastolic' => 181, 'heart_rate' => 221, 'hba1c' => 21,
        ])->assertRedirect(route('caregiver.patient.vital.create', $this->patient))->assertSessionHasErrors(['glucose_level', 'stress_level', 'notes', 'systolic', 'diastolic', 'heart_rate', 'hba1c']);
    }

    public function test_inertia_store_uses_full_page_location_for_caregiver_dashboard(): void
    {
        $this->actingAs($this->caregiver)->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('caregiver.patient.vital.store', $this->patient), ['glucose_level' => 120, 'measurement_moment' => 'Ayunas'])
            ->assertStatus(409)->assertHeader('X-Inertia-Location', route('caregiver.dashboard', ['patient_id' => $this->patient->id]));
    }
}
