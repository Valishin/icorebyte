<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagePollingTest extends TestCase
{
    use RefreshDatabase;

    private array $conv;

    protected function setUp(): void
    {
        parent::setUp();

        $response = $this->postJson('/api/chat/conversations', [
            'visitor_name' => 'Test User',
            'consent'      => '1',
        ]);

        $this->conv = [
            'uuid'  => $response->json('uuid'),
            'token' => $response->json('visitor_token'),
        ];
    }

    private function authed(): static
    {
        return $this->withHeaders(['X-Chat-Visitor-Token' => $this->conv['token']]);
    }

    public function test_poll_returns_messages_in_order(): void
    {
        $response = $this->authed()
            ->getJson("/api/chat/conversations/{$this->conv['uuid']}/messages");

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'agent_typing', 'messages'])
            ->assertJsonPath('status', 'open');

        // El mensaje de bienvenida del sistema debe estar presente
        $messages = $response->json('messages');
        $this->assertNotEmpty($messages);
        $this->assertEquals('system', $messages[0]['sender']);
    }

    public function test_after_param_returns_only_newer_messages(): void
    {
        // Enviar un mensaje como visitante
        $this->authed()->postJson("/api/chat/conversations/{$this->conv['uuid']}/messages", [
            'body' => 'Hola',
        ]);

        // Obtener todos los mensajes y capturar el último ID
        $all = $this->authed()
            ->getJson("/api/chat/conversations/{$this->conv['uuid']}/messages")
            ->json('messages');

        $lastId = end($all)['id'];

        // Enviar otro mensaje
        $this->authed()->postJson("/api/chat/conversations/{$this->conv['uuid']}/messages", [
            'body' => 'Mensaje nuevo',
        ]);

        // Polling con after=lastId debe devolver solo el nuevo
        $response = $this->authed()
            ->getJson("/api/chat/conversations/{$this->conv['uuid']}/messages?after={$lastId}");

        $response->assertStatus(200);
        $messages = $response->json('messages');
        $this->assertCount(1, $messages);
        $this->assertEquals('Mensaje nuevo', $messages[0]['body']);
    }

    public function test_message_to_closed_conversation_returns_409(): void
    {
        $conversation = ChatConversation::where('uuid', $this->conv['uuid'])->first();
        $conversation->update(['status' => 'closed']);

        $response = $this->authed()
            ->postJson("/api/chat/conversations/{$this->conv['uuid']}/messages", [
                'body' => 'Hola',
            ]);

        $response->assertStatus(409)
            ->assertJsonStructure(['error']);
    }

    public function test_empty_body_without_attachment_fails(): void
    {
        $response = $this->authed()
            ->postJson("/api/chat/conversations/{$this->conv['uuid']}/messages", [
                'body' => '',
            ]);

        $response->assertStatus(422);
    }

    public function test_message_body_is_trimmed_and_capped(): void
    {
        $longBody = str_repeat('a', 2500);

        $response = $this->authed()
            ->postJson("/api/chat/conversations/{$this->conv['uuid']}/messages", [
                'body' => '  ' . $longBody . '  ',
            ]);

        // La request rechaza >2000 chars
        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('body');
    }

    public function test_visitor_token_is_not_stored_in_plaintext(): void
    {
        $rawToken = $this->conv['token'];

        $this->assertDatabaseMissing('chat_conversations', [
            'visitor_token' => $rawToken,
        ]);
        $this->assertDatabaseHas('chat_conversations', [
            'visitor_token' => hash('sha256', $rawToken),
        ]);
    }
}
