<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\CreateConversationRequest;
use App\Http\Requests\Chat\UpdateContactRequest;
use App\Models\ChatConversation;
use App\Services\Chat\AvailabilityService;
use App\Services\Chat\TelegramDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConversationController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly TelegramDispatcher $dispatcher,
    ) {
    }

    // ── Endpoints públicos ────────────────────────────────────────────────────

    /** GET /api/chat/availability — el widget lo consulta antes de mostrar el botón. */
    public function availability(Request $request): JsonResponse
    {
        $locale = $this->detectLocale($request);
        return response()->json($this->availability->status($locale));
    }

    /** POST /api/chat/conversations */
    public function store(CreateConversationRequest $request): JsonResponse
    {
        if ($request->filled('website')) {
            return response()->json(['error' => 'No se ha podido procesar la solicitud.'], 422);
        }

        $locale   = $this->detectLocale($request);
        $rawToken = bin2hex(random_bytes(32));

        $conversation = ChatConversation::create([
            'uuid'          => Str::uuid(),
            'visitor_token' => hash('sha256', $rawToken),
            'status'        => 'open',
            'locale'        => $locale,
            'visitor_name'  => $request->visitor_name,
            'visitor_phone' => $request->visitor_phone,
            'visitor_email' => $request->visitor_email,
            'page_url'      => $request->page_url,
            'ip_hash'       => hash('sha256', $request->ip()),
            'consent_at'    => now(),
        ]);

        $this->sendWelcomeMessage($conversation);
        $this->dispatchNewConversationNotification($conversation);

        $status = $this->availability->status($locale);

        return response()->json([
            'uuid'           => $conversation->uuid,
            'visitor_token'  => $rawToken,
            'is_open'        => $status['is_open'],
            'is_online'      => $status['is_online'],
            'next_open_text' => $status['next_open_text'],
        ], 201);
    }

    /** POST /api/chat/conversations/{uuid}/presence */
    public function updatePresence(Request $request, string $uuid): JsonResponse
    {
        $conversation = $request->attributes->get('chat_conversation');
        $conversation->update(['visitor_last_seen_at' => now()]);
        return response()->json(['ok' => true]);
    }

    /** PATCH /api/chat/conversations/{uuid}/contact */
    public function updateContact(UpdateContactRequest $request, string $uuid): JsonResponse
    {
        /** @var ChatConversation $conversation */
        $conversation = $request->attributes->get('chat_conversation');

        $updates = array_filter([
            'visitor_phone' => $request->phone,
            'visitor_email' => $request->email,
        ], fn ($v) => $v !== null);

        $conversation->update($updates);

        // Mensaje de confirmación
        $locale  = $conversation->locale;
        $name    = e($conversation->visitor_name);
        $contact = implode(' / ', array_filter([$request->phone, $request->email]));

        $conversation->messages()->create([
            'sender' => 'system',
            'body'   => (string) trans('chat.contact_updated', ['name' => $name, 'contact' => $contact], $locale),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Detecta el locale del visitante:
     * 1. Parámetro ?locale= (o campo JSON "locale")
     * 2. Cabecera Accept-Language
     * 3. 'es' como respaldo
     */
    private function detectLocale(Request $request): string
    {
        /** @var string[] $supported */
        $supported = config('chat.supported_locales', ['es', 'ca', 'en', 'fr']);

        $param = strtolower((string) $request->input('locale', ''));
        if (in_array($param, $supported, true)) {
            return $param;
        }

        $acceptLang = $request->header('Accept-Language', '');
        foreach (explode(',', $acceptLang) as $tag) {
            $primary = strtolower(explode('-', trim(explode(';', $tag)[0]))[0]);
            if (in_array($primary, $supported, true)) {
                return $primary;
            }
        }

        return 'es';
    }

    /**
     * Notifica a Telegram sobre la nueva conversación (si está habilitado).
     * Los errores de Telegram no bloquean la respuesta al visitante.
     */
    private function dispatchNewConversationNotification(ChatConversation $conversation): void
    {
        if (! config('chat.telegram_enabled')) {
            return;
        }

        $name     = htmlspecialchars((string) $conversation->visitor_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $locale   = htmlspecialchars((string) $conversation->locale,        ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $pageUrl  = htmlspecialchars((string) ($conversation->page_url ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $status   = $this->availability->isOnline() ? '🟢 En línea' : '🟡 Fuera de horario';

        $text  = "💬 <b>Nueva conversación</b>\n";
        $text .= "👤 <b>{$name}</b> · idioma: <code>{$locale}</code> · {$status}\n";
        if ($pageUrl !== '') {
            $text .= "🌐 {$pageUrl}\n";
        }

        $whatsappUrl = 'https://wa.me/' . preg_replace('/\D/', '', (string) config('chat.agent_phone', ''));
        $uuid        = (string) $conversation->uuid;

        // URL de la bandeja en el panel (/admin — ChatInbox es la página por defecto)
        $panelUrl  = rtrim((string) config('app.url'), '/') . '/admin';
        $buttons   = [
            ['text' => '📋 Abrir en panel', 'url' => $panelUrl],
        ];

        $buttons[] = ['text' => '💬 WhatsApp', 'url'           => $whatsappUrl];
        $buttons[] = ['text' => '❌ Cerrar',    'callback_data' => "close:{$uuid}"];

        $replyMarkup = json_encode(['inline_keyboard' => [$buttons]]);

        $welcomeMsg = $conversation->messages()->where('sender', 'system')->orderBy('id')->first();

        $this->dispatcher->sendText(
            $text,
            ['reply_markup' => $replyMarkup],
            $conversation->id,
            TelegramDispatcher::TIPO_NEW_CONV,
            $welcomeMsg?->id,
        );
    }

    /**
     * Envía el mensaje de bienvenida según el estado (online / away / closed).
     *
     * Estados:
     *  1. isOnline()                       → welcome_open  (atendemos enseguida)
     *  2. isOpen() && !isAgentAvailable()  → welcome_away  (manual: ausente)
     *  3. !isOpen()                        → welcome_closed (fuera de horario)
     */
    private function sendWelcomeMessage(ChatConversation $conversation): void
    {
        $locale = $conversation->locale;
        $name   = $conversation->visitor_name;
        $phone  = (string) config('chat.agent_phone', '+34 678 758 210');

        if ($this->availability->isOnline()) {
            $body = (string) trans('chat.welcome_open', ['name' => $name], $locale);
        } elseif ($this->availability->isOpen()) {
            // Dentro de horario pero interruptor manual en "ausente"
            $body = (string) trans('chat.welcome_away', ['name' => $name, 'phone' => $phone], $locale);
        } else {
            // Fuera de horario, festivo, vacaciones
            $nextOpen = $this->availability->nextOpenText($locale);
            $body = (string) trans('chat.welcome_closed', [
                'name'      => $name,
                'next_open' => $nextOpen,
                'phone'     => $phone,
            ], $locale);
        }

        $conversation->messages()->create(['sender' => 'system', 'body' => $body]);
    }
}
