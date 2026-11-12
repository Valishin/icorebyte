<?php

namespace App\Jobs\Chat;

use App\Models\TelegramOutbound;
use App\Services\Chat\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Reintento de cola cuando el envío directo (afterResponse) falla.
 * Al enviar con éxito, crea el registro en telegram_outbound.
 */
class SendTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 3;
    /** @var int[] */
    public array $backoff  = [5, 30, 60];

    public const TYPE_TEXT  = 'text';
    public const TYPE_PHOTO = 'photo';

    /**
     * @param  array<string, mixed> $extra
     */
    public function __construct(
        private readonly string  $type,
        private readonly string  $text,
        private readonly array   $extra        = [],
        private readonly ?string $storageDisk  = null,
        private readonly ?string $storagePath  = null,
        private readonly int     $conversationId = 0,
        private readonly string  $tipo         = 'system',
        private readonly ?int    $chatMessageId = null,
    ) {
    }

    public function handle(TelegramService $telegram): void
    {
        // Rate limit: ≈1 msg/s
        $lock = Cache::lock('telegram.rate_limit', 1);
        if (! $lock->block(2)) {
            $this->release(1);
            return;
        }

        try {
            $result = $this->type === self::TYPE_PHOTO
                ? $telegram->sendPhoto((string) $this->storageDisk, (string) $this->storagePath, $this->text, $this->extra)
                : $telegram->sendMessage($this->text, $this->extra);

            if ($result !== null && isset($result['message_id'])) {
                TelegramOutbound::create([
                    'telegram_message_id' => (int) $result['message_id'],
                    'conversation_id'     => $this->conversationId,
                    'chat_message_id'     => $this->chatMessageId,
                    'tipo'                => $this->tipo,
                ]);
            }
        } finally {
            $lock->release();
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendTelegramMessageJob fallido', [
            'type'  => $this->type,
            'error' => $e->getMessage(),
        ]);
    }
}
