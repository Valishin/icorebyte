<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Chat API: sin web middleware (sin sesión, sin CSRF, sin cookies).
            // La autenticación usa el header X-Chat-Visitor-Token (token hasheado en BD).
            \Illuminate\Support\Facades\Route::prefix('api/chat')
                ->group(base_path('routes/chat.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // trustProxies se configura en AppServiceProvider::boot() con config(),
        // para que funcione con la config cacheada (php artisan optimize).
        // env() aquí devuelve null con cache activa porque Dotenv no ha corrido aún.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
