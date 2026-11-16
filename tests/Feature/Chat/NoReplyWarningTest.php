<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\Chat\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class NoReplyWarningTest extends TestCase
{
    use RefreshDatabase;

    private function openConversation(array $attrs = []): ChatConversation
    {
        return ChatConversation::create(array_merge([
            'uuid'          => \Illuminate\Support\Str::uuid(),
            'visitor_token' => hash('sha256', 'test-token'),
            'status'        => 'open',
            'locale'        => 'es',
            'visitor_name'  => 'Test',
            'ip_hash'       => hash('sha256', '127.0.0.1'),
            'consent_at'    => now(),
        ], $attrs));
    }

    public function test_warning_sent_when_no_reply_threshold_exceeded(): void
    {
        $this->mock(AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isOnline')->andReturn(true);
        });

        config(['chat.no_reply_minutes' => 5]);

        $conv = $this->openConversation([
            'last_visitor_message_at' => now()->subMinutes(10),
        ]);

        Artisan::call('chat:no-reply-warning');

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'system',
        ]);
        $this->assertNotNull($conv->fresh()->no_reply_warned_at);
    }

    public function test_warning_not_sent_when_within_threshold(): void
    {
        $this->mock(AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isOnline')->andReturn(true);
        });

        config(['chat.no_reply_minutes' => 5]);

        $conv = $this->openConversation([
            'last_visitor_message_at' => now()->subMinutes(2),
        ]);

        Artisan::call('chat:no-reply-warning');

        $this->assertDatabaseMissing('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'system',
        ]);
        $this->assertNull($conv->fresh()->no_reply_warned_at);
    }

    public function test_warning_not_sent_twice(): void
    {
        $this->mock(AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isOnline')->andReturn(true);
        });

        config(['chat.no_reply_minutes' => 5]);

        $conv = $this->openConversation([
            'last_visitor_message_at' => now()->subMinutes(10),
            'no_reply_warned_at'      => now()->subMinutes(3),
        ]);

        Artisan::call('chat:no-reply-warning');

        $this->assertDatabaseMissing('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'system',
        ]);
    }

    public function test_warning_not_sent_when_offline(): void
    {
        $this->mock(AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isOnline')->andReturn(false);
        });

        config(['chat.no_reply_minutes' => 5]);

        $conv = $this->openConversation([
            'last_visitor_message_at' => now()->subMinutes(10),
        ]);

        Artisan::call('chat:no-reply-warning');

        $this->assertDatabaseMissing('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'system',
        ]);
    }

    public function test_warning_not_sent_when_agent_already_replied_after_visitor(): void
    {
        $this->mock(AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isOnline')->andReturn(true);
        });

        config(['chat.no_reply_minutes' => 5]);

        $conv = $this->openConversation([
            'last_visitor_message_at' => now()->subMinutes(10),
            'last_agent_message_at'   => now()->subMinutes(3),
        ]);

        Artisan::call('chat:no-reply-warning');

        $this->assertDatabaseMissing('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'system',
        ]);
    }

    public function test_warning_not_sent_to_closed_conversation(): void
    {
        $this->mock(AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isOnline')->andReturn(true);
        });

        config(['chat.no_reply_minutes' => 5]);

        $conv = $this->openConversation([
            'status'                  => 'closed',
            'last_visitor_message_at' => now()->subMinutes(10),
        ]);

        Artisan::call('chat:no-reply-warning');

        $this->assertDatabaseMissing('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'system',
        ]);
    }
}
