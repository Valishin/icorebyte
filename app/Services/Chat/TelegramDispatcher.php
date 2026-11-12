<?php

namespace App\Services\Chat;

use App\Jobs\Chat\SendTelegramMessageJob;
use App\Models\TelegramOutbound;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Envía mensajes a Telegram de dos formas:
 *
 *  1. Producción: directamente TRAS responder al visitante (app()->terminating()),
 *     sin cola. Si el envío falla, despacha a la cola database como reintento.
 *
 *  2. Tests: despacha a la cola (capturada por Queue::fake) para que los tests
 *     puedan usar Queue::assertPushed() sin depender de llamadas HTTP reales.
 *
 * En ambos casos, si el envío tiene éxito se crea un registro en telegram_outbound
 * para que el webhook pueda resolver a qué conversación pertenece cada respuesta.
 */
class TelegramDispatcher
{
    // Tipos de mensaje (usados en telegram_outbound.tipo)
    public const TIPO_NEW_CONV      = 'new_conversation';
    public const TIPO_VISITOR_MSG   = 'visitor_message';
    public const TIPO_VISITOR_PHOTO = 'visitor_photo';
    public const TIPO_SYSTEM        = 'system';

    private static bool $forceProductionPath = false;

    /** Solo para tests: fuerza el camino de producción (app()->terminating) en vez de la cola. */
    public static function forceProduction(bool $value = true): void
    {
        self::$forceProductionPath = $value;
    }

    public function __construct(private readonly TelegramService $telegram)
    {
    }

    // ── API pública ───────────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed> $extra  Parámetros adicionales para Telegram
     */
    public function sendText(
        string $text,
        array $extra,
        int $conversationId,
        string $tipo,
        ?int $chatMessageId = null,
    ): void {
        $this->schedule(
            SendTelegramMessageJob::TYPE_TEXT,
            $text, $extra,
            null, null,
            $conversationId, $tipo, $chatMessageId
        );
    }

    /**
     * @param  array<string, mixed> $extra
     */
    public function sendPhoto(
        string $storageDisk,
        string $storagePath,
        string $caption,
        array $extra,
        int $conversationId,
        string $tipo,
        ?int $chatMessageId = null,
    ): void {
        $this->schedule(
            SendTelegramMessageJob::TYPE_PHOTO,
            $caption, $extra,
            $storageDisk, $storagePath,
            $conversationId, $tipo, $chatMessageId
        );
    }

    // ── Lógica interna ────────────────────────────────────────────────────────

    private function schedule(
        string $jobType,
        string $text,
        array $extra,
        ?string $storageDisk,
        ?string $storagePath,
        int $conversationId,
        string $tipo,
        ?int $chatMessageId,
    ): void {
        if (! config('chat.telegram_enabled')) {
            return;
        }

        if (app()->runningUnitTests() && ! self::$forceProductionPath) {
            // En tests Queue::fake() captura el job para assertPushed()
            dispatch(new SendTelegramMessageJob(
                $jobType, $text, $extra,
                $storageDisk, $storagePath,
                $conversationId, $tipo, $chatMessageId
            ));
            return;
        }

        // Producción: envío directo tras la respuesta HTTP
        app()->terminating(function () use (
            $jobType, $text, $extra,
            $storageDisk, $storagePath,
            $conversationId, $tipo, $chatMessageId
        ): void {
            $this->sendNow(
                $jobType, $text, $extra,
                $storageDisk, $storagePath,
                $conversationId, $tipo, $chatMessageId
            );
        });
    }

    private function sendNow(
        string $jobType,
        string $text,
        array $extra,
        ?string $storageDisk,
        ?string $storagePath,
        int $conversationId,
        string $tipo,
        ?int $chatMessageId,
    ): void {
        // Rate limit: ≈1 msg/s al mismo chat (usar Cache; usleep si llegamos muy rápido)
        $lastMs = (int) Cache::get('telegram.last_sent_ms', 0);
        $nowMs  = (int) (microtime(true) * 1000);
        $wait   = 1000 - ($nowMs - $lastMs);
        if ($wait > 0) {
            usleep($wait * 1000);
        }
        Cache::put('telegram.last_sent_ms', (int) (microtime(true) * 1000), 10);

        try {
            $result = $jobType === SendTelegramMessageJob::TYPE_PHOTO
                ? $this->telegram->sendPhoto((string) $storageDisk, (string) $storagePath, $text, $extra)
                : $this->telegram->sendMessage($text, $extra);

            if ($result !== null && isset($result['message_id'])) {
                TelegramOutbound::create([
                    'telegram_message_id' => (int) $result['message_id'],
                    'conversation_id'     => $conversationId,
                    'chat_message_id'     => $chatMessageId,
                    'tipo'                => $tipo,
                ]);
            } else {
                // Envío directo falló — cola como reintento
                dispatch(new SendTelegramMessageJob(
                    $jobType, $text, $extra,
                    $storageDisk, $storagePath,
                    $conversationId, $tipo, $chatMessageId
                ));
            }
        } catch (\Throwable $e) {
            Log::error('TelegramDispatcher: error al enviar', ['error' => $e->getMessage()]);
            dispatch(new SendTelegramMessageJob(
                $jobType, $text, $extra,
                $storageDisk, $storagePath,
                $conversationId, $tipo, $chatMessageId
            ));
        }
    }
}
