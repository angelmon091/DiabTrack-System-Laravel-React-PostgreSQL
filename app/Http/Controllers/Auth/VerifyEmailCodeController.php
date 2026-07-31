<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Valida el código temporal utilizado para confirmar la dirección de correo del usuario.
 */
class VerifyEmailCodeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            $response = redirect()->route('onboarding.index');

            return $request->header('X-Inertia')
                ? Inertia::location($response->getTargetUrl())
                : $response;
        }

        $verification = EmailVerificationCode::whereBelongsTo($user)->first();

        if (! $verification || $verification->expires_at->isPast()) {
            $verification?->delete();

            return back()->withErrors([
                'code' => 'El código venció. Solicita uno nuevo para continuar.',
            ]);
        }

        if ($verification->attempts >= 5) {
            $verification->delete();

            return back()->withErrors([
                'code' => 'Se alcanzó el límite de intentos. Solicita un código nuevo.',
            ]);
        }

        if (! Hash::check((string) $request->input('code'), $verification->code_hash)) {
            $verification->increment('attempts');

            return back()->withErrors([
                'code' => 'El código ingresado no es válido.',
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $verification->delete();

        $response = redirect()->route('onboarding.index')->with('status', 'email-verified');

        return $request->header('X-Inertia')
            ? Inertia::location($response->getTargetUrl())
            : $response;
    }
}
