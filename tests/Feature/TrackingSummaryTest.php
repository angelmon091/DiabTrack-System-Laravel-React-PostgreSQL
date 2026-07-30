<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\NutritionLog;
use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TrackingSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_serializes_metrics_charts_and_histories_without_ai_call(): void
    {
        Http::fake();
        $patient = User::factory()->create();
        $patient->roles()->attach(Role::firstOrCreate(['name' => 'paciente']));
        PatientProfile::create(['user_id' => $patient->id, 'birth_date' => '1990-01-01', 'gender' => 'Femenino', 'diabetes_type' => 'Tipo 2', 'weight' => 70, 'height' => 165]);
        VitalSign::create(['user_id' => $patient->id, 'glucose_level' => 125, 'measurement_moment' => 'Ayunas', 'systolic' => 120, 'diastolic' => 80, 'heart_rate' => 72]);
        NutritionLog::create(['user_id' => $patient->id, 'meal_type' => 'Comida', 'carbs_grams' => 40, 'food_categories' => ['Verduras'], 'consumed_at' => now()]);
        ActivityLog::create(['user_id' => $patient->id, 'activity_type' => 'caminar', 'duration_minutes' => 30, 'intensity' => 'media']);
        $this->withoutVite();
        $this->actingAs($patient)->get(route('tracking.summary'))->assertInertia(fn (Assert $page) => $page
            ->component('Tracking/Summary')->where('metrics.avgGlucose', 125)->where('metrics.totalCarbs', 40)
            ->has('charts.glucose.labels', 7)->has('charts.moments.labels', 4)
            ->has('histories.vitals', 1)->has('histories.nutrition', 1)->has('histories.activity', 1)->has('histories.symptoms', 0));
        Http::assertNothingSent();
    }
}
