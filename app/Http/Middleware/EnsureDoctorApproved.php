<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Impide que un médico vincule pacientes mientras su perfil profesional no esté aprobado.
 */
class EnsureDoctorApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->doctorProfile?->isApproved()) {
            return redirect()->route('doctor.dashboard')->with(
                'warning',
                'Tu perfil profesional debe ser aprobado antes de vincular pacientes.'
            );
        }

        return $next($request);
    }
}
