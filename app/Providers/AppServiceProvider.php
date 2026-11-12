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