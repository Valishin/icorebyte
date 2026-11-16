<?php

namespace Tests\Feature\Chat;

use App\Jobs\Chat\SendTelegramMessageJob;
use App\Models\ChatConversation;
use App\Services\Chat\TelegramDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Verifica el camino de producción del dispatcher:
 *  - Éxito  → TelegramOutbound creado, ningún job encolado.
 *  - Fallo  → job reencolado en database, ningún TelegramOutbound.
 *  - Excepción → job reencolado, ningún TelegramOutbound.
 *
 * Forzamos el camino de producción con TelegramDispatcher::forceProduction(true)
 * y disparamos los callbacks de terminación con app()->terminate().
 */
class TelegramDispatcherProductionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'chat.telegram_enabled'     => true,
            'services.telegram.token'   => 'fake-token',
            'services.telegram.chat_id' => '123456789',
        ]);
        Queue::fake();
        TelegramDispatcher::forceProduction(true);
    }

    protected function tearDown(): void
    {
        TelegramDispatcher::forceProduction(false);
        parent::tearDown();
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

    // ── Éxito: Telegram responde OK ───────────────────────────────────────────

    public function test_success_creates_outbound_record_and_does_not_queue(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response([
                'ok'     => true,
                'result' => ['message_id' => 42],
            ], 200),
        ]);

        $conv = $this->openConversation();

        app(TelegramDispatcher::class)->sendText(
            'Hola desde producción',
            [],
            $conv->id,
            TelegramDispatcher::TIPO_NEW_CONV,
        );

        // El envío ocurre en el callback de terminación
        app()->terminate();

        $this->assertDatabaseHas('telegram_outbound', [
            'telegram_message_id' => 42,
            'conversation_id'     => $conv->id,
            'tipo'                => TelegramDispatcher::TIPO_NEW_CONV,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_success_saves_chat_message_id_in_outbound(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response([
                'ok'     => true,
                'result' => ['message_id' => 99],
            ], 200),
        ]);

        $conv    = $this->openConversation();
        $message = $conv->messages()->create(['sender' => 'visitor', 'body' => 'Test']);

        app(TelegramDispatcher::class)->sendText(
            'Nuevo mensaje',
            [],
            $conv->id,
            TelegramDispatcher::TIPO_VISITOR_MSG,
            $message->id,
        );

        app()->terminate();

        $this->assertDatabaseHas('telegram_outbound', [
            'telegram_message_id' => 99,
            'conversation_id'     => $conv->id,
            'chat_message_id'     => $message->id,
            'tipo'                => TelegramDispatcher::TIPO_VISITOR_MSG,
        ]);

        Queue::assertNothingPushed();
    }

    // ── Fallo: Telegram responde ok=false ─────────────────────────────────────

    public function test_failure_queues_job_and_does_not_create_outbound(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'Bad Request'], 400),
        ]);

        $conv = $this->openConversation();

        app(TelegramDispatcher::class)->sendText(
            'Mensaje que fallará',
            [],
            $conv->id,
            TelegramDispatcher::TIPO_NEW_CONV,
        );

        app()->terminate();

        Queue::assertPushed(SendTelegramMessageJob::class);
        $this->assertDatabaseMissing('telegram_outbound', ['conversation_id' => $conv->id]);
    }

    // ── Excepción: conexión rechazada ─────────────────────────────────────────

    public function test_exception_queues_job_and_does_not_create_outbound(): void
    {
        Http::fake([
            'api.telegram.org/*' => fn () => throw new \RuntimeException('Connection refused'),
        ]);

        $conv = $this->openConversation();

        app(TelegramDispatcher::class)->sendText(
            'Mensaje con excepción',
            [],
            $conv->id,
            TelegramDispatcher::TIPO_NEW_CONV,
        );

        app()->terminate();

        Queue::assertPushed(SendTelegramMessageJob::class);
        $this->assertDatabaseMissing('telegram_outbound', ['conversation_id' => $conv->id]);
    }

    // ── Re-encola con los parámetros correctos ────────────────────────────────

    public function test_queued_job_has_correct_parameters_on_failure(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => false], 400),
        ]);

        $conv = $this->openConversation();

        app(TelegramDispatcher::class)->sendText(
            'Texto de prueba',
            ['reply_markup' => '{}'],
            $conv->id,
            TelegramDispatcher::TIPO_VISITOR_MSG,
            null,
        );

        app()->terminate();

        Queue::assertPushed(SendTelegramMessageJob::class, function (SendTelegramMessageJob $job) use ($conv): bool {
            $r = new \ReflectionObject($job);

            $getText = fn (string $prop) => tap($r->getProperty($prop), fn ($p) => $p->setAccessible(true))->getValue($job);

            return $getText('text') === 'Texto de prueba'
                && $getText('conversationId') === $conv->id
                && $getText('tipo') === TelegramDispatcher::TIPO_VISITOR_MSG;
        });
    }

    // ── telegram_enabled = false → nada ocurre ────────────────────────────────

    public function test_disabled_telegram_does_not_send_or_queue(): void
    {
        config(['chat.telegram_enabled' => false]);

        Http::fake();
        $conv = $this->openConversation();

        app(TelegramDispatcher::class)->sendText('Hola', [], $conv->id, TelegramDispatcher::TIPO_NEW_CONV);
        app()->terminate();

        Http::assertNothingSent();
        Queue::assertNothingPushed();
    }
}
