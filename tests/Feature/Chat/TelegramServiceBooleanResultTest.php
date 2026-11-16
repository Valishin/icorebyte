<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\TelegramService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Verifica que TelegramService gestiona correctamente las respuestas de la API
 * de Telegram que devuelven result: true (booleano) en lugar de un objeto.
 *
 * Métodos afectados: setWebhook, deleteWebhook, deleteMessage, answerCallbackQuery.
 */
class TelegramServiceBooleanResultTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.telegram.token'   => 'fake-token',
            'services.telegram.chat_id' => '123456789',
        ]);
    }

    // ── setWebhook ────────────────────────────────────────────────────────────

    public function test_set_webhook_returns_true_when_api_returns_boolean_result(): void
    {
        Http::fake([
            'api.telegram.org/botfake-token/setWebhook' => Http::response([
                'ok'          => true,
                'result'      => true,
                'description' => 'Webhook was set',
            ], 200),
        ]);

        $service = new TelegramService();
        $result  = $service->setWebhook('https://example.com/webhook', 'secret123');

        $this->assertTrue($result);
    }

    public function test_set_webhook_returns_false_when_api_returns_ok_false(): void
    {
        Http::fake([
            'api.telegram.org/botfake-token/setWebhook' => Http::response([
                'ok'          => false,
                'description' => 'Bad Request: bad webhook: HTTPS url must be provided for webhook',
            ], 400),
        ]);

        $service = new TelegramService();
        $result  = $service->setWebhook('http://insecure.com/webhook', 'secret123');

        $this->assertFalse($result);
    }

    // ── deleteWebhook ─────────────────────────────────────────────────────────

    public function test_delete_webhook_returns_true_when_api_returns_boolean_result(): void
    {
        Http::fake([
            'api.telegram.org/botfake-token/deleteWebhook' => Http::response([
                'ok'          => true,
                'result'      => true,
                'description' => 'Webhook was deleted',
            ], 200),
        ]);

        $service = new TelegramService();
        $result  = $service->deleteWebhook();

        $this->assertTrue($result);
    }

    // ── deleteMessage ─────────────────────────────────────────────────────────

    public function test_delete_message_returns_empty_array_when_api_returns_boolean_result(): void
    {
        Http::fake([
            'api.telegram.org/botfake-token/deleteMessage' => Http::response([
                'ok'     => true,
                'result' => true,
            ], 200),
        ]);

        $service = new TelegramService();
        $result  = $service->deleteMessage(999);

        // [] !== null → callers interpretan esto como éxito
        $this->assertSame([], $result);
    }

    public function test_delete_message_returns_null_when_api_returns_ok_false(): void
    {
        Http::fake([
            'api.telegram.org/botfake-token/deleteMessage' => Http::response([
                'ok'          => false,
                'description' => 'Bad Request: message to delete not found',
            ], 400),
        ]);

        $service = new TelegramService();
        $result  = $service->deleteMessage(404);

        $this->assertNull($result);
    }

    // ── answerCallbackQuery ───────────────────────────────────────────────────

    public function test_answer_callback_query_does_not_throw_when_api_returns_boolean_result(): void
    {
        Http::fake([
            'api.telegram.org/botfake-token/answerCallbackQuery' => Http::response([
                'ok'     => true,
                'result' => true,
            ], 200),
        ]);

        $service = new TelegramService();

        // Void method — solo verificamos que no lanza excepción
        $service->answerCallbackQuery('cbq-id-123', 'Hecho');

        Http::assertSent(fn ($req) => str_contains($req->url(), 'answerCallbackQuery'));
    }

    // ── sendMessage sigue devolviendo array con message_id ────────────────────

    public function test_send_message_still_returns_array_result(): void
    {
        Http::fake([
            'api.telegram.org/botfake-token/sendMessage' => Http::response([
                'ok'     => true,
                'result' => ['message_id' => 42, 'chat' => ['id' => 123456789]],
            ], 200),
        ]);

        $service = new TelegramService();
        $result  = $service->sendMessage('Hola');

        $this->assertIsArray($result);
        $this->assertSame(42, $result['message_id']);
    }
}
