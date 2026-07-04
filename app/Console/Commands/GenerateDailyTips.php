<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\DailyTip;
use App\Models\ApiUsageLog;
use App\Models\VitalSign;
use App\Services\DailyTipSuggestionService;
use App\Services\DashboardMetricsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

#[Signature('app:generate-daily-tips')]
#[Description('Genera tips de salud diarios personalizados para pacientes usando todos sus datos de salud disponibles')]
class GenerateDailyTips extends Command
{
    public function __construct(private readonly DailyTipSuggestionService $suggestionService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $patients = User::whereHas('roles', function ($query) {
            $query->where('name', 'paciente');
        })->with('patientProfile')->get();

        if ($patients->isEmpty()) {
            $this->info('No se encontraron pacientes para generar tips.');
            return;
        }

        $geminiKey = config('services.gemini.key');
        $geminiModel = config('services.gemini.model', 'gemini-2.5-flash');
        $anthropicKey = config('services.anthropic.key');
        $anthropicModel = config('services.anthropic.model', 'claude-haiku-4-5');

        $maxInactivityDays = (int) env('DAILY_TIPS_MAX_INACTIVITY_DAYS', 3);

        foreach ($patients as $patient) {
            $this->info("Procesando paciente: {$patient->name} (ID: {$patient->id})");

            // Verificar si el paciente ha ingresado datos clínicos o de estilo de vida recientemente
            $since = Carbon::now()->subDays($maxInactivityDays);
            $hasRecentData = $patient->vitalSigns()->where('created_at', '>=', $since)->exists() ||
                             $patient->activityLogs()->where('created_at', '>=', $since)->exists() ||
                             $patient->nutritionLogs()->where('created_at', '>=', $since)->exists() ||
                             DB::table('symptom_user')->where('user_id', $patient->id)->where('logged_at', '>=', $since)->exists();

            if (!$hasRecentData) {
                $this->warn("  Paciente inactivo en los últimos {$maxInactivityDays} días. Omitiendo generación de tip.");
                continue;
            }

            $context = $this->buildPatientContext($patient);
            $tipGenerado = null;

            $resultado = null;

            if ($geminiKey) {
                try {
                    $resultado = $this->suggestionService->generateGemini($context, $geminiKey, $geminiModel);
                    $this->line("  Tip generado con Gemini: {$geminiModel}");
                } catch (\Throwable $e) {
                    $this->error("  Error con Gemini: " . $e->getMessage());
                }
            }

            if (!$resultado && $anthropicKey) {
                try {
                    $resultado = $this->suggestionService->generateAnthropic($context, $anthropicKey, $anthropicModel);
                    $this->line("  Tip generado con Anthropic: {$anthropicModel}");
                } catch (\Throwable $e) {
                    $this->error("  Error con Anthropic: " . $e->getMessage());
                }
            }

            if (!$resultado) {
                $this->error("  No se pudo generar tip para el paciente {$patient->id} con ninguna IA configurada.");
                continue;
            }

            $tip = DailyTip::create([
                'user_id'  => $patient->id,
                'tip_text' => $resultado['tip'],
                'status'   => 'approved',
            ]);

            ApiUsageLog::create([
                'provider'          => $resultado['provider'],
                'model'             => $resultado['model'],
                'input_tokens'      => $resultado['input_tokens'],
                'output_tokens'     => $resultado['output_tokens'],
                'estimated_cost_usd'=> ApiUsageLog::calculateCost($resultado['provider'], $resultado['input_tokens'], $resultado['output_tokens']),
                'daily_tip_id'      => $tip->id,
                'patient_id'        => $patient->id,
            ]);

            DashboardMetricsService::forgetUserCache($patient->id);

            $this->info("  ✓ Tip diario generado con éxito para el paciente {$patient->id}. Tokens: {$resultado['input_tokens']} entrada / {$resultado['output_tokens']} salida.");
        }

        $this->info('Proceso de generación de tips diarios completado.');
    }

    private function buildPatientContext(User $patient): array
    {
        $profile   = $patient->patientProfile;
        $now       = Carbon::now();
        $today     = Carbon::today();
        $ayer      = $today->copy()->subDay();
        $hace30    = $now->copy()->subDays(30);

        // ── 1. Perfil clínico ───────────────────────────────────────────────
        $edad = $profile?->birth_date
            ? Carbon::parse($profile->birth_date)->age
            : null;

        $imc = null;
        if ($profile?->weight && $profile?->height && $profile->height > 0) {
            $alturaM = $profile->height / 100;
            $imc     = round($profile->weight / ($alturaM * $alturaM), 1);
        }

        $targetMin = $profile?->target_glucose_min ?? VitalSign::GLUCOSE_DEFAULT_MIN;
        $targetMax = $profile?->target_glucose_max ?? VitalSign::GLUCOSE_DEFAULT_MAX;

        // ── 2. Glucosa: lecturas individuales clasificadas (Bloque 0+1) ────
        $vitals48h = $patient->vitalSigns()
            ->where('created_at', '>=', $now->copy()->subHours(48))
            ->get();

        $lecturasGlucosa = $vitals48h
            ->whereNotNull('glucose_level')
            ->where('glucose_level', '>', 0)
            ->map(function ($v) use ($targetMin, $targetMax) {
                $valor   = (int) $v->glucose_level;
                $momento = $v->measurement_moment;
                return [
                    'hora'    => $v->created_at->format('H:i'),
                    'momento' => $momento ?? 'No especificado',
                    'valor'   => $valor,
                    'clase'   => $this->clasificarGlucosa($valor, $momento, $targetMin, $targetMax),
                ];
            })
            ->values()
            ->toArray();

        $glucosaVals    = $vitals48h->whereNotNull('glucose_level')->where('glucose_level', '>', 0);
        $glucosaPromedio = $glucosaVals->avg('glucose_level');
        $glucosaMin      = $glucosaVals->min('glucose_level');
        $glucosaMax      = $glucosaVals->max('glucose_level');
        $fueraDeRango    = collect($lecturasGlucosa)->filter(fn($l) => $l['clase'] !== 'Normal')->count();

        // ── 3. Otros signos vitales — presión y FC limitadas a 30 días (Bloque 2)
        $ultimaPresion = $patient->vitalSigns()
            ->whereNotNull('systolic')->whereNotNull('diastolic')
            ->where('created_at', '>=', $hace30)
            ->latest('id')->first();

        $ultimaFc = $patient->vitalSigns()
            ->whereNotNull('heart_rate')
            ->where('created_at', '>=', $hace30)
            ->latest('id')->first();

        $ultimoNivelEstres = $patient->vitalSigns()
            ->whereNotNull('stress_level')
            ->latest('id')->first();

        $ultimaHba1c = $patient->vitalSigns()
            ->whereNotNull('hba1c')
            ->latest('id')->first();

        $ultimaNotaVital = $patient->vitalSigns()
            ->whereNotNull('notes')
            ->where('notes', '!=', '')
            ->latest('id')->first();

        // ── 4. Actividad Física (ayer) — con hora de inicio (Bloque 3) ────
        $actividadAyer = $patient->activityLogs()
            ->whereDate('created_at', $ayer)
            ->get();

        $minutosActividadAyer = $actividadAyer->sum('duration_minutes');
        $tiposActividad       = $actividadAyer->pluck('activity_type')->filter()->unique()->values()->toArray();
        $intensidades         = $actividadAyer->pluck('intensity')->filter()->unique()->values()->toArray();
        $energiaPostActividad = $actividadAyer->pluck('energy_level')->filter()->last();
        $horaEjercicio        = $actividadAyer->first()?->start_time;

        // ── 5. Nutrición (ayer) — con horario y completitud (Bloque 3) ────
        $nutricionAyer = $patient->nutritionLogs()
            ->whereDate('created_at', $ayer)
            ->get();

        $carbsAyer          = $nutricionAyer->sum('carbs_grams');
        $tiposComidaAyer    = $nutricionAyer->pluck('meal_type')->filter()->unique()->values()->toArray();
        $categoriaAlimentos = $nutricionAyer
            ->pluck('food_categories')
            ->filter()
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        $comidasConHora = $nutricionAyer
            ->whereNotNull('consumed_at')
            ->map(fn($n) => ['comida' => $n->meal_type, 'hora' => $n->consumed_at])
            ->values()
            ->toArray();

        // ── 6. Síntomas (últimas 48h) ──────────────────────────────────────
        $sintomasRecientes = DB::table('symptom_user')
            ->join('symptoms', 'symptom_user.symptom_id', '=', 'symptoms.id')
            ->where('symptom_user.user_id', $patient->id)
            ->where('symptom_user.logged_at', '>=', $now->copy()->subHours(48))
            ->pluck('symptoms.name')
            ->toArray();

        // ── 7. Último consejo dado — ahora sí incluye el texto (Bloque 2) ─
        $ultimoTip = DailyTip::where('user_id', $patient->id)
            ->latest('id')->first();

        return [
            // Perfil
            'nombre'            => $patient->name,
            'edad'              => $edad,
            'genero'            => $profile?->gender,
            'tipo_diabetes'     => $profile?->diabetes_type,
            'imc'               => $imc,
            'peso_kg'           => $profile?->weight,
            'altura_cm'         => $profile?->height,
            'rango_glucosa_min' => $targetMin,
            'rango_glucosa_max' => $targetMax,

            // Glucosa individual clasificada
            'lecturas_glucosa'     => $lecturasGlucosa,
            'total_lecturas'       => count($lecturasGlucosa),
            'lecturas_fuera_rango' => $fueraDeRango,
            'glucosa_promedio_48h' => $glucosaPromedio !== null ? round((float) $glucosaPromedio, 1) : null,
            'glucosa_min_48h'      => $glucosaMin,
            'glucosa_max_48h'      => $glucosaMax,

            // Otros signos vitales
            'presion_sistolica'   => $ultimaPresion?->systolic,
            'presion_diastolica'  => $ultimaPresion?->diastolic,
            'frecuencia_cardiaca' => $ultimaFc?->heart_rate,
            'nivel_estres'        => $ultimoNivelEstres?->stress_level,
            'hba1c'               => $ultimaHba1c?->hba1c,
            'hba1c_fecha'         => $ultimaHba1c?->created_at?->format('d/m/Y'),
            'nota_vital'          => $ultimaNotaVital?->notes,

            // Actividad
            'minutos_actividad_ayer' => (int) $minutosActividadAyer,
            'tipos_actividad_ayer'   => $tiposActividad,
            'intensidad_actividad'   => $intensidades,
            'energia_post_actividad' => $energiaPostActividad,
            'hora_ejercicio'         => $horaEjercicio,

            // Nutrición
            'carbs_ayer_gramos'    => (int) $carbsAyer,
            'tipos_comida_ayer'    => $tiposComidaAyer,
            'categorias_alimentos' => $categoriaAlimentos,
            'registro_desayuno'    => in_array('desayuno', $tiposComidaAyer),
            'registro_almuerzo'    => in_array('almuerzo', $tiposComidaAyer),
            'registro_cena'        => in_array('cena', $tiposComidaAyer),
            'tiene_azucares'       => in_array('azucares', $categoriaAlimentos),
            'comidas_con_hora'     => $comidasConHora,

            // Síntomas
            'sintomas_recientes' => $sintomasRecientes,

            // Último consejo (fix: ahora sí lleva el texto)
            'ultimo_consejo'     => $ultimoTip?->tip_text,
        ];
    }

    private function clasificarGlucosa(int $valor, ?string $momento, int $targetMin, int $targetMax): string
    {
        // Fuente única de verdad: el mismo criterio clínico que usa el dashboard.
        return ucfirst(
            VitalSign::clasificarGlucosa($valor, $momento, $targetMin, $targetMax) ?? 'normal'
        );
    }
}
