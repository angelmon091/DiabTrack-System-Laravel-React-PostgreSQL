<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tracking\SymptomLogRequest;
use App\Http\Resources\SymptomResource;
use App\Models\Symptom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Gestiona la selección y persistencia de síntomas reportados por el paciente.
 */
class SymptomLogController extends Controller
{
    public function create(): InertiaResponse
    {
        $categoryLabels = [
            'physical' => 'Síntomas físicos',
            'nocturnal' => 'Síntomas nocturnos',
            'neurological' => 'Síntomas neurológicos',
            'atypical' => 'Síntomas atípicos',
        ];

        $symptoms = Symptom::query()->orderBy('id')->get()->groupBy('category')->map(
            fn ($items, $category) => [
                'key' => $category,
                'label' => $categoryLabels[$category] ?? ucfirst($category),
                'symptoms' => SymptomResource::collection($items)->resolve(),
            ]
        )->values();

        return Inertia::render('Tracking/Symptoms/Create', [
            'storeUrl' => route('tracking.symptom.store', absolute: false),
            'dashboardUrl' => route('dashboard', absolute: false),
            'trackingNavigation' => [
                ['key' => 'vitals', 'label' => 'Signos vitales', 'url' => route('tracking.vital.create', absolute: false)],
                ['key' => 'symptoms', 'label' => 'Síntomas', 'url' => route('tracking.symptom.create', absolute: false)],
                ['key' => 'nutrition', 'label' => 'Nutrición', 'url' => route('tracking.nutrition.create', absolute: false)],
                ['key' => 'activity', 'label' => 'Movimiento', 'url' => route('tracking.activity.create', absolute: false)],
            ],
            'symptomGroups' => $symptoms,
        ]);
    }

    public function store(SymptomLogRequest $request)
    {
        $user = Auth::user();
        $now = now();

        $pivotData = [];
        foreach ($request->symptoms as $symptomId) {
            $pivotData[$symptomId] = ['logged_at' => $now];
        }

        $user->symptoms()->attach($pivotData);

        // Invalidar el caché de métricas del dashboard del usuario
        Cache::forget("dashboard_metrics_{$user->id}_v2");

        return redirect()->route('tracking.symptom.create')->with('status', __('Registro de síntomas guardado con éxito.'));
    }
}
