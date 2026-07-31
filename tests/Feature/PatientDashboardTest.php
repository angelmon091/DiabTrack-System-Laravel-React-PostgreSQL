<?php

namespace Tests\Feature;

use App\Models\DailyTip;
use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PatientDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_dashboard_renders_metrics_and_existing_tip_without_ai_call(): void
    {
        Http::fake();
        $patient = User::factory()->create();
        $patient->roles()->attach(Role::firstOrCreate(['name' => 'paciente']));
        PatientProfile::create(['user_id' => $patient->id, 'birth_date' => '1990-01-01', 'gender' => 'Femenino', 'diabetes_type' => 'Tipo 2', 'weight' => 70, 'height' => 165, 'target_glucose_min' => 70, 'target_glucose_max' => 140]);
        VitalSign::create(['user_id' => $patient->id, 'glucose_level' => 125, 'measurement_moment' => 'Ayunas']);
        DailyTip::create(['user_id' => $patient->id, 'tip_text' => 'Tip local para QA', 'status' => 'approved']);
        $this->withoutVite();
        $this->actingAs($patient)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')->where('metrics.latestGlucose', 125)->where('metrics.timeInRange', 100)
            ->where('metrics.measurementMoment', 'Ayunas')->where('metrics.glucoseStatusKey', 'normal')
            ->has('metrics.glucoseLabels', 7)->where('tip.text', 'Tip local para QA')->where('tip.isAi', true)
            ->where('profile.targetMin', 70)->where('profile.targetMax', 140)->has('recentLogs', 1));
        Http::assertNotSent(fn ($request) => preg_match('/api\.anthropic\.com|generativelanguage\.googleapis\.com/', $request->url()) === 1);
    }

    public function test_weight_reminder_exposes_the_original_empty_and_expired_states(): void
    {
        $withoutWeight = User::factory()->create();
        $withoutWeight->roles()->attach(Role::firstOrCreate(['name' => 'paciente']));
        PatientProfile::create(['user_id' => $withoutWeight->id, 'birth_date' => '1990-01-01', 'gender' => 'Femenino', 'diabetes_type' => 'Tipo 2', 'weight' => 70, 'height' => 165]);

        $withExpiredWeight = User::factory()->create();
        $withExpiredWeight->roles()->attach(Role::firstOrCreate(['name' => 'paciente']));
        PatientProfile::create(['user_id' => $withExpiredWeight->id, 'birth_date' => '1990-01-01', 'gender' => 'Masculino', 'diabetes_type' => 'Tipo 2', 'weight' => 82, 'height' => 175]);
        $expiredWeight = VitalSign::create(['user_id' => $withExpiredWeight->id, 'weight' => 81.5]);
        VitalSign::query()->whereKey($expiredWeight)->update(['created_at' => now()->subDays(31)]);

        $this->withoutVite();
        $this->actingAs($withoutWeight)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('metrics.needsWeightUpdate', true)->where('metrics.lastWeight', null));
        $this->actingAs($withExpiredWeight)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('metrics.needsWeightUpdate', true)->where('metrics.lastWeight', 81.5));
    }

    public function test_invite_code_is_returned_as_inertia_flash_prop(): void
    {
        $patient = User::factory()->create();
        $patient->roles()->attach(Role::firstOrCreate(['name' => 'paciente']));
        PatientProfile::create(['user_id' => $patient->id, 'birth_date' => '1990-01-01', 'gender' => 'Femenino', 'diabetes_type' => 'Tipo 2', 'weight' => 70, 'height' => 165]);
        $response = $this->actingAs($patient)->post(route('dashboard.invite'), ['role' => 'doctor']);
        $response->assertRedirect(route('dashboard'));
        $code = session('invite_code');
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{6}$/', $code);
        $this->withoutVite();
        $this->actingAs($patient)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page->where('inviteCode', $code));
        $this->assertDatabaseHas('patient_links', ['patient_id' => $patient->id, 'role' => 'doctor', 'invite_code' => $code, 'status' => 'pending']);
    }

    public function test_inertia_invite_request_redirects_instead_of_returning_legacy_json(): void
    {
        $patient = User::factory()->create();
        $patient->roles()->attach(Role::firstOrCreate(['name' => 'paciente']));
        PatientProfile::create(['user_id' => $patient->id, 'birth_date' => '1990-01-01', 'gender' => 'Femenino', 'diabetes_type' => 'Tipo 2', 'weight' => 70, 'height' => 165]);
        $this->actingAs($patient)->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('dashboard.invite'), ['role' => 'caregiver'])->assertRedirect(route('dashboard'))->assertSessionHas('invite_code');
    }
}
