<?php

namespace App\Http\Middleware;

use App\Models\ChatConversation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateChatToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $uuid  = $request->route('uuid');
        $token = $request->header('X-Chat-Visitor-Token');

        if (! $uuid || ! $token || strlen($token) < 16) {
            return response()->json(['error' => 'No autorizado.'], 401);
        }

        $conversation = ChatConversation::where('uuid', $uuid)->first();

        if (! $conversation) {
            return response()->json(['error' => 'No autorizado.'], 401);
        }

        // hash_equals evita timing attacks. Acepta el token original (device A)
        // o el token generado al usar el enlace de reanudación (device B).
        $provided = hash('sha256', $token);
        $valid = hash_equals((string) $conversation->visitor_token, $provided)
              || (
                  $conversation->resume_token_hash !== null
                  && hash_equals((string) $conversation->resume_token_hash, $provided)
              );
        if (! $valid) {
            return response()->json(['error' => 'No autorizado.'], 401);
        }

        $request->attributes->set('chat_conversation', $conversation);

        return $next($request);
    }
}
