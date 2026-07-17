<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Clase EmailVerificationPromptController
 *
 * Gestiona la vista de solicitud de verificación de correo electrónico.
 * Se muestra a los usuarios que no han verificado su dirección de correo.
 */
class EmailVerificationPromptController extends Controller
{
    /**
     * Muestra la vista de solicitud de verificación de correo electrónico.
     *
     * Si el usuario ya tiene su correo verificado, redirige al dashboard.
     * De lo contrario, muestra la vista 'auth.verify-email'.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : view('auth.verify-email');
    }
}
