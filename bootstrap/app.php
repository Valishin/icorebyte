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

        // Proxies de confianza: configurar en .env (TRUSTED_PROXIES).
        // Dejar vacío por defecto — '*' permite falsear X-Forwarded-For y saltarse el rate limit.
        // Formato: IP única, lista separada por comas, o '*' solo si hay un proxy de confianza delante.
        $proxiesEnv = env('TRUSTED_PROXIES', '');
        $proxies = match (true) {
            $proxiesEnv === '*' => '*',
            $proxiesEnv !== ''  => array_map('trim', explode(',', $proxiesEnv)),
            default             => null,
        };
        $middleware->trustProxies(at: $proxies);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
