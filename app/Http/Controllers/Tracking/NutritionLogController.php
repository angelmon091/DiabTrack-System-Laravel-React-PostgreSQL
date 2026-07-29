<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tracking\NutritionLogRequest;
use App\Models\NutritionLog;
use App\Services\DashboardMetricsService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

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

    public function create(): InertiaResponse
    {
        return Inertia::render('Tracking/Nutrition/Create', [
            'storeUrl' => route('tracking.nutrition.store', absolute: false),
            'dashboardUrl' => route('dashboard', absolute: false),
            'trackingNavigation' => [
                ['key' => 'vitals', 'label' => 'Signos vitales', 'url' => route('tracking.vital.create', absolute: false)],
                ['key' => 'symptoms', 'label' => 'Síntomas', 'url' => route('tracking.symptom.create', absolute: false)],
                ['key' => 'nutrition', 'label' => 'Nutrición', 'url' => route('tracking.nutrition.create', absolute: false)],
                ['key' => 'activity', 'label' => 'Movimiento', 'url' => route('tracking.activity.create', absolute: false)],
            ],
            'mealTypes' => [
                ['value' => 'desayuno', 'label' => 'Desayuno', 'description' => 'Primera comida del día.'],
                ['value' => 'almuerzo', 'label' => 'Comida', 'description' => 'Comida del mediodía.'],
                ['value' => 'cena', 'label' => 'Cena', 'description' => 'Última comida del día.'],
                ['value' => 'snack', 'label' => 'Snack', 'description' => 'Algo pequeño entre comidas.'],
                ['value' => 'correccion', 'label' => 'Corrección', 'description' => 'Jugo o azúcar rápida para subir glucosa.'],
            ],
            'foodCategories' => [
                ['value' => 'frutas', 'label' => 'Frutas'],
                ['value' => 'verduras', 'label' => 'Verduras'],
                ['value' => 'cereales', 'label' => 'Cereales / Granos'],
                ['value' => 'proteinas', 'label' => 'Proteínas'],
                ['value' => 'lacteos', 'label' => 'Lácteos'],
                ['value' => 'grasas', 'label' => 'Grasas saludables'],
                ['value' => 'azucares', 'label' => 'Azúcares / Dulces'],
                ['value' => 'bebidas', 'label' => 'Bebidas'],
            ],
        ]);
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

        $response = redirect()->route('dashboard')->with('status', __('Registro de nutrición guardado con éxito.'));

        return $request->header('X-Inertia')
            ? Inertia::location($response->getTargetUrl())
            : $response;
    }
}
