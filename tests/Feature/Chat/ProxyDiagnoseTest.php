<?php

namespace Tests\Feature\Chat;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProxyDiagnoseTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_404_when_token_not_configured(): void
    {
        config(['chat.diagnose_token' => '']);

        $response = $this->withHeaders(['X-Diagnose-Token' => 'cualquier-valor'])
            ->getJson('/api/chat/proxy-info');

        $response->assertStatus(404);
    }

    public function test_returns_404_when_token_is_wrong(): void
    {
        config(['chat.diagnose_token' => 'secreto-correcto']);

        $response = $this->withHeaders(['X-Diagnose-Token' => 'token-incorrecto'])
            ->getJson('/api/chat/proxy-info');

        $response->assertStatus(404);
    }

    public function test_returns_404_when_no_token_header_sent(): void
    {
        config(['chat.diagnose_token' => 'secreto-correcto']);

        $response = $this->getJson('/api/chat/proxy-info');
        $response->assertStatus(404);
    }

    public function test_returns_200_with_ip_info_when_token_correct(): void
    {
        config(['chat.diagnose_token' => 'mi-secreto']);

        $response = $this->withHeaders(['X-Diagnose-Token' => 'mi-secreto'])
            ->getJson('/api/chat/proxy-info');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'remote_addr',
                'x_forwarded_for',
                'cf_connecting_ip',
                'x_real_ip',
                'laravel_ip',
                'trusted_proxies',
            ]);
    }
}
