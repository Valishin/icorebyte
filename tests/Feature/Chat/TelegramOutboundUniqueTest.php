<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\TelegramOutbound;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramOutboundUniqueTest extends TestCase
{
    use RefreshDatabase;

    private function conversation(): ChatConversation
    {
        return ChatConversation::create([
            'uuid'          => \Illuminate\Support\Str::uuid(),
            'visitor_token' => hash('sha256', bin2hex(random_bytes(16))),
            'status'        => 'open',
            'locale'        => 'es',
            'visitor_name'  => 'Test',
            'ip_hash'       => hash('sha256', '127.0.0.1'),
            'consent_at'    => now(),
        ]);
    }

    public function test_duplicate_telegram_message_id_raises_unique_violation(): void
    {
        $conv = $this->conversation();

        TelegramOutbound::create([
            'telegram_message_id' => 1001,
            'conversation_id'     => $conv->id,
            'tipo'                => 'visitor_msg',
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        TelegramOutbound::create([
            'telegram_message_id' => 1001,
            'conversation_id'     => $conv->id,
            'tipo'                => 'visitor_msg',
        ]);
    }

    public function test_different_telegram_message_ids_are_allowed(): void
    {
        $conv = $this->conversation();

        TelegramOutbound::create([
            'telegram_message_id' => 2001,
            'conversation_id'     => $conv->id,
            'tipo'                => 'visitor_msg',
        ]);
        TelegramOutbound::create([
            'telegram_message_id' => 2002,
            'conversation_id'     => $conv->id,
            'tipo'                => 'visitor_msg',
        ]);

        $this->assertDatabaseCount('telegram_outbound', 2);
    }
}
