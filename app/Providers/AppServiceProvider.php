<?php

namespace App\Providers;

use App\Services\DashboardMetricsService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Clase AppServiceProvider
 *
 * Proveedor de servicios de la aplicación.
 * Se ejecuta durante el registro y arranque de la aplicación.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra cualquier servicio de la aplicación.
     */
    public function register(): void
    {
        //
    }

    /**
     * Arranque de cualquier servicio de la aplicación.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer(['dashboard', 'tracking.nutrition.index'], function ($view) {
            if (! auth()->check()) {
                return;
            }

            $view->with(app(DashboardMetricsService::class)->getDailyTipForUser(auth()->id()));
        });
    }
}
