<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    // 'sender' is intentionally fillable so internal code can use create([...]).
    // It must NEVER be sourced from request data — controllers always hardcode the value.
    // The SecurityAttackTest verifies this invariant survives refactors.
    protected $fillable = [
        'conversation_id',
        'sender',
        'body',
        'attachment_path',
        'telegram_message_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /** @return BelongsTo<ChatConversation, $this> */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
