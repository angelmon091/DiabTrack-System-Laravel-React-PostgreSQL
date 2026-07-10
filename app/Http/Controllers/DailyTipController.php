<?php

namespace App\Http\Controllers;

use App\Models\DailyTip;
use App\Models\PatientLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DailyTipController extends Controller
{
    /**
     * Rechaza un consejo que pertenece a un paciente vinculado al revisor.
     */
    public function reject(Request $request, DailyTip $dailyTip): RedirectResponse
    {
        $reviewer = $request->user();

        $isLinked = PatientLink::query()
            ->where('patient_id', $dailyTip->user_id)
            ->where('linked_user_id', $reviewer->id)
            ->where('status', 'active')
            ->exists();

        abort_unless($isLinked, 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $dailyTip->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $validated['reason'],
        ]);

        return back()->with('status', 'Consejo rechazado correctamente.');
    }
}
