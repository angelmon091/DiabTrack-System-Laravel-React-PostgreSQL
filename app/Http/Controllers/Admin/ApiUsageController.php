<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiUsageLog;
use App\Services\ApiUsageService;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Presenta al administrador las métricas de consumo y costo de los proveedores de inteligencia artificial.
 */
class ApiUsageController extends Controller
{
    public function index(ApiUsageService $service): InertiaResponse
    {
        $summary = $service->getSummary();
        $serializePeriod = fn ($rows) => $rows->map(fn (array $row) => [
            'label' => $row['label'], 'anthropicTokens' => $row['anthropic_tokens'], 'geminiTokens' => $row['gemini_tokens'],
            'anthropicCost' => $row['anthropic_cost'], 'geminiCost' => $row['gemini_cost'], 'totalCalls' => $row['total_calls'],
        ])->values();
        $logs = ApiUsageLog::with('patient')->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/ApiUsage/Index', [
            'summary' => [
                'totalTokens' => $summary['total_tokens'], 'totalCost' => $summary['total_cost'], 'totalCalls' => $summary['total_calls'],
                'averageCost' => $summary['avg_cost_per_tip'], 'anthropicCalls' => $summary['anthropic_calls'], 'geminiCalls' => $summary['gemini_calls'],
                'anthropicCost' => $summary['anthropic_cost'], 'geminiCost' => $summary['gemini_cost'],
            ],
            'periods' => ['daily' => $serializePeriod($service->getDailyStats(30)), 'weekly' => $serializePeriod($service->getDailyStats(7)), 'monthly' => $serializePeriod($service->getMonthlyStats(6))],
            'logs' => [
                'data' => $logs->getCollection()->map(fn (ApiUsageLog $log) => ['id' => $log->id, 'provider' => $log->provider, 'model' => $log->model, 'dailyTip' => $log->daily_tip_id !== null, 'inputTokens' => $log->input_tokens, 'outputTokens' => $log->output_tokens, 'cost' => (float) $log->estimated_cost_usd, 'patient' => $log->patient?->name, 'createdAt' => $log->created_at->format('d/m/Y H:i')])->values(),
                'links' => $logs->linkCollection(),
            ],
        ]);
    }
}
