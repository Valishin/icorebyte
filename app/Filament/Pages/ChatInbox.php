<?php

namespace App\Filament\Pages;

use App\Jobs\Chat\SendConversationNotificationJob;
use App\Services\Chat\ImageProcessor;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\QuickReply;
use App\Services\Chat\AvailabilityService;
use App\Services\Chat\TelegramDispatcher;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

/**
 * Bandeja de chat tipo WhatsApp Web.
 *
 * wire:poll.10000ms  — refresca la lista cada 10 s
 * wire:poll.3000ms   — refresca los mensajes de la conv. abierta cada 3 s
 *
 * @property-read \Illuminate\Support\Collection<int, ChatConversation> $conversations
 * @property-read \Illuminate\Support\Collection<int, ChatConversation> $openConversations
 * @property-read \Illuminate\Support\Collection<int, ChatConversation> $closedConversations
 * @property-read int $unreadCount
 * @property-read ChatConversation|null $activeConversation
 * @property-read \Illuminate\Support\Collection<int, ChatMessage> $activeMessages
 * @property-read \Illuminate\Support\Collection<int, QuickReply> $quickReplies
 * @property-read bool $agentAvailable
 * @property-read bool $visitorTyping
 */
class ChatInbox extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected string $view = 'filament.pages.chat-inbox';

    protected static ?string $navigationLabel = 'Bandeja de chat';

    protected static ?string $title = 'Bandeja de chat';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) config('chat.enabled', true);
    }

    // ── Estado Livewire ───────────────────────────────────────────────────────

    public ?string $activeUuid = null;

    public string $replyBody = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $attachmentFile = null;

    // ── Computed ──────────────────────────────────────────────────────────────

    /**
     * Lista de conversaciones abiertas (últimas 50).
     *
     * @return Collection<int, ChatConversation>
     */
    public function getConversationsProperty(): Collection
    {
        return ChatConversation::with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_visitor_message_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
    }

    /** @return Collection<int, ChatConversation> */
    public function getOpenConversationsProperty(): Collection
    {
        return $this->conversations->where('status', 'open')->values();
    }

    /** @return Collection<int, ChatConversation> */
    public function getClosedConversationsProperty(): Collection
    {
        return $this->conversations->where('status', 'closed')->values();
    }

    /** @return int total de mensajes sin leer */
    public function getUnreadCountProperty(): int
    {
        return ChatConversation::where('status', 'open')
            ->whereHas('messages', fn ($q) => $q->where('sender', 'visitor')->whereNull('read_at'))
            ->count();
    }

    public function getActiveConversationProperty(): ?ChatConversation
    {
        if ($this->activeUuid === null) {
            return null;
        }
        return ChatConversation::where('uuid', $this->activeUuid)->first();
    }

    /**
     * Mensajes de la conversación activa.
     *
     * @return Collection<int, ChatMessage>
     */
    public function getActiveMessagesProperty(): Collection
    {
        if ($this->activeUuid === null) {
            return collect();
        }
        return ChatConversation::where('uuid', $this->activeUuid)
            ->first()
            ?->messages()
            ->orderBy('id')
            ->get() ?? collect();
    }

    /**
     * Respuestas rápidas activas.
     *
     * @return Collection<int, QuickReply>
     */
    public function getQuickRepliesProperty(): Collection
    {
        return QuickReply::where('active', true)->orderBy('sort_order')->get();
    }

    public function getAgentAvailableProperty(): bool
    {
        return app(AvailabilityService::class)->isAgentAvailable();
    }

    public function getVisitorTypingProperty(): bool
    {
        if ($this->activeUuid === null) {
            return false;
        }
        return (bool) Cache::get("chat.typing.visitor.{$this->activeUuid}");
    }

    // ── Acciones Livewire ─────────────────────────────────────────────────────

    public function openConversation(string $uuid): void
    {
        $this->activeUuid     = $uuid;
        $this->replyBody      = '';
        $this->attachmentFile = null;
        $this->markAsRead();
    }

    /** Refresca la lista (wire:poll.10000ms) */
    public function pollList(): void
    {
        // El polling actualiza propiedades computed automáticamente
    }

    /** Refresca los mensajes de la conv. activa (wire:poll.3000ms) */
    public function pollMessages(): void
    {
        $this->markAsRead();
    }

    public function markAsRead(): void
    {
        if ($this->activeUuid === null) {
            return;
        }

        $conv = ChatConversation::where('uuid', $this->activeUuid)->first();
        if ($conv === null) {
            return;
        }

        $conv->messages()
            ->where('sender', 'visitor')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conv->update(['agent_last_read_at' => now()]);
    }

    /** Fires when the agent types in the reply box — sets the typing cache key. */
    public function updatedReplyBody(): void
    {
        if ($this->activeUuid !== null) {
            Cache::put("chat.typing.agent.{$this->activeUuid}", true, 5);
        }
    }

    public function sendReply(): void
    {
        $this->validate([
            'replyBody'      => ['required_without:attachmentFile', 'nullable', 'string', 'max:2000'],
            'attachmentFile' => ['nullable', 'image', 'max:5120'],
        ]);

        $conv = $this->activeConversation;
        if ($conv === null || ! $conv->isOpen()) {
            Notification::make()->title('La conversación está cerrada.')->danger()->send();
            return;
        }

        $body             = mb_substr(trim((string) $this->replyBody), 0, 2000);
        $attachmentPath   = null;

        if ($this->attachmentFile !== null) {
            try {
                $attachmentPath = ImageProcessor::process($this->attachmentFile);
            } catch (ValidationException $e) {
                $this->addError('attachmentFile', $e->validator->errors()->first('attachment'));
                return;
            }
        }

        $message = $conv->messages()->create([
            'sender'          => 'agent',
            'body'            => $body,
            'attachment_path' => $attachmentPath,
        ]);

        $conv->update(['last_agent_message_at' => now()]);

        $this->replyBody      = '';
        $this->attachmentFile = null;

        // Notificar a Telegram: aparece como "Respondido desde el panel"
        if (config('chat.telegram_enabled')) {
            $name    = htmlspecialchars((string) $conv->visitor_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $escaped = htmlspecialchars($body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $text    = "📋 <b>Respondido desde el panel</b> a <b>{$name}</b>" . ($body !== '' ? ":\n{$escaped}" : ' [imagen]');
            app(TelegramDispatcher::class)->sendText(
                $text,
                [],
                $conv->id,
                TelegramDispatcher::TIPO_SYSTEM,
                $message->id,
            );
        }

        // Email al visitante si no ha visto la conv. en los últimos 10 min
        SendConversationNotificationJob::dispatch($conv, $message);
    }

    public function useQuickReply(int $id): void
    {
        $qr = QuickReply::find($id);
        if ($qr !== null) {
            $this->replyBody = $qr->body;
        }
    }

    public function closeConversation(): void
    {
        $conv = $this->activeConversation;
        if ($conv === null) {
            return;
        }

        $this->attachmentFile = null;
        $conv->update(['status' => 'closed']);
        $conv->messages()->create(['sender' => 'system', 'body' => 'Conversación cerrada por el agente.']);

        Notification::make()->title('Conversación cerrada.')->success()->send();
    }

    public function reopenConversation(): void
    {
        $conv = $this->activeConversation;
        if ($conv === null) {
            return;
        }

        $conv->update(['status' => 'open']);
        $conv->messages()->create(['sender' => 'system', 'body' => 'Conversación reabierta por el agente.']);

        Notification::make()->title('Conversación reabierta.')->success()->send();
    }

    public function toggleAvailability(): void
    {
        $svc = app(AvailabilityService::class);
        $svc->setAgentAvailable(! $svc->isAgentAvailable());
    }

    // ── Acciones header (Filament) ────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggle_availability')
                ->label(fn () => $this->agentAvailable ? '🟢 Disponible' : '🔴 Ausente')
                ->color(fn () => $this->agentAvailable ? 'success' : 'danger')
                ->action('toggleAvailability'),
        ];
    }
}
