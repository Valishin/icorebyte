<?php

namespace App\Console\Commands\Chat;

use App\Models\ChatConversation;
use App\Services\Chat\AvailabilityService;
use Illuminate\Console\Command;

/**
 * Detecta conversaciones abiertas sin respuesta del agente en más de
 * CHAT_NO_REPLY_MINUTES y envía UNA sola vez un mensaje system al visitante.
 *
 * Solo actúa cuando la tienda está online (dentro de horario Y agente disponible).
 * Registrar en el scheduler: Schedule::command('chat:no-reply-warning')->everyMinute();
 */
class SendNoReplyWarning extends Command
{
    protected $signature   = 'chat:no-reply-warning';
    protected $description = 'Envía aviso de no-respuesta a conversaciones sin atender';

    public function handle(AvailabilityService $availability): int
    {
        // Fuera de horario no tiene sentido enviar el aviso
        // (el mensaje de bienvenida ya informa de la situación)
        if (! $availability->isOnline()) {
            return self::SUCCESS;
        }

        $threshold = (int) config('chat.no_reply_minutes', 5);
        $phone     = (string) config('chat.agent_phone', '+34 678 758 210');

        $conversations = ChatConversation::query()
            ->where('status', 'open')
            ->whereNotNull('last_visitor_message_at')
            ->where('last_visitor_message_at', '<', now()->subMinutes($threshold))
            ->whereNull('no_reply_warned_at')
            ->where(function ($q) {
                // El agente no ha respondido desde el último mensaje del visitante
                $q->whereNull('last_agent_message_at')
                  ->orWhereColumn('last_agent_message_at', '<', 'last_visitor_message_at');
            })
            ->get();

        foreach ($conversations as $conversation) {
            $locale = $conversation->locale ?? 'es';

            $conversation->messages()->create([
                'sender' => 'system',
                'body'   => (string) trans('chat.no_reply_warning', ['phone' => $phone], $locale),
            ]);

            $conversation->update(['no_reply_warned_at' => now()]);

            $this->line("  [chat:no-reply-warning] Aviso enviado a conversación {$conversation->uuid}");
        }

        return self::SUCCESS;
    }
}
