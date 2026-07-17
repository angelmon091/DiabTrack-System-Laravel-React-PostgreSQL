<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tracking\NutritionLogRequest;
use App\Models\NutritionLog;
use App\Services\DashboardMetricsService;
use Illuminate\Support\Facades\Auth;

/**
 * Gestiona los registros nutricionales y las métricas relacionadas con la alimentación.
 */
class NutritionLogController extends Controller
{
    protected $metricsService;

    public function __construct(DashboardMetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    public function index()
    {
        $metrics = $this->metricsService->getDashboardMetrics(Auth::id());

        return view('tracking.nutrition.index', $metrics);
    }

    public function create()
    {
        return view('tracking.nutrition.create');
    }

    public function store(NutritionLogRequest $request)
    {
        NutritionLog::create([
            'user_id' => Auth::id(),
            'meal_type' => $request->meal_type,
            'carbs_grams' => $request->carbs_grams,
            'consumed_at' => $request->consumed_at,
            'food_categories' => $request->food_categories,
            'medication_taken' => $request->medication_taken,
            'medication_dose' => $request->medication_dose,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Registro de nutrición guardado con éxito.'),
            ]);
        }

        return redirect()->route('dashboard')->with('status', __('Registro de nutrición guardado con éxito.'));
    }
}
