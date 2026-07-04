<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * Buscador global del paciente: secciones de la app + sus propios registros.
     */
    public function index(Request $request): JsonResponse
    {
        $raw = trim((string) $request->query('q', ''));

        if (Str::length($raw) < 2) {
            return response()->json(['sections' => [], 'records' => []]);
        }

        $q = mb_strtolower($raw);

        return response()->json([
            'sections' => $this->searchSections($q),
            'records'  => $this->searchRecords($q),
        ]);
    }

    /**
     * Secciones/acciones de la app que coinciden con la búsqueda.
     */
    private function searchSections(string $q): array
    {
        $norm = $this->stripAccents($q);

        $sections = [
            ['label' => 'Panel principal',       'icon' => 'fa-solid fa-gauge-high',       'route' => 'dashboard',                'keywords' => ['dashboard', 'inicio', 'panel', 'principal', 'home']],
            ['label' => 'Registrar signo vital', 'icon' => 'fa-solid fa-droplet',          'route' => 'tracking.vital.create',    'keywords' => ['glucosa', 'vital', 'signo', 'presion', 'peso', 'azucar', 'medir', 'hba1c', 'ritmo', 'cardiaco', 'estres', 'registrar']],
            ['label' => 'Registrar actividad',   'icon' => 'fa-solid fa-person-running',    'route' => 'tracking.activity.create', 'keywords' => ['actividad', 'ejercicio', 'caminar', 'correr', 'deporte', 'movimiento', 'entrenar', 'registrar']],
            ['label' => 'Registrar comida',      'icon' => 'fa-solid fa-utensils',         'route' => 'tracking.nutrition.create','keywords' => ['comida', 'comidas', 'registrar', 'alimento', 'alimentacion', 'nutricion', 'dieta', 'desayuno', 'almuerzo', 'cena', 'snack', 'carbohidratos', 'carbs']],
            ['label' => 'Registrar síntomas',    'icon' => 'fa-solid fa-notes-medical',    'route' => 'tracking.symptom.create',  'keywords' => ['sintoma', 'sintomas', 'malestar', 'dolor', 'mareo', 'registrar']],
            ['label' => 'Gráficos y resumen',    'icon' => 'fa-solid fa-chart-line',       'route' => 'tracking.summary',         'keywords' => ['graficos', 'resumen', 'tendencias', 'historial', 'analisis', 'estadisticas']],
            ['label' => 'Mi perfil',             'icon' => 'fa-solid fa-user',             'route' => 'profile.edit',             'keywords' => ['perfil', 'ajustes', 'configuracion', 'cuenta', 'datos']],
        ];

        return collect($sections)
            ->filter(function (array $s) use ($norm) {
                $haystack = $this->stripAccents(mb_strtolower($s['label'])) . ' ' . implode(' ', $s['keywords']);
                return str_contains($haystack, $norm);
            })
            ->map(fn (array $s) => [
                'label' => $s['label'],
                'icon'  => $s['icon'],
                'url'   => route($s['route']),
            ])
            ->take(6)
            ->values()
            ->all();
    }

    /**
     * Registros del propio paciente que coinciden con la búsqueda.
     */
    private function searchRecords(string $q): array
    {
        $user     = Auth::user();
        $like     = '%' . $q . '%';
        $isNumber = is_numeric($q);
        $results  = [];

        // ── Signos vitales ──────────────────────────────────────────────
        $vitals = $user->vitalSigns()
            ->where(function ($w) use ($like, $isNumber, $q) {
                $w->where('measurement_moment', 'like', $like)
                  ->orWhere('notes', 'like', $like);
                if ($isNumber) {
                    $w->orWhere('glucose_level', $q);
                }
            })
            ->latest('created_at')
            ->limit(5)
            ->get();

        foreach ($vitals as $v) {
            $titulo = $v->glucose_level
                ? 'Glucosa ' . $v->glucose_level . ' mg/dL'
                : 'Signo vital';
            $detalle = collect([$v->measurement_moment, $v->created_at?->format('d/m/Y')])
                ->filter()->implode(' · ');

            $results[] = [
                'type'     => 'Signo vital',
                'icon'     => 'fa-solid fa-droplet',
                'title'    => $titulo,
                'subtitle' => $detalle,
                'url'      => route('tracking.summary'),
            ];
        }

        // ── Actividad física ────────────────────────────────────────────
        $activities = $user->activityLogs()
            ->where(fn ($w) => $w->where('activity_type', 'like', $like)->orWhere('intensity', 'like', $like))
            ->latest('created_at')
            ->limit(5)
            ->get();

        foreach ($activities as $a) {
            $detalle = collect([
                    $a->duration_minutes ? $a->duration_minutes . ' min' : null,
                    $a->intensity ? 'intensidad ' . $a->intensity : null,
                    $a->created_at?->format('d/m/Y'),
                ])->filter()->implode(' · ');

            $results[] = [
                'type'     => 'Actividad',
                'icon'     => 'fa-solid fa-person-running',
                'title'    => Str::ucfirst($a->activity_type ?? 'Actividad física'),
                'subtitle' => $detalle,
                'url'      => route('tracking.summary'),
            ];
        }

        // ── Nutrición ───────────────────────────────────────────────────
        $meals = $user->nutritionLogs()
            ->where(function ($w) use ($like) {
                $w->where('meal_type', 'like', $like)
                  ->orWhereRaw('CAST(food_categories AS CHAR) LIKE ?', [$like]);
            })
            ->latest('created_at')
            ->limit(5)
            ->get();

        foreach ($meals as $m) {
            $categorias = collect($m->food_categories ?? [])->implode(', ');
            $detalle = collect([
                    $m->carbs_grams ? $m->carbs_grams . ' g carbs' : null,
                    $categorias ?: null,
                    $m->created_at?->format('d/m/Y'),
                ])->filter()->implode(' · ');

            $results[] = [
                'type'     => 'Comida',
                'icon'     => 'fa-solid fa-utensils',
                'title'    => Str::ucfirst($m->meal_type ?? 'Comida'),
                'subtitle' => $detalle,
                'url'      => route('tracking.summary'),
            ];
        }

        // ── Síntomas ────────────────────────────────────────────────────
        $symptoms = $user->symptoms()
            ->where('name', 'like', $like)
            ->orderByPivot('logged_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($symptoms as $s) {
            $fecha = optional($s->pivot->logged_at ? \Illuminate\Support\Carbon::parse($s->pivot->logged_at) : null)?->format('d/m/Y');

            $results[] = [
                'type'     => 'Síntoma',
                'icon'     => 'fa-solid fa-notes-medical',
                'title'    => $s->name,
                'subtitle' => collect(['Síntoma', $fecha])->filter()->implode(' · '),
                'url'      => route('tracking.summary'),
            ];
        }

        return $results;
    }

    private function stripAccents(string $text): string
    {
        return strtr($text, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n',
        ]);
    }
}
