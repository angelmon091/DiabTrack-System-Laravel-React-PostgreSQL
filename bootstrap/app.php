<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureDoctorApproved;
use App\Http\Middleware\EnsureOnboardingComplete;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\TimezoneMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            TimezoneMiddleware::class,
        ]);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'role' => RoleMiddleware::class,
            'onboarding' => EnsureOnboardingComplete::class,
            'doctor.approved' => EnsureDoctorApproved::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
