<?php

namespace Tests\Feature\Chat;

use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica que un X-Forwarded-For falso no puede bypasear el rate limit
 * cuando TRUSTED_PROXIES no está configurado (valor por defecto: ninguno).
 */
class TrustProxiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(RateLimiter::class)->clear('chat.create');
    }

    public function test_spoofed_x_forwarded_for_does_not_change_rate_limit_key(): void
    {
        // Sin TRUSTED_PROXIES configurado, Laravel ignora X-Forwarded-For.
        // Todas las peticiones vienen de 127.0.0.1 (REMOTE_ADDR de los tests).

        $payload = ['visitor_name' => 'Test', 'consent' => '1'];

        // 3 peticiones desde la IP real (127.0.0.1) — agotan el límite por minuto
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/chat/conversations', $payload)->assertStatus(201);
        }

        // 4ª petición con X-Forwarded-For falseado a una IP "diferente"
        // Debe bloquearse igual porque la IP real sigue siendo 127.0.0.1
        $this->withHeaders(['X-Forwarded-For' => '9.9.9.9'])
            ->postJson('/api/chat/conversations', $payload)
            ->assertStatus(429); // bloqueado — el spoof no funciona
    }

    public function test_rate_limit_key_uses_remote_addr_not_forwarded_for(): void
    {
        $payload = ['visitor_name' => 'Test', 'consent' => '1'];

        // Primera petición con X-Forwarded-For falseado: cuenta contra 127.0.0.1
        $this->withHeaders(['X-Forwarded-For' => '1.2.3.4'])
            ->postJson('/api/chat/conversations', $payload)
            ->assertStatus(201);

        // Segunda petición SIN el header falso: misma IP real, mismo contador
        $this->postJson('/api/chat/conversations', $payload)->assertStatus(201);
        $this->postJson('/api/chat/conversations', $payload)->assertStatus(201);

        // La 4ª debe bloquearse — confirma que las 3 anteriores compartían el mismo bucket
        $this->postJson('/api/chat/conversations', $payload)->assertStatus(429);
    }
}
