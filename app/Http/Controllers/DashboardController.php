<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\NutritionLog;
use App\Models\PatientLink;
use App\Models\VitalSign;
use App\Services\DashboardMetricsService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Clase DashboardController
 *
 * Gestiona la visualización del panel principal del usuario, integrando
 * las métricas de salud procesadas por el servicio correspondiente.
 */
class DashboardController extends Controller
{
    /**
     * Instancia del servicio de métricas.
     *
     * @var DashboardMetricsService
     */
    protected $metricsService;

    /**
     * Crea una nueva instancia del controlador.
     *
     * @return void
     */
    public function __construct(DashboardMetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    /**
     * Muestra el panel de control con analíticas y resumen para el usuario autenticado.
     *
     * Verifica si el usuario tiene un perfil de paciente completado antes de
     * renderizar la vista con las métricas de salud.
     *
     * @return View|RedirectResponse
     */
    public function index(): InertiaResponse|RedirectResponse
    {
        $user = auth()->user();

        // Redirigir al proceso administrativo si es administrador
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Redirigir al proceso de configuración inicial si no tiene rol asignado
        if (! $user->hasCompletedOnboarding()) {
            return redirect()->route('onboarding.index');
        }

        // Redirigir al dashboard correcto según el rol
        if (! $user->isPatient() && $user->isCaregiver()) {
            return redirect()->route('caregiver.dashboard');
        }
        if (! $user->isPatient() && $user->isDoctor()) {
            return redirect()->route('doctor.dashboard');
        }

        // Si es paciente pero no tiene perfil aún
        if (! $user->patientProfile) {
            return redirect()->route('onboarding.index');
        }

        // Obtiene los datos procesados mediante la capa de servicios de la aplicación.
        $metrics = $this->metricsService->getDashboardMetrics($user->id);

        // Obtener últimos 5 registros para llenar el espacio del dashboard
        $recentLogs = VitalSign::where('user_id', $user->id)
            ->whereNotNull('glucose_level')
            ->where('glucose_level', '>', 0)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'metrics' => [
                'latestGlucose' => $metrics['ultimaMedicion']['glucose_level'] ?? null,
                'measurementMoment' => $metrics['ultimaMedicion']['measurement_moment'] ?? null,
                'glucoseStatus' => isset($metrics['ultimaMedicion']['status']) ? VitalSign::glucoseStatusUi($metrics['ultimaMedicion']['status'])['label'] : null,
                'glucoseStatusKey' => $metrics['ultimaMedicion']['status'] ?? null,
                'latestHba1c' => $metrics['ultimaHba1c']['hba1c'] ?? null,
                'carbsToday' => $metrics['carbsHoy'], 'caloriesToday' => $metrics['caloriasHoy'],
                'calorieGoal' => $metrics['metaCalorias'], 'caloriePercent' => $metrics['porcentajeCalorias'],
                'activityMinutes' => $metrics['actividadMinutos'], 'activityGoal' => $metrics['metaActividad'], 'activityPercent' => $metrics['porcentajeActividad'],
                'estimatedSteps' => $metrics['pasosEstimados'], 'stepGoal' => $metrics['metaPasos'], 'stepPercent' => $metrics['porcentajePasos'],
                'timeInRange' => $metrics['tiempoEnRango'], 'symptomsToday' => $metrics['sintomasHoy'],
                'glucoseLabels' => $metrics['glucosaLabels'], 'glucoseData' => $metrics['glucosaData'],
                'needsWeightUpdate' => $metrics['needsWeightUpdate'], 'lastWeight' => $metrics['ultimoPesoValor'],
            ],
            'recentLogs' => $recentLogs->map(fn (VitalSign $log) => [
                'id' => $log->id, 'date' => $log->created_at->format('d M, Y H:i'), 'glucose' => $log->glucose_level,
                'moment' => $log->measurement_moment ?? 'Ayunas', 'hba1c' => $log->hba1c,
                'status' => VitalSign::glucoseStatusUi(VitalSign::clasificarGlucosa((int) $log->glucose_level, $log->measurement_moment, $user->patientProfile?->target_glucose_min, $user->patientProfile?->target_glucose_max))['badge'],
            ])->values(),
            'tip' => ['text' => $metrics['tipDelDia'] ?? '', 'isAi' => (bool) ($metrics['tipEsIA'] ?? false)],
            'profile' => ['targetMin' => $user->patientProfile?->target_glucose_min ?? VitalSign::GLUCOSE_DEFAULT_MIN, 'targetMax' => $user->patientProfile?->target_glucose_max ?? VitalSign::GLUCOSE_DEFAULT_MAX],
            'urls' => ['summary' => route('tracking.summary', absolute: false), 'vitals' => route('tracking.vital.create', absolute: false), 'profile' => route('profile.edit', absolute: false), 'weight' => route('dashboard.weight.store', absolute: false), 'invite' => route('dashboard.invite', absolute: false)],
            'inviteCode' => session('invite_code'),
        ]);
    }

    /**
     * Guarda el peso del usuario desde la tarjeta rápida del Dashboard.
     * Crea un registro mínimo de VitalSign con solo el peso.
     *
     * @return RedirectResponse
     */
    public function storeWeight(Request $request)
    {
        $request->validate([
            'weight' => ['required', 'numeric', 'min:20', 'max:350'],
        ]);

        VitalSign::create([
            'user_id' => auth()->id(),
            'weight' => $request->weight,
            'measurement_moment' => 'Ayunas',
        ]);

        // Actualizar también el perfil del paciente
        $profile = auth()->user()->patientProfile;
        if ($profile) {
            $profile->update(['weight' => $request->weight]);
        }

        return redirect()->route('dashboard')->with('status', __('Peso registrado correctamente.'));
    }

    /**
     * Muestra una previsualización detallada de todos los datos y métricas (Vista Resumen).
     *
     * @return View
     */
    public function summary()
    {
        $user = auth()->user();
        $metrics = $this->metricsService->getDashboardMetrics($user->id);

        // Obtener registros históricos para la vista detallada
        $vitalsHistory = VitalSign::where('user_id', $user->id)
            ->latest()
            ->take(30)
            ->get();

        $nutritionHistory = NutritionLog::where('user_id', $user->id)
            ->latest()
            ->take(30)
            ->get();

        $activityHistory = ActivityLog::where('user_id', $user->id)
            ->latest()
            ->take(30)
            ->get();

        $symptomsHistory = DB::table('symptom_user')
            ->join('symptoms', 'symptom_user.symptom_id', '=', 'symptoms.id')
            ->where('symptom_user.user_id', $user->id)
            ->select('symptoms.name', 'symptoms.category', 'symptom_user.logged_at')
            ->latest('symptom_user.logged_at')
            ->take(50)
            ->get();

        // Métricas adicionales para el resumen profundo
        $extraMetrics = [
            'avgGlucose' => $vitalsHistory->avg('glucose_level') ? round($vitalsHistory->avg('glucose_level')) : 0,
            'avgSystolic' => $vitalsHistory->avg('systolic') ? round($vitalsHistory->avg('systolic')) : 0,
            'avgDiastolic' => $vitalsHistory->avg('diastolic') ? round($vitalsHistory->avg('diastolic')) : 0,
            'avgHeartRate' => $vitalsHistory->avg('heart_rate') ? round($vitalsHistory->avg('heart_rate')) : 0,
            'totalWeight' => $user->patientProfile?->weight ?? '--',
            'weightCount' => $vitalsHistory->whereNotNull('weight')->count(),
            'symptomsCount' => $symptomsHistory->count(),
            'medicationCount' => $nutritionHistory->whereNotNull('medication_taken')->count(),
            'totalActivityMinutes' => $activityHistory->sum('duration_minutes'),
        ];

        // Estado clínico calculado (antes eran etiquetas fijas)
        $targetMin = $user->patientProfile?->target_glucose_min ?? 70;
        $extraMetrics['glucoseStatus'] = $this->classifyGlucoseAverage($extraMetrics['avgGlucose'], $targetMin);
        $extraMetrics['bpStatus'] = $this->classifyBloodPressure($extraMetrics['avgSystolic'], $extraMetrics['avgDiastolic']);
        $extraMetrics['hrStatus'] = $this->classifyHeartRate($extraMetrics['avgHeartRate']);

        // Glucosa promedio por momento del día (dato ya recolectado que no se usaba en el resumen)
        $momentos = ['Ayunas', 'Antes de Comer', 'Después de Comer', 'Al Dormir'];
        $targetMax = $user->patientProfile?->target_glucose_max ?? VitalSign::GLUCOSE_DEFAULT_MAX;
        $avgByMoment = VitalSign::where('user_id', $user->id)
            ->whereNotNull('glucose_level')->where('glucose_level', '>', 0)
            ->whereNotNull('measurement_moment')
            ->where('created_at', '>=', now()->subDays(90))
            ->selectRaw('measurement_moment, AVG(glucose_level) as avg_glucose')
            ->groupBy('measurement_moment')
            ->pluck('avg_glucose', 'measurement_moment');
        $countByMoment = VitalSign::where('user_id', $user->id)
            ->whereNotNull('glucose_level')->where('glucose_level', '>', 0)
            ->whereNotNull('measurement_moment')
            ->where('created_at', '>=', now()->subDays(90))
            ->selectRaw('measurement_moment, COUNT(*) as measurement_count')
            ->groupBy('measurement_moment')
            ->pluck('measurement_count', 'measurement_moment');

        // Colores por nivel clínico real de cada momento (misma fuente de verdad que el resto de la app)
        $glucoseMomentColors = [
            'baja' => 'rgba(255,159,67,0.85)',
            'normal' => 'rgba(40,199,111,0.75)',
            'elevada' => 'rgba(234,84,85,0.75)',
            'sin' => 'rgba(0,0,0,0.08)',
        ];
        $extraMetrics['glucoseByMomentLabels'] = $momentos;
        $extraMetrics['glucoseByMomentData'] = array_map(fn ($m) => round((float) ($avgByMoment[$m] ?? 0)), $momentos);
        $extraMetrics['glucoseByMomentCounts'] = array_map(
            fn ($m) => (int) ($countByMoment[$m] ?? 0),
            $momentos
        );
        $extraMetrics['glucoseByMomentStatuses'] = array_map(function ($m) use ($avgByMoment, $targetMin, $targetMax) {
            $avg = isset($avgByMoment[$m]) ? (int) round((float) $avgByMoment[$m]) : 0;

            return match (VitalSign::clasificarGlucosa($avg ?: null, $m, $targetMin, $targetMax)) {
                'baja' => 'Bajo',
                'normal' => 'En rango',
                'elevada' => 'Alto',
                default => 'Sin registros',
            };
        }, $momentos);
        $extraMetrics['glucoseByMomentColors'] = array_map(function ($m) use ($avgByMoment, $targetMin, $targetMax, $glucoseMomentColors) {
            $avg = isset($avgByMoment[$m]) ? (int) round((float) $avgByMoment[$m]) : 0;
            $estado = VitalSign::clasificarGlucosa($avg ?: null, $m, $targetMin, $targetMax);

            return $glucoseMomentColors[$estado ?? 'sin'];
        }, $momentos);

        // Preparar datos para gráfica de categorías de comida
        $foodCategoryCounts = [];
        foreach ($nutritionHistory as $log) {
            if ($log->food_categories) {
                foreach ($log->food_categories as $cat) {
                    $foodCategoryCounts[$cat] = ($foodCategoryCounts[$cat] ?? 0) + 1;
                }
            }
        }
        $extraMetrics['foodCategoryLabels'] = array_keys($foodCategoryCounts);
        $extraMetrics['foodCategoryData'] = array_values($foodCategoryCounts);
        $extraMetrics['targetGlucoseMin'] = $targetMin;
        $extraMetrics['targetGlucoseMax'] = $targetMax;

        $symptomFrequency = $symptomsHistory->groupBy('name')->map->count()->sortDesc();

        return Inertia::render('Tracking/Summary', [
            'metrics' => [
                'avgGlucose' => $extraMetrics['avgGlucose'], 'timeInRange' => $metrics['tiempoEnRango'],
                'latestHba1c' => $metrics['ultimaHba1c']['hba1c'] ?? null, 'weight' => $extraMetrics['totalWeight'],
                'avgSystolic' => $extraMetrics['avgSystolic'], 'avgDiastolic' => $extraMetrics['avgDiastolic'],
                'avgHeartRate' => $extraMetrics['avgHeartRate'], 'totalCarbs' => $nutritionHistory->sum('carbs_grams'),
                'activityHours' => round($extraMetrics['totalActivityMinutes'] / 60, 1),
                'glucoseStatus' => $extraMetrics['glucoseStatus']['label'], 'bpStatus' => $extraMetrics['bpStatus']['label'], 'hrStatus' => $extraMetrics['hrStatus']['label'],
            ],
            'charts' => [
                'glucose' => ['labels' => $metrics['glucosaLabels'], 'values' => $metrics['glucosaData']],
                'food' => ['labels' => $extraMetrics['foodCategoryLabels'], 'values' => $extraMetrics['foodCategoryData']],
                'symptoms' => ['labels' => $symptomFrequency->keys()->values(), 'values' => $symptomFrequency->values()],
                'moments' => ['labels' => $extraMetrics['glucoseByMomentLabels'], 'values' => $extraMetrics['glucoseByMomentData'], 'colors' => $extraMetrics['glucoseByMomentColors'], 'counts' => $extraMetrics['glucoseByMomentCounts'], 'statuses' => $extraMetrics['glucoseByMomentStatuses']],
            ],
            'histories' => [
                'vitals' => $vitalsHistory->map(fn (VitalSign $vital) => ['isoDate' => $vital->created_at->toDateString(), 'date' => $vital->created_at->format('d M, H:i'), 'glucose' => $vital->glucose_level, 'moment' => $vital->measurement_moment, 'pressure' => $vital->systolic && $vital->diastolic ? "{$vital->systolic}/{$vital->diastolic}" : '--', 'heartRate' => $vital->heart_rate, 'weight' => $vital->weight, 'stress' => $vital->stress_level, 'notes' => $vital->notes])->values(),
                'nutrition' => $nutritionHistory->map(fn (NutritionLog $log) => ['isoDate' => Carbon::parse($log->consumed_at)->toDateString(), 'date' => Carbon::parse($log->consumed_at)->format('d M, H:i'), 'mealType' => $log->meal_type, 'carbs' => $log->carbs_grams, 'categories' => $log->food_categories ?? [], 'medication' => $log->medication_taken])->values(),
                'activity' => $activityHistory->map(fn (ActivityLog $log) => ['isoDate' => $log->created_at->toDateString(), 'date' => $log->created_at->format('d M'), 'type' => $log->activity_type, 'duration' => $log->duration_minutes, 'intensity' => $log->intensity, 'energy' => $log->energy_level])->values(),
                'symptoms' => $symptomsHistory->map(fn ($log) => ['isoDate' => Carbon::parse($log->logged_at)->toDateString(), 'date' => Carbon::parse($log->logged_at)->format('d M'), 'name' => $log->name, 'category' => $log->category, 'time' => Carbon::parse($log->logged_at)->format('H:i')])->values(),
            ],
        ]);
    }

    /**
     * Clasifica el promedio de glucosa (sin datos / baja / en rango / sobre rango).
     */
    private function classifyGlucoseAverage(int $avg, int $targetMin = 70): array
    {
        if ($avg <= 0) {
            return ['label' => 'Sin datos',    'short' => 'Sin datos', 'color' => 'text-muted',   'icon' => ''];
        }
        if ($avg < $targetMin) {
            return ['label' => 'Bajo el rango', 'short' => 'Bajo',      'color' => 'text-warning', 'icon' => 'fa-arrow-trend-down'];
        }
        if ($avg > 140) {
            return ['label' => 'Sobre el rango', 'short' => 'Alto',     'color' => 'text-danger',  'icon' => 'fa-arrow-trend-up'];
        }

        return ['label' => 'En rango meta', 'short' => 'Normal', 'color' => 'text-success', 'icon' => 'fa-check'];
    }

    /**
     * Clasifica la presión arterial media según criterios ACC/AHA (+ hipotensión).
     */
    private function classifyBloodPressure(int $sys, int $dia): array
    {
        if ($sys <= 0 || $dia <= 0) {
            return ['label' => 'Sin datos', 'color' => 'text-muted'];
        }
        if ($sys < 90 || $dia < 60) {
            return ['label' => 'Baja',      'color' => 'text-warning'];
        }
        if ($sys >= 180 || $dia >= 120) {
            return ['label' => 'Crisis',    'color' => 'text-danger'];
        }
        if ($sys >= 140 || $dia >= 90) {
            return ['label' => 'Alta',      'color' => 'text-danger'];
        }
        if ($sys >= 130 || $dia >= 80) {
            return ['label' => 'Elevada',   'color' => 'text-warning'];
        }
        if ($sys >= 120) {
            return ['label' => 'Ligeramente alta', 'color' => 'text-warning'];
        }

        return ['label' => 'Estable', 'color' => 'text-info'];
    }

    /**
     * Clasifica la frecuencia cardíaca media en reposo.
     */
    private function classifyHeartRate(int $hr): array
    {
        if ($hr <= 0) {
            return ['label' => 'Sin datos',      'color' => 'text-muted'];
        }
        if ($hr < 60) {
            return ['label' => 'Ritmo bajo',     'color' => 'text-warning'];
        }
        if ($hr > 100) {
            return ['label' => 'Ritmo elevado',  'color' => 'text-danger'];
        }

        return ['label' => 'Ritmo regular', 'color' => 'text-info'];
    }

    /**
     * Genera un código de invitación para vincular un cuidador o médico.
     */
    public function generateInviteCode(Request $request)
    {
        $user = auth()->user();

        // Eliminar códigos previos pendientes del usuario
        PatientLink::where('patient_id', $user->id)
            ->where('status', 'pending')
            ->delete();

        $code = strtoupper(Str::random(6));

        PatientLink::create([
            'patient_id' => $user->id,
            'role' => $request->input('role', 'caregiver'),
            'invite_code' => $code,
            'status' => 'pending',
            'expires_at' => now()->addHours(24),
        ]);

        return redirect()->route('dashboard')
            ->with('invite_code', $code)
            ->with('status', 'Código de invitación generado. Compártelo con tu cuidador o médico.');
    }
}
