<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

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
    public function create(): InertiaResponse
    {
        return Inertia::render('Auth/Login', [
            'loginUrl' => route('login', absolute: false),
            'forgotPasswordUrl' => route('password.request', absolute: false),
            'registerUrl' => route('register', absolute: false),
            'googleLoginUrl' => route('socialite.redirect', 'google', absolute: false),
        ]);
    }

    /**
     * Procesa una solicitud de autenticación entrante.
     *
     * Autentica las credenciales, regenera la sesión y verifica si el usuario
     * necesita completar su perfil de paciente (onboarding).
     */
    public function store(LoginRequest $request): Response
    {
        // Ejecuta la validación y el intento de autenticación definido en el Request
        $request->authenticate();

        // Regenera el ID de sesión para prevenir ataques de fijación de sesión
        $request->session()->regenerate();

        if (! Auth::user()->hasVerifiedEmail()) {
            return $this->inertiaAwareRedirect(
                $request,
                redirect()->route('verification.notice'),
            );
        }

        // Si el usuario es administrador, redirigir directamente al panel administrativo
        if (Auth::user()->isAdmin()) {
            return $this->inertiaAwareRedirect(
                $request,
                redirect()->route('admin.dashboard'),
            );
        }

        // Si el usuario autenticado no tiene perfil de paciente, cuidador o médico ni ha completado el onboarding, lo envía al onboarding
        if (! Auth::user()->patientProfile && ! Auth::user()->caregiverProfile && ! Auth::user()->doctorProfile && ! Auth::user()->hasCompletedOnboarding()) {
            return $this->inertiaAwareRedirect(
                $request,
                redirect()->route('onboarding.index'),
            );
        }

        // Redirige al destino deseado o al panel de control (Dashboard)
        return $this->inertiaAwareRedirect(
            $request,
            redirect()->intended(route('dashboard', absolute: false)),
        );
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

    private function inertiaAwareRedirect(Request $request, RedirectResponse $response): Response
    {
        if ($request->header('X-Inertia')) {
            return Inertia::location($response->getTargetUrl());
        }

        return $response;
    }
}
