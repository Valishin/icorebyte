<?php

namespace Tests\Feature\Chat;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    private array $conv;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');

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

    public function test_invalid_mime_type_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->authed()
            ->postJson("/api/chat/conversations/{$this->conv['uuid']}/messages", [
                'attachment' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('attachment');
    }

    public function test_oversized_attachment_is_rejected(): void
    {
        // 6 MB > límite de 5 MB
        $file = UploadedFile::fake()->image('big.jpg')->size(6144);

        $response = $this->authed()
            ->postJson("/api/chat/conversations/{$this->conv['uuid']}/messages", [
                'attachment' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('attachment');
    }

    public function test_valid_image_returns_signed_url(): void
    {
        // GD no procesa un fake; mockeamos AttachmentController::process
        $this->mock(\App\Http\Controllers\Chat\AttachmentController::class);

        // Crear mensaje con attachment_path directamente via modelo para testear el formateo
        $conversation = \App\Models\ChatConversation::where('uuid', $this->conv['uuid'])->first();
        Storage::disk('private')->put('chat/attachments/test-uuid.jpg', 'fake-image-data');

        $conversation->messages()->create([
            'sender'          => 'agent',
            'body'            => 'Mira esta imagen',
            'attachment_path' => 'test-uuid.jpg',
        ]);

        $response = $this->authed()
            ->getJson("/api/chat/conversations/{$this->conv['uuid']}/messages");

        $response->assertStatus(200);

        $messages = $response->json('messages');
        $agentMsg = collect($messages)->firstWhere('sender', 'agent');

        $this->assertNotNull($agentMsg['attachment_url']);
        $this->assertStringContainsString('signature', $agentMsg['attachment_url']);
        $this->assertStringContainsString('/api/chat/attachments/', $agentMsg['attachment_url']);
    }
}
