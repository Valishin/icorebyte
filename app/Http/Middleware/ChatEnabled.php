<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards all /api/chat/* routes.
 *
 * When CHAT_ENABLED=false:
 *   - Telegram webhook → 200 OK (Telegram must always get 200)
 *   - Everything else  → 503 Service Unavailable
 */
class ChatEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('chat.enabled')) {
            return $next($request);
        }

        // The Telegram webhook must always return 200 to avoid Telegram retries
        if ($request->routeIs('chat.telegram.webhook')) {
            return response()->json(['ok' => true]);
        }

        return response()->json(
            ['error' => 'El chat no está disponible en este momento.'],
            Response::HTTP_SERVICE_UNAVAILABLE,
        );
    }
}
