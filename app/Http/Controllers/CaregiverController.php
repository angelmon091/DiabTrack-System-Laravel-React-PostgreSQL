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
 * Dashboard y gestión de pacientes para cuidadores.
 */
class CaregiverController extends Controller
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

        return view('caregiver.dashboard', array_merge($metrics, compact('user', 'patients', 'selectedPatient', 'recentLogs')));
    }

    /**
     * Muestra el formulario para vincular un paciente con código.
     */
    public function showLinkForm(): InertiaResponse
    {
        return Inertia::render('Caregiver/LinkPatient', [
            'storeUrl' => route('caregiver.link.store', absolute: false),
            'dashboardUrl' => route('caregiver.dashboard', absolute: false),
            'relationships' => [
                ['value' => 'Padre/Madre', 'label' => __('Padre / Madre')],
                ['value' => 'Hijo/a', 'label' => __('Hijo / a')],
                ['value' => 'Hermano/a', 'label' => __('Hermano / a')],
                ['value' => 'Pareja', 'label' => __('Pareja')],
                ['value' => 'Médico Particular', 'label' => __('Médico Particular')],
                ['value' => 'Otro', 'label' => __('Otro')],
            ],
        ]);
    }

    /**
     * Vincula un paciente usando su código de invitación.
     */
    public function linkPatient(Request $request)
    {
        $request->validate([
            'invite_code' => 'required|string|size:6',
            'relationship' => 'required|string|max:255',
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
            'relationship' => $request->relationship,
        ]);

        PatientNotification::create([
            'user_id' => $link->patient_id,
            'type' => 'system',
            'title' => 'Nuevo cuidador vinculado',
            'body' => Auth::user()->name.' se ha vinculado a tu cuenta como cuidador.',
            'icon' => 'fa-solid fa-user-nurse',
        ]);

        $response = redirect()->route('caregiver.dashboard')
            ->with('status', '¡Paciente vinculado exitosamente!');

        return $request->header('X-Inertia')
            ? Inertia::location($response->getTargetUrl())
            : $response;
    }

    /**
     * Muestra el detalle de un paciente vinculado (Redirige al dashboard unificado).
     */
    public function showPatient(User $patient)
    {
        $this->checkLink($patient->id);

        return redirect()->route('caregiver.dashboard', ['patient_id' => $patient->id]);
    }

    /**
     * Registra signos vitales para un paciente vinculado.
     */
    public function storeVital(Request $request, User $patient)
    {
        $this->checkLink($patient->id);

        $validated = $request->validate([
            'glucose_level' => 'required|numeric|min:20|max:600',
            'measurement_moment' => 'required|string',
            'stress_level' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'systolic' => 'nullable|integer|min:40|max:250',
            'diastolic' => 'nullable|integer|min:30|max:180',
            'heart_rate' => 'nullable|integer|min:30|max:220',
            'hba1c' => 'nullable|numeric|min:3|max:20',
        ]);

        VitalSign::create([
            'user_id' => $patient->id,
            'glucose_level' => $validated['glucose_level'],
            'measurement_moment' => $validated['measurement_moment'],
            'stress_level' => $validated['stress_level'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'systolic' => $validated['systolic'] ?? null,
            'diastolic' => $validated['diastolic'] ?? null,
            'heart_rate' => $validated['heart_rate'] ?? null,
            'hba1c' => $validated['hba1c'] ?? null,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Registro de salud guardado con éxito.'),
                'redirect_url' => route('caregiver.dashboard', ['patient_id' => $patient->id]),
            ]);
        }

        return redirect()->route('caregiver.dashboard', ['patient_id' => $patient->id])
            ->with('status', 'Registro de salud añadido correctamente.');
    }

    /**
     * Muestra el formulario premium para registrar signos vitales.
     */
    public function createVital(User $patient)
    {
        $this->checkLink($patient->id);

        return view('caregiver.tracking.vital-create', compact('patient'));
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

        return redirect()->route('caregiver.dashboard')
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
