<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TokenAuthTest extends TestCase
{
    use RefreshDatabase;

    private function createConversation(): array
    {
        $response = $this->postJson('/api/chat/conversations', [
            'visitor_name' => 'Test User',
            'consent'      => '1',
        ]);

        return [
            'uuid'  => $response->json('uuid'),
            'token' => $response->json('visitor_token'),
        ];
    }

    public function test_valid_token_grants_access(): void
    {
        ['uuid' => $uuid, 'token' => $token] = $this->createConversation();

        $response = $this->withHeaders(['X-Chat-Visitor-Token' => $token])
            ->getJson("/api/chat/conversations/{$uuid}/messages");

        $response->assertStatus(200);
    }

    public function test_missing_token_returns_401(): void
    {
        ['uuid' => $uuid] = $this->createConversation();

        $response = $this->getJson("/api/chat/conversations/{$uuid}/messages");
        $response->assertStatus(401);
    }

    public function test_wrong_token_returns_401(): void
    {
        ['uuid' => $uuid] = $this->createConversation();

        $response = $this->withHeaders(['X-Chat-Visitor-Token' => 'wrong-token-value'])
            ->getJson("/api/chat/conversations/{$uuid}/messages");

        $response->assertStatus(401);
    }

    public function test_token_of_another_conversation_returns_401(): void
    {
        ['uuid' => $uuid1]              = $this->createConversation();
        ['token' => $tokenOfSecond]     = $this->createConversation();

        // Token de la segunda conversación no sirve para la primera
        $response = $this->withHeaders(['X-Chat-Visitor-Token' => $tokenOfSecond])
            ->getJson("/api/chat/conversations/{$uuid1}/messages");

        $response->assertStatus(401);
    }

    public function test_nonexistent_uuid_returns_401(): void
    {
        $token = $this->createConversation()['token'];

        $response = $this->withHeaders(['X-Chat-Visitor-Token' => $token])
            ->getJson('/api/chat/conversations/' . Str::uuid() . '/messages');

        $response->assertStatus(401);
    }
}
