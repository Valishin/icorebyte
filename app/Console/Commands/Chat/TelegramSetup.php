<?php

namespace App\Console\Commands\Chat;

use App\Services\Chat\TelegramService;
use Illuminate\Console\Command;

/**
 * Registra el webhook de Telegram con secret_token y allowed_updates.
 *
 * Uso:
 *   php artisan chat:telegram-setup
 *
 * El webhook se registra en APP_URL + /api/chat/telegram/webhook.
 * Asegúrate de que TELEGRAM_BOT_TOKEN, TELEGRAM_CHAT_ID y
 * TELEGRAM_WEBHOOK_SECRET estén definidos en .env antes de ejecutar.
 */
class TelegramSetup extends Command
{
    protected $signature   = 'chat:telegram-setup';
    protected $description = 'Registra el webhook de Telegram con secret_token';

    public function handle(TelegramService $telegram): int
    {
        $token  = (string) config('services.telegram.token', '');
        $chatId = (string) config('services.telegram.chat_id', '');
        $secret = (string) config('services.telegram.webhook_secret', '');

        if ($token === '') {
            $this->error('TELEGRAM_BOT_TOKEN no está configurado en .env.');
            return self::FAILURE;
        }
        if ($chatId === '') {
            $this->error('TELEGRAM_CHAT_ID no está configurado en .env.');
            return self::FAILURE;
        }
        if ($secret === '') {
            $this->error('TELEGRAM_WEBHOOK_SECRET no está configurado en .env.');
            return self::FAILURE;
        }

        $url = rtrim((string) config('app.url'), '/') . '/api/chat/telegram/webhook';
        $this->line("Registrando webhook en: {$url}");

        $ok = $telegram->setWebhook($url, $secret, ['message', 'callback_query']);

        if ($ok) {
            $this->info('✅ Webhook registrado correctamente.');
            $this->line("  TELEGRAM_CHAT_ID: {$chatId}");
            return self::SUCCESS;
        }

        $this->error('❌ No se pudo registrar el webhook. Revisa el log para más detalles.');
        return self::FAILURE;
    }
}
