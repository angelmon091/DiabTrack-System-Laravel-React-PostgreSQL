<?php

namespace Tests\Feature;

use App\Models\DailyTip;
use App\Models\NutritionLog;
use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NutritionDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_nutrition_dashboard_uses_existing_metrics_and_algorithmic_ideas_without_ai_call(): void
    {
        Http::fake();
        $patient = User::factory()->create();
        $patient->roles()->attach(Role::firstOrCreate(['name' => 'paciente']));
        PatientProfile::create(['user_id' => $patient->id, 'birth_date' => '1990-01-01', 'gender' => 'Femenino', 'diabetes_type' => 'Tipo 2', 'weight' => 70, 'height' => 165]);
        NutritionLog::create(['user_id' => $patient->id, 'meal_type' => 'Comida', 'carbs_grams' => 40, 'consumed_at' => now()]);
        DailyTip::create(['user_id' => $patient->id, 'tip_text' => 'Tip nutricional local', 'status' => 'approved']);
        $this->withoutVite();
        $this->actingAs($patient)->get(route('tracking.nutrition.index'))->assertInertia(fn (Assert $page) => $page
            ->component('Tracking/Nutrition/Index')->where('metrics.carbsToday', 40)->where('metrics.caloriesToday', 160)
            ->where('metrics.dailyTip', 'Tip nutricional local')->has('foods', 5)->has('recommendations', 5)
            ->where('urls.create', '/tracking/nutrition/create'));
        Http::assertNotSent(fn ($request) => preg_match('/api\.anthropic\.com|generativelanguage\.googleapis\.com/', $request->url()) === 1);
    }
}
