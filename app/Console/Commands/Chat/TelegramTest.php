<?php

namespace App\Console\Commands\Chat;

use App\Services\Chat\TelegramService;
use Illuminate\Console\Command;

/**
 * Envía un mensaje de prueba al chat configurado para verificar la integración.
 *
 * Uso:
 *   php artisan chat:telegram-test
 */
class TelegramTest extends Command
{
    protected $signature   = 'chat:telegram-test';
    protected $description = 'Envía un mensaje de prueba a Telegram';

    public function handle(TelegramService $telegram): int
    {
        $token  = (string) config('services.telegram.token', '');
        $chatId = (string) config('services.telegram.chat_id', '');

        if ($token === '' || $chatId === '') {
            $this->error('TELEGRAM_BOT_TOKEN o TELEGRAM_CHAT_ID no están configurados.');
            return self::FAILURE;
        }

        $result = $telegram->sendMessage(
            '✅ <b>Test de integración iCoreByte Chat</b>\n\nSi ves este mensaje, el bot está correctamente configurado.'
        );

        if ($result !== null) {
            $this->info('✅ Mensaje enviado correctamente. message_id: ' . ($result['message_id'] ?? '?'));
            return self::SUCCESS;
        }

        $this->error('❌ No se pudo enviar el mensaje. Revisa el log para más detalles.');
        return self::FAILURE;
    }
}
