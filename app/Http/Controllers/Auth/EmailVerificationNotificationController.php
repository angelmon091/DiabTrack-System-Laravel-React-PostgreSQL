<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

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
    public function store(Request $request): Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            $response = redirect()->intended(route('dashboard', absolute: false));

            return $request->header('X-Inertia')
                ? Inertia::location($response->getTargetUrl())
                : $response;
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-code-sent');
    }
}
