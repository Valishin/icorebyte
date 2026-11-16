<?php

namespace App\Providers;

use App\Http\Middleware\ValidateChatToken;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Proxies de confianza — leído con config() para que funcione con la config cacheada.
        // env() devuelve null con `php artisan optimize`; config() lee del cache compilado.
        $proxiesConfig = (string) config('chat.trusted_proxies', '');
        $proxies = match (true) {
            $proxiesConfig === '*' => ['REMOTE_ADDR'],
            $proxiesConfig !== ''  => array_map('trim', explode(',', $proxiesConfig)),
            default                => [],
        };
        if (! empty($proxies)) {
            Request::setTrustedProxies($proxies, Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PREFIX);
        }

        // Alias de middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('chat.token', ValidateChatToken::class);

        // Rate limiters
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('chat.create', function (Request $request) {
            return [
                Limit::perMinute(3)->by($request->ip()),
                Limit::perHour(10)->by($request->ip()),
                Limit::perDay(20)->by($request->ip()),
            ];
        });

        RateLimiter::for('chat.message', function (Request $request) {
            $uuid = $request->route('uuid') ?? 'unknown';
            return Limit::perMinute(10)->by($request->ip() . '|' . $uuid);
        });

        RateLimiter::for('chat.availability', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}