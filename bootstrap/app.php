<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Routes web (sesión, vistas Blade).
        web: __DIR__.'/../routes/web.php',
        // Routes API (token Sanctum, JSON) — agregadas por php artisan install:api.
        api: __DIR__.'/../routes/api.php',
        // Comandos artisan custom.
        commands: __DIR__.'/../routes/console.php',
        // Endpoint de health-check (sirve para el pipeline CI/CD).
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias 'rol' para usar en rutas:
        //   ->middleware('rol:admin')
        //   ->middleware('rol:ganadero,veterinario')
        $middleware->alias([
            'rol' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
