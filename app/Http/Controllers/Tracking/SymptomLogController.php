<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tracking\SymptomLogRequest;
use App\Models\Symptom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Gestiona la selección y persistencia de síntomas reportados por el paciente.
 */
class SymptomLogController extends Controller
{
    public function create()
    {
        $symptoms = Symptom::all()->groupBy('category');

        return view('tracking.symptom.create', compact('symptoms'));
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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Registro de síntomas guardado con éxito.'),
            ]);
        }

        return redirect()->route('dashboard')->with('status', __('Registro de síntomas guardado con éxito.'));
    }
}
