<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Chat: aviso de no-respuesta — cada minuto
// En producción: configurar cron en Hostinger cPanel:
// * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
Schedule::command('chat:no-reply-warning')->everyMinute();

// Chat: limpieza de registros de idempotencia Telegram (> 7 días)
Schedule::call(function () {
    \App\Models\TelegramUpdate::where('created_at', '<', now()->subDays(7))->delete();
})->daily()->name('chat:cleanup-telegram-updates');

// Chat: RGPD — eliminar conversaciones > 90 días (con sus mensajes, adjuntos, nonces y tokens)
Schedule::command('chat:purge')->dailyAt('03:00')->name('chat:purge-old-conversations');
