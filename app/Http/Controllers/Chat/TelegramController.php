<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Jobs\Chat\SendTelegramMessageJob;
use App\Models\ChatConversation;
use App\Models\TelegramOutbound;
use App\Models\TelegramUpdate;
use App\Services\Chat\AvailabilityService;
use App\Services\Chat\ImageProcessor;
use App\Services\Chat\TelegramDispatcher;
use App\Services\Chat\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
    ) {
    }

    // ── Webhook ───────────────────────────────────────────────────────────────

    public function webhook(Request $request): Response
    {
        // 1. Verificar secret token
        $secret = (string) config('services.telegram.webhook_secret', '');
        $header = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');

        if ($secret === '' || ! hash_equals($secret, $header)) {
            return response('', 200);
        }

        // 2. Parsear
        /** @var array<string, mixed> $update */
        $update   = (array) $request->json()->all();
        $updateId = isset($update['update_id']) ? (int) $update['update_id'] : null;

        // 3. Idempotencia
        if ($updateId !== null) {
            if (TelegramUpdate::where('update_id', $updateId)->exists()) {
                return response('', 200);
            }
            TelegramUpdate::create(['update_id' => $updateId]);
        }

        // 4. Validar chat_id (tanto message como callback_query)
        $configured = (string) config('services.telegram.chat_id', '');
        $incoming   = $this->extractChatId($update);
        if ($configured !== '' && $incoming !== $configured) {
            return response('', 200);
        }

        // 5. Procesar
        try {
            if (isset($update['callback_query'])) {
                /** @var array<string, mixed> $cbq */
                $cbq = $update['callback_query'];
                $this->handleCallbackQuery($cbq);
            } elseif (isset($update['message'])) {
                /** @var array<string, mixed> $msg */
                $msg = $update['message'];
                $this->handleMessage($msg);
            }
        } catch (\Throwable $e) {
            Log::error('TelegramController::webhook — error', ['error' => $e->getMessage()]);
        }

        return response('', 200);
    }

    // ── Procesamiento de mensajes ──────────────────────────────────────────────

    private function handleMessage(array $message): void
    {
        $text = trim((string) ($message['text'] ?? ''));

        // Comandos
        if ($text === '/disponible' || str_starts_with($text, '/disponible ')) {
            $this->handleAvailableCommand(true);
            return;
        }
        if ($text === '/ausente' || str_starts_with($text, '/ausente ')) {
            $this->handleAvailableCommand(false);
            return;
        }
        if ($text === '/abiertas') {
            $this->handleAbiertasCommand();
            return;
        }

        // /cerrar respondiendo a cualquier mensaje de la conversación
        if (($text === '/cerrar' || str_starts_with($text, '/cerrar ')) && isset($message['reply_to_message'])) {
            $this->handleCloseByReply($message);
            return;
        }

        // Respuesta del agente a un mensaje del visitante
        if (isset($message['reply_to_message'])) {
            $this->handleAgentReply($message);
            return;
        }

        // Mensaje directo — avisar cómo usar el bot
        $this->dispatchHelp();
    }

    private function handleAgentReply(array $message): void
    {
        $replyToTgId = (int) ($message['reply_to_message']['message_id'] ?? 0);

        $outbound = TelegramOutbound::where('telegram_message_id', $replyToTgId)->first();

        if ($outbound === null) {
            $this->dispatchText('⚠️ No encuentro la conversación para ese mensaje.');
            return;
        }

        $conversation = $outbound->conversation;

        if ($conversation === null || ! $conversation->isOpen()) {
            $this->dispatchText('⚠️ Esta conversación ya está cerrada.');
            return;
        }

        // Foto del agente
        if (isset($message['photo'])) {
            $this->handleAgentPhoto($message, $conversation);
            return;
        }

        $body = mb_substr(trim((string) ($message['text'] ?? '')), 0, 2000);
        if ($body === '') {
            return;
        }

        $conversation->messages()->create([
            'sender' => 'agent',
            'body'   => $body,
        ]);

        $conversation->update([
            'last_agent_message_at' => now(),
            'no_reply_warned_at'    => null,
        ]);
    }

    private function handleAgentPhoto(array $message, ChatConversation $conversation): void
    {
        /** @var array<int, array{file_id: string, file_size?: int}> $photos */
        $photos = $message['photo'];
        usort($photos, fn ($a, $b) => ($b['file_size'] ?? 0) <=> ($a['file_size'] ?? 0));
        $fileId = (string) ($photos[0]['file_id'] ?? '');

        if ($fileId === '') {
            return;
        }

        $telegram = app(TelegramService::class);
        $bytes    = $telegram->downloadFile($fileId);

        if ($bytes === null) {
            $this->dispatchText('⚠️ No pude descargar la imagen de Telegram.');
            return;
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'tg_photo_') . '.jpg';
        file_put_contents($tmpPath, $bytes);

        try {
            $file     = new UploadedFile($tmpPath, 'photo.jpg', 'image/jpeg', null, true);
            $filename = ImageProcessor::process($file);
        } catch (\Throwable $e) {
            Log::error('TelegramController::handleAgentPhoto — error al procesar imagen', ['error' => $e->getMessage()]);
            @unlink($tmpPath);
            return;
        }

        @unlink($tmpPath);

        $conversation->messages()->create([
            'sender'          => 'agent',
            'body'            => '',
            'attachment_path' => $filename,
        ]);

        $conversation->update([
            'last_agent_message_at' => now(),
            'no_reply_warned_at'    => null,
        ]);
    }

    // ── Callback queries ──────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed> $callbackQuery
     */
    private function handleCallbackQuery(array $callbackQuery): void
    {
        $data    = (string) ($callbackQuery['data'] ?? '');
        $queryId = (string) ($callbackQuery['id'] ?? '');

        $telegram = app(TelegramService::class);

        if (str_starts_with($data, 'close:')) {
            $uuid         = substr($data, 6);
            $conversation = ChatConversation::where('uuid', $uuid)->first();

            if ($conversation && $conversation->isOpen()) {
                $conversation->update(['status' => 'closed']);
                $conversation->messages()->create([
                    'sender' => 'system',
                    'body'   => 'Conversación cerrada por el agente.',
                ]);
                $telegram->answerCallbackQuery($queryId, '✅ Conversación cerrada.');
            } else {
                $telegram->answerCallbackQuery($queryId, 'Ya estaba cerrada.');
            }

            return;
        }

        $telegram->answerCallbackQuery($queryId);
    }

    // ── Comandos de disponibilidad ────────────────────────────────────────────

    private function handleAvailableCommand(bool $available): void
    {
        $this->availability->setAgentAvailable($available);

        $status = $this->availability->isOnline()
            ? '🟢 <b>En línea</b> — recibirás los mensajes del chat.'
            : ($available
                ? '🟡 <b>Disponible</b>, pero fuera de horario ahora mismo.'
                : '🔴 <b>Ausente</b> — el chat mostrará el mensaje de no disponible.');

        $this->dispatchText($status);
    }

    // ── /abiertas ─────────────────────────────────────────────────────────────

    private function handleAbiertasCommand(): void
    {
        $conversations = ChatConversation::where('status', 'open')
            ->orderByDesc('last_visitor_message_at')
            ->limit(10)
            ->get();

        if ($conversations->isEmpty()) {
            $this->dispatchText('✅ No hay conversaciones abiertas.');
            return;
        }

        $count = $conversations->count();
        $lines = ["📋 <b>Conversaciones abiertas</b> ({$count})\n"];

        /** @var array<int, array<int, array<string, string>>> $rows */
        $rows = [];

        foreach ($conversations as $conv) {
            $name    = htmlspecialchars((string) $conv->visitor_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $locale  = htmlspecialchars((string) $conv->locale, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $age     = $conv->last_visitor_message_at
                ? $this->humanAge($conv->last_visitor_message_at)
                : '—';

            $lastMsg = $conv->messages()
                ->where('sender', 'visitor')
                ->latest()
                ->first();
            $preview = $lastMsg
                ? mb_substr(htmlspecialchars((string) $lastMsg->body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), 0, 60)
                : '(sin mensajes)';

            $lines[] = "👤 <b>{$name}</b> ({$locale}) · {$age}\n\"{$preview}\"";

            $uuid = (string) $conv->uuid;
            $rows[] = [
                ['text' => "❌ Cerrar {$name}", 'callback_data' => "close:{$uuid}"],
            ];
        }

        $text = implode("\n\n", $lines);
        $replyMarkup = json_encode(['inline_keyboard' => $rows]);

        $this->dispatchText($text, ['reply_markup' => $replyMarkup]);
    }

    // ── /cerrar por reply ──────────────────────────────────────────────────────

    private function handleCloseByReply(array $message): void
    {
        $replyToTgId = (int) ($message['reply_to_message']['message_id'] ?? 0);

        $outbound = TelegramOutbound::where('telegram_message_id', $replyToTgId)->first();

        if ($outbound === null) {
            $this->dispatchText('⚠️ No encuentro la conversación para ese mensaje.');
            return;
        }

        $conversation = $outbound->conversation;

        if ($conversation === null) {
            $this->dispatchText('⚠️ La conversación ya no existe.');
            return;
        }

        if (! $conversation->isOpen()) {
            $this->dispatchText('ℹ️ La conversación ya estaba cerrada.');
            return;
        }

        $conversation->update(['status' => 'closed']);
        $conversation->messages()->create([
            'sender' => 'system',
            'body'   => 'Conversación cerrada por el agente.',
        ]);

        $name = htmlspecialchars((string) $conversation->visitor_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $this->dispatchText("✅ Conversación de <b>{$name}</b> cerrada.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function dispatchHelp(): void
    {
        $this->dispatchText(
            "ℹ️ Para responder a un visitante, <b>responde al mensaje de notificación</b>.\n\n"
            . "Comandos:\n"
            . "/disponible — marcar como disponible\n"
            . "/ausente — marcar como ausente\n"
            . "/abiertas — listar conversaciones abiertas\n"
            . "/cerrar — cerrar conversación (respondiendo a un mensaje suyo)"
        );
    }

    /** @param  array<string, mixed> $extra */
    private function dispatchText(string $text, array $extra = []): void
    {
        // Para los mensajes del propio bot (sin conversación asociada), usamos
        // conversationId=0 y tipo=system (no se va a buscar en outbound).
        dispatch(new SendTelegramMessageJob(
            SendTelegramMessageJob::TYPE_TEXT,
            $text,
            $extra,
            null, null,
            0, 'system', null,
        ));
    }

    private function humanAge(\Carbon\CarbonInterface $date): string
    {
        $minutes = (int) $date->diffInMinutes(now());
        if ($minutes < 60) {
            return "hace {$minutes} min";
        }
        $hours = (int) $date->diffInHours(now());
        if ($hours < 24) {
            return "hace {$hours} h";
        }
        return "hace " . (int) $date->diffInDays(now()) . " d";
    }

    private function extractChatId(array $update): ?string
    {
        if (isset($update['message']['chat']['id'])) {
            return (string) $update['message']['chat']['id'];
        }
        if (isset($update['callback_query']['message']['chat']['id'])) {
            return (string) $update['callback_query']['message']['chat']['id'];
        }
        return null;
    }
}
