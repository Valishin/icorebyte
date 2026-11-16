<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tests for the CHAT_ENABLED feature flag (ChatEnabled middleware).
 *
 * When CHAT_ENABLED=false:
 *   - All /api/chat/* endpoints return 503 (except the Telegram webhook)
 *   - Telegram webhook returns 200 and ignores the update
 */
class ChatEnabledTest extends TestCase
{
    use RefreshDatabase;

    private function withChatDisabled(): void
    {
        config(['chat.enabled' => false]);
    }

    private function withChatEnabled(): void
    {
        config(['chat.enabled' => true]);
    }

    // ── Chat disabled ─────────────────────────────────────────────────────────

    public function test_start_conversation_returns_503_when_chat_disabled(): void
    {
        $this->withChatDisabled();

        $this->postJson('/api/chat/conversations', [
            'visitor_name' => 'Test',
            'locale'       => 'es',
            'page_url'     => 'https://example.com',
            'consent'      => true,
            'website'      => '',
        ])->assertStatus(503);
    }

    public function test_availability_returns_503_when_chat_disabled(): void
    {
        $this->withChatDisabled();

        $this->getJson('/api/chat/availability')->assertStatus(503);
    }

    public function test_messages_endpoint_returns_503_when_chat_disabled(): void
    {
        $this->withChatDisabled();

        $uuid  = Str::uuid()->toString();
        $token = bin2hex(random_bytes(32));

        $this->getJson("/api/chat/conversations/{$uuid}/messages", [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(503);
    }

    public function test_telegram_webhook_returns_200_when_chat_disabled(): void
    {
        $this->withChatDisabled();

        $this->postJson('/api/chat/telegram/webhook', ['update_id' => 1])
            ->assertOk();
    }

    // ── Chat enabled (default) ────────────────────────────────────────────────

    public function test_availability_returns_200_when_chat_enabled(): void
    {
        $this->withChatEnabled();

        $this->getJson('/api/chat/availability')->assertOk();
    }

    public function test_all_visitor_routes_blocked_when_chat_disabled(): void
    {
        $this->withChatDisabled();

        $uuid  = Str::uuid()->toString();

        $routes = [
            ['POST',  "/api/chat/conversations"],
            ['GET',   "/api/chat/availability"],
            ['GET',   "/api/chat/conversations/{$uuid}/messages"],
            ['POST',  "/api/chat/conversations/{$uuid}/messages"],
            ['POST',  "/api/chat/conversations/{$uuid}/presence"],
            ['POST',  "/api/chat/conversations/{$uuid}/typing"],
            ['PATCH', "/api/chat/conversations/{$uuid}/contact"],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->json($method, $url);
            $this->assertEquals(
                503,
                $response->getStatusCode(),
                "Expected 503 for {$method} {$url} when chat disabled",
            );
        }
    }
}
