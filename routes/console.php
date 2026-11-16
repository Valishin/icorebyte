<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Chat: aviso de no-respuesta — cada minuto
Schedule::command('chat:no-reply-warning')->everyMinute();

// Cola de trabajos: procesa jobs pendientes después de cada tick del scheduler.
// En hosting compartido sin queue:work en background, este comando vacía la
// cola (Telegram, emails de notificación) una vez por minuto y termina.
Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=55 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping()
    ->name('chat:process-queue');

// Chat: limpieza de registros de idempotencia Telegram (> 7 días)
Schedule::call(function () {
    \App\Models\TelegramUpdate::where('created_at', '<', now()->subDays(7))->delete();
})->daily()->name('chat:cleanup-telegram-updates');

// Chat: RGPD — eliminar conversaciones > 90 días (con sus mensajes, adjuntos, nonces y tokens)
Schedule::command('chat:purge')->dailyAt('03:00')->name('chat:purge-old-conversations');
