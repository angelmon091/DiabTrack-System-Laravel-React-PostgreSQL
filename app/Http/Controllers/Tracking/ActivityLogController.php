<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tracking\ActivityLogRequest;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Atiende la consulta y el registro de actividad física del paciente.
 */
class ActivityLogController extends Controller
{
    public function create(): InertiaResponse
    {
        return Inertia::render('Tracking/Activity/Create', [
            'storeUrl' => route('tracking.activity.store', absolute: false),
            'dashboardUrl' => route('dashboard', absolute: false),
            'trackingNavigation' => [
                ['key' => 'vitals', 'label' => 'Signos vitales', 'url' => route('tracking.vital.create', absolute: false)],
                ['key' => 'symptoms', 'label' => 'Síntomas', 'url' => route('tracking.symptom.create', absolute: false)],
                ['key' => 'nutrition', 'label' => 'Nutrición', 'url' => route('tracking.nutrition.create', absolute: false)],
                ['key' => 'activity', 'label' => 'Movimiento', 'url' => route('tracking.activity.create', absolute: false)],
            ],
            'activityTypes' => [
                ['value' => 'caminar', 'label' => 'Caminar'],
                ['value' => 'correr', 'label' => 'Correr'],
                ['value' => 'nadar', 'label' => 'Nadar'],
                ['value' => 'bicicleta', 'label' => 'Bicicleta'],
                ['value' => 'yoga', 'label' => 'Yoga'],
                ['value' => 'gimnasio', 'label' => 'Gimnasio'],
                ['value' => 'baile', 'label' => 'Baile'],
                ['value' => 'estiramiento', 'label' => 'Estiramiento'],
                ['value' => 'otro', 'label' => 'Otro'],
            ],
            'intensities' => [
                ['value' => 'baja', 'label' => 'Baja', 'description' => 'Podías platicar fácilmente.', 'icon' => 'gauge', 'iconClass' => 'text-emerald-600'],
                ['value' => 'media', 'label' => 'Media', 'description' => 'Costaba hablar seguido.', 'icon' => 'gauge', 'iconClass' => 'text-amber-500'],
                ['value' => 'alta', 'label' => 'Alta', 'description' => 'Sin aliento para hablar.', 'icon' => 'gauge', 'iconClass' => 'text-red-500'],
            ],
            'energyLevels' => [
                ['value' => 'muy_baja', 'label' => 'Muy baja', 'description' => 'Sin energía, agotado.', 'icon' => 'batteryWarning'],
                ['value' => 'baja', 'label' => 'Baja', 'description' => 'Algo cansado.', 'icon' => 'batteryLow'],
                ['value' => 'normal', 'label' => 'Normal', 'description' => 'Energía habitual.', 'icon' => 'batteryMedium'],
                ['value' => 'alta', 'label' => 'Alta', 'description' => 'Con más fuerza de lo usual.', 'icon' => 'batteryFull'],
                ['value' => 'muy_alta', 'label' => 'Muy alta', 'description' => 'Lleno de energía.', 'icon' => 'batteryFull', 'iconClass' => 'text-emerald-600'],
            ],
        ]);
    }

    public function store(ActivityLogRequest $request)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => $request->activity_type,
            'duration_minutes' => $request->duration_minutes,
            'intensity' => $request->intensity,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'energy_level' => $request->energy_level,
        ]);

        return redirect()->route('tracking.activity.create')->with('status', __('Registro de actividad guardado con éxito.'));
    }
}
