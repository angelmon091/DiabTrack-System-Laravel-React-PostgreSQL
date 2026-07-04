<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DailyTipSuggestionService
{


    public function generateAnthropic(array $context, ?string $apiKey = null, ?string $modelName = null): array
    {
        return $this->callAnthropic(
            $apiKey,
            $modelName ?: config('services.anthropic.model', 'claude-haiku-4-5'),
            $this->systemPrompt(),
            $this->buildUserPrompt($context),
            300
        );
    }

    public function generateGemini(array $context, ?string $apiKey = null, ?string $modelName = null): array
    {
        return $this->callGemini(
            $apiKey ?: config('services.gemini.key'),
            $modelName ?: config('services.gemini.model', 'gemini-2.5-flash'),
            $this->systemPrompt(),
            $this->buildUserPrompt($context),
            1000
        );
    }

    /**
     * Recordatorio breve (≤20 palabras) con contexto mínimo — llamada barata.
     */
    public function generateReminderGemini(array $context, ?string $apiKey = null, ?string $modelName = null): array
    {
        $result = $this->callGemini(
            $apiKey ?: config('services.gemini.key'),
            $modelName ?: config('services.gemini.model', 'gemini-2.5-flash'),
            $this->reminderSystemPrompt(),
            $this->buildReminderPrompt($context),
            60
        );
        $result['tip'] = $this->truncateWords($result['tip'], 20);
        return $result;
    }

    public function generateReminderAnthropic(array $context, ?string $apiKey = null, ?string $modelName = null): array
    {
        $result = $this->callAnthropic(
            $apiKey,
            $modelName ?: config('services.anthropic.model', 'claude-haiku-4-5'),
            $this->reminderSystemPrompt(),
            $this->buildReminderPrompt($context),
            60
        );
        $result['tip'] = $this->truncateWords($result['tip'], 20);
        return $result;
    }

    private function callAnthropic(?string $apiKey, string $model, string $system, string $userText, int $maxTokens): array
    {
        if (empty($apiKey)) {
            throw new \RuntimeException('ANTHROPIC_API_KEY no configurada.');
        }

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => config('services.anthropic.version', '2023-06-01'),
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'temperature' => 0.65,
                    'system' => $system,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $userText],
                            ],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                throw new \RuntimeException('Anthropic respondió con estado HTTP ' . $response->status());
            }

            $tip = trim(
                collect($response->json('content', []))->pluck('text')->filter()->implode(' ')
            );

            if ($tip === '') {
                throw new \RuntimeException('Anthropic devolvió una respuesta vacía.');
            }

            return [
                'tip'           => $tip,
                'provider'      => 'anthropic',
                'model'         => $model,
                'input_tokens'  => (int) $response->json('usage.input_tokens', 0),
                'output_tokens' => (int) $response->json('usage.output_tokens', 0),
            ];

        } catch (\Throwable $e) {
            Log::warning('GenerateDailyTips: error de Anthropic — ' . $e->getMessage());
            throw new \RuntimeException('No se pudo generar el texto con Anthropic.', 0, $e);
        }
    }

    private function callGemini(?string $apiKey, string $model, string $system, string $userText, int $maxOutputTokens): array
    {
        if (empty($apiKey)) {
            throw new \RuntimeException('GEMINI_API_KEY no configurada.');
        }

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->post('https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . urlencode($apiKey), [
                    'contents' => [
                        ['parts' => [['text' => $userText]]],
                    ],
                    'systemInstruction' => [
                        'parts' => [['text' => $system]],
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => $maxOutputTokens,
                        'temperature' => 0.65,
                    ],
                ]);

            if ($response->failed()) {
                throw new \RuntimeException('Gemini respondió con estado HTTP ' . $response->status());
            }

            $tip = trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text', ''));

            if ($tip === '') {
                throw new \RuntimeException('Gemini devolvió una respuesta vacía.');
            }

            return [
                'tip'           => $tip,
                'provider'      => 'gemini',
                'model'         => $model,
                'input_tokens'  => (int) data_get($response->json(), 'usageMetadata.promptTokenCount', 0),
                'output_tokens' => (int) data_get($response->json(), 'usageMetadata.candidatesTokenCount', 0),
            ];

        } catch (\Throwable $e) {
            Log::warning('GenerateDailyTips: error de Gemini — ' . $e->getMessage());
            throw new \RuntimeException('No se pudo generar el texto con Gemini.', 0, $e);
        }
    }

    private function truncateWords(string $text, int $limit): string
    {
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) <= $limit) {
            return $text;
        }
        return rtrim(implode(' ', array_slice($words, 0, $limit)), " ,.;:") . '…';
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
Eres un asistente de bienestar especializado en diabetes mellitus. Tu tarea es generar UN ÚNICO mensaje diario, breve, cálido y accionable para el paciente, basándote en sus datos del día anterior.

═══════════════════════════════
LÍMITES DE BIENESTAR — LEE PRIMERO
═══════════════════════════════
Eres un asistente de BIENESTAR, no un médico ni un profesional de la salud.
1. NUNCA menciones medicamentos, insulina, dosis ni tratamientos farmacológicos.
2. NUNCA hagas diagnósticos ni interpretes síntomas como enfermedades.
3. NUNCA uses lenguaje alarmista: prohibido "urgente", "peligroso", "crisis", "inmediatamente".
4. Ante cualquier valor fuera de rango: el ÚNICO llamado a la acción médica permitido es sugerir con calma que vale la pena comentárselo al médico en su próxima visita.
5. Responde SOLO el texto del mensaje: sin saludos, títulos ni explicaciones adicionales.
6. Siempre en español, tono cálido, claro y motivador.
7. Máximo 220 caracteres.
8. ACCIÓN INMEDIATA OBLIGATORIA: el mensaje SIEMPRE debe terminar con algo concreto que el paciente pueda hacer HOY en los próximos minutos u horas. No basta con referir al médico o dar información; añade una acción pequeña e inmediata. Ejemplos según contexto:
   - Síntomas o referencia médica → "mientras tanto, descansa y mantente bien hidratado."
   - Glucosa elevada → "prueba a empezar la comida con verduras o agua antes del plato principal."
   - Sin actividad → "date una caminata de 10 minutos después de comer."
   - Estrés → "haz 3 respiraciones profundas ahora mismo."
   - Todo bien → "bebe un vaso de agua ahora y sigue así."
   La acción debe ser realista, no condicionada ("si puedes") y adaptada al contexto del paciente.

REGLA CRÍTICA SOBRE DATOS FALTANTES:
La ausencia de un registro en la app NO significa que el paciente no realizó esa acción.
- "Desayuno ✗" = no registró el desayuno, NO que no desayunó.
- "Sin actividad registrada" = no lo anotó, NO que estuvo inactivo.
NUNCA digas "no te saltaste el desayuno" ni "recuerda no saltarte comidas" cuando el dato falta.
El mensaje correcto ante datos faltantes es motivar a REGISTRAR, no asumir conductas.

═══════════════════════════════
INTERPRETACIÓN DE GLUCOSA POR MOMENTO
═══════════════════════════════
Los datos de glucosa ya vienen clasificados (Normal / Elevada / Baja). Úsalos directamente.
Si hay múltiples lecturas, analiza el PATRÓN, no un valor aislado.

Referencia clínica — metas de manejo ADA para diabéticos (uso interno, no la repitas al paciente):
- Ayunas / Antes de Comer : Normal 80–130 mg/dL | Elevada >130 | Baja <70
- Después de Comer (1–2h) : Normal <180 mg/dL   | Elevada ≥180 | Baja <70
- Al Dormir               : Normal 90–150 mg/dL | Elevada >150 | Baja <70
- Sin momento             : usa el rango objetivo personal del paciente

Estas son metas de MANEJO para personas con diabetes, no los valores de una persona sana
(por eso una glucosa de 110 en ayunas está DENTRO de meta, no elevada).

Glucosa "Elevada después de comer" puede ser esperada si hubo carbohidratos o azúcares → consejo: orden de alimentos, fibra primero.
Glucosa "Elevada en ayunas" es más significativa → sugiere mencionarlo al médico con calma.
Glucosa "Baja" de cualquier tipo → no saltarse comidas, tener un snack disponible.

═══════════════════════════════
PERSONALIZACIÓN DEMOGRÁFICA
═══════════════════════════════
Cruza siempre los datos del día con el perfil del paciente:

EDAD:
- Menor de 30 años: hábitos a largo plazo, sin restricción de intensidad de ejercicio.
- 30–60 años: equilibrio entre actividad y recuperación, hábitos sostenibles.
- Mayor de 60 años: actividad de bajo impacto (caminar, estirar), constancia sobre intensidad; mayor precaución con glucosa baja.

IMC (peso[kg] / altura[m]²):
- Menor de 18.5: nunca sugerir restricción alimentaria; énfasis en nutrición suficiente y variada.
- 18.5–24.9: reforzar hábitos actuales, mantenimiento.
- 25–29.9: pequeños ajustes en porciones y más movimiento gradual.
- 30 o más: cambios pequeños y sostenibles; NUNCA lenguaje de "debes bajar de peso" ni juicios sobre el cuerpo.

TIPO DE DIABETES:
- Tipo 1: énfasis en consistencia de medición y reconocimiento de patrones; no en peso ni en resistencia.
- Tipo 2: actividad física, alimentación balanceada y regularidad de comidas como principales palancas.
- LADA o No especificado: tratar con la misma cautela que Tipo 1.

GÉNERO:
- Femenino: si el contexto lo justifica, mencionar que factores hormonales pueden influir en los niveles de glucosa.
- Masculino: mayor énfasis en riesgo cardiovascular si la presión o la FC aparece elevada.

ESTRÉS (campo: Bajo / Medio / Alto):
- Alto: conecta estrés con glucosa y propón una técnica concreta (respiración 4-7-8, pausa activa de 5 minutos).
- Medio: menciona brevemente el impacto del estrés sin alarmar.
- Bajo o sin registro: no menciones el estrés.

INTENSIDAD DE EJERCICIO (campo: baja / media / alta):
- alta: felicitar y sugerir recuperación activa (estirar, hidratación).
- media: reforzar constancia.
- baja o ninguna: proponer algo concreto y fácil según edad e IMC.

ENERGÍA POST-ACTIVIDAD (campo: muy_baja / baja / normal / alta / muy_alta):
- muy_baja o baja: sugiere no exigirse tanto la próxima vez y priorizar el descanso.
- muy_alta o alta: refuerza que el ejercicio está funcionando.

═══════════════════════════════
PRIORIDAD PARA ELEGIR EL CONSEJO DEL DÍA
═══════════════════════════════
Analiza TODOS los datos y elige el aspecto MÁS relevante hoy, en este orden:

1. SÍNTOMAS reportados — siempre prioritarios; respuesta empática y sugerir hablar con el médico.
2. GLUCOSA Baja — no saltarse comidas, tener snack disponible.
3. GLUCOSA Elevada en ayunas — mencionar con calma, sugerir comentarlo al médico.
4. ESTRÉS Alto — técnica concreta de manejo.
5. GLUCOSA Elevada post-comida + azúcares o muchos carbohidratos — orden de alimentos, fibra y verdura primero.
6. REGISTRO INCOMPLETO DE COMIDAS (desayuno, almuerzo o cena sin anotar) — motivar a registrar para identificar patrones; NUNCA asumir que no comió.
7. SIN ACTIVIDAD FÍSICA — propuesta concreta según edad e IMC.
8. PRESIÓN o FC fuera de rango — mencionar con calma y referir al médico.
9. ACTIVIDAD INTENSA — felicitar y sugerir recuperación activa.
10. AUTOCUIDADO — revisión de pies, hidratación, salud ocular; específico y práctico.
11. MEDICIÓN INCONSISTENTE — valor de medir siempre a la misma hora.

NUNCA des consejos genéricos sin datos que los justifiquen.
NUNCA repitas el tema del último consejo dado (viene indicado en el prompt del usuario).
PROMPT;
    }

    private function buildUserPrompt(array $c): string
    {
        $lines = [];

        // ── Perfil clínico ──────────────────────────────────────────────────
        $lines[] = '=== PERFIL CLÍNICO ===';
        $lines[] = '- Nombre: ' . ($c['nombre'] ?? 'No especificado');
        $lines[] = '- Tipo de diabetes: ' . ($c['tipo_diabetes'] ?? 'No especificado');
        $lines[] = '- Edad: ' . ($c['edad'] ? $c['edad'] . ' años' : 'No especificada');
        $lines[] = '- Género: ' . ($c['genero'] ?? 'No especificado');
        $lines[] = '- Peso: ' . ($c['peso_kg'] ? $c['peso_kg'] . ' kg' : 'No registrado');
        $lines[] = '- Altura: ' . ($c['altura_cm'] ? $c['altura_cm'] . ' cm' : 'No registrada');
        $lines[] = '- IMC: ' . ($c['imc'] !== null ? $c['imc'] : 'Sin datos');
        $lines[] = '- Rango glucosa objetivo personal: ' . ($c['rango_glucosa_min'] ?? 70) . '–' . ($c['rango_glucosa_max'] ?? 180) . ' mg/dL';

        // ── Glucosa: tabla individual clasificada (Bloque 1) ───────────────
        $lines[] = '';
        $lines[] = '=== GLUCOSA (últimas 48h) ===';

        if (!empty($c['lecturas_glucosa'])) {
            $lines[] = '- Total de lecturas: ' . $c['total_lecturas'] . ' (' . $c['lecturas_fuera_rango'] . ' fuera de rango objetivo)';
            $lines[] = '';
            $lines[] = 'Hora  | Momento               | Valor      | Clasificación';
            $lines[] = '------|------------------------|------------|---------------';
            foreach ($c['lecturas_glucosa'] as $l) {
                $lines[] = sprintf(
                    '%s | %-22s | %s mg/dL | %s',
                    $l['hora'],
                    $l['momento'],
                    $l['valor'],
                    $l['clase']
                );
            }
            if ($c['glucosa_promedio_48h'] !== null) {
                $lines[] = '';
                $lines[] = '- Promedio: ' . $c['glucosa_promedio_48h'] . ' mg/dL | Mín: ' . $c['glucosa_min_48h'] . ' mg/dL | Máx: ' . $c['glucosa_max_48h'] . ' mg/dL';
            }
        } else {
            $lines[] = '- Sin lecturas de glucosa en las últimas 48h.';
        }

        if ($c['hba1c'] ?? null) {
            $hba1cFecha = $c['hba1c_fecha'] ? ' (registrada el ' . $c['hba1c_fecha'] . ')' : '';
            $lines[] = '- HbA1c más reciente: ' . $c['hba1c'] . '%' . $hba1cFecha;
        }

        // ── Otros signos vitales ────────────────────────────────────────────
        $lines[] = '';
        $lines[] = '=== OTROS SIGNOS VITALES ===';

        if (($c['presion_sistolica'] ?? null) && ($c['presion_diastolica'] ?? null)) {
            $lines[] = '- Presión arterial (últimos 30 días): ' . $c['presion_sistolica'] . '/' . $c['presion_diastolica'] . ' mmHg';
        } else {
            $lines[] = '- Presión arterial: Sin registro en los últimos 30 días';
        }

        $lines[] = '- Frecuencia cardíaca: ' . ($c['frecuencia_cardiaca'] ? $c['frecuencia_cardiaca'] . ' bpm (últimos 30 días)' : 'Sin registro en los últimos 30 días');
        $lines[] = '- Nivel de estrés: ' . ($c['nivel_estres'] ?? 'No registrado');

        if ($c['nota_vital'] ?? null) {
            $lines[] = '- Nota del paciente: "' . $c['nota_vital'] . '"';
        }

        // ── Actividad física (Bloque 3: con hora de inicio) ────────────────
        $lines[] = '';
        $lines[] = '=== ACTIVIDAD FÍSICA (ayer) ===';

        if (($c['minutos_actividad_ayer'] ?? 0) > 0) {
            $lines[] = '- Minutos de actividad: ' . $c['minutos_actividad_ayer'] . ' min';
            $lines[] = '- Tipos de ejercicio: ' . (count($c['tipos_actividad_ayer'] ?? []) > 0 ? implode(', ', $c['tipos_actividad_ayer']) : 'No especificado');
            $lines[] = '- Intensidad: ' . (count($c['intensidad_actividad'] ?? []) > 0 ? implode(', ', $c['intensidad_actividad']) : 'No especificada');
            $lines[] = '- Energía post-actividad: ' . ($c['energia_post_actividad'] ?? 'No registrada');
            if ($c['hora_ejercicio'] ?? null) {
                $lines[] = '- Hora de inicio: ' . $c['hora_ejercicio'];
            }
        } else {
            $lines[] = '- Sin actividad física registrada ayer.';
        }

        // ── Nutrición (Bloque 3: completitud + horario) ────────────────────
        $lines[] = '';
        $lines[] = '=== NUTRICIÓN (ayer) ===';

        $desayuno = ($c['registro_desayuno'] ?? false) ? '✓ registrado' : '✗ sin registro en app';
        $almuerzo = ($c['registro_almuerzo'] ?? false) ? '✓ registrado' : '✗ sin registro en app';
        $cena     = ($c['registro_cena'] ?? false)     ? '✓ registrado' : '✗ sin registro en app';
        $lines[]  = '- Estado de registro (✗ = no anotado en la app, NO significa que no comió):';
        $lines[]  = '  Desayuno: ' . $desayuno . ' | Almuerzo: ' . $almuerzo . ' | Cena: ' . $cena;

        $lines[] = '- Carbohidratos totales: ' . (($c['carbs_ayer_gramos'] ?? 0) > 0 ? $c['carbs_ayer_gramos'] . ' g' : 'Sin registros');
        $lines[] = '- Grupos de alimentos: ' . (count($c['categorias_alimentos'] ?? []) > 0 ? implode(', ', $c['categorias_alimentos']) : 'No especificados');

        if ($c['tiene_azucares'] ?? false) {
            $lines[] = '- Consumo de azúcares/dulces: Sí';
        }

        if (!empty($c['comidas_con_hora'])) {
            $horariosStr = collect($c['comidas_con_hora'])
                ->map(fn($m) => ucfirst($m['comida']) . ' a las ' . $m['hora'])
                ->implode(', ');
            $lines[] = '- Horario de comidas: ' . $horariosStr;
        }

        // ── Síntomas ────────────────────────────────────────────────────────
        $lines[] = '';
        $lines[] = '=== SÍNTOMAS RECIENTES (48h) ===';
        $lines[] = count($c['sintomas_recientes'] ?? []) > 0
            ? '- Síntomas reportados: ' . implode(', ', $c['sintomas_recientes'])
            : '- Sin síntomas reportados.';

        // ── Último consejo dado (Bloque 2: fix — ahora llega el texto real) ─
        if (!empty($c['ultimo_consejo'])) {
            $lines[] = '';
            $lines[] = '=== ÚLTIMO CONSEJO DADO (NO repetir el mismo tema) ===';
            $lines[] = '"' . $c['ultimo_consejo'] . '"';
        }

        // ── Instrucción final ───────────────────────────────────────────────
        $lines[] = '';
        $lines[] = 'Analiza el patrón más relevante clínicamente cruzando el perfil demográfico (edad, género, IMC, tipo de diabetes) con los datos del día (glucosa por momento, alimentación, actividad, estrés, síntomas).';
        $lines[] = 'Genera UN mensaje breve, cálido y accionable de máximo 220 caracteres. Recuerda: eres un asistente de bienestar. Si hay valores fuera de rango, menciona con calma que vale la pena comentárselo al médico en la próxima visita. Sin saludos, títulos ni diagnósticos.';

        return implode("\n", $lines);
    }

    private function reminderSystemPrompt(): string
    {
        return <<<'PROMPT'
Eres un asistente de bienestar en diabetes. El paciente YA tiene cuenta y está dentro de la app usándola. Genera UN recordatorio breve y cálido en español que lo motive a ANOTAR (registrar en su bitácora) el dato de salud que aún no ha capturado hoy.
REGLAS:
- MÁXIMO 20 palabras. Una sola frase.
- Tono cercano y motivador, trátalo de "tú".
- "Registrar" significa ANOTAR una medición/comida/actividad. Usa verbos como "anota", "apunta" o "registra TU glucosa/comida/actividad".
- PROHIBIDO pedirle crear cuenta o iniciar sesión: NUNCA uses "regístrate", "regístrate en la app", "crea tu cuenta" ni "inicia sesión". Ya está registrado y conectado.
- Termina con una acción concreta e inmediata (p. ej. "anota tu glucosa de hoy", "apunta lo que comiste").
- La ausencia de un registro NO significa que no lo hizo: invita a ANOTARLO, nunca asumas que no comió o no se movió.
- NUNCA menciones medicamentos, dosis ni diagnósticos. Sin alarmismo.
- Responde SOLO el texto del recordatorio: sin saludos, comillas, títulos ni emojis.
PROMPT;
    }

    private function buildReminderPrompt(array $c): string
    {
        $faltantes = [];
        if ($c['falta_glucosa'] ?? false)   { $faltantes[] = 'su nivel de glucosa'; }
        if ($c['falta_comidas'] ?? false)   { $faltantes[] = 'sus comidas'; }
        if ($c['falta_actividad'] ?? false) { $faltantes[] = 'su actividad física'; }

        $lines = [];
        $lines[] = 'Paciente: ' . ($c['nombre'] ?? 'el paciente');
        $lines[] = 'Datos de salud que aún no ha anotado hoy en su bitácora: ' . (count($faltantes) ? implode(', ', $faltantes) : 'nada relevante');

        if (!empty($c['ultima_glucosa_clase'])) {
            $lines[] = 'Su última lectura de glucosa fue: ' . $c['ultima_glucosa_clase'];
        }
        if (!empty($c['sintomas_recientes'])) {
            $lines[] = 'Reportó síntomas recientes: ' . implode(', ', $c['sintomas_recientes']);
        }

        $lines[] = '';
        $lines[] = 'Genera el recordatorio (máximo 20 palabras) enfocado en el dato faltante más importante.';

        return implode("\n", $lines);
    }

}
