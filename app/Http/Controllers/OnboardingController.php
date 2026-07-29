<?php

namespace App\Http\Controllers;

use App\Http\Requests\Onboarding\PersonalDataRequest;
use App\Models\CaregiverProfile;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Clase OnboardingController
 *
 * Gestiona el proceso de onboarding para nuevos usuarios.
 * Adapta el formulario según el rol seleccionado (paciente, cuidador o médico).
 */
class OnboardingController extends Controller implements HasMiddleware
{
    /**
     * Define los middlewares que se aplican a este controlador.
     */
    public static function middleware(): array
    {
        return [
            function ($request, $next) {
                if (Auth::check() && Auth::user()->isAdmin()) {
                    $response = redirect()->route('admin.dashboard');

                    return $request->header('X-Inertia')
                        ? Inertia::location($response->getTargetUrl())
                        : $response;
                }

                return $next($request);
            },
        ];
    }

    /**
     * Muestra la pantalla de selección de rol.
     */
    public function index(Request $request): InertiaResponse|Response
    {
        if (Auth::user()->hasCompletedOnboarding()) {
            $response = redirect()->route('dashboard');

            return $request->header('X-Inertia')
                ? Inertia::location($response->getTargetUrl())
                : $response;
        }

        return Inertia::render('Onboarding/RoleSelection', [
            'patientUrl' => route('onboarding.patient', absolute: false),
            'caregiverUrl' => route('onboarding.caregiver', absolute: false),
            'doctorUrl' => route('onboarding.doctor', absolute: false),
        ]);
    }

    /**
     * Muestra el formulario de datos de paciente.
     */
    public function showPatientForm()
    {
        return view('onboarding.personal-data');
    }

    /**
     * Almacena los datos del paciente.
     */
    public function storePatient(PersonalDataRequest $request)
    {
        $validated = $request->validated();

        PatientProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'birth_date' => $validated['birth_date'],
                'diabetes_type' => $validated['diabetes_type'],
                'weight' => $validated['weight'],
                'height' => $validated['height'],
                'gender' => $validated['gender'],
            ]
        );

        // Asignar rol de paciente
        $role = Role::firstOrCreate(['name' => 'paciente']);
        Auth::user()->roles()->syncWithoutDetaching([$role->id]);

        return redirect()->route('dashboard')->with('status', __('¡Bienvenido! Tu perfil de paciente ha sido configurado.'));
    }

    /**
     * Muestra el formulario de datos de cuidador.
     */
    public function showCaregiverForm()
    {
        return view('onboarding.caregiver-data');
    }

    /**
     * Almacena los datos del cuidador.
     */
    public function storeCaregiver(Request $request)
    {
        $validated = $request->validate([
            'gender' => 'required|string|in:Masculino,Femenino',
            'relationship' => 'required|string',
        ]);

        CaregiverProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'gender' => $validated['gender'],
                'relationship' => $validated['relationship'],
            ]
        );

        // Asignar rol de cuidador
        $role = Role::firstOrCreate(['name' => 'cuidador']);
        Auth::user()->roles()->syncWithoutDetaching([$role->id]);

        return redirect()->route('caregiver.dashboard')->with('status', __('¡Bienvenido! Tu perfil de cuidador ha sido configurado.'));
    }

    /**
     * Muestra el formulario de datos del médico.
     */
    public function showDoctorForm()
    {
        return view('onboarding.doctor-data');
    }

    /**
     * Almacena los datos del médico.
     */
    public function storeDoctor(Request $request)
    {
        $validated = $request->validate([
            'gender' => 'required|string|in:Masculino,Femenino',
            'license_number' => 'required|string|max:50',
            'specialty' => 'required|string',
        ]);

        DoctorProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'gender' => $validated['gender'],
                'license_number' => $validated['license_number'],
                'specialty' => $validated['specialty'],
                'approval_status' => DoctorProfile::STATUS_PENDING,
                'review_notes' => null,
                'approved_by' => null,
                'approved_at' => null,
            ]
        );

        // Asignar rol de médico
        $role = Role::firstOrCreate(['name' => 'médico']);
        Auth::user()->roles()->syncWithoutDetaching([$role->id]);

        return redirect()->route('doctor.dashboard')->with('status', __('Tu perfil profesional fue registrado y será revisado por un administrador.'));
    }
}
