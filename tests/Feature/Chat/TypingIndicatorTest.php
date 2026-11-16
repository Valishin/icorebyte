<?php

namespace Tests\Feature\Chat;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TypingIndicatorTest extends TestCase
{
    use RefreshDatabase;

    private array $conv;

    protected function setUp(): void
    {
        parent::setUp();

        $r = $this->postJson('/api/chat/conversations', [
            'visitor_name' => 'Typing Tester',
            'consent'      => '1',
        ]);

        $this->conv = [
            'uuid'  => $r->json('uuid'),
            'token' => $r->json('visitor_token'),
        ];
    }

    private function authed(): static
    {
        return $this->withHeaders(['X-Chat-Visitor-Token' => $this->conv['token']]);
    }

    // ── Visitor → panel ───────────────────────────────────────────────────────

    public function test_visitor_typing_sets_cache(): void
    {
        $uuid = $this->conv['uuid'];

        $this->authed()
            ->postJson("/api/chat/conversations/{$uuid}/typing")
            ->assertOk();

        $this->assertTrue(
            (bool) Cache::get("chat.typing.visitor.{$uuid}"),
            'visitor typing flag must be set in cache',
        );
    }

    public function test_messages_index_includes_visitor_typing_flag(): void
    {
        $uuid = $this->conv['uuid'];

        // Pre-set the cache as if the visitor just typed
        Cache::put("chat.typing.visitor.{$uuid}", true, 5);

        $this->authed()
            ->getJson("/api/chat/conversations/{$uuid}/messages")
            ->assertStatus(200)
            ->assertJsonPath('visitor_typing', true);
    }

    public function test_visitor_typing_absent_when_cache_expired(): void
    {
        $uuid = $this->conv['uuid'];

        // Cache not set → flag must be false
        Cache::forget("chat.typing.visitor.{$uuid}");

        $this->authed()
            ->getJson("/api/chat/conversations/{$uuid}/messages")
            ->assertJsonPath('visitor_typing', false);
    }

    // ── Agent → widget ────────────────────────────────────────────────────────

    public function test_agent_typing_cache_exposed_on_message_poll(): void
    {
        $uuid = $this->conv['uuid'];

        // Simulate the panel setting the agent-typing cache (via updatedReplyBody)
        Cache::put("chat.typing.agent.{$uuid}", true, 5);

        $this->authed()
            ->getJson("/api/chat/conversations/{$uuid}/messages")
            ->assertStatus(200)
            ->assertJsonPath('agent_typing', true);
    }

    public function test_agent_typing_false_when_not_set(): void
    {
        $uuid = $this->conv['uuid'];
        Cache::forget("chat.typing.agent.{$uuid}");

        $this->authed()
            ->getJson("/api/chat/conversations/{$uuid}/messages")
            ->assertJsonPath('agent_typing', false);
    }
}
