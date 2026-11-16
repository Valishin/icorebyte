<?php

namespace Tests\Feature\Chat;

use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Limpiar contadores entre tests
        app(RateLimiter::class)->clear('chat.create|127.0.0.1');
    }

    public function test_rate_limit_blocks_after_3_conversations_per_minute(): void
    {
        $validPayload = [
            'visitor_name' => 'User',
            'consent'      => '1',
        ];

        // 3 peticiones deben pasar
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/chat/conversations', $validPayload)
                ->assertStatus(201);
        }

        // La 4ª debe ser bloqueada
        $this->postJson('/api/chat/conversations', $validPayload)
            ->assertStatus(429);
    }
}
