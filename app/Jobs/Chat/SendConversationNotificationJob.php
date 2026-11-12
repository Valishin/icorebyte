<?php

namespace App\Jobs\Chat;

use App\Mail\Chat\ConversationNotificationMailable;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendConversationNotificationJob implements ShouldQueue
{
    use Queueable;

    private const UNSEEN_MINUTES  = 10;  // visitor must not have been seen in this window
    private const THROTTLE_HOURS  = 1;   // at most 1 email per conversation per hour

    public function __construct(
        private readonly ChatConversation $conversation,
        private readonly ChatMessage $message,
    ) {
    }

    public function handle(): void
    {
        $conv = $this->conversation;

        if (! $this->shouldSend($conv)) {
            return;
        }

        // Generate a one-time nonce. Store only its hash — the plain value travels
        // in the signed URL and is never stored unencrypted.
        $plainNonce = bin2hex(random_bytes(32));
        $conv->update(['resume_nonce_hash' => hash('sha256', $plainNonce)]);

        Mail::to((string) $conv->visitor_email)
            ->send(new ConversationNotificationMailable($conv, $this->message, $plainNonce));

        $conv->update(['notification_email_sent_at' => now()]);
    }

    private function shouldSend(ChatConversation $conv): bool
    {
        if (blank($conv->visitor_email)) {
            return false;
        }

        $lastSeen = $conv->visitor_last_read_at;
        if ($lastSeen !== null && $lastSeen->gt(now()->subMinutes(self::UNSEEN_MINUTES))) {
            return false;
        }

        $lastSent = $conv->notification_email_sent_at;
        if ($lastSent !== null && $lastSent->gt(now()->subHours(self::THROTTLE_HOURS))) {
            return false;
        }

        return true;
    }
}
