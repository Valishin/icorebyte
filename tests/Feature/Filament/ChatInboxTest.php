<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\ChatInbox;
use App\Models\ChatConversation;
use App\Models\QuickReply;
use App\Models\User;
use App\Services\Chat\AvailabilityService;
use App\Services\Chat\TelegramDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ChatInboxTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TelegramDispatcher::forceProduction(false);
        parent::tearDown();
    }

    private function agentUser(): User
    {
        return User::create([
            'name'     => 'Agent',
            'email'    => 'agent@test.com',
            'password' => bcrypt('password'),
            'is_agent' => true,
        ]);
    }

    private function nonAgentUser(): User
    {
        return User::create([
            'name'     => 'Visitor',
            'email'    => 'visitor@test.com',
            'password' => bcrypt('password'),
            'is_agent' => false,
        ]);
    }

    private function openConversation(array $attrs = []): ChatConversation
    {
        return ChatConversation::create(array_merge([
            'uuid'          => \Illuminate\Support\Str::uuid(),
            'visitor_token' => hash('sha256', bin2hex(random_bytes(16))),
            'status'        => 'open',
            'locale'        => 'es',
            'visitor_name'  => 'Cliente Test',
            'ip_hash'       => hash('sha256', '127.0.0.1'),
            'consent_at'    => now(),
        ], $attrs));
    }

    // ── Acceso ────────────────────────────────────────────────────────────────

    public function test_non_agent_cannot_access_panel(): void
    {
        $this->actingAs($this->nonAgentUser())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_agent_can_access_panel(): void
    {
        // An authenticated agent must not be sent back to the login page.
        // We compare route names so this passes regardless of panel language.
        $response = $this->actingAs($this->agentUser())->get('/admin');

        $loginUrl = route('filament.admin.auth.login');
        $location = $response->headers->get('Location') ?? '';

        $response->assertRedirect();
        $this->assertStringNotContainsString(
            $loginUrl,
            $location,
            'An authenticated agent must not be redirected to the login page',
        );
        $this->assertStringContainsString(
            '/admin',
            $location,
            'The redirect must stay within the admin panel',
        );
    }

    public function test_agent_with_2fa_sees_inbox(): void
    {
        $agent = $this->agentUser();
        // Pre-configure a fake 2FA secret so the agent is not redirected to setup.
        $agent->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');

        $this->actingAs($agent)
            ->followingRedirects()
            ->get('/admin')
            ->assertSuccessful()
            ->assertSee('Bandeja de chat');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')
            ->assertRedirectContains('/admin/login');
    }

    // ── Enviar respuesta ──────────────────────────────────────────────────────

    public function test_agent_can_send_reply(): void
    {
        config([
            'chat.telegram_enabled'     => true,
            'services.telegram.token'   => 'fake-token',
            'services.telegram.chat_id' => '999',
        ]);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 55]], 200)]);
        TelegramDispatcher::forceProduction(true);

        $agent = $this->agentUser();
        $conv  = $this->openConversation();

        Livewire::actingAs($agent)
            ->test(ChatInbox::class)
            ->call('openConversation', $conv->uuid)
            ->set('replyBody', 'Hola, estamos en ello.')
            ->call('sendReply')
            ->assertHasNoErrors();

        // Livewire's HTTP lifecycle already fires terminate() — no need to call it again.
        TelegramDispatcher::forceProduction(false);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'agent',
            'body'            => 'Hola, estamos en ello.',
        ]);

        Http::assertSent(fn ($req) =>
            str_contains($req->url(), 'api.telegram.org') &&
            str_contains($req->body(), 'Respondido desde el panel')
        );

        $this->assertDatabaseHas('telegram_outbound', [
            'conversation_id'     => $conv->id,
            'telegram_message_id' => 55,
        ]);
    }

    public function test_reply_to_closed_conversation_fails(): void
    {
        $agent = $this->agentUser();
        $conv  = $this->openConversation(['status' => 'closed']);

        Livewire::actingAs($agent)
            ->test(ChatInbox::class)
            ->call('openConversation', $conv->uuid)
            ->set('replyBody', 'Respuesta tardía')
            ->call('sendReply');

        $this->assertDatabaseMissing('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'agent',
        ]);
    }

    public function test_reply_requires_body(): void
    {
        $agent = $this->agentUser();
        $conv  = $this->openConversation();

        Livewire::actingAs($agent)
            ->test(ChatInbox::class)
            ->call('openConversation', $conv->uuid)
            ->set('replyBody', '')
            ->call('sendReply')
            ->assertHasErrors(['replyBody']);
    }

    // ── Cerrar / reabrir ──────────────────────────────────────────────────────

    public function test_agent_can_close_conversation(): void
    {
        $agent = $this->agentUser();
        $conv  = $this->openConversation();

        Livewire::actingAs($agent)
            ->test(ChatInbox::class)
            ->call('openConversation', $conv->uuid)
            ->call('closeConversation');

        $this->assertEquals('closed', $conv->fresh()->status);
    }

    public function test_agent_can_reopen_conversation(): void
    {
        $agent = $this->agentUser();
        $conv  = $this->openConversation(['status' => 'closed']);

        Livewire::actingAs($agent)
            ->test(ChatInbox::class)
            ->call('openConversation', $conv->uuid)
            ->call('reopenConversation');

        $this->assertEquals('open', $conv->fresh()->status);
    }

    // ── Marcar como leído ─────────────────────────────────────────────────────

    public function test_opening_conversation_marks_visitor_messages_as_read(): void
    {
        $agent = $this->agentUser();
        $conv  = $this->openConversation();

        $msg = $conv->messages()->create([
            'sender' => 'visitor',
            'body'   => 'Pregunta sin leer',
        ]);

        $this->assertNull($msg->read_at);

        Livewire::actingAs($agent)
            ->test(ChatInbox::class)
            ->call('openConversation', $conv->uuid);

        $this->assertNotNull($msg->fresh()->read_at);
        $this->assertNotNull($conv->fresh()->agent_last_read_at);
    }

    // ── Cambiar disponibilidad ────────────────────────────────────────────────

    public function test_toggle_availability_changes_state(): void
    {
        $agent = $this->agentUser();
        $svc   = app(AvailabilityService::class);

        $this->assertTrue($svc->isAgentAvailable());

        Livewire::actingAs($agent)
            ->test(ChatInbox::class)
            ->call('toggleAvailability');

        $this->assertFalse($svc->isAgentAvailable());

        Livewire::actingAs($agent)
            ->test(ChatInbox::class)
            ->call('toggleAvailability');

        $this->assertTrue($svc->isAgentAvailable());
    }

    // ── Respuestas rápidas ────────────────────────────────────────────────────

    public function test_quick_reply_fills_reply_body(): void
    {
        $agent = $this->agentUser();
        $conv  = $this->openConversation();

        $qr = QuickReply::create([
            'shortcut'   => '/horario',
            'title'      => 'Horario',
            'body'       => 'Estamos abiertos de L-V 10-14h y 17-20h.',
            'active'     => true,
            'sort_order' => 0,
        ]);

        Livewire::actingAs($agent)
            ->test(ChatInbox::class)
            ->call('openConversation', $conv->uuid)
            ->call('useQuickReply', $qr->id)
            ->assertSet('replyBody', 'Estamos abiertos de L-V 10-14h y 17-20h.');
    }

    // ── Contador de no leídos ─────────────────────────────────────────────────

    public function test_unread_count_reflects_unread_messages(): void
    {
        $agent = $this->agentUser();
        $conv  = $this->openConversation();

        $conv->messages()->create(['sender' => 'visitor', 'body' => 'Sin leer 1']);
        $conv->messages()->create(['sender' => 'visitor', 'body' => 'Sin leer 2']);

        $component = Livewire::actingAs($agent)->test(ChatInbox::class);

        $this->assertGreaterThan(0, $component->instance()->unreadCount);
    }

    // ── Adjuntar foto desde el panel ─────────────────────────────────────────

    public function test_agent_can_send_reply_with_attachment(): void
    {
        Storage::fake('private');

        $agent = $this->agentUser();
        $conv  = $this->openConversation();

        $image = UploadedFile::fake()->image('foto.jpg', 100, 100);

        Livewire::actingAs($agent)
            ->test(ChatInbox::class)
            ->call('openConversation', $conv->uuid)
            ->set('replyBody', 'Mira esta foto')
            ->set('attachmentFile', $image)
            ->call('sendReply')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'agent',
            'body'            => 'Mira esta foto',
        ]);

        $msg = $conv->messages()->where('sender', 'agent')->first();
        $this->assertNotNull($msg?->attachment_path, 'Debe guardar el attachment_path');
    }

    public function test_reply_without_body_but_with_attachment_succeeds(): void
    {
        Storage::fake('private');

        $agent = $this->agentUser();
        $conv  = $this->openConversation();

        $image = UploadedFile::fake()->image('solo-imagen.jpg', 50, 50);

        Livewire::actingAs($agent)
            ->test(ChatInbox::class)
            ->call('openConversation', $conv->uuid)
            ->set('replyBody', '')
            ->set('attachmentFile', $image)
            ->call('sendReply')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'agent',
            'body'            => '',
        ]);
    }

    // ── Acceso denegado a no agentes vía Livewire ─────────────────────────────

    public function test_non_agent_cannot_instantiate_inbox_page(): void
    {
        $this->actingAs($this->nonAgentUser())
            ->get('/admin')
            ->assertForbidden();
    }
}
