<?php

use App\Http\Controllers\Chat\AttachmentController;
use App\Http\Controllers\Chat\ConversationController;
use App\Http\Controllers\Chat\MessageController;
use App\Http\Controllers\Chat\TelegramController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Chat API Routes  —  prefijo: /api/chat
|--------------------------------------------------------------------------
| Registradas en bootstrap/app.php sin web middleware: sin sesión, sin cookies,
| sin CSRF. La autenticación usa el header X-Chat-Visitor-Token (hash en BD).
*/

// ── Interruptor global ────────────────────────────────────────────────────────
// ChatEnabled middleware: si CHAT_ENABLED=false devuelve 503 en todas las rutas
// excepto el webhook de Telegram (que siempre recibe 200 para evitar reintentos).
Route::middleware(\App\Http\Middleware\ChatEnabled::class)->group(function () {

// ── Webhook de Telegram ───────────────────────────────────────────────────────
// La verificación de identidad se hace dentro del controlador (hash_equals del secret).
// Siempre devuelve 200 para evitar reintento de Telegram.
Route::post('/telegram/webhook', [TelegramController::class, 'webhook'])
    ->name('chat.telegram.webhook');

// ── Diagnóstico de proxy ──────────────────────────────────────────────────────
// GET /api/chat/proxy-info   Header: X-Diagnose-Token: <CHAT_DIAGNOSE_TOKEN en .env>
//
// Pasos de despliegue:
//   1. En .env de producción: CHAT_DIAGNOSE_TOKEN=<secreto largo aleatorio>
//   2. Llama a la ruta con el token correcto y pasa el resultado.
//   3. Una vez configurado TRUSTED_PROXIES, vacía la variable: CHAT_DIAGNOSE_TOKEN=
//      → la ruta devuelve 404 automáticamente (como si no existiera).
//
// Devuelve 404 si CHAT_DIAGNOSE_TOKEN no está configurado (ruta invisible).
// Devuelve 404 si el token no coincide (no revela que la ruta existe).
Route::get('/proxy-info', function (\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse {
    $secret = env('CHAT_DIAGNOSE_TOKEN', '');

    // Sin token configurado → la ruta no existe
    if ($secret === '') {
        abort(404);
    }

    // Token incorrecto → misma respuesta (no revela que la ruta existe)
    if (! hash_equals($secret, (string) $request->header('X-Diagnose-Token', ''))) {
        abort(404);
    }

    return response()->json([
        'remote_addr'      => $request->server('REMOTE_ADDR'),
        'x_forwarded_for'  => $request->header('X-Forwarded-For'),
        'cf_connecting_ip' => $request->header('CF-Connecting-IP'),
        'x_real_ip'        => $request->header('X-Real-IP'),
        'laravel_ip'        => $request->ip(),
        'trusted_proxies'  => env('TRUSTED_PROXIES', '(ninguno)'),
    ]);
})->name('chat.proxy-info');
// ─────────────────────────────────────────────────────────────────────────────

// Estado de disponibilidad (sin auth; el widget lo consulta antes de mostrar el botón)
Route::get('/availability', [ConversationController::class, 'availability'])
    ->middleware('throttle:chat.availability')
    ->name('chat.availability');

// Iniciar conversación (sin token; el token en claro se devuelve solo aquí)
Route::post('/conversations', [ConversationController::class, 'store'])
    ->middleware('throttle:chat.create')
    ->name('chat.conversations.store');

// Adjuntos con URL firmada temporal — sin chat.token (la firma ES la autorización)
Route::get('/attachments/{filename}', [AttachmentController::class, 'show'])
    ->name('chat.attachment')
    ->where('filename', '[A-Za-z0-9\-]+\.jpg'); // solo UUIDs .jpg, nada más

// Rutas que requieren token de visitante
Route::middleware('chat.token')->group(function () {

    Route::post('/conversations/{uuid}/presence', [ConversationController::class, 'updatePresence'])
        ->name('chat.conversations.presence');

    Route::get('/conversations/{uuid}/messages', [MessageController::class, 'index'])
        ->name('chat.messages.index');

    Route::post('/conversations/{uuid}/messages', [MessageController::class, 'store'])
        ->middleware('throttle:chat.message')
        ->name('chat.messages.store');

    Route::post('/conversations/{uuid}/read', [MessageController::class, 'markRead'])
        ->name('chat.messages.read');

    Route::post('/conversations/{uuid}/typing', [MessageController::class, 'typing'])
        ->name('chat.messages.typing');

    Route::patch('/conversations/{uuid}/contact', [ConversationController::class, 'updateContact'])
        ->name('chat.conversations.contact');
});

}); // end ChatEnabled group
