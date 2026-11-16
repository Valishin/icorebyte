<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateContactTest extends TestCase
{
    use RefreshDatabase;

    private function createConversation(): array
    {
        $rawToken = bin2hex(random_bytes(32));
        $conv = ChatConversation::create([
            'uuid'          => \Illuminate\Support\Str::uuid(),
            'visitor_token' => hash('sha256', $rawToken),
            'status'        => 'open',
            'locale'        => 'es',
            'visitor_name'  => 'Test',
            'ip_hash'       => hash('sha256', '127.0.0.1'),
            'consent_at'    => now(),
        ]);
        return ['conv' => $conv, 'token' => $rawToken];
    }

    private function authed(string $token): static
    {
        return $this->withHeaders(['X-Chat-Visitor-Token' => $token]);
    }

    public function test_update_contact_with_phone(): void
    {
        ['conv' => $conv, 'token' => $token] = $this->createConversation();

        $response = $this->authed($token)
            ->patchJson("/api/chat/conversations/{$conv->uuid}/contact", [
                'phone' => '+34 678 123 456',
            ]);

        $response->assertStatus(200)
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('chat_conversations', [
            'id'            => $conv->id,
            'visitor_phone' => '+34 678 123 456',
        ]);
    }

    public function test_update_contact_with_email(): void
    {
        ['conv' => $conv, 'token' => $token] = $this->createConversation();

        $response = $this->authed($token)
            ->patchJson("/api/chat/conversations/{$conv->uuid}/contact", [
                'email' => 'test@example.com',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('chat_conversations', [
            'id'            => $conv->id,
            'visitor_email' => 'test@example.com',
        ]);
    }

    public function test_update_contact_with_both_phone_and_email(): void
    {
        ['conv' => $conv, 'token' => $token] = $this->createConversation();

        $response = $this->authed($token)
            ->patchJson("/api/chat/conversations/{$conv->uuid}/contact", [
                'phone' => '+34 678 123 456',
                'email' => 'test@example.com',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('chat_conversations', [
            'id'            => $conv->id,
            'visitor_phone' => '+34 678 123 456',
            'visitor_email' => 'test@example.com',
        ]);
    }

    public function test_update_contact_requires_at_least_one_field(): void
    {
        ['conv' => $conv, 'token' => $token] = $this->createConversation();

        $response = $this->authed($token)
            ->patchJson("/api/chat/conversations/{$conv->uuid}/contact", []);

        $response->assertStatus(422);
    }

    public function test_update_contact_rejects_invalid_phone(): void
    {
        ['conv' => $conv, 'token' => $token] = $this->createConversation();

        $response = $this->authed($token)
            ->patchJson("/api/chat/conversations/{$conv->uuid}/contact", [
                'phone' => 'not-a-phone',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_update_contact_creates_system_confirmation_message(): void
    {
        ['conv' => $conv, 'token' => $token] = $this->createConversation();

        $this->authed($token)
            ->patchJson("/api/chat/conversations/{$conv->uuid}/contact", [
                'phone' => '+34 678 123 456',
            ]);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'system',
        ]);

        $msg = $conv->messages()->where('sender', 'system')->latest()->first();
        $this->assertNotNull($msg);
        $this->assertStringContainsString('678 123 456', $msg->body);
    }

    public function test_update_contact_requires_valid_token(): void
    {
        ['conv' => $conv] = $this->createConversation();

        $response = $this->withHeaders(['X-Chat-Visitor-Token' => 'wrong-token'])
            ->patchJson("/api/chat/conversations/{$conv->uuid}/contact", [
                'phone' => '+34 678 123 456',
            ]);

        $response->assertStatus(401);
    }
}
