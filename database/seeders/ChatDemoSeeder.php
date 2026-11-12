<?php

namespace Database\Seeders;

use App\Models\ChatConversation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Solo para entorno local — crea conversaciones de ejemplo para revisar la bandeja visualmente.
 * Uso: php artisan db:seed --class=ChatDemoSeeder
 */
class ChatDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->warn('ChatDemoSeeder saltado en producción.');
            return;
        }

        // 1. Abierta con mensajes sin leer (badge de no leído)
        $conv1 = $this->conv([
            'visitor_name'  => 'María García',
            'locale'        => 'es',
            'status'        => 'open',
            'visitor_phone' => '+34 612 345 678',
        ]);
        $this->msg($conv1, 'visitor', 'Buenas tardes, necesito información sobre vuestros servicios de electricidad.');
        $this->msg($conv1, 'agent',   '¡Hola María! Por supuesto, ¿en qué te puedo ayudar?');
        $this->msg($conv1, 'visitor', 'Tengo un cuadro eléctrico que suena raro por las noches. ¿Podéis venir a revisarlo?');
        $this->msg($conv1, 'visitor', 'También me gustaría saber si hacéis instalaciones en viviendas antiguas.');
        $conv1->update(['last_visitor_message_at' => now()->subMinutes(5)]);

        // 2. Abierta con foto (attachment) — sin leer
        $conv2 = $this->conv([
            'visitor_name' => 'Jordi Puig',
            'locale'       => 'ca',
            'status'       => 'open',
        ]);
        $this->msg($conv2, 'visitor', 'Hola! Tinc un problema amb la instal·lació. Us envio una foto.');
        $this->msg($conv2, 'visitor', 'Com podeu veure, el cable sembla cremat.', null);
        // simula un attachment (el fichero no existe, pero la columna sí)
        $conv2->messages()->create([
            'sender'          => 'visitor',
            'body'            => '',
            'attachment_path' => 'demo-no-existe.jpg',
            'created_at'      => now()->subMinutes(3),
        ]);
        $conv2->update(['last_visitor_message_at' => now()->subMinutes(3)]);

        // 3. Abierta con teléfono/email, respuesta dada (fuera de horario)
        $conv3 = $this->conv([
            'visitor_name'  => 'Luis Fernández',
            'locale'        => 'es',
            'status'        => 'open',
            'visitor_phone' => '+34 698 765 432',
            'visitor_email' => 'luis@example.com',
        ]);
        $this->msg($conv3, 'system', 'Ahora mismo estamos fuera de horario. Os escribimos en cuanto abramos. También nos puedes llamar al +34 678 758 210.');
        $this->msg($conv3, 'visitor', 'Ok, muchas gracias. Es para un presupuesto de reforma eléctrica de un local.');
        $this->msg($conv3, 'agent',   'Buenos días Luis, en breve te llamamos para concretar el presupuesto.');
        // Estos mensajes de visitor están marcados como leídos
        $conv3->messages()->where('sender', 'visitor')->update(['read_at' => now()]);
        $conv3->update([
            'last_visitor_message_at' => now()->subHours(2),
            'last_agent_message_at'   => now()->subHour(),
            'agent_last_read_at'      => now()->subHour(),
        ]);

        // 4. Abierta en inglés — sin respuesta
        $conv4 = $this->conv([
            'visitor_name' => 'James Smith',
            'locale'       => 'en',
            'status'       => 'open',
        ]);
        $this->msg($conv4, 'system', 'Hi James! We\'ll be right with you.');
        $this->msg($conv4, 'visitor', 'Hi, I need to rewire my flat in Barcelona. Could you give me a quote?');
        $this->msg($conv4, 'visitor', 'I have about 80m² and the wiring is from the 1980s.');
        $conv4->update(['last_visitor_message_at' => now()->subHours(1)]);

        // 5. Cerrada — completada
        $conv5 = $this->conv([
            'visitor_name'  => 'Ana Martínez',
            'locale'        => 'es',
            'status'        => 'closed',
            'visitor_phone' => '+34 655 111 222',
        ]);
        $this->msg($conv5, 'system',  'Hola Ana, estamos aquí para ayudarte.');
        $this->msg($conv5, 'visitor', '¿Cuánto cuesta instalar un punto de recarga para coche eléctrico?');
        $this->msg($conv5, 'agent',   'Depende de la potencia y la distancia al cuadro. Para un Wallbox estándar, entre 400 y 700€ con instalación incluida.');
        $this->msg($conv5, 'visitor', 'Perfecto, ya lo tenemos. ¡Muchas gracias!');
        $this->msg($conv5, 'agent',   '¡Un placer! Si necesitas algo más, escríbenos.');
        $this->msg($conv5, 'system',  'Conversación cerrada por el agente.');
        $conv5->messages()->where('sender', 'visitor')->update(['read_at' => now()]);
        $conv5->update([
            'last_visitor_message_at' => now()->subDays(2),
            'last_agent_message_at'   => now()->subDays(2),
            'agent_last_read_at'      => now()->subDays(2),
        ]);

        // 6. Abierta reciente — visitante escribiendo ahora
        $conv6 = $this->conv([
            'visitor_name' => 'Carmen Ruiz',
            'locale'       => 'es',
            'status'       => 'open',
        ]);
        $this->msg($conv6, 'system',  'Hola Carmen, en breve te atendemos.');
        $this->msg($conv6, 'visitor', 'Hola, se me ha ido la luz en toda la cocina.');
        $conv6->update(['last_visitor_message_at' => now()->subMinutes(1)]);

        $this->command->info('ChatDemoSeeder: 6 conversaciones de ejemplo creadas.');
    }

    /** @param array<string, mixed> $attrs */
    private function conv(array $attrs): ChatConversation
    {
        return ChatConversation::create(array_merge([
            'uuid'          => Str::uuid(),
            'visitor_token' => hash('sha256', bin2hex(random_bytes(16))),
            'ip_hash'       => hash('sha256', '127.0.0.1'),
            'consent_at'    => now()->subHours(rand(1, 48)),
        ], $attrs));
    }

    private function msg(ChatConversation $conv, string $sender, string $body, ?string $placeholder = 'skip'): void
    {
        $conv->messages()->create([
            'sender'     => $sender,
            'body'       => $body,
            'created_at' => now()->subMinutes(rand(2, 120)),
        ]);
    }
}
