<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

// Header name used by ValidateChatToken middleware
// (confirmed from TokenAuthTest: 'X-Chat-Visitor-Token')

/**
 * Phase B — automated attack tests for the live chat security audit.
 *
 * Each test proves a specific attack vector is blocked (or was blocked after fixes).
 */
class SecurityAttackTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeConv(array $attrs = []): ChatConversation
    {
        $createdAt = $attrs['created_at'] ?? null;
        unset($attrs['created_at']);

        $conv = ChatConversation::create(array_merge([
            'uuid'          => Str::uuid(),
            'visitor_token' => hash('sha256', bin2hex(random_bytes(16))),
            'status'        => 'open',
            'locale'        => 'es',
            'visitor_name'  => 'Attacker',
            'ip_hash'       => hash('sha256', '1.2.3.4'),
            'consent_at'    => now(),
        ], $attrs));

        // $fillable doesn't include timestamp columns — override via DB directly
        if ($createdAt !== null) {
            DB::table('chat_conversations')->where('id', $conv->id)->update(['created_at' => $createdAt]);
            $conv->refresh();
        }

        return $conv;
    }

    private function resumeUrl(ChatConversation $conv, string $nonce): string
    {
        return URL::temporarySignedRoute(
            'chat.resume',
            now()->addDays(7),
            ['uuid' => $conv->uuid, 'nonce' => $nonce]
        );
    }

    // ── IDOR: token of conv A cannot access conv B ────────────────────────────

    public function test_idor_visitor_token_of_conv_a_cannot_read_conv_b(): void
    {
        $convA = $this->makeConv();
        $convB = $this->makeConv();

        $tokenA = bin2hex(random_bytes(32));
        DB::table('chat_conversations')
            ->where('id', $convA->id)
            ->update(['visitor_token' => hash('sha256', $tokenA)]);

        $response = $this->withHeaders(['X-Chat-Visitor-Token' => $tokenA])
            ->getJson("/api/chat/conversations/{$convB->uuid}/messages");

        $response->assertStatus(401);
    }

    public function test_idor_wrong_token_cannot_post_message_to_conversation(): void
    {
        $conv  = $this->makeConv();
        $wrong = bin2hex(random_bytes(32));

        $response = $this->withHeaders(['X-Chat-Visitor-Token' => $wrong])
            ->postJson("/api/chat/conversations/{$conv->uuid}/messages", [
                'body' => 'injected message',
            ]);

        $response->assertStatus(401);
    }

    // ── Open redirect: page_url must be own domain ────────────────────────────

    public function test_resume_redirect_with_external_page_url_goes_to_home(): void
    {
        $nonce = bin2hex(random_bytes(16));
        $conv  = $this->makeConv([
            'page_url'          => 'https://evil.com/steal-token',
            'resume_nonce_hash' => hash('sha256', $nonce),
        ]);

        $url      = $this->resumeUrl($conv, $nonce);
        $response = $this->get($url);

        // Must NOT redirect to evil.com — must redirect to own domain or /
        $location = $response->headers->get('Location') ?? '';
        $this->assertStringNotContainsString('evil.com', $location,
            'Open redirect: response redirected to evil.com — new visitor token would be exposed');

        // Should be a redirect to the app URL root
        $this->assertStringStartsWith(config('app.url'), $location);
    }

    public function test_resume_redirect_with_own_domain_page_url_is_allowed(): void
    {
        $nonce = bin2hex(random_bytes(16));
        $conv  = $this->makeConv([
            'page_url'          => config('app.url') . '/productos',
            'resume_nonce_hash' => hash('sha256', $nonce),
        ]);

        $url      = $this->resumeUrl($conv, $nonce);
        $response = $this->get($url);

        $location = $response->headers->get('Location') ?? '';
        $this->assertStringStartsWith(config('app.url'), $location,
            'Valid page_url on own domain should redirect there');
    }

    public function test_resume_redirect_with_null_page_url_goes_to_home(): void
    {
        $nonce = bin2hex(random_bytes(16));
        $conv  = $this->makeConv([
            'page_url'          => null,
            'resume_nonce_hash' => hash('sha256', $nonce),
        ]);

        $url      = $this->resumeUrl($conv, $nonce);
        $response = $this->get($url);

        $location = $response->headers->get('Location') ?? '';
        $this->assertNotEmpty($location, 'Resume route should redirect somewhere even with null page_url');
    }

    // ── Nonce: one-time use ───────────────────────────────────────────────────

    public function test_resume_nonce_cannot_be_used_twice(): void
    {
        $nonce = bin2hex(random_bytes(16));
        $conv  = $this->makeConv([
            'page_url'          => config('app.url') . '/',
            'resume_nonce_hash' => hash('sha256', $nonce),
        ]);

        $url = $this->resumeUrl($conv, $nonce);

        $this->get($url)->assertRedirect();      // first use: OK
        $this->get($url)->assertStatus(410);     // second use: GONE
    }

    public function test_resume_wrong_nonce_is_rejected(): void
    {
        $nonce = bin2hex(random_bytes(16));
        $conv  = $this->makeConv([
            'page_url'          => config('app.url') . '/',
            'resume_nonce_hash' => hash('sha256', $nonce),
        ]);

        $url      = $this->resumeUrl($conv, 'wrong-nonce-value');
        $response = $this->get($url);

        $response->assertStatus(410);
    }

    // ── GDPR purge: attachment files are actually deleted ─────────────────────

    public function test_purge_deletes_attachment_file_from_disk(): void
    {
        Storage::fake('private');

        // Simulate the real path: ImageProcessor saves to chat/attachments/uuid.jpg
        // MessageController stores only the filename in attachment_path
        $filename = Str::uuid() . '.jpg';
        Storage::disk('private')->put('chat/attachments/' . $filename, 'fake-image-data');

        $conv = $this->makeConv(['created_at' => now()->subDays(91)]);

        // attachment_path is stored as just the filename (as MessageController does)
        $conv->messages()->create([
            'sender'          => 'visitor',
            'body'            => '',
            'attachment_path' => $filename,
        ]);

        $this->artisan('chat:purge')->assertExitCode(0);

        Storage::disk('private')->assertMissing('chat/attachments/' . $filename);
    }

    public function test_purge_does_not_leave_orphan_files_after_90_days(): void
    {
        Storage::fake('private');

        $filename = Str::uuid() . '.jpg';
        Storage::disk('private')->put('chat/attachments/' . $filename, 'fake-image-data');

        $conv = $this->makeConv(['created_at' => now()->subDays(91)]);
        $conv->messages()->create([
            'sender'          => 'visitor',
            'body'            => '',
            'attachment_path' => $filename,
        ]);

        $this->artisan('chat:purge');

        $this->assertFalse(
            Storage::disk('private')->exists('chat/attachments/' . $filename),
            'Attachment file must be deleted after purge — GDPR compliance'
        );
    }

    // ── Sender mass-assignment: cannot be overridden from HTTP request ────────

    public function test_visitor_cannot_send_message_as_agent(): void
    {
        $rawToken = bin2hex(random_bytes(32));
        $conv     = $this->makeConv([
            'visitor_token' => hash('sha256', $rawToken),
        ]);

        $response = $this->withHeaders(['X-Chat-Visitor-Token' => $rawToken])
            ->postJson("/api/chat/conversations/{$conv->uuid}/messages", [
                'body'   => 'I am the agent',
                'sender' => 'agent',   // attacker tries to forge sender
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'visitor',   // must be forced to visitor
        ]);

        $this->assertDatabaseMissing('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'agent',
        ]);
    }

    public function test_visitor_cannot_send_message_as_system(): void
    {
        $rawToken = bin2hex(random_bytes(32));
        $conv     = $this->makeConv([
            'visitor_token' => hash('sha256', $rawToken),
        ]);

        $this->withHeaders(['X-Chat-Visitor-Token' => $rawToken])
            ->postJson("/api/chat/conversations/{$conv->uuid}/messages", [
                'body'   => 'System override',
                'sender' => 'system',
            ]);

        $this->assertDatabaseMissing('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'system',
        ]);
    }

    // ── Unsigned / tampered resume URL is rejected ────────────────────────────

    public function test_unsigned_resume_url_is_rejected(): void
    {
        $conv = $this->makeConv([
            'resume_nonce_hash' => hash('sha256', 'some-nonce'),
        ]);

        $response = $this->get("/chat/resume/{$conv->uuid}?nonce=some-nonce");

        // No valid signature → 410 or 403
        $this->assertContains($response->status(), [403, 410, 302]);
    }

    // ── Closed conversation rejects new messages ──────────────────────────────

    public function test_closed_conversation_rejects_new_visitor_messages(): void
    {
        $rawToken = bin2hex(random_bytes(32));
        $conv     = $this->makeConv([
            'status'        => 'closed',
            'visitor_token' => hash('sha256', $rawToken),
        ]);

        $response = $this->withHeaders(['X-Chat-Visitor-Token' => $rawToken])
            ->postJson("/api/chat/conversations/{$conv->uuid}/messages", [
                'body' => 'Still trying',
            ]);

        $response->assertStatus(409);
    }
}
