<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tracking\ActivityLogRequest;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

/**
 * Atiende la consulta y el registro de actividad física del paciente.
 */
class ActivityLogController extends Controller
{
    public function create()
    {
        return view('tracking.activity.create');
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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Registro de actividad guardado con éxito.'),
            ]);
        }

        return redirect()->route('dashboard')->with('status', __('Registro de actividad guardado con éxito.'));
    }
}
