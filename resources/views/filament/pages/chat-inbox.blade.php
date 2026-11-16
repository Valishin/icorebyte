<x-filament-panels::page>

{{-- ── Sonido / Notificaciones push / título de pestaña ────────────────────── --}}
<script>
(function () {
    var PREF_KEY = 'chat_sounds_enabled';
    var audio    = null;

    function getAudio() {
        if (!audio) {
            audio = new Audio('/sounds/ping.mp3');
            audio.volume = 0.6;
        }
        return audio;
    }

    window.ChatPanel = {
        soundsEnabled: localStorage.getItem(PREF_KEY) === '1',

        activateSound: function () {
            Notification.requestPermission().then(function (perm) {
                window.ChatPanel.soundsEnabled = true;
                localStorage.setItem(PREF_KEY, '1');
                getAudio().play().catch(function(){});
                // Ocultar el botón tras activar
                var btn = document.getElementById('chat-activate-btn');
                if (btn) btn.style.display = 'none';
            });
        },

        notifyNewMessage: function (name, body) {
            if (window.ChatPanel.soundsEnabled) {
                getAudio().play().catch(function(){});
            }
            if (Notification.permission === 'granted') {
                new Notification('\u{1F4AC} ' + name, { body: body, icon: '/favicon.ico' });
            }
        },

        updateTitle: function (unread) {
            document.title = unread > 0
                ? '(' + unread + ') Bandeja de chat'
                : 'Bandeja de chat';
        },
    };

    document.addEventListener('livewire:initialized', function () {
        Livewire.on('new-visitor-message', function (data) {
            window.ChatPanel.notifyNewMessage(data.name, data.preview);
        });
    });

    // Inicializar visibilidad del botón de activación
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('chat-activate-btn');
        if (btn && window.ChatPanel.soundsEnabled) btn.style.display = 'none';
    });
})();
</script>

{{-- ── Botón activar sonido (siempre renderizado, oculto por JS si ya activo) --}}
<div id="chat-activate-btn" class="mb-3">
    <button
        onclick="window.ChatPanel.activateSound()"
        class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-300 transition"
    >🔔 Activar sonido y notificaciones</button>
</div>

{{-- ── Layout WhatsApp: dos columnas ──────────────────────────────────────── --}}
<div class="flex h-[calc(100vh-13rem)] gap-0 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">

    {{-- ── Columna izquierda: lista ─────────────────────────────────────────── --}}
    <div
        class="w-72 flex-shrink-0 flex flex-col border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
        wire:poll.10000ms="pollList"
    >
        {{-- Cabecera con contador --}}
        <div class="p-3 border-b border-gray-200 dark:border-gray-700">
            <span class="font-semibold text-sm text-gray-700 dark:text-gray-200">
                Conversaciones
                @if($this->unreadCount > 0)
                    <span class="ml-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold rounded-full bg-primary-500 text-white">
                        {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
                    </span>
                @endif
            </span>
        </div>

        <div class="flex-1 overflow-y-auto">
            {{-- Abiertas --}}
            @forelse($this->openConversations as $conv)
                @php
                    $lastMsg   = $conv->messages->first();
                    $preview   = $lastMsg ? \Illuminate\Support\Str::limit($lastMsg->body, 50) : '';
                    $isActive  = $this->activeUuid === (string) $conv->uuid;
                    $hasUnread = $conv->messages->where('sender', 'visitor')->whereNull('read_at')->isNotEmpty();
                @endphp
                <button
                    wire:click="openConversation('{{ $conv->uuid }}')"
                    class="w-full text-left px-4 py-3 flex items-start gap-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition
                           {{ $isActive ? 'bg-primary-50 dark:bg-primary-900/20 border-l-4 border-primary-500' : '' }}"
                >
                    <div class="flex-shrink-0 w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-sm font-bold text-primary-700 dark:text-primary-300">
                        {{ strtoupper(substr((string) $conv->visitor_name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-sm {{ $hasUnread ? 'font-bold' : 'font-medium' }} text-gray-900 dark:text-gray-100 truncate">
                                {{ $conv->visitor_name }}
                            </span>
                            <span class="text-xs text-gray-400 ml-1 flex-shrink-0">
                                {{ ($conv->last_visitor_message_at ?? $conv->created_at)->diffForHumans(short: true) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $preview }}</p>
                    </div>
                    @if($hasUnread)
                        <span class="flex-shrink-0 w-2 h-2 mt-1.5 rounded-full bg-primary-500"></span>
                    @endif
                </button>
            @empty
                <p class="text-center text-xs text-gray-400 dark:text-gray-500 py-8">Sin conversaciones abiertas</p>
            @endforelse

            {{-- Cerradas --}}
            @if($this->closedConversations->isNotEmpty())
                <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Cerradas ({{ $this->closedConversations->count() }})
                    </p>
                </div>
                @foreach($this->closedConversations->take(10) as $conv)
                    @php $isActive = $this->activeUuid === (string) $conv->uuid; @endphp
                    <button
                        wire:click="openConversation('{{ $conv->uuid }}')"
                        class="w-full text-left px-4 py-2.5 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition opacity-60
                               {{ $isActive ? 'bg-gray-100 dark:bg-gray-800' : '' }}"
                    >
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-xs font-medium text-gray-500">
                            {{ strtoupper(substr((string) $conv->visitor_name, 0, 1)) }}
                        </div>
                        <span class="text-xs text-gray-600 dark:text-gray-300 truncate">{{ $conv->visitor_name }}</span>
                    </button>
                @endforeach
            @endif
        </div>
    </div>

    {{-- ── Columna derecha: conversación activa ───────────────────────────── --}}
    <div class="flex-1 flex flex-col bg-gray-50 dark:bg-gray-950">

        @if($this->activeConversation !== null)
            @php $conv = $this->activeConversation; @endphp

            {{-- Cabecera conversación --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                <div>
                    <h2 class="font-semibold text-gray-900 dark:text-gray-100">{{ $conv->visitor_name }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ implode(' · ', array_filter([(string)($conv->visitor_email ?? ''), (string)($conv->visitor_phone ?? ''), (string)$conv->locale])) }}
                        · {{ $conv->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if($conv->isOpen())
                        <button
                            wire:click="closeConversation"
                            wire:confirm="¿Cerrar esta conversación?"
                            class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 transition"
                        >✕ Cerrar</button>
                    @else
                        <span class="text-xs text-gray-400 italic">Cerrada</span>
                        <button
                            wire:click="reopenConversation"
                            class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 transition"
                        >↩ Reabrir</button>
                    @endif
                </div>
            </div>

            {{-- Hilo de mensajes --}}
            <div
                class="flex-1 overflow-y-auto p-4 space-y-3"
                wire:poll.3000ms="pollMessages"
                x-data
                x-init="$el.scrollTop = $el.scrollHeight"
                x-on:livewire:update.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
            >
                @foreach($this->activeMessages as $msg)
                    @if($msg->sender === 'system')
                        <div class="flex justify-center">
                            <span class="text-xs text-gray-400 dark:text-gray-500 italic bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full">
                                {{ $msg->body }}
                            </span>
                        </div>
                    @else
                        <div class="flex {{ $msg->sender === 'agent' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[70%]">
                                <div class="px-4 py-2 rounded-2xl text-sm break-words
                                    {{ $msg->sender === 'agent'
                                        ? 'bg-primary-500 text-white rounded-br-sm'
                                        : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm rounded-bl-sm' }}">
                                    {{ $msg->body }}
                                </div>
                                <div class="mt-0.5 {{ $msg->sender === 'agent' ? 'text-right' : 'text-left' }}">
                                    <span class="text-xs text-gray-400">
                                        {{ $msg->created_at->format('H:i') }}
                                        @if($msg->sender === 'agent')
                                            {{ $msg->read_at ? '✓✓' : '✓' }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Campo de respuesta --}}
            @if($conv->isOpen())
                <div class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">

                    {{-- Selector respuestas rápidas (aparece al escribir "/") --}}
                    @if($this->quickReplies->isNotEmpty() && str_starts_with($replyBody, '/'))
                        @php
                            $filter  = strtolower(ltrim($replyBody, '/'));
                            $matches = $this->quickReplies->filter(fn ($qr) =>
                                $filter === '' ||
                                str_contains(strtolower((string)$qr->shortcut), $filter) ||
                                str_contains(strtolower((string)$qr->title), $filter)
                            );
                        @endphp
                        @if($matches->isNotEmpty())
                            <div class="mb-2 max-h-40 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 shadow-lg">
                                @foreach($matches as $qr)
                                    <button
                                        wire:click="useQuickReply({{ $qr->id }})"
                                        class="w-full text-left px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-baseline gap-2 border-b border-gray-100 dark:border-gray-700 last:border-0"
                                    >
                                        <span class="text-xs font-mono text-primary-600 dark:text-primary-400 flex-shrink-0">{{ $qr->shortcut }}</span>
                                        <span class="text-xs text-gray-600 dark:text-gray-300 truncate">{{ $qr->title }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    {{-- Preview del adjunto seleccionado --}}
                    @if($attachmentFile)
                        <div class="mb-2 flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-primary-500 flex-shrink-0">
                                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="truncate">{{ $attachmentFile->getClientOriginalName() }}</span>
                            <button wire:click="$set('attachmentFile', null)" class="ml-auto text-red-400 hover:text-red-600 flex-shrink-0" title="Quitar imagen">✕</button>
                        </div>
                    @endif
                    @error('attachmentFile')
                        <p class="mb-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    <div class="flex items-end gap-2">
                        <textarea
                            wire:model.live="replyBody"
                            wire:keydown.ctrl.enter="sendReply"
                            placeholder="Escribe una respuesta… (Ctrl+Enter para enviar, / para respuestas rápidas)"
                            rows="2"
                            class="flex-1 resize-none rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        ></textarea>

                        {{-- Botón adjuntar imagen --}}
                        <label class="flex-shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer" title="Adjuntar imagen">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                            <input type="file" wire:model="attachmentFile" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        </label>

                        <button
                            wire:click="sendReply"
                            wire:loading.attr="disabled"
                            class="flex-shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-xl bg-primary-500 text-white hover:bg-primary-600 transition disabled:opacity-50"
                            title="Enviar (Ctrl+Enter)"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

        @else
            <div class="flex-1 flex flex-col items-center justify-center text-gray-400 dark:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 mb-4 opacity-40">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                </svg>
                <p class="text-sm">Selecciona una conversación</p>
                <p class="text-xs mt-1">Las nuevas conversaciones aparecen automáticamente</p>
            </div>
        @endif

    </div>
</div>

{{-- Actualización del título con no leídos vía polling --}}
<script>
    document.addEventListener('livewire:initialized', function () {
        // Actualizar título cada vez que refresca la lista
        Livewire.hook('commit', function ({ component, respond, succeed, fail }) {
            succeed(function ({ snapshot, effect }) {
                if (window.ChatPanel) {
                    window.ChatPanel.updateTitle({{ $this->unreadCount }});
                }
            });
        });
    });
</script>

</x-filament-panels::page>
