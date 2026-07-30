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

    public function index(): InertiaResponse
    {
        $metrics = $this->metricsService->getDashboardMetrics(Auth::id());

        return Inertia::render('Tracking/Nutrition/Index', [
            'metrics' => [
                'caloriesToday' => $metrics['caloriasHoy'], 'calorieGoal' => $metrics['metaCalorias'], 'caloriePercent' => $metrics['porcentajeCalorias'],
                'carbsToday' => $metrics['carbsHoy'], 'carbGoal' => $metrics['metaCarbs'],
                'carbPercent' => min(($metrics['carbsHoy'] / ($metrics['metaCarbs'] ?: 1)) * 100, 100),
                'dailyTip' => $metrics['tipDelDia'], 'latestGlucose' => $metrics['ultimaMedicion']['glucose_level'] ?? null,
            ],
            'foods' => [
                ['image' => 'https://mirecetafacil.com/wp-content/uploads/2021/01/pollo-saludable-con-lechuga-y-aguacate.jpg', 'name' => 'Pollo con aguacate', 'calories' => '350 kcal'],
                ['image' => 'https://thumbs.dreamstime.com/b/plato-de-cena-diab%C3%A9tico-equilibrado-con-prote%C3%ADna-verduras-y-granos-para-una-alimentaci%C3%B3n-saludable-un-controlado-en-parte-394989319.jpg', 'name' => 'Plato equilibrado', 'calories' => '420 kcal'],
                ['image' => 'https://foodsmartcolorado.colostate.edu/wp-content/uploads/2020/01/Screenshot-50.png', 'name' => 'Ensalada de granos', 'calories' => '280 kcal'],
                ['image' => 'https://th.bing.com/th/id/OIP.fM_L4M7wB_E_R4X6uI_m1AHaE8?rs=1&pid=ImgDetMain', 'name' => 'Salmón a la plancha', 'calories' => '310 kcal'],
                ['image' => 'https://cdn.loveandlemons.com/wp-content/uploads/2021/04/pesto-pasta.jpg', 'name' => 'Pasta integral con pesto', 'calories' => '390 kcal'],
            ],
            'recommendations' => [
                'Para tu próxima comida, prioriza vegetales de hoja verde y una proteína magra. Estás cerca de tu límite de carbohidratos.',
                'Tus niveles están estables. Podrías incluir una porción pequeña de fruta como snack.',
                'Considera realizar 15 minutos de caminata ligera después de tu siguiente ingesta.',
                'Excelente balance hoy. Mantén la hidratación antes de la cena.',
                'Tu elección de plato equilibrado sería ideal para mantener estos niveles.',
            ],
            'urls' => ['create' => route('tracking.nutrition.create', absolute: false)],
        ]);
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
                ['value' => 'desayuno', 'label' => 'Desayuno', 'description' => 'Primera comida del día.', 'icon' => 'coffee'],
                ['value' => 'almuerzo', 'label' => 'Comida', 'description' => 'Comida del mediodía.', 'icon' => 'cloudSun'],
                ['value' => 'cena', 'label' => 'Cena', 'description' => 'Última comida del día.', 'icon' => 'moon'],
                ['value' => 'snack', 'label' => 'Snack', 'description' => 'Algo pequeño entre comidas.', 'icon' => 'apple'],
                ['value' => 'correccion', 'label' => 'Corrección', 'description' => 'Jugo o azúcar rápida para subir glucosa.', 'icon' => 'cookie'],
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
