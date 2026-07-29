<?php

namespace App\Http\Controllers;

use App\Models\PatientLink;
use App\Models\PatientNotification;
use App\Models\User;
use App\Models\VitalSign;
use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Dashboard y gestión de pacientes para médicos.
 */
class DoctorController extends Controller
{
    public function dashboard(Request $request, DashboardMetricsService $metricsService)
    {
        $user = Auth::user();
        $patients = $user->linkedPatients()->with('patientProfile', 'vitalSigns')->get();

        $selectedPatient = null;
        $metrics = [];
        $recentLogs = collect();

        if ($patients->isNotEmpty()) {
            $selectedPatientId = $request->query('patient_id');
            $selectedPatient = $selectedPatientId
                ? $patients->firstWhere('id', $selectedPatientId)
                : $patients->first();

            if (! $selectedPatient) {
                $selectedPatient = $patients->first();
            }

            $metrics = $metricsService->getDashboardMetrics($selectedPatient->id);

            $recentLogs = VitalSign::where('user_id', $selectedPatient->id)
                ->whereNotNull('glucose_level')
                ->latest()
                ->take(5)
                ->get();
        }

        return view('doctor.dashboard', array_merge($metrics, compact('user', 'patients', 'selectedPatient', 'recentLogs')));
    }

    /**
     * Muestra el formulario para vincular un paciente con código.
     */
    public function showLinkForm(): InertiaResponse
    {
        return Inertia::render('Doctor/LinkPatient', [
            'storeUrl' => route('doctor.link.store', absolute: false),
            'dashboardUrl' => route('doctor.dashboard', absolute: false),
        ]);
    }

    /**
     * Vincula un paciente usando su código de invitación.
     */
    public function linkPatient(Request $request)
    {
        $request->validate([
            'invite_code' => 'required|string|size:6',
        ]);

        $link = PatientLink::where('invite_code', strtoupper($request->invite_code))
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (! $link) {
            return back()->withErrors(['invite_code' => 'El código es inválido o ha expirado.']);
        }

        $link->update([
            'linked_user_id' => Auth::id(),
            'status' => 'active',
        ]);

        PatientNotification::create([
            'user_id' => $link->patient_id,
            'type' => 'system',
            'title' => 'Nuevo médico vinculado',
            'body' => 'El Dr./Dra. '.Auth::user()->name.' se ha vinculado a tu cuenta.',
            'icon' => 'fa-solid fa-user-doctor',
        ]);

        $response = redirect()->route('doctor.dashboard')
            ->with('status', '¡Paciente vinculado exitosamente!');

        return $request->header('X-Inertia')
            ? Inertia::location($response->getTargetUrl())
            : $response;
    }

    /**
     * Muestra el detalle clínico de un paciente vinculado (Redirige al dashboard unificado).
     */
    public function showPatient(User $patient)
    {
        $this->checkLink($patient->id);

        return redirect()->route('doctor.dashboard', ['patient_id' => $patient->id]);
    }

    /**
     * Actualiza las metas glucémicas de un paciente.
     */
    public function updateTargets(Request $request, User $patient)
    {
        $this->checkLink($patient->id);

        $validated = $request->validate([
            'target_glucose_min' => 'required|integer|min:40|max:150',
            'target_glucose_max' => 'required|integer|min:100|max:300',
        ]);

        if ($patient->patientProfile) {
            $patient->patientProfile->update([
                'target_glucose_min' => $validated['target_glucose_min'],
                'target_glucose_max' => $validated['target_glucose_max'],
            ]);
        } else {
            return back()->withErrors(['general' => 'El perfil del paciente no existe.']);
        }

        return redirect()->route('doctor.patient.show', $patient)
            ->with('status', 'Metas terapéuticas actualizadas correctamente.');
    }

    /**
     * Desvincula un paciente.
     */
    public function unlinkPatient(User $patient)
    {
        $this->checkLink($patient->id);

        PatientLink::where('patient_id', $patient->id)
            ->where('linked_user_id', Auth::id())
            ->where('status', 'active')
            ->delete();

        return redirect()->route('doctor.dashboard')
            ->with('status', 'Paciente desvinculado exitosamente.');
    }

    private function checkLink($patientId)
    {
        $isLinked = PatientLink::where('patient_id', $patientId)
            ->where('linked_user_id', Auth::id())
            ->where('status', 'active')
            ->exists();

        if (! $isLinked) {
            abort(403, 'No tienes permiso para ver los datos de este paciente.');
        }
    }
}
