<?php

namespace App\Services\Chat;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Wrapper de la Bot API de Telegram.
 *
 * Todos los métodos devuelven el payload "result" en éxito, o null si la API
 * falla, registrando el error en el log. Nunca propagan excepciones al llamador.
 */
class TelegramService
{
    private string $apiBase;

    public function __construct()
    {
        $token = (string) config('services.telegram.token', '');
        $this->apiBase = "https://api.telegram.org/bot{$token}";
    }

    // ── Mensajes de texto ─────────────────────────────────────────────────────

    /**
     * Envía un mensaje de texto al chat configurado.
     * Siempre usa parse_mode HTML; el llamador debe htmlspecialchars() el texto del usuario.
     *
     * @param  array<string, mixed> $extra  Parámetros adicionales (reply_markup, etc.)
     * @return array<string, mixed>|null    El resultado de Telegram, o null si falla
     */
    public function sendMessage(string $text, array $extra = []): ?array
    {
        return $this->post('sendMessage', array_merge([
            'chat_id'    => config('services.telegram.chat_id'),
            'text'       => $text,
            'parse_mode' => 'HTML',
        ], $extra));
    }

    /**
     * Envía una foto desde disco privado como multipart (evita URLs firmadas que caducan).
     *
     * @param  array<string, mixed> $extra
     * @return array<string, mixed>|null
     */
    public function sendPhoto(string $storageDisk, string $storagePath, string $caption = '', array $extra = []): ?array
    {
        $localPath = Storage::disk($storageDisk)->path($storagePath);

        if (! file_exists($localPath)) {
            Log::warning('TelegramService::sendPhoto — archivo no encontrado', ['path' => $localPath]);
            return null;
        }

        $params = array_merge([
            'chat_id'    => config('services.telegram.chat_id'),
            'caption'    => $caption,
            'parse_mode' => 'HTML',
        ], $extra);

        return $this->postMultipart('sendPhoto', $params, 'photo', $localPath);
    }

    // ── Descarga de archivos ──────────────────────────────────────────────────

    /**
     * Descarga un archivo de Telegram y lo devuelve como contenido binario.
     * Retorna null si falla.
     */
    public function downloadFile(string $fileId): ?string
    {
        $result = $this->post('getFile', ['file_id' => $fileId]);
        if ($result === null || ! isset($result['file_path'])) {
            return null;
        }

        $token    = (string) config('services.telegram.token', '');
        $url      = "https://api.telegram.org/file/bot{$token}/{$result['file_path']}";

        try {
            $response = Http::timeout(30)->get($url);
            if (! $response->successful()) {
                Log::warning('TelegramService::downloadFile — fallo al descargar', ['url' => $url, 'status' => $response->status()]);
                return null;
            }
            return $response->body();
        } catch (\Throwable $e) {
            Log::error('TelegramService::downloadFile — excepción', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ── Gestión de mensajes ───────────────────────────────────────────────────

    /**
     * Elimina un mensaje del chat de Telegram.
     * Devuelve [] (array vacío) si se borró correctamente, null si Telegram lo rechaza.
     *
     * @return array<string, mixed>|null
     */
    public function deleteMessage(int $messageId): ?array
    {
        return $this->post('deleteMessage', [
            'chat_id'    => config('services.telegram.chat_id'),
            'message_id' => $messageId,
        ]);
    }

    // ── Callback queries ──────────────────────────────────────────────────────

    public function answerCallbackQuery(string $callbackQueryId, string $text = ''): void
    {
        $this->post('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text'              => $text,
        ]);
    }

    // ── Webhook ───────────────────────────────────────────────────────────────

    /**
     * @param  string[] $allowedUpdates
     */
    public function setWebhook(string $url, string $secretToken, array $allowedUpdates = ['message', 'callback_query']): bool
    {
        $result = $this->post('setWebhook', [
            'url'             => $url,
            'secret_token'    => $secretToken,
            'allowed_updates' => $allowedUpdates,
        ]);

        return $result !== null;
    }

    public function deleteWebhook(): bool
    {
        return $this->post('deleteWebhook', []) !== null;
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed> $params
     * @return array<string, mixed>|null
     */
    private function post(string $method, array $params): ?array
    {
        try {
            $response = Http::timeout(10)->post("{$this->apiBase}/{$method}", $params);
            return $this->parseResponse($method, $response);
        } catch (\Throwable $e) {
            Log::error("TelegramService::{$method} — excepción", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @param  array<string, mixed> $params
     * @return array<string, mixed>|null
     */
    private function postMultipart(string $method, array $params, string $fileField, string $filePath): ?array
    {
        try {
            $request = Http::timeout(30)->attach($fileField, (string) file_get_contents($filePath), basename($filePath));
            foreach ($params as $key => $value) {
                $request = $request->attach($key, (string) $value);
            }
            $response = $request->post("{$this->apiBase}/{$method}");
            return $this->parseResponse($method, $response);
        } catch (\Throwable $e) {
            Log::error("TelegramService::{$method}(multipart) — excepción", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseResponse(string $method, Response $response): ?array
    {
        $body = $response->json();
        if (! is_array($body) || ! ($body['ok'] ?? false)) {
            Log::warning("TelegramService::{$method} — respuesta no OK", [
                'status'      => $response->status(),
                'description' => is_array($body) ? ($body['description'] ?? '(sin descripción)') : '(respuesta inválida)',
            ]);
            return null;
        }
        $result = $body['result'] ?? [];
        // Telegram devuelve true (booleano) en métodos como setWebhook, deleteWebhook,
        // deleteMessage y answerCallbackQuery. Lo normalizamos a [] para respetar ?array.
        if (! is_array($result)) {
            return [];
        }
        /** @var array<string, mixed> $result */
        return $result;
    }
}
