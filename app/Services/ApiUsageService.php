<?php

namespace App\Services;

use App\Models\ApiUsageLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Calcula resúmenes y series estadísticas sobre el uso de las API de inteligencia artificial.
 */
class ApiUsageService
{
    public function getSummary(): array
    {
        $all = ApiUsageLog::selectRaw('
            COUNT(*) as total_calls,
            SUM(input_tokens + output_tokens) as total_tokens,
            SUM(estimated_cost_usd) as total_cost
        ')->first();

        $last30 = ApiUsageLog::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('COUNT(*) as calls, SUM(estimated_cost_usd) as cost')
            ->first();

        $avgCostPerTip = $last30->calls > 0
            ? round((float) $last30->cost / $last30->calls, 6)
            : 0;

        $byProvider = ApiUsageLog::selectRaw('provider, COUNT(*) as calls, SUM(estimated_cost_usd) as cost')
            ->groupBy('provider')
            ->get()
            ->keyBy('provider');

        return [
            'total_calls' => (int) $all->total_calls,
            'total_tokens' => (int) $all->total_tokens,
            'total_cost' => round((float) $all->total_cost, 4),
            'avg_cost_per_tip' => $avgCostPerTip,
            'anthropic_calls' => (int) ($byProvider['anthropic']->calls ?? 0),
            'gemini_calls' => (int) ($byProvider['gemini']->calls ?? 0),
            'anthropic_cost' => round((float) ($byProvider['anthropic']->cost ?? 0), 4),
            'gemini_cost' => round((float) ($byProvider['gemini']->cost ?? 0), 4),
        ];
    }

    public function getDailyStats(int $days = 30): Collection
    {
        $since = now()->subDays($days - 1)->startOfDay();

        $rows = ApiUsageLog::where('created_at', '>=', $since)
            ->selectRaw('
                DATE(created_at) as date,
                provider,
                COUNT(*) as calls,
                SUM(input_tokens + output_tokens) as tokens,
                SUM(estimated_cost_usd) as cost
            ')
            ->groupBy('date', 'provider')
            ->orderBy('date')
            ->get();

        // Construye el período completo para representar con cero los días sin consumo.
        $range = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $range->push(now()->subDays($i)->format('Y-m-d'));
        }

        return $range->map(function (string $date) use ($rows) {
            $anthropic = $rows->where('date', $date)->where('provider', 'anthropic')->first();
            $gemini = $rows->where('date', $date)->where('provider', 'gemini')->first();

            return [
                'date' => $date,
                'label' => Carbon::parse($date)->format('d/m'),
                'anthropic_tokens' => (int) ($anthropic->tokens ?? 0),
                'gemini_tokens' => (int) ($gemini->tokens ?? 0),
                'anthropic_cost' => round((float) ($anthropic->cost ?? 0), 6),
                'gemini_cost' => round((float) ($gemini->cost ?? 0), 6),
                'total_calls' => (int) ($anthropic->calls ?? 0) + (int) ($gemini->calls ?? 0),
            ];
        });
    }

    public function getMonthlyStats(int $months = 6): Collection
    {
        $since = now()->subMonths($months - 1)->startOfMonth();

        $rows = ApiUsageLog::where('created_at', '>=', $since)
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                provider,
                COUNT(*) as calls,
                SUM(input_tokens + output_tokens) as tokens,
                SUM(estimated_cost_usd) as cost
            ')
            ->groupBy('month', 'provider')
            ->orderBy('month')
            ->get();

        $range = collect();
        for ($i = $months - 1; $i >= 0; $i--) {
            $range->push(now()->subMonths($i)->format('Y-m'));
        }

        return $range->map(function (string $month) use ($rows) {
            $anthropic = $rows->where('month', $month)->where('provider', 'anthropic')->first();
            $gemini = $rows->where('month', $month)->where('provider', 'gemini')->first();

            return [
                'month' => $month,
                'label' => Carbon::parse($month.'-01')->translatedFormat('M Y'),
                'anthropic_tokens' => (int) ($anthropic->tokens ?? 0),
                'gemini_tokens' => (int) ($gemini->tokens ?? 0),
                'anthropic_cost' => round((float) ($anthropic->cost ?? 0), 4),
                'gemini_cost' => round((float) ($gemini->cost ?? 0), 4),
                'total_calls' => (int) ($anthropic->calls ?? 0) + (int) ($gemini->calls ?? 0),
            ];
        });
    }
}
