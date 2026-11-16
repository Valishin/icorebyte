<?php

namespace Tests\Feature\Chat;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProxyDiagnoseTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_404_when_token_not_configured(): void
    {
        // Sin CHAT_DIAGNOSE_TOKEN en .env → la ruta no existe
        $this->app['config']->set('app.chat_diagnose_token', '');

        $response = $this->withHeaders(['X-Diagnose-Token' => 'cualquier-valor'])
            ->getJson('/api/chat/proxy-info');

        $response->assertStatus(404);
    }

    public function test_returns_404_when_token_is_wrong(): void
    {
        $this->withEnvironmentVariables(['CHAT_DIAGNOSE_TOKEN' => 'secreto-correcto'], function () {
            $response = $this->withHeaders(['X-Diagnose-Token' => 'token-incorrecto'])
                ->getJson('/api/chat/proxy-info');

            $response->assertStatus(404);
        });
    }

    public function test_returns_404_when_no_token_header_sent(): void
    {
        $this->withEnvironmentVariables(['CHAT_DIAGNOSE_TOKEN' => 'secreto-correcto'], function () {
            $response = $this->getJson('/api/chat/proxy-info');
            $response->assertStatus(404);
        });
    }

    public function test_returns_200_with_ip_info_when_token_correct(): void
    {
        $this->withEnvironmentVariables(['CHAT_DIAGNOSE_TOKEN' => 'mi-secreto'], function () {
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
        });
    }

    /**
     * Ejecuta $callback con variables de entorno temporales, restaurando los valores originales.
     */
    private function withEnvironmentVariables(array $vars, callable $callback): void
    {
        $original = [];
        foreach ($vars as $key => $value) {
            $original[$key] = getenv($key);
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }

        try {
            $callback();
        } finally {
            foreach ($original as $key => $value) {
                if ($value === false) {
                    putenv($key);
                    unset($_ENV[$key]);
                } else {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                }
            }
        }
    }
}
