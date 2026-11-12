<?php

namespace Tests\Feature\Chat;

use App\Jobs\Chat\SendConversationNotificationJob;
use App\Mail\Chat\ConversationNotificationMailable;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ConversationNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function conv(array $attrs = []): ChatConversation
    {
        $rawToken = bin2hex(random_bytes(16));

        return ChatConversation::create(array_merge([
            'uuid'          => \Illuminate\Support\Str::uuid(),
            'visitor_token' => hash('sha256', $rawToken),
            'status'        => 'open',
            'locale'        => 'es',
            'visitor_name'  => 'Test User',
            'visitor_email' => 'visitor@example.com',
            'ip_hash'       => hash('sha256', '127.0.0.1'),
            'consent_at'    => now(),
        ], $attrs));
    }

    private function agentMessage(ChatConversation $conv, string $body = 'Hola'): ChatMessage
    {
        return $conv->messages()->create(['sender' => 'agent', 'body' => $body]);
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_email_sent_when_visitor_has_email_and_not_seen_recently(): void
    {
        Mail::fake();

        $conv = $this->conv(['visitor_last_read_at' => now()->subMinutes(15)]);
        $msg  = $this->agentMessage($conv);

        (new SendConversationNotificationJob($conv, $msg))->handle();

        Mail::assertSent(ConversationNotificationMailable::class, function ($mail) use ($conv) {
            return $mail->hasTo((string) $conv->visitor_email);
        });

        $this->assertNotNull($conv->fresh()->notification_email_sent_at);
    }

    public function test_email_sent_when_visitor_has_never_read(): void
    {
        Mail::fake();

        $conv = $this->conv(['visitor_last_read_at' => null]);
        $msg  = $this->agentMessage($conv);

        (new SendConversationNotificationJob($conv, $msg))->handle();

        Mail::assertSent(ConversationNotificationMailable::class);
    }

    public function test_job_stores_nonce_hash_before_sending(): void
    {
        Mail::fake();

        $conv = $this->conv(['visitor_last_read_at' => null]);
        $msg  = $this->agentMessage($conv);

        (new SendConversationNotificationJob($conv, $msg))->handle();

        $this->assertNotNull($conv->fresh()->resume_nonce_hash, 'nonce hash must be stored');
    }

    // ── Conditions that SKIP the email ────────────────────────────────────────

    public function test_no_email_when_visitor_has_no_email(): void
    {
        Mail::fake();

        $conv = $this->conv(['visitor_email' => null]);
        $msg  = $this->agentMessage($conv);

        (new SendConversationNotificationJob($conv, $msg))->handle();

        Mail::assertNotSent(ConversationNotificationMailable::class);
    }

    public function test_no_email_when_visitor_read_recently(): void
    {
        Mail::fake();

        $conv = $this->conv(['visitor_last_read_at' => now()->subMinutes(5)]);
        $msg  = $this->agentMessage($conv);

        (new SendConversationNotificationJob($conv, $msg))->handle();

        Mail::assertNotSent(ConversationNotificationMailable::class);
    }

    public function test_no_second_email_within_throttle_window(): void
    {
        Mail::fake();

        $conv = $this->conv([
            'visitor_last_read_at'       => now()->subMinutes(15),
            'notification_email_sent_at' => now()->subMinutes(30),
        ]);
        $msg = $this->agentMessage($conv);

        (new SendConversationNotificationJob($conv, $msg))->handle();

        Mail::assertNotSent(ConversationNotificationMailable::class);
    }

    public function test_email_sent_after_throttle_window_expires(): void
    {
        Mail::fake();

        $conv = $this->conv([
            'visitor_last_read_at'       => now()->subMinutes(15),
            'notification_email_sent_at' => now()->subHours(2),
        ]);
        $msg = $this->agentMessage($conv);

        (new SendConversationNotificationJob($conv, $msg))->handle();

        Mail::assertSent(ConversationNotificationMailable::class);
    }

    // ── Resume URL security ───────────────────────────────────────────────────

    public function test_resume_url_contains_no_visitor_token(): void
    {
        $conv        = $this->conv();
        $msg         = $this->agentMessage($conv, 'Tienes una respuesta');
        $plainNonce  = bin2hex(random_bytes(32));

        $mailable = new ConversationNotificationMailable($conv, $msg, $plainNonce);

        // Token must NOT appear in the URL — only the nonce and Laravel signature params
        $this->assertStringNotContainsString(
            (string) $conv->visitor_token,
            $mailable->resumeUrl,
            'visitor_token hash must not appear in the resume URL',
        );
    }

    public function test_resume_url_uses_named_route_with_uuid_in_path(): void
    {
        $conv       = $this->conv();
        $msg        = $this->agentMessage($conv);
        $plainNonce = bin2hex(random_bytes(32));

        $mailable = new ConversationNotificationMailable($conv, $msg, $plainNonce);

        $this->assertStringContainsString('/chat/resume/' . $conv->uuid, $mailable->resumeUrl);
        $this->assertStringContainsString('signature=', $mailable->resumeUrl);
        $this->assertStringContainsString('nonce=', $mailable->resumeUrl);
    }

    // ── Resume route — valid flow ─────────────────────────────────────────────

    public function test_valid_resume_link_issues_new_token_in_fragment(): void
    {
        $conv       = $this->conv(['page_url' => 'http://localhost/']);
        $plainNonce = bin2hex(random_bytes(32));
        $conv->update(['resume_nonce_hash' => hash('sha256', $plainNonce)]);
        $msg = $this->agentMessage($conv);

        $mailable = new ConversationNotificationMailable($conv, $msg, $plainNonce);

        $response = $this->get($mailable->resumeUrl);

        $response->assertRedirect();
        $location = $response->headers->get('Location') ?? '';

        // Token must be in the fragment, NOT in the query string
        $this->assertStringContainsString('#chat=', $location, 'token must be in URL fragment');
        $this->assertStringNotContainsString('?chat', $location, 'token must NOT be in query string');
        $this->assertStringNotContainsString('token=', $location, 'token must NOT appear as query param');
    }

    public function test_valid_resume_link_sets_security_headers(): void
    {
        $conv       = $this->conv(['page_url' => 'http://localhost/']);
        $plainNonce = bin2hex(random_bytes(32));
        $conv->update(['resume_nonce_hash' => hash('sha256', $plainNonce)]);
        $msg      = $this->agentMessage($conv);
        $mailable = new ConversationNotificationMailable($conv, $msg, $plainNonce);

        $response = $this->get($mailable->resumeUrl);

        $response->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control') ?? '');
    }

    public function test_valid_resume_link_stores_new_token_hash(): void
    {
        $conv       = $this->conv(['page_url' => 'http://localhost/']);
        $plainNonce = bin2hex(random_bytes(32));
        $conv->update(['resume_nonce_hash' => hash('sha256', $plainNonce)]);
        $msg      = $this->agentMessage($conv);
        $mailable = new ConversationNotificationMailable($conv, $msg, $plainNonce);

        $this->get($mailable->resumeUrl);

        $fresh = $conv->fresh();
        $this->assertNull($fresh->resume_nonce_hash, 'nonce must be invalidated after use');
        $this->assertNotNull($fresh->resume_token_hash, 'new token hash must be stored');
    }

    // ── Resume route — rejection cases ────────────────────────────────────────

    public function test_resume_link_reused_is_rejected(): void
    {
        $conv       = $this->conv(['page_url' => 'http://localhost/']);
        $plainNonce = bin2hex(random_bytes(32));
        $conv->update(['resume_nonce_hash' => hash('sha256', $plainNonce)]);
        $msg      = $this->agentMessage($conv);
        $mailable = new ConversationNotificationMailable($conv, $msg, $plainNonce);
        $url      = $mailable->resumeUrl;

        $this->get($url);                        // first use — succeeds
        $second = $this->get($url);              // second use — nonce now null

        // Should show the error page, not redirect
        $second->assertStatus(410);
    }

    public function test_resume_link_with_tampered_signature_is_rejected(): void
    {
        $response = $this->get('/chat/resume/fake-uuid?nonce=abc&expires=9999999999&signature=tampered');
        $response->assertStatus(410);
    }

    public function test_resume_link_with_wrong_nonce_is_rejected(): void
    {
        $conv       = $this->conv(['page_url' => 'http://localhost/']);
        $plainNonce = bin2hex(random_bytes(32));
        $conv->update(['resume_nonce_hash' => hash('sha256', $plainNonce)]);
        $msg      = $this->agentMessage($conv);

        // Build a valid-signature URL but with the wrong nonce
        $badNonce = bin2hex(random_bytes(32));
        $mailable = new ConversationNotificationMailable($conv, $msg, $badNonce);

        $response = $this->get($mailable->resumeUrl);
        $response->assertStatus(410);
    }

    // ── New token is usable for auth ──────────────────────────────────────────

    public function test_new_token_from_resume_authenticates_successfully(): void
    {
        $conv       = $this->conv(['page_url' => 'http://localhost/']);
        $plainNonce = bin2hex(random_bytes(32));
        $conv->update(['resume_nonce_hash' => hash('sha256', $plainNonce)]);
        $msg      = $this->agentMessage($conv);
        $mailable = new ConversationNotificationMailable($conv, $msg, $plainNonce);

        $response = $this->get($mailable->resumeUrl);
        $location = $response->headers->get('Location') ?? '';

        // Extract new token from fragment (#chat=uuid.token)
        preg_match('/#chat=[^.]+\.([0-9a-f]+)$/', $location, $matches);
        $newToken = $matches[1] ?? null;
        $this->assertNotNull($newToken, 'new token not found in fragment');

        // New token must work for authenticated API calls
        $this->withHeaders(['X-Chat-Visitor-Token' => $newToken])
            ->getJson("/api/chat/conversations/{$conv->uuid}/messages")
            ->assertOk();
    }

    public function test_original_token_still_valid_after_resume(): void
    {
        // The original device (device A) must not be logged out
        $rawToken   = bin2hex(random_bytes(32));
        $conv       = ChatConversation::create([
            'uuid'          => \Illuminate\Support\Str::uuid(),
            'visitor_token' => hash('sha256', $rawToken),
            'status'        => 'open',
            'locale'        => 'es',
            'visitor_name'  => 'Device A',
            'visitor_email' => 'a@example.com',
            'ip_hash'       => hash('sha256', '1.2.3.4'),
            'consent_at'    => now(),
            'page_url'      => 'http://localhost/',
        ]);

        // Simulate resume flow
        $plainNonce = bin2hex(random_bytes(32));
        $conv->update(['resume_nonce_hash' => hash('sha256', $plainNonce)]);
        $msg      = $conv->messages()->create(['sender' => 'agent', 'body' => 'Hola']);
        $mailable = new ConversationNotificationMailable($conv, $msg, $plainNonce);
        $this->get($mailable->resumeUrl);

        // Original token must still work
        $this->withHeaders(['X-Chat-Visitor-Token' => $rawToken])
            ->getJson("/api/chat/conversations/{$conv->uuid}/messages")
            ->assertOk();
    }
}
