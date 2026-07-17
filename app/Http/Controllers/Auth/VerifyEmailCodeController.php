<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Valida el código temporal utilizado para confirmar la dirección de correo del usuario.
 */
class VerifyEmailCodeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('onboarding.index');
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

        return redirect()->route('onboarding.index')->with('status', 'email-verified');
    }
}
