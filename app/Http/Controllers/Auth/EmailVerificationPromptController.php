<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

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
    public function __invoke(Request $request): InertiaResponse|Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            $response = redirect()->intended(route('dashboard', absolute: false));

            return $request->header('X-Inertia')
                ? Inertia::location($response->getTargetUrl())
                : $response;
        }

        return Inertia::render('Auth/VerifyEmail', [
            'email' => $request->user()->email,
            'verificationCodeUrl' => route('verification.code', absolute: false),
            'resendUrl' => route('verification.send', absolute: false),
            'logoutUrl' => route('logout', absolute: false),
        ]);
    }
}
