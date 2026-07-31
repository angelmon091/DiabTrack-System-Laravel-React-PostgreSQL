<?php

namespace Tests\Feature;

use App\Models\ApiUsageLog;
use App\Models\User;
use App\Services\ApiUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminApiUsageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_api_usage_page_serializes_summary_periods_and_logs(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $patient = User::factory()->create();
        ApiUsageLog::create(['provider' => 'anthropic', 'model' => 'claude-test', 'input_tokens' => 1000, 'output_tokens' => 200, 'estimated_cost_usd' => 0.0016, 'patient_id' => $patient->id]);
        ApiUsageLog::create(['provider' => 'gemini', 'model' => 'gemini-test', 'input_tokens' => 500, 'output_tokens' => 100, 'estimated_cost_usd' => 0.000068, 'patient_id' => $patient->id]);
        $service = $this->mock(ApiUsageService::class);
        $service->shouldReceive('getSummary')->once()->andReturn(['total_calls' => 2, 'total_tokens' => 1800, 'total_cost' => 0.0017, 'avg_cost_per_tip' => 0.000834, 'anthropic_calls' => 1, 'gemini_calls' => 1, 'anthropic_cost' => 0.0016, 'gemini_cost' => 0.0001]);
        $row = ['date' => now()->toDateString(), 'label' => now()->format('d/m'), 'anthropic_tokens' => 1200, 'gemini_tokens' => 600, 'anthropic_cost' => 0.0016, 'gemini_cost' => 0.0001, 'total_calls' => 2];
        $service->shouldReceive('getDailyStats')->with(30)->once()->andReturn(collect(array_fill(0, 30, $row)));
        $service->shouldReceive('getDailyStats')->with(7)->once()->andReturn(collect(array_fill(0, 7, $row)));
        $service->shouldReceive('getMonthlyStats')->with(6)->once()->andReturn(collect(array_fill(0, 6, $row)));
        $this->withoutVite();
        $this->actingAs($admin)->get(route('admin.api-usage.index'))->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ApiUsage/Index')->where('summary.totalCalls', 2)->where('summary.totalTokens', 1800)
            ->where('summary.anthropicCalls', 1)->where('summary.geminiCalls', 1)
            ->has('periods.daily', 30)->has('periods.weekly', 7)->has('periods.monthly', 6)
            ->has('logs.data', 2)->where('logs.data.0.patient', $patient->name));
    }

    public function test_non_admin_cannot_view_api_usage(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.api-usage.index'))->assertRedirect(route('dashboard'));
    }
}
