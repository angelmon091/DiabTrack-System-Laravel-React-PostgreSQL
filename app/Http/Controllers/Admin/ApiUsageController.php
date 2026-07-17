<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiUsageLog;
use App\Services\ApiUsageService;
use Illuminate\View\View;

/**
 * Presenta al administrador las métricas de consumo y costo de los proveedores de inteligencia artificial.
 */
class ApiUsageController extends Controller
{
    public function index(ApiUsageService $service): View
    {
        return view('admin.api-usage.index', [
            'summary' => $service->getSummary(),
            'dailyStats' => $service->getDailyStats(30),
            'weeklyStats' => $service->getDailyStats(7),
            'monthlyStats' => $service->getMonthlyStats(6),
            'recentLogs' => ApiUsageLog::with('patient')->latest()->paginate(15),
        ]);
    }
}
