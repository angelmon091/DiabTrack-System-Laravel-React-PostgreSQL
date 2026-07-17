<?php

namespace Tests\Feature;

use App\Models\DailyTip;
use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DailyTipApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_daily_tip_invalidates_dashboard_cache(): void
    {
        $patient = $this->createUserWithRole('paciente');
        $cacheKey = DashboardMetricsService::cacheKey($patient->id);

        Cache::put($cacheKey, ['tipDelDia' => 'Tip en caché desactualizado'], 300);

        DailyTip::create([
            'user_id' => $patient->id,
            'tip_text' => 'Camina 10 minutos después de comer.',
            'status' => 'approved',
        ]);

        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_patient_dashboard_shows_latest_approved_ai_tip_even_with_stale_cache(): void
    {
        $patient = $this->createUserWithRole('paciente');
        $service = app(DashboardMetricsService::class);
        $cacheKey = DashboardMetricsService::cacheKey($patient->id);

        Cache::put($cacheKey, [
            'tipDelDia' => 'Tip estático en caché',
            'tipEsIA' => false,
            'carbsHoy' => 0,
        ], 300);

        DailyTip::create([
            'user_id' => $patient->id,
            'tip_text' => 'Tu glucosa ha estado estable; mantén la rutina de caminata post-comida.',
            'status' => 'approved',
            'created_at' => now()->subDay(),
        ]);

        $metrics = $service->getDashboardMetrics($patient->id);

        $this->assertTrue($metrics['tipEsIA']);
        $this->assertSame(
            'Tu glucosa ha estado estable; mantén la rutina de caminata post-comida.',
            $metrics['tipDelDia']
        );
    }

    public function test_patient_dashboard_page_renders_ai_tip_from_database(): void
    {
        $patient = $this->createPatientWithProfile();
        $tipText = 'Bebe agua antes de tu caminata de hoy para mantener la glucosa estable.';

        DailyTip::create([
            'user_id' => $patient->id,
            'tip_text' => $tipText,
            'status' => 'approved',
        ]);

        Cache::put(DashboardMetricsService::cacheKey($patient->id), [
            'tipDelDia' => 'Tip viejo en caché que no debería mostrarse',
            'tipEsIA' => false,
            'carbsHoy' => 0,
            'caloriasHoy' => 0,
            'metaCalorias' => 2000,
            'metaCaloriasPersonalizada' => true,
            'metaCarbs' => 200,
            'actividadMinutos' => 0,
            'metaActividad' => 60,
            'pasosEstimados' => 0,
            'metaPasos' => 8000,
            'sintomasHoy' => 0,
            'porcentajeCalorias' => 0,
            'porcentajeActividad' => 0,
            'porcentajePasos' => 0,
            'tiempoEnRango' => 0,
            'glucosaLabels' => [],
            'glucosaData' => [],
            'needsWeightUpdate' => false,
            'ultimoPesoValor' => null,
            'ultimaMedicion' => null,
            'ultimaHba1c' => null,
        ], 300);

        $response = $this->actingAs($patient)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('✦ IA', false);
        $response->assertSee($tipText, false);
        $response->assertDontSee('Tip viejo en caché que no debería mostrarse', false);
    }

    public function test_daily_tip_can_be_persisted_with_approved_status(): void
    {
        $patient = $this->createUserWithRole('paciente');

        $tip = DailyTip::create([
            'user_id' => $patient->id,
            'tip_text' => 'Camina 10 minutos después de comer.',
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('daily_tips', [
            'id' => $tip->id,
            'status' => 'approved',
        ]);
    }

    public function test_daily_tip_is_approved_by_default_without_human_review(): void
    {
        $patient = $this->createUserWithRole('paciente');
        // Los consejos forman parte del flujo automático del MVP.
        $tip = DailyTip::create([
            'user_id' => $patient->id,
            'tip_text' => 'Mantén horarios consistentes para registrar tus mediciones.',
        ]);

        $this->assertDatabaseHas('daily_tips', [
            'id' => $tip->id,
            'status' => 'approved',
        ]);
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['description' => ucfirst($roleName)]
        );

        $user->roles()->attach($role->id);

        return $user;
    }

    private function createPatientWithProfile(): User
    {
        $patient = $this->createUserWithRole('paciente');

        PatientProfile::create([
            'user_id' => $patient->id,
            'birth_date' => '1990-01-01',
            'gender' => 'masculino',
            'diabetes_type' => 'Tipo 2',
            'weight' => 75,
            'height' => 170,
        ]);

        return $patient->fresh();
    }
}
