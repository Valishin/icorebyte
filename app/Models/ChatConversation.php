<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TelegramOutbound;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property-read \Illuminate\Database\Eloquent\Collection<int, ChatMessage> $messages */

class ChatConversation extends Model
{
    protected $fillable = [
        'uuid',
        'visitor_token',
        'status',
        'locale',
        'visitor_name',
        'visitor_phone',
        'visitor_email',
        'page_url',
        'ip_hash',
        'consent_at',
        'last_visitor_message_at',
        'last_agent_message_at',
        'visitor_last_seen_at',
        'agent_last_read_at',
        'visitor_last_read_at',
        'no_reply_warned_at',
        'notification_email_sent_at',
        'resume_nonce_hash',
        'resume_token_hash',
    ];

    /** @var list<string> */
    protected $hidden = ['visitor_token', 'resume_nonce_hash', 'resume_token_hash'];

    protected $casts = [
        'consent_at'                  => 'datetime',
        'last_visitor_message_at'     => 'datetime',
        'last_agent_message_at'       => 'datetime',
        'visitor_last_seen_at'        => 'datetime',
        'agent_last_read_at'          => 'datetime',
        'visitor_last_read_at'        => 'datetime',
        'no_reply_warned_at'          => 'datetime',
        'notification_email_sent_at'  => 'datetime',
    ];

    /** @return HasMany<ChatMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    /** @return HasMany<TelegramOutbound, $this> */
    public function telegramOutbound(): HasMany
    {
        return $this->hasMany(TelegramOutbound::class, 'conversation_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function unreadByAgent(): int
    {
        return $this->messages()
            ->where('sender', 'visitor')
            ->whereNull('read_at')
            ->count();
    }
}
