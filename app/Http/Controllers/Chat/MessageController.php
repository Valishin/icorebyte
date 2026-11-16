<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\Chat\ImageProcessor;
use App\Services\Chat\TelegramDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class MessageController extends Controller
{
    // Tiempo de vida del indicador "está escribiendo" en caché (segundos)
    private const TYPING_TTL = 5;

    public function index(Request $request, string $uuid): JsonResponse
    {
        /** @var ChatConversation $conversation */
        $conversation = $request->attributes->get('chat_conversation');

        $after = (int) ($request->query('after', 0));

        $messages = $conversation->messages()
            ->where('id', '>', $after)
            ->orderBy('id')
            ->get()
            ->map(fn (ChatMessage $m) => $this->formatMessage($m));

        // Marcar mensajes del agente como leídos por el visitante
        $conversation->messages()
            ->where('sender', 'agent')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->update(['visitor_last_read_at' => now()]);

        return response()->json([
            'status'          => $conversation->status,
            'agent_typing'    => (bool) Cache::get("chat.typing.agent.{$uuid}"),
            'visitor_typing'  => (bool) Cache::get("chat.typing.visitor.{$uuid}"),
            'messages'        => $messages,
        ]);
    }

    public function store(SendMessageRequest $request, string $uuid): JsonResponse
    {
        /** @var ChatConversation $conversation */
        $conversation = $request->attributes->get('chat_conversation');

        if (! $conversation->isOpen()) {
            return response()->json([
                'error' => 'La conversación está cerrada. Para una nueva consulta, inicia un nuevo chat.',
            ], 409);
        }

        $attachmentFilename = null;

        if ($request->hasFile('attachment')) {
            $attachmentFilename = ImageProcessor::process($request->file('attachment'));
        }

        // Sin strip_tags: trim + límite de longitud. El frontend renderiza con {{ }} (escapado).
        $body = $request->body !== null
            ? mb_substr(trim($request->body), 0, 2000)
            : '';

        $message = $conversation->messages()->create([
            'sender'          => 'visitor',
            'body'            => $body,
            'attachment_path' => $attachmentFilename,
        ]);

        $conversation->update(['last_visitor_message_at' => now()]);

        $this->dispatchVisitorMessageNotification($conversation, $message, $attachmentFilename);

        return response()->json($this->formatMessage($message), 201);
    }

    public function markRead(Request $request, string $uuid): JsonResponse
    {
        $conversation = $request->attributes->get('chat_conversation');

        $conversation->messages()
            ->where('sender', 'agent')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->update(['visitor_last_read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /**
     * El visitante indica que está escribiendo — se guarda en caché con TTL corto,
     * sin tocar la BD para evitar escrituras innecesarias.
     */
    public function typing(Request $request, string $uuid): JsonResponse
    {
        Cache::put("chat.typing.visitor.{$uuid}", true, self::TYPING_TTL);

        return response()->json(['ok' => true]);
    }

    private function dispatchVisitorMessageNotification(
        ChatConversation $conversation,
        ChatMessage $message,
        ?string $attachmentFilename,
    ): void {
        if (! config('chat.telegram_enabled')) {
            return;
        }

        $dispatcher = app(TelegramDispatcher::class);
        $name       = htmlspecialchars((string) $conversation->visitor_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if ($attachmentFilename !== null) {
            $dispatcher->sendPhoto(
                'private',
                'chat/attachments/' . $attachmentFilename,
                "📷 Foto de <b>{$name}</b>",
                [],
                $conversation->id,
                TelegramDispatcher::TIPO_VISITOR_PHOTO,
                $message->id,
            );
            return;
        }

        $escapedBody = htmlspecialchars((string) $message->body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $dispatcher->sendText(
            "💬 <b>{$name}:</b>\n{$escapedBody}",
            [],
            $conversation->id,
            TelegramDispatcher::TIPO_VISITOR_MSG,
            $message->id,
        );
    }

    private function formatMessage(ChatMessage $m): array
    {
        return [
            'id'             => $m->id,
            'sender'         => $m->sender,
            'body'           => $m->body,
            'attachment_url' => $m->attachment_path
                ? URL::temporarySignedRoute(
                    'chat.attachment',
                    now()->addMinutes(60),
                    ['filename' => $m->attachment_path]
                )
                : null,
            'created_at' => $m->created_at->toIso8601String(),
            'read_at'    => $m->read_at?->toIso8601String(),
        ];
    }
}
