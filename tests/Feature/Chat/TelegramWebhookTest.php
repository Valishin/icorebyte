<?php

namespace Tests\Feature\Chat;

use App\Jobs\Chat\SendTelegramMessageJob;
use App\Models\ChatConversation;
use App\Models\TelegramOutbound;
use App\Models\TelegramUpdate;
use App\Services\Chat\AvailabilityService;
use App\Services\Chat\TelegramDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TelegramWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-webhook-secret-1234567890ab';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.telegram.webhook_secret' => self::SECRET,
            'services.telegram.chat_id'         => '123456789',
            'chat.telegram_enabled'             => true,
        ]);
        Queue::fake();
    }

    private function webhook(array $update, string $secret = self::SECRET): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(['X-Telegram-Bot-Api-Secret-Token' => $secret])
            ->postJson('/api/chat/telegram/webhook', $update);
    }

    private function openConversation(): ChatConversation
    {
        return ChatConversation::create([
            'uuid'          => \Illuminate\Support\Str::uuid(),
            'visitor_token' => hash('sha256', bin2hex(random_bytes(16))),
            'status'        => 'open',
            'locale'        => 'es',
            'visitor_name'  => 'Test',
            'ip_hash'       => hash('sha256', '127.0.0.1'),
            'consent_at'    => now(),
        ]);
    }

    /**
     * Crea un registro en telegram_outbound simulando que el bot ya envió
     * un mensaje con ese telegram_message_id para la conversación dada.
     */
    private function outbound(ChatConversation $conv, int $tgMsgId, string $tipo = 'visitor_message'): TelegramOutbound
    {
        return TelegramOutbound::create([
            'telegram_message_id' => $tgMsgId,
            'conversation_id'     => $conv->id,
            'chat_message_id'     => null,
            'tipo'                => $tipo,
        ]);
    }

    // ── Autenticación ─────────────────────────────────────────────────────────

    public function test_wrong_secret_returns_200_silently(): void
    {
        $response = $this->webhook(['update_id' => 1], 'wrong-secret');
        $response->assertStatus(200);
        Queue::assertNothingPushed();
    }

    public function test_missing_secret_returns_200_silently(): void
    {
        $response = $this->postJson('/api/chat/telegram/webhook', ['update_id' => 1]);
        $response->assertStatus(200);
        Queue::assertNothingPushed();
    }

    public function test_message_from_wrong_chat_id_is_ignored(): void
    {
        $this->webhook([
            'update_id' => 100,
            'message'   => [
                'message_id' => 1,
                'chat'       => ['id' => '999999999'],
                'text'       => '/disponible',
            ],
        ])->assertStatus(200);
        Queue::assertNothingPushed();
    }

    // ── chat_id en callback_query ─────────────────────────────────────────────

    public function test_callback_query_from_wrong_chat_id_is_ignored(): void
    {
        $conv = $this->openConversation();

        $this->webhook([
            'update_id'      => 150,
            'callback_query' => [
                'id'      => 'cbq_evil',
                'data'    => "close:{$conv->uuid}",
                'message' => [
                    'message_id' => 15,
                    'chat'       => ['id' => '999999999'], // chat_id incorrecto
                ],
            ],
        ])->assertStatus(200);

        // La conversación NO debe cerrarse
        $this->assertEquals('open', $conv->fresh()->status);
    }

    // ── Idempotencia ──────────────────────────────────────────────────────────

    public function test_duplicate_update_id_is_ignored(): void
    {
        TelegramUpdate::create(['update_id' => 42]);

        $conv = $this->openConversation();
        $this->outbound($conv, 10);

        $this->webhook([
            'update_id' => 42,
            'message'   => [
                'message_id'       => 20,
                'chat'             => ['id' => '123456789'],
                'reply_to_message' => ['message_id' => 10],
                'text'             => 'Hola visitante',
            ],
        ])->assertStatus(200);

        $this->assertDatabaseMissing('chat_messages', ['sender' => 'agent', 'body' => 'Hola visitante']);
    }

    // ── Respuestas del agente ─────────────────────────────────────────────────

    public function test_agent_reply_creates_agent_message(): void
    {
        $conv = $this->openConversation();
        $this->outbound($conv, 10);

        $this->webhook([
            'update_id' => 200,
            'message'   => [
                'message_id'       => 20,
                'chat'             => ['id' => '123456789'],
                'reply_to_message' => ['message_id' => 10],
                'text'             => 'Estamos en ello, en 5 minutos te llamamos.',
            ],
        ])->assertStatus(200);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'agent',
            'body'            => 'Estamos en ello, en 5 minutos te llamamos.',
        ]);
        $this->assertNotNull($conv->fresh()->last_agent_message_at);
    }

    public function test_reply_to_new_conversation_notification_reaches_correct_visitor(): void
    {
        // Simulamos que el bot envió la notificación de nueva conversación
        // con telegram_message_id = 100 para la conversación de María
        $maria = $this->openConversation();
        $this->outbound($maria, 100, TelegramDispatcher::TIPO_NEW_CONV);

        // Segunda conversación (no debe recibir el mensaje)
        $juan = $this->openConversation();
        $this->outbound($juan, 200, TelegramDispatcher::TIPO_NEW_CONV);

        // El agente responde al mensaje 100 (notificación de María)
        $this->webhook([
            'update_id' => 300,
            'message'   => [
                'message_id'       => 301,
                'chat'             => ['id' => '123456789'],
                'reply_to_message' => ['message_id' => 100],
                'text'             => 'Hola María, te atendemos ahora.',
            ],
        ])->assertStatus(200);

        // Solo María recibe el mensaje
        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $maria->id,
            'sender'          => 'agent',
            'body'            => 'Hola María, te atendemos ahora.',
        ]);
        $this->assertDatabaseMissing('chat_messages', [
            'conversation_id' => $juan->id,
            'sender'          => 'agent',
        ]);
    }

    public function test_agent_reply_to_unknown_message_queues_warning(): void
    {
        $this->webhook([
            'update_id' => 300,
            'message'   => [
                'message_id'       => 30,
                'chat'             => ['id' => '123456789'],
                'reply_to_message' => ['message_id' => 9999],
                'text'             => 'esto a quién va',
            ],
        ])->assertStatus(200);

        Queue::assertPushed(SendTelegramMessageJob::class);
    }

    public function test_agent_reply_to_closed_conversation_queues_warning(): void
    {
        $conv = $this->openConversation();
        $conv->update(['status' => 'closed']);
        $this->outbound($conv, 50);

        $this->webhook([
            'update_id' => 400,
            'message'   => [
                'message_id'       => 40,
                'chat'             => ['id' => '123456789'],
                'reply_to_message' => ['message_id' => 50],
                'text'             => 'Hola',
            ],
        ])->assertStatus(200);

        Queue::assertPushed(SendTelegramMessageJob::class);
        $this->assertDatabaseMissing('chat_messages', ['sender' => 'agent', 'body' => 'Hola']);
    }

    // ── /disponible y /ausente ────────────────────────────────────────────────

    public function test_disponible_command_sets_agent_available(): void
    {
        $svc = app(AvailabilityService::class);
        $svc->setAgentAvailable(false);

        $this->webhook([
            'update_id' => 500,
            'message'   => ['message_id' => 50, 'chat' => ['id' => '123456789'], 'text' => '/disponible'],
        ])->assertStatus(200);

        $this->assertTrue($svc->isAgentAvailable());
        Queue::assertPushed(SendTelegramMessageJob::class);
    }

    public function test_ausente_command_sets_agent_unavailable(): void
    {
        $this->webhook([
            'update_id' => 600,
            'message'   => ['message_id' => 60, 'chat' => ['id' => '123456789'], 'text' => '/ausente'],
        ])->assertStatus(200);

        $this->assertFalse(app(AvailabilityService::class)->isAgentAvailable());
        Queue::assertPushed(SendTelegramMessageJob::class);
    }

    // ── /abiertas ─────────────────────────────────────────────────────────────

    public function test_abiertas_command_lists_open_conversations(): void
    {
        $this->openConversation();
        $this->openConversation();

        $this->webhook([
            'update_id' => 700,
            'message'   => ['message_id' => 70, 'chat' => ['id' => '123456789'], 'text' => '/abiertas'],
        ])->assertStatus(200);

        Queue::assertPushed(SendTelegramMessageJob::class, function ($job) {
            $r    = new \ReflectionObject($job);
            $prop = $r->getProperty('text');
            $prop->setAccessible(true);
            return str_contains($prop->getValue($job), 'Conversaciones abiertas');
        });
    }

    public function test_abiertas_command_with_no_open_conversations(): void
    {
        $this->webhook([
            'update_id' => 701,
            'message'   => ['message_id' => 71, 'chat' => ['id' => '123456789'], 'text' => '/abiertas'],
        ])->assertStatus(200);

        Queue::assertPushed(SendTelegramMessageJob::class, function ($job) {
            $r    = new \ReflectionObject($job);
            $prop = $r->getProperty('text');
            $prop->setAccessible(true);
            return str_contains($prop->getValue($job), 'No hay conversaciones');
        });
    }

    // ── /cerrar por reply ─────────────────────────────────────────────────────

    public function test_cerrar_command_by_reply_closes_conversation(): void
    {
        $conv = $this->openConversation();
        $this->outbound($conv, 80);

        $this->webhook([
            'update_id' => 800,
            'message'   => [
                'message_id'       => 81,
                'chat'             => ['id' => '123456789'],
                'reply_to_message' => ['message_id' => 80],
                'text'             => '/cerrar',
            ],
        ])->assertStatus(200);

        $this->assertEquals('closed', $conv->fresh()->status);
        Queue::assertPushed(SendTelegramMessageJob::class);
    }

    public function test_cerrar_command_on_already_closed_conversation(): void
    {
        $conv = $this->openConversation();
        $conv->update(['status' => 'closed']);
        $this->outbound($conv, 90);

        $this->webhook([
            'update_id' => 900,
            'message'   => [
                'message_id'       => 91,
                'chat'             => ['id' => '123456789'],
                'reply_to_message' => ['message_id' => 90],
                'text'             => '/cerrar',
            ],
        ])->assertStatus(200);

        // Sigue cerrada y el bot avisa
        $this->assertEquals('closed', $conv->fresh()->status);
        Queue::assertPushed(SendTelegramMessageJob::class);
    }

    // ── Callback — botón Cerrar ───────────────────────────────────────────────

    public function test_close_callback_closes_conversation(): void
    {
        $conv = $this->openConversation();

        $this->webhook([
            'update_id'      => 1000,
            'callback_query' => [
                'id'      => 'cbq123',
                'data'    => "close:{$conv->uuid}",
                'message' => ['message_id' => 100, 'chat' => ['id' => '123456789']],
            ],
        ])->assertStatus(200);

        $this->assertEquals('closed', $conv->fresh()->status);
    }

    // ── Mensaje directo ───────────────────────────────────────────────────────

    public function test_direct_message_without_reply_queues_help_text(): void
    {
        $this->webhook([
            'update_id' => 1100,
            'message'   => ['message_id' => 110, 'chat' => ['id' => '123456789'], 'text' => 'hola bot'],
        ])->assertStatus(200);

        Queue::assertPushed(SendTelegramMessageJob::class);
    }

    // ── Notificación de nueva conversación ────────────────────────────────────

    public function test_new_conversation_dispatches_telegram_notification(): void
    {
        $this->mock(AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isOnline')->andReturn(true);
            $mock->shouldReceive('isOpen')->andReturn(true);
            $mock->shouldReceive('isAgentAvailable')->andReturn(true);
            $mock->shouldReceive('status')->andReturn([
                'is_open' => true, 'is_online' => true, 'next_open_text' => '', 'timezone' => 'Europe/Madrid',
            ]);
        });

        $this->postJson('/api/chat/conversations', [
            'visitor_name' => 'María',
            'consent'      => '1',
        ])->assertStatus(201);

        Queue::assertPushed(SendTelegramMessageJob::class, function ($job) {
            $r = new \ReflectionObject($job);
            $p = $r->getProperty('text');
            $p->setAccessible(true);
            return str_contains($p->getValue($job), 'María');
        });
    }

    // ── Notificación de mensaje del visitante ─────────────────────────────────

    public function test_visitor_message_dispatches_telegram_notification(): void
    {
        $rawToken = bin2hex(random_bytes(32));
        $conv     = ChatConversation::create([
            'uuid'          => \Illuminate\Support\Str::uuid(),
            'visitor_token' => hash('sha256', $rawToken),
            'status'        => 'open',
            'locale'        => 'es',
            'visitor_name'  => 'Cliente',
            'ip_hash'       => hash('sha256', '127.0.0.1'),
            'consent_at'    => now(),
        ]);

        $this->withHeaders(['X-Chat-Visitor-Token' => $rawToken])
            ->postJson("/api/chat/conversations/{$conv->uuid}/messages", [
                'body' => 'Tengo una pregunta sobre mi pedido',
            ])->assertStatus(201);

        Queue::assertPushed(SendTelegramMessageJob::class);
    }

    // ── Escaping HTML ─────────────────────────────────────────────────────────

    public function test_visitor_name_with_html_is_escaped_in_notification(): void
    {
        $this->mock(AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isOnline')->andReturn(true);
            $mock->shouldReceive('isOpen')->andReturn(true);
            $mock->shouldReceive('isAgentAvailable')->andReturn(true);
            $mock->shouldReceive('status')->andReturn([
                'is_open' => true, 'is_online' => true, 'next_open_text' => '', 'timezone' => 'Europe/Madrid',
            ]);
        });

        $this->postJson('/api/chat/conversations', [
            'visitor_name' => '<script>alert(1)</script>',
            'consent'      => '1',
        ])->assertStatus(201);

        Queue::assertPushed(SendTelegramMessageJob::class, function ($job) {
            $r    = new \ReflectionObject($job);
            $prop = $r->getProperty('text');
            $prop->setAccessible(true);
            $text = $prop->getValue($job);
            return ! str_contains($text, '<script>') && str_contains($text, '&lt;script&gt;');
        });
    }

    // ── TelegramOutbound — el job crea el registro ────────────────────────────

    public function test_outbound_record_created_by_dispatcher_in_tests(): void
    {
        // En tests el dispatcher va a la cola. Simulamos que el job corre
        // creando el registro manualmente (como haría el job en producción).
        $conv = $this->openConversation();

        TelegramOutbound::create([
            'telegram_message_id' => 999,
            'conversation_id'     => $conv->id,
            'chat_message_id'     => null,
            'tipo'                => TelegramDispatcher::TIPO_NEW_CONV,
        ]);

        // El webhook puede usar ese registro para resolver la conversación
        $this->webhook([
            'update_id' => 2000,
            'message'   => [
                'message_id'       => 2001,
                'chat'             => ['id' => '123456789'],
                'reply_to_message' => ['message_id' => 999],
                'text'             => 'Respuesta al aviso de nueva conversación',
            ],
        ])->assertStatus(200);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conv->id,
            'sender'          => 'agent',
            'body'            => 'Respuesta al aviso de nueva conversación',
        ]);
    }
}
