<?php

namespace Tests\Feature\Chat;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateConversationTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'visitor_name' => 'Ana García',
            'visitor_email' => 'ana@example.com',
            'page_url'     => 'https://icorebyte.com',
            'consent'      => '1',
        ], $overrides);
    }

    public function test_creates_conversation_and_returns_token(): void
    {
        $response = $this->postJson('/api/chat/conversations', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure(['uuid', 'visitor_token']);

        $token = $response->json('visitor_token');

        // El token en claro tiene 64 chars hex
        $this->assertEquals(64, strlen($token));

        // En BD debe estar el hash, no el token en claro
        $this->assertDatabaseMissing('chat_conversations', ['visitor_token' => $token]);
        $this->assertDatabaseHas('chat_conversations', ['visitor_token' => hash('sha256', $token)]);
    }

    public function test_creates_welcome_system_message(): void
    {
        $response = $this->postJson('/api/chat/conversations', $this->validPayload());
        $response->assertStatus(201);

        $this->assertDatabaseHas('chat_messages', [
            'sender' => 'system',
        ]);
    }

    public function test_requires_consent(): void
    {
        $response = $this->postJson('/api/chat/conversations', $this->validPayload([
            'consent' => '0',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('consent');
    }

    public function test_consent_missing_fails(): void
    {
        $payload = $this->validPayload();
        unset($payload['consent']);

        $response = $this->postJson('/api/chat/conversations', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('consent');
    }

    public function test_requires_visitor_name(): void
    {
        $response = $this->postJson('/api/chat/conversations', $this->validPayload([
            'visitor_name' => '',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('visitor_name');
    }

    public function test_honeypot_blocks_spam(): void
    {
        $response = $this->postJson('/api/chat/conversations', $this->validPayload([
            'website' => 'https://spam.example.com',
        ]));

        $response->assertStatus(422);
        $this->assertDatabaseCount('chat_conversations', 0);
    }

    public function test_saves_consent_at(): void
    {
        $response = $this->postJson('/api/chat/conversations', $this->validPayload());
        $response->assertStatus(201);

        $uuid = $response->json('uuid');
        $conversation = \App\Models\ChatConversation::where('uuid', $uuid)->firstOrFail();

        $this->assertNotNull($conversation->consent_at);
    }

    public function test_stores_ip_as_hash_not_plaintext(): void
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])
            ->postJson('/api/chat/conversations', $this->validPayload());

        $response->assertStatus(201);

        // La IP nunca debe aparecer en claro
        $this->assertDatabaseMissing('chat_conversations', ['ip_hash' => '1.2.3.4']);
        $this->assertDatabaseHas('chat_conversations', ['ip_hash' => hash('sha256', '1.2.3.4')]);
    }
}
