<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TimezoneMiddleware
{
    /**
     * Ajusta la zona horaria de la aplicación antes de continuar con la solicitud.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = 'America/Monterrey'; // Zona de respaldo cuando no existe una preferencia válida.

        // Detectar zona horaria desde la cookie
        $cookieTimezone = $request->cookie('user_timezone');

        // Decodificar en caso de que esté URL-encoded
        if ($cookieTimezone) {
            $cookieTimezone = urldecode($cookieTimezone);
        }

        // Validar que sea una zona horaria soportada por PHP
        $validTimezones = timezone_identifiers_list();
        $isCookieValid = $cookieTimezone && in_array($cookieTimezone, $validTimezones);

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->timezone && in_array($user->timezone, $validTimezones)) {
                // Si el usuario tiene una zona horaria preferida en base de datos, tiene prioridad
                $timezone = $user->timezone;
            } elseif ($isCookieValid) {
                // Guardar la zona horaria de la cookie en la base de datos si aún no tiene una configurada
                $user->update(['timezone' => $cookieTimezone]);
                $timezone = $cookieTimezone;
            }
        } elseif ($isCookieValid) {
            $timezone = $cookieTimezone;
        }

        // Aplicar la zona horaria configurada de forma dinámica en Laravel y PHP
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);

        return $next($request);
    }
}
