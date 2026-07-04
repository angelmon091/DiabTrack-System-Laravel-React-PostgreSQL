<?php

namespace App\Services;

use App\Models\ApiUsageLog;
use App\Models\PatientNotification;
use App\Models\User;
use App\Models\VitalSign;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PatientReminderService
{
    public function __construct(private readonly DailyTipSuggestionService $suggestionService)
    {
    }

    /**
     * Genera (si procede) un recordatorio IA para el paciente sobre el dato
     * que aún no ha registrado hoy. Devuelve la notificación creada o null.
     */
    public function generateFor(User $patient): ?PatientNotification
    {
        $today = Carbon::today();

        $hasGlucose  = $patient->vitalSigns()->whereDate('created_at', $today)->exists();
        $hasMeals    = $patient->nutritionLogs()->whereDate('consumed_at', $today)->exists();
        $hasActivity = $patient->activityLogs()->whereDate('created_at', $today)->exists();

        // Título contextual (barato) + texto de respaldo, en orden de prioridad.
        $faltantes = [];
        if (!$hasGlucose) {
            $faltantes[] = ['title' => '¿Ya mediste tu glucosa hoy?', 'fallback' => 'Registra tu nivel de glucosa de hoy para darte un consejo más personalizado mañana.'];
        }
        if (!$hasMeals) {
            $faltantes[] = ['title' => 'Anota lo que comiste hoy', 'fallback' => 'Registra tus comidas de hoy; cada una ayuda a entender cómo reacciona tu glucosa.'];
        }
        if (!$hasActivity) {
            $faltantes[] = ['title' => '¿Te moviste hoy?', 'fallback' => 'Registra tu actividad física de hoy, aunque sea una caminata corta.'];
        }

        // Nada pendiente: no se genera recordatorio.
        if (empty($faltantes)) {
            return null;
        }

        $principal = $faltantes[0];

        // Evita apilar recordatorios idénticos sin leer del mismo día (anti-spam).
        $duplicado = PatientNotification::where('user_id', $patient->id)
            ->where('type', 'ai_reminder')
            ->where('title', $principal['title'])
            ->whereNull('read_at')
            ->whereDate('created_at', $today)
            ->exists();

        if ($duplicado) {
            return null;
        }

        $context   = $this->buildReminderContext($patient, !$hasGlucose, !$hasMeals, !$hasActivity);
        $resultado = $this->callAi($context);

        // Cuerpo generado por IA, o respaldo estático si la IA falla.
        $body = $resultado['tip'] ?? $principal['fallback'];

        $notification = PatientNotification::create([
            'user_id' => $patient->id,
            'type'    => 'ai_reminder',
            'title'   => $principal['title'],
            'body'    => $body,
            'icon'    => 'fa-solid fa-robot',
        ]);

        if ($resultado) {
            ApiUsageLog::create([
                'provider'          => $resultado['provider'],
                'model'             => $resultado['model'],
                'input_tokens'      => $resultado['input_tokens'],
                'output_tokens'     => $resultado['output_tokens'],
                'estimated_cost_usd'=> ApiUsageLog::calculateCost($resultado['provider'], $resultado['input_tokens'], $resultado['output_tokens']),
                'daily_tip_id'      => null,
                'patient_id'        => $patient->id,
            ]);
        }

        return $notification;
    }

    /**
     * Intenta generar el texto con Gemini y, si falla, con Anthropic.
     * Devuelve null si ninguna IA está disponible o ambas fallan (usará respaldo).
     */
    private function callAi(array $context): ?array
    {
        $geminiKey      = config('services.gemini.key');
        $geminiModel    = config('services.gemini.model', 'gemini-2.5-flash');
        $anthropicKey   = config('services.anthropic.key');
        $anthropicModel = config('services.anthropic.model', 'claude-haiku-4-5');

        if ($geminiKey) {
            try {
                return $this->suggestionService->generateReminderGemini($context, $geminiKey, $geminiModel);
            } catch (\Throwable $e) {
                // Continúa al fallback de Anthropic.
            }
        }

        if ($anthropicKey) {
            try {
                return $this->suggestionService->generateReminderAnthropic($context, $anthropicKey, $anthropicModel);
            } catch (\Throwable $e) {
                // Sin IA disponible; se usará el respaldo estático.
            }
        }

        return null;
    }

    /**
     * Contexto mínimo para el recordatorio (mantiene el costo de tokens al mínimo).
     */
    private function buildReminderContext(User $patient, bool $faltaGlucosa, bool $faltaComidas, bool $faltaActividad): array
    {
        $profile   = $patient->patientProfile;
        $targetMin = $profile?->target_glucose_min ?? VitalSign::GLUCOSE_DEFAULT_MIN;
        $targetMax = $profile?->target_glucose_max ?? VitalSign::GLUCOSE_DEFAULT_MAX;

        $ultimaGlucosa = $patient->vitalSigns()
            ->whereNotNull('glucose_level')
            ->where('glucose_level', '>', 0)
            ->latest('id')
            ->first();

        $ultimaClase = $ultimaGlucosa
            ? ucfirst(VitalSign::clasificarGlucosa((int) $ultimaGlucosa->glucose_level, $ultimaGlucosa->measurement_moment, $targetMin, $targetMax) ?? 'normal')
            : null;

        $sintomas = DB::table('symptom_user')
            ->join('symptoms', 'symptom_user.symptom_id', '=', 'symptoms.id')
            ->where('symptom_user.user_id', $patient->id)
            ->where('symptom_user.logged_at', '>=', Carbon::now()->subHours(48))
            ->pluck('symptoms.name')
            ->toArray();

        return [
            'nombre'               => $patient->name,
            'falta_glucosa'        => $faltaGlucosa,
            'falta_comidas'        => $faltaComidas,
            'falta_actividad'      => $faltaActividad,
            'ultima_glucosa_clase' => $ultimaClase,
            'sintomas_recientes'   => $sintomas,
        ];
    }
}
