<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Clase AdminMiddleware
 *
 * Middleware que restringe el acceso a rutas solo para usuarios administradores.
 * Si el usuario no está autenticado o no es administrador, redirige al dashboard.
 */
class AdminMiddleware
{
    /**
     * Maneja una solicitud entrante.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
