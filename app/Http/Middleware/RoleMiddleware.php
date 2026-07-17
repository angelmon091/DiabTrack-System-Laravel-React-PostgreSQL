<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de verificación de rol.
 *
 * Restringe el acceso a rutas según el rol del usuario.
 * Uso: Route::middleware('role:paciente') o Route::middleware('role:médico,cuidador')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Si es administrador, redirigir directamente al panel de administración
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // Si es paciente intentando acceder a rutas de cuidador/médico o viceversa
        if ($user->isPatient()) {
            return redirect()->route('dashboard');
        } elseif ($user->isCaregiver()) {
            return redirect()->route('caregiver.dashboard');
        } elseif ($user->isDoctor()) {
            return redirect()->route('doctor.dashboard');
        }

        return redirect()->route('dashboard');
    }
}
