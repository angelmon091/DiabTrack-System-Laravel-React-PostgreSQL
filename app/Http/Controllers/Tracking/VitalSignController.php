<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tracking\VitalSignRequest;
use App\Models\VitalSign;
use Illuminate\Support\Facades\Auth;

/**
 * Gestiona la captura de glucosa, presión arterial y otros signos vitales.
 */
class VitalSignController extends Controller
{
    public function create()
    {
        return view('tracking.vital.create');
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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Registro de salud guardado con éxito.'),
            ]);
        }

        return redirect()->route('dashboard')->with('status', __('Registro de salud guardado con éxito.'));
    }
}
