<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registra cada mensaje que el bot envía a Telegram.
 * El webhook usa telegram_message_id para resolver a qué conversación
 * pertenece una respuesta del agente.
 *
 * @property int         $id
 * @property int         $telegram_message_id
 * @property int         $conversation_id
 * @property int|null    $chat_message_id
 * @property string      $tipo
 */
class TelegramOutbound extends Model
{
    protected $table = 'telegram_outbound';

    protected $fillable = [
        'telegram_message_id',
        'conversation_id',
        'chat_message_id',
        'tipo',
    ];

    /** @return BelongsTo<ChatConversation, $this> */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class);
    }

    /** @return BelongsTo<ChatMessage, $this> */
    public function chatMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }
}
