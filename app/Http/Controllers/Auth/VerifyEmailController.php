<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Clase VerifyEmailController
 *
 * Gestiona la verificación de la dirección de correo electrónico del usuario.
 * Se activa cuando el usuario hace clic en el enlace de verificación enviado.
 */
class VerifyEmailController extends Controller
{
    /**
     * Marca el correo electrónico del usuario autenticado como verificado.
     *
     * Si el correo ya está verificado, redirige al dashboard. De lo contrario,
     * marca el correo como verificado y dispara el evento Verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
