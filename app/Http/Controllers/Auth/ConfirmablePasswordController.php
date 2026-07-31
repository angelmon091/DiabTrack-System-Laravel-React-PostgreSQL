<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Clase ConfirmablePasswordController
 *
 * Gestiona la vista y validación de confirmación de contraseña, un paso
 * de seguridad requerido antes de acceder a rutas sensibles como el dashboard.
 */
class ConfirmablePasswordController extends Controller
{
    /**
     * Muestra la vista de confirmación de contraseña.
     *
     * Esta vista se presenta al usuario cuando su sesión ha expirado o
     * cuando intenta acceder a rutas protegidas que requieren reautenticación.
     */
    public function show(): InertiaResponse
    {
        return Inertia::render('Auth/ConfirmPassword', [
            'confirmPasswordUrl' => route('password.confirm', absolute: false),
        ]);
    }

    /**
     * Confirma la contraseña actual del usuario antes de una operación sensible.
     */
    public function store(Request $request): Response
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        $response = redirect()->intended(route('dashboard', absolute: false));

        return $request->header('X-Inertia')
            ? Inertia::location($response->getTargetUrl())
            : $response;
    }
}
