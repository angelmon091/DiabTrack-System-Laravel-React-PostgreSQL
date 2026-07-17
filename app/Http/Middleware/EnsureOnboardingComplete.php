<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que verifica si el usuario completó el onboarding.
 * Si no lo ha hecho, lo redirige a la selección de rol.
 */
class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && ! $user->hasCompletedOnboarding() && ! $user->isAdmin()) {
            return redirect()->route('onboarding.index');
        }

        return $next($request);
    }
}
