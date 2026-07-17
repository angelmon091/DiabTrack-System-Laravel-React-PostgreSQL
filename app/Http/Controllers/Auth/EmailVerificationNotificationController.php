<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Clase EmailVerificationNotificationController
 *
 * Gestiona el reenvío de notificaciones de verificación de correo electrónico.
 * Permite a los usuarios solicitar un nuevo enlace de verificación si no lo han recibido.
 */
class EmailVerificationNotificationController extends Controller
{
    /**
     * Envía una nueva notificación de verificación de correo electrónico.
     *
     * Si el usuario ya tiene su correo verificado, redirige al dashboard.
     * De lo contrario, genera y envía un nuevo enlace de verificación.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-code-sent');
    }
}
