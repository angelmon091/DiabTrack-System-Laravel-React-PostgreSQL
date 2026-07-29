<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tracking\VitalSignRequest;
use App\Models\VitalSign;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Gestiona la captura de glucosa, presión arterial y otros signos vitales.
 */
class VitalSignController extends Controller
{
    public function create(): InertiaResponse
    {
        return Inertia::render('Tracking/Vitals/Create', [
            'storeUrl' => route('tracking.vital.store', absolute: false),
            'dashboardUrl' => route('dashboard', absolute: false),
            'trackingNavigation' => [
                ['key' => 'vitals', 'label' => 'Signos vitales', 'url' => route('tracking.vital.create', absolute: false)],
                ['key' => 'symptoms', 'label' => 'Síntomas', 'url' => route('tracking.symptom.create', absolute: false)],
                ['key' => 'nutrition', 'label' => 'Nutrición', 'url' => route('tracking.nutrition.create', absolute: false)],
                ['key' => 'activity', 'label' => 'Movimiento', 'url' => route('tracking.activity.create', absolute: false)],
            ],
            'measurementMoments' => [
                ['value' => 'Ayunas', 'label' => 'En ayunas', 'description' => 'Al despertar, sin haber comido durante 8 horas o más.'],
                ['value' => 'Antes de Comer', 'label' => 'Antes de comer', 'description' => 'Justo antes de desayunar, comer o cenar.'],
                ['value' => 'Después de Comer', 'label' => 'Después de comer', 'description' => 'Entre una y dos horas después de la comida.'],
                ['value' => 'Al Dormir', 'label' => 'Al dormir', 'description' => 'Antes de acostarte.'],
            ],
            'stressLevels' => [
                ['value' => 'Bajo', 'label' => 'Bajo', 'description' => 'Relajado, sin tensión.'],
                ['value' => 'Medio', 'label' => 'Medio', 'description' => 'Algo de presión o ansiedad.'],
                ['value' => 'Alto', 'label' => 'Alto', 'description' => 'Muy estresado o tenso.'],
            ],
        ]);
    }

    public function store(VitalSignRequest $request)
    {
        VitalSign::create([
            'user_id' => Auth::id(),
            'glucose_level' => $request->glucose_level,
            'systolic' => $request->systolic,
            'diastolic' => $request->diastolic,
            'heart_rate' => $request->heart_rate,
            'weight' => $request->weight,
            'hba1c' => $request->hba1c,
            'measurement_moment' => $request->measurement_moment,
            'stress_level' => $request->stress_level,
            'notes' => $request->notes,
        ]);

        $response = redirect()->route('dashboard')->with('status', __('Registro de salud guardado con éxito.'));

        return $request->header('X-Inertia')
            ? Inertia::location($response->getTargetUrl())
            : $response;
    }
}
