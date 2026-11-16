<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Live Chat Configuration
    |--------------------------------------------------------------------------
    */

    // Interruptor global del chat.
    // false → el widget no se monta, /api/chat/* devuelve 503,
    //         el webhook de Telegram devuelve 200 y descarta el update,
    //         la bandeja de Filament queda oculta.
    'enabled' => env('CHAT_ENABLED', true),

    // Tiempo máximo sin actividad para cerrar la conversación automáticamente (horas)
    'auto_close_hours' => env('CHAT_AUTO_CLOSE_HOURS', 24),

    // Nombre y teléfono del agente mostrado al visitante
    'agent_name'  => env('CHAT_AGENT_NAME', 'iCoreByte'),
    'agent_phone' => env('CHAT_AGENT_PHONE', '+34 678 758 210'),

    // Minutos sin respuesta antes de enviar el aviso de "no hay respuesta" (1 vez por conversación)
    'no_reply_minutes' => env('CHAT_NO_REPLY_MINUTES', 5),

    // Rate limiting
    'rate_limits' => [
        'create_conversation' => 3,  // por IP por minuto
        'send_message'        => 10, // por conversación por minuto
    ],

    // Adjuntos — HEIC excluido (GD no lo soporta)
    // Requisitos de PHP: upload_max_filesize >= 5M, post_max_size >= 6M
    // memory_limit >= 256M (25 MP × 4 bytes × 2 buffers ≈ 200 MB)
    // Si tu memory_limit es menor de 256M, cambia MAX_MEGAPIXELS en AttachmentController
    'allowed_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
    ],

    // Telegram (Fase 3)
    // Habilitar con CHAT_TELEGRAM_ENABLED=true + TELEGRAM_BOT_TOKEN + TELEGRAM_CHAT_ID
    'telegram_enabled' => env('CHAT_TELEGRAM_ENABLED', false),

    // Locales admitidos en el widget (es=respaldo)
    'supported_locales' => ['es', 'ca', 'en', 'fr'],

    // ── Horario de disponibilidad ─────────────────────────────────────────────
    // Días según ISO 8601: 1 = lunes … 7 = domingo
    // Cada día puede tener varias franjas [inicio, fin] en formato 'H:i'.
    'timezone' => env('CHAT_TIMEZONE', 'Europe/Madrid'),

    'schedule' => [
        1 => [['10:00', '13:30'], ['17:00', '20:00']], // lunes
        2 => [['10:00', '13:30'], ['17:00', '20:00']], // martes
        3 => [['10:00', '13:30'], ['17:00', '20:00']], // miércoles
        4 => [['10:00', '13:30'], ['17:00', '20:00']], // jueves
        5 => [['10:00', '13:30'], ['17:00', '20:00']], // viernes
        6 => [['10:00', '13:00']],                      // sábado
        7 => [],                                         // domingo — cerrado
    ],

    // Festivos puntuales (formato YYYY-MM-DD)
    'holidays' => [],

    // Rangos de vacaciones — el chat estará cerrado y mostrará el mensaje opcional
    // Ejemplo:
    // 'vacation_ranges' => [
    //     ['start' => '2026-12-24', 'end' => '2027-01-07', 'message' => 'Volvemos el 7 de enero.'],
    // ],
    'vacation_ranges' => [],
];
