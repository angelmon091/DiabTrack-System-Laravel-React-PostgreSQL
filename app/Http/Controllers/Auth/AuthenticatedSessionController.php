<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Clase AuthenticatedSessionController
 *
 * Se encarga de gestionar el inicio de sesión y cierre de sesión de los usuarios.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Muestra la vista de inicio de sesión.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Procesa una solicitud de autenticación entrante.
     *
     * Autentica las credenciales, regenera la sesión y verifica si el usuario
     * necesita completar su perfil de paciente (onboarding).
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Ejecuta la validación y el intento de autenticación definido en el Request
        $request->authenticate();

        // Regenera el ID de sesión para prevenir ataques de fijación de sesión
        $request->session()->regenerate();

        if (! Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // Si el usuario es administrador, redirigir directamente al panel administrativo
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Si el usuario autenticado no tiene perfil de paciente, cuidador o médico ni ha completado el onboarding, lo envía al onboarding
        if (! Auth::user()->patientProfile && ! Auth::user()->caregiverProfile && ! Auth::user()->doctorProfile && ! Auth::user()->hasCompletedOnboarding()) {
            return redirect()->route('onboarding.index');
        }

        // Redirige al destino deseado o al panel de control (Dashboard)
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destruye una sesión autenticada (Cierre de sesión).
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Cierra la sesión en el guard web
        Auth::guard('web')->logout();

        // Invalida la sesión actual del servidor
        $request->session()->invalidate();

        // Regenera el token CSRF para mayor seguridad
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
