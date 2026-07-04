<?php

namespace App\Http\Controllers;

use App\Services\DashboardMetricsService;
use App\Models\PatientLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
     * @param DashboardMetricsService $metricsService
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
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = auth()->user();
        
        // Redirigir al proceso administrativo si es administrador
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        // Redirigir al proceso de configuración inicial si no tiene rol asignado
        if (!$user->hasCompletedOnboarding()) {
            return redirect()->route('onboarding.index');
        }

        // Redirigir al dashboard correcto según el rol
        if (!$user->isPatient() && $user->isCaregiver()) {
            return redirect()->route('caregiver.dashboard');
        }
        if (!$user->isPatient() && $user->isDoctor()) {
            return redirect()->route('doctor.dashboard');
        }

        // Si es paciente pero no tiene perfil aún
        if (!$user->patientProfile) {
            return redirect()->route('onboarding.index');
        }

        // Obtener los datos procesados a través de la capa de servicios (Service Layer)
        $metrics = $this->metricsService->getDashboardMetrics($user->id);

        // Obtener últimos 5 registros para llenar el espacio del dashboard
        $recentLogs = \App\Models\VitalSign::where('user_id', $user->id)
            ->whereNotNull('glucose_level')
            ->where('glucose_level', '>', 0)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        return view('dashboard', array_merge($metrics, compact('recentLogs')));
    }

    /**
     * Guarda el peso del usuario desde la tarjeta rápida del Dashboard.
     * Crea un registro mínimo de VitalSign con solo el peso.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeWeight(Request $request)
    {
        $request->validate([
            'weight' => ['required', 'numeric', 'min:20', 'max:350'],
        ]);

        \App\Models\VitalSign::create([
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
     * @return \Illuminate\View\View
     */
    public function summary()
    {
        $user = auth()->user();
        $metrics = $this->metricsService->getDashboardMetrics($user->id);

        // Obtener registros históricos para la vista detallada
        $vitalsHistory = \App\Models\VitalSign::where('user_id', $user->id)
            ->latest()
            ->take(30)
            ->get();

        $nutritionHistory = \App\Models\NutritionLog::where('user_id', $user->id)
            ->latest()
            ->take(30)
            ->get();

        $activityHistory = \App\Models\ActivityLog::where('user_id', $user->id)
            ->latest()
            ->take(30)
            ->get();

        $symptomsHistory = \Illuminate\Support\Facades\DB::table('symptom_user')
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
        $targetMax = $user->patientProfile?->target_glucose_max ?? \App\Models\VitalSign::GLUCOSE_DEFAULT_MAX;
        $avgByMoment = \App\Models\VitalSign::where('user_id', $user->id)
            ->whereNotNull('glucose_level')->where('glucose_level', '>', 0)
            ->whereNotNull('measurement_moment')
            ->where('created_at', '>=', now()->subDays(90))
            ->selectRaw('measurement_moment, AVG(glucose_level) as avg_glucose')
            ->groupBy('measurement_moment')
            ->pluck('avg_glucose', 'measurement_moment');

        // Colores por nivel clínico real de cada momento (misma fuente de verdad que el resto de la app)
        $glucoseMomentColors = [
            'baja'    => 'rgba(255,159,67,0.85)',
            'normal'  => 'rgba(40,199,111,0.75)',
            'elevada' => 'rgba(234,84,85,0.75)',
            'sin'     => 'rgba(0,0,0,0.08)',
        ];
        $extraMetrics['glucoseByMomentLabels'] = $momentos;
        $extraMetrics['glucoseByMomentData'] = array_map(fn ($m) => round((float) ($avgByMoment[$m] ?? 0)), $momentos);
        $extraMetrics['glucoseByMomentColors'] = array_map(function ($m) use ($avgByMoment, $targetMin, $targetMax, $glucoseMomentColors) {
            $avg = isset($avgByMoment[$m]) ? (int) round((float) $avgByMoment[$m]) : 0;
            $estado = \App\Models\VitalSign::clasificarGlucosa($avg ?: null, $m, $targetMin, $targetMax);
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

        return view('tracking.summary', array_merge($metrics, $extraMetrics, compact(
            'vitalsHistory', 'nutritionHistory', 'activityHistory', 'symptomsHistory'
        )));
    }

    /**
     * Clasifica el promedio de glucosa (sin datos / baja / en rango / sobre rango).
     */
    private function classifyGlucoseAverage(int $avg, int $targetMin = 70): array
    {
        if ($avg <= 0)         return ['label' => 'Sin datos',    'short' => 'Sin datos', 'color' => 'text-muted',   'icon' => ''];
        if ($avg < $targetMin) return ['label' => 'Bajo el rango','short' => 'Bajo',      'color' => 'text-warning', 'icon' => 'fa-arrow-trend-down'];
        if ($avg > 140)        return ['label' => 'Sobre el rango','short' => 'Alto',     'color' => 'text-danger',  'icon' => 'fa-arrow-trend-up'];
        return ['label' => 'En rango meta', 'short' => 'Normal', 'color' => 'text-success', 'icon' => 'fa-check'];
    }

    /**
     * Clasifica la presión arterial media según criterios ACC/AHA (+ hipotensión).
     */
    private function classifyBloodPressure(int $sys, int $dia): array
    {
        if ($sys <= 0 || $dia <= 0)      return ['label' => 'Sin datos', 'color' => 'text-muted'];
        if ($sys < 90  || $dia < 60)     return ['label' => 'Baja',      'color' => 'text-warning'];
        if ($sys >= 180 || $dia >= 120)  return ['label' => 'Crisis',    'color' => 'text-danger'];
        if ($sys >= 140 || $dia >= 90)   return ['label' => 'Alta',      'color' => 'text-danger'];
        if ($sys >= 130 || $dia >= 80)   return ['label' => 'Elevada',   'color' => 'text-warning'];
        if ($sys >= 120)                 return ['label' => 'Ligeramente alta', 'color' => 'text-warning'];
        return ['label' => 'Estable', 'color' => 'text-info'];
    }

    /**
     * Clasifica la frecuencia cardíaca media en reposo.
     */
    private function classifyHeartRate(int $hr): array
    {
        if ($hr <= 0)   return ['label' => 'Sin datos',      'color' => 'text-muted'];
        if ($hr < 60)   return ['label' => 'Ritmo bajo',     'color' => 'text-warning'];
        if ($hr > 100)  return ['label' => 'Ritmo elevado',  'color' => 'text-danger'];
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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'code' => $code,
                'message' => 'Código de invitación generado. Compártelo con tu cuidador o médico.'
            ]);
        }

        return redirect()->route('dashboard')
            ->with('invite_code', $code)
            ->with('status', 'Código de invitación generado. Compártelo con tu cuidador o médico.');
    }
}
