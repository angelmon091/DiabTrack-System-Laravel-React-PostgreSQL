<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\DailyTip;
use App\Models\NutritionLog;
use App\Models\PatientProfile;
use App\Models\User;
use App\Models\VitalSign;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Clase DashboardMetricsService
 *
 * Se encarga de toda la lógica de cálculo y procesamiento de datos de salud
 * para el panel principal (Dashboard). Centraliza las consultas a modelos de
 * nutrición, actividad física y signos vitales.
 */
class DashboardMetricsService
{
    /**
     * Calcula y retorna todas las métricas necesarias para el panel principal del usuario.
     *
     * Los resultados se almacenan en caché Redis durante 5 minutos.
     * La caché se invalida automáticamente cuando se crean o actualizan
     * registros en VitalSign, NutritionLog, ActivityLog, SymptomLog o DailyTip.
     * El tip del día se resuelve fuera del caché para mostrar siempre el último consejo IA.
     *
     * @param  int  $userId  ID del usuario autenticado.
     * @return array Conjunto de métricas procesadas.
     */
    public function getDashboardMetrics($userId)
    {
        $metrics = Cache::remember(self::cacheKey($userId), 300, function () use ($userId) {
            return $this->calculateMetrics($userId);
        });

        // Eliminar cualquier tip obsoleto que haya quedado en caché de versiones anteriores.
        unset($metrics['tipDelDia'], $metrics['tipEsIA']);

        return array_merge($metrics, $this->getDailyTipForUser($userId));
    }

    /**
     * Obtiene el tip del día directamente de la BD (sin caché).
     */
    public function getDailyTipForUser(int $userId): array
    {
        $maxInactivityDays = (int) env('DAILY_TIPS_MAX_INACTIVITY_DAYS', 3);
        $since = Carbon::now()->subDays($maxInactivityDays);

        $tipAprobado = DailyTip::query()
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->where('created_at', '>=', $since)
            ->orderByDesc('id')
            ->first();

        if ($tipAprobado) {
            return [
                'tipDelDia' => $tipAprobado->tip_text,
                'tipEsIA' => true,
            ];
        }

        $user = User::find($userId);

        if ($user) {
            $hasRecentData = $user->vitalSigns()->where('created_at', '>=', $since)->exists() ||
                $user->activityLogs()->where('created_at', '>=', $since)->exists() ||
                $user->nutritionLogs()->where('created_at', '>=', $since)->exists() ||
                DB::table('symptom_user')->where('user_id', $userId)->where('logged_at', '>=', $since)->exists();

            if (! $hasRecentData) {
                return [
                    'tipDelDia' => "¡Hola! Para darte tips de salud 100% personalizados y precisos generados con Inteligencia Artificial, recuerda registrar tus datos (como tu nivel de glucosa, comidas o actividad física de hoy) en la sección 'Registrar o Nuevo'",
                    'tipEsIA' => false,
                ];
            }
        }

        $tips = [
            'Mantener un horario regular de comidas ayuda a estabilizar tus niveles de glucosa durante el día.',
            'Beber al menos 2 litros de agua diarios mejora la circulación y reduce el riesgo de hiperglucemia.',
            'Caminar 15 minutos después de comer reduce significativamente los picos de azúcar en sangre.',
            'Revisa tus pies a diario y mantenlos hidratados para prevenir posibles complicaciones.',
            'Prioriza el consumo de proteínas y fibra en tus desayunos para evitar hipoglucemias reactivas.',
            'Lleva siempre contigo un carbohidrato de rápida absorción (jugo o caramelos) para emergencias.',
            'Dormir de 7 a 8 horas cada noche promueve una mejor sensibilidad a la insulina.',
            'Anotar lo que comes te ayudará a detectar patrones en cómo ciertos alimentos afectan tu glucosa.',
            'El estrés eleva el azúcar en sangre de forma natural. Prueba técnicas de respiración si te sientes tenso.',
            'Comer la ensalada o fibra antes de los carbohidratos ayuda a aplanar tu curva de glucosa.',
        ];

        return [
            'tipDelDia' => $tips[Carbon::now()->dayOfYear % count($tips)],
            'tipEsIA' => false,
        ];
    }

    public static function cacheKey(int $userId): string
    {
        return "dashboard_metrics_{$userId}_v2";
    }

    public static function forgetUserCache(int $userId): void
    {
        Cache::forget(self::cacheKey($userId));
    }

    /**
     * Ejecuta todas las consultas y cálculos de métricas del dashboard.
     *
     * Este método procesa:
     * - Última medición de glucosa y HbA1c.
     * - Ingesta calórica diaria basada en carbohidratos.
     * - Progreso de metas de actividad y pasos (cálculos en tiempo real).
     * - Estadísticas semanales para gráficas de tendencias.
     * - Cálculo del "Tiempo en Rango" de glucosa (70-140 mg/dL).
     *
     * @param  int  $userId
     * @return array
     */
    protected function calculateMetrics($userId)
    {
        $today = Carbon::today();
        $user = User::with('patientProfile')->findOrFail($userId);
        $profile = $user->patientProfile;

        if (! $profile && $user->isPatient()) {
            // Fallback si es paciente pero el perfil no existe por alguna razón
            $profile = new PatientProfile(['user_id' => $userId]);
        }

        // 1. Signos Vitales (Glucosa y HbA1c)
        $ultimaMedicionRaw = VitalSign::where('user_id', $userId)
            ->whereNotNull('glucose_level')
            ->where('glucose_level', '>', 0)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        $ultimaMedicion = $ultimaMedicionRaw ? [
            'created_at' => $ultimaMedicionRaw->created_at->toDateTimeString(),
            'glucose_level' => $ultimaMedicionRaw->glucose_level,
            'measurement_moment' => $ultimaMedicionRaw->measurement_moment,
            // Clasificación clínica por momento (mismo criterio que usa la IA).
            'status' => VitalSign::clasificarGlucosa(
                (int) $ultimaMedicionRaw->glucose_level,
                $ultimaMedicionRaw->measurement_moment,
                $profile?->target_glucose_min,
                $profile?->target_glucose_max
            ),
        ] : null;

        $ultimaHba1cRaw = VitalSign::where('user_id', $userId)->whereNotNull('hba1c')->latest('id')->first();
        $ultimaHba1c = $ultimaHba1cRaw ? [
            'hba1c' => $ultimaHba1cRaw->hba1c,
            'created_at' => $ultimaHba1cRaw->created_at->toDateTimeString(),
        ] : null;

        // 2. Nutrición
        $carbsHoy = NutritionLog::where('user_id', $userId)->whereDate('created_at', $today)->sum('carbs_grams');
        $caloriasHoy = $carbsHoy * 4;

        // Meta calórica personalizada (Mifflin-St Jeor × factor de actividad ligera).
        // Es orientativa (asistente de bienestar), no una prescripción médica.
        $edadPerfil = $profile?->birth_date ? Carbon::parse($profile->birth_date)->age : null;
        $pesoPerfil = $profile?->weight;
        $alturaPerfil = $profile?->height;
        $generoPerfil = strtolower($profile?->gender ?? '');

        $metaCaloriasPersonalizada = (bool) ($pesoPerfil && $alturaPerfil && $edadPerfil);
        if ($metaCaloriasPersonalizada) {
            $bmr = (10 * $pesoPerfil) + (6.25 * $alturaPerfil) - (5 * $edadPerfil)
                 + ($generoPerfil === 'masculino' ? 5 : -161);
            $metaCalorias = (int) max(1200, round(($bmr * 1.375) / 50) * 50);
        } else {
            $metaCalorias = 2000; // Perfil incompleto: valor genérico de referencia.
        }
        $metaCarbs = 200;
        $porcentajeCalorias = $metaCalorias > 0 ? min(round(($caloriasHoy / $metaCalorias) * 100), 100) : 0;

        // 3. Actividad
        $actividadMinutos = ActivityLog::where('user_id', $userId)->whereDate('created_at', $today)->sum('duration_minutes');
        $metaActividad = 30; // Guía OMS/ADA: 150 min/semana ≈ 30 min/día de actividad moderada.
        $porcentajeActividad = $metaActividad > 0 ? min(round(($actividadMinutos / $metaActividad) * 100), 100) : 0;

        $pasosEstimados = ActivityLog::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->where('activity_type', 'caminar')
            ->sum('duration_minutes') * 100;

        $metaPasos = 8000;
        $porcentajePasos = $metaPasos > 0 ? min(round(($pasosEstimados / $metaPasos) * 100), 100) : 0;

        // 4. Síntomas registrados
        $sintomasHoy = DB::table('symptom_user')
            ->where('user_id', $userId)
            ->whereDate('logged_at', $today)
            ->count();

        // 5. Estadísticas Semanales de Glucosa y Rango
        $medicionesGlucosaSemana = VitalSign::where('user_id', $userId)
            ->where('glucose_level', '>', 0)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->get();

        $medicionesRecientes = $medicionesGlucosaSemana->count();
        $minRango = $profile?->target_glucose_min ?? VitalSign::GLUCOSE_DEFAULT_MIN;
        $maxRango = $profile?->target_glucose_max ?? VitalSign::GLUCOSE_DEFAULT_MAX;

        $medicionesEnRango = $medicionesGlucosaSemana->filter(function ($item) use ($minRango, $maxRango) {
            return $item->glucose_level >= $minRango && $item->glucose_level <= $maxRango;
        })->count();

        $tiempoEnRango = $medicionesRecientes > 0 ? round(($medicionesEnRango / $medicionesRecientes) * 100) : 0;

        $registrosGlucosaAgrupados = $medicionesGlucosaSemana->groupBy(function ($item) {
            return $item->created_at->toDateString();
        });

        $glucosaLabels = [];
        $glucosaData = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $dateString = $day->toDateString();

            $glucosaLabels[] = $day->locale('es')->isoFormat('ddd D');

            if ($registrosGlucosaAgrupados->has($dateString)) {
                $avgGlucose = $registrosGlucosaAgrupados->get($dateString)->avg('glucose_level');
                $glucosaData[] = $avgGlucose ? round($avgGlucose) : null;
            } else {
                $glucosaData[] = null;
            }
        }

        // 6. Recordatorio Mensual de Peso
        $ultimoPesoRegistro = VitalSign::where('user_id', $userId)
            ->whereNotNull('weight')
            ->latest('id')
            ->first();

        $needsWeightUpdate = true;
        $ultimoPesoValor = null;

        if ($ultimoPesoRegistro) {
            $ultimoPesoValor = $ultimoPesoRegistro->weight;
            if ($ultimoPesoRegistro->created_at->diffInDays(Carbon::now()) < 30) {
                $needsWeightUpdate = false;
            }
        }

        $needsWeightUpdate = $needsWeightUpdate ?? false;
        $ultimoPesoValor = $ultimoPesoValor ?? null;
        $porcentajeCalorias = $porcentajeCalorias ?? 0;
        $porcentajeActividad = $porcentajeActividad ?? 0;
        $porcentajePasos = $porcentajePasos ?? 0;
        $tiempoEnRango = $tiempoEnRango ?? 0;

        return compact(
            'ultimaMedicion',
            'ultimaHba1c',
            'carbsHoy',
            'caloriasHoy',
            'metaCalorias',
            'metaCaloriasPersonalizada',
            'metaCarbs',
            'actividadMinutos',
            'metaActividad',
            'pasosEstimados',
            'metaPasos',
            'sintomasHoy',
            'porcentajeCalorias',
            'porcentajeActividad',
            'porcentajePasos',
            'tiempoEnRango',
            'glucosaLabels',
            'glucosaData',
            'needsWeightUpdate',
            'ultimoPesoValor'
        );
    }
}
