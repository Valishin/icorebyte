<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Horario configurado en config/chat.php:
 *   L-V: 10:00-13:30 y 17:00-20:00
 *   Sábado: 10:00-13:00
 *   Domingo: cerrado
 */
class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function svc(): AvailabilityService
    {
        return app(AvailabilityService::class);
    }

    // ── Franjas múltiples ────────────────────────────────────────────────────

    public function test_1329_on_tuesday_is_open(): void
    {
        $t = CarbonImmutable::parse('2026-11-17 13:29:00', 'Europe/Madrid'); // martes
        $this->assertTrue($this->svc()->isOpen($t));
    }

    public function test_1331_on_tuesday_is_closed_between_slots(): void
    {
        $t = CarbonImmutable::parse('2026-11-17 13:31:00', 'Europe/Madrid');
        $this->assertFalse($this->svc()->isOpen($t));
    }

    public function test_1500_on_tuesday_is_closed_with_next_open_today(): void
    {
        $t = CarbonImmutable::parse('2026-11-17 15:00:00', 'Europe/Madrid');
        $this->assertFalse($this->svc()->isOpen($t));

        $text = $this->svc()->nextOpenText('es', $t);
        $this->assertStringContainsString('17:00', $text);
        $this->assertStringContainsString('hoy', $text);
    }

    public function test_2001_on_tuesday_is_closed_with_next_open_tomorrow(): void
    {
        $t = CarbonImmutable::parse('2026-11-17 20:01:00', 'Europe/Madrid');
        $this->assertFalse($this->svc()->isOpen($t));

        $text = $this->svc()->nextOpenText('es', $t);
        $this->assertStringContainsString('10:00', $text);
        $this->assertStringContainsString('mañana', $text);
        $this->assertStringContainsString('miércoles', $text);
    }

    public function test_saturday_1301_next_open_is_monday(): void
    {
        $t = CarbonImmutable::parse('2026-11-14 13:01:00', 'Europe/Madrid'); // sábado
        $this->assertFalse($this->svc()->isOpen($t));

        $text = $this->svc()->nextOpenText('es', $t);
        $this->assertStringContainsString('lunes', $text);
        $this->assertStringContainsString('10:00', $text);
    }

    public function test_sunday_is_closed(): void
    {
        $t = CarbonImmutable::parse('2026-11-15 12:00:00', 'Europe/Madrid'); // domingo
        $this->assertFalse($this->svc()->isOpen($t));
    }

    public function test_saturday_1100_is_open(): void
    {
        $t = CarbonImmutable::parse('2026-11-14 11:00:00', 'Europe/Madrid');
        $this->assertTrue($this->svc()->isOpen($t));
    }

    public function test_monday_1030_is_open(): void
    {
        $t = CarbonImmutable::parse('2026-11-16 10:30:00', 'Europe/Madrid');
        $this->assertTrue($this->svc()->isOpen($t));
    }

    public function test_monday_1730_is_open_in_afternoon_slot(): void
    {
        $t = CarbonImmutable::parse('2026-11-16 17:30:00', 'Europe/Madrid');
        $this->assertTrue($this->svc()->isOpen($t));
    }

    // ── DST — cruzan el cambio de hora ───────────────────────────────────────

    /**
     * Sáb 24/10/2026 13:01 (CEST +2, horario de verano).
     * El dom 25/10 cae el cambio a CET+1 (fall-back).
     * Próxima apertura: lunes 26/10 a las 10:00 (hora Madrid, ya en CET+1).
     */
    public function test_dst_fallback_saturday_1301_next_open_is_monday(): void
    {
        $t = CarbonImmutable::parse('2026-10-24 13:01:00', 'Europe/Madrid');
        $this->assertFalse($this->svc()->isOpen($t));

        $text = $this->svc()->nextOpenText('es', $t);
        $this->assertStringContainsString('lunes', $text);
        $this->assertStringContainsString('10:00', $text);
    }

    /**
     * Sáb 24/10/2026 20:00 — resultado idéntico.
     */
    public function test_dst_fallback_saturday_2000_next_open_is_monday(): void
    {
        $t = CarbonImmutable::parse('2026-10-24 20:00:00', 'Europe/Madrid');
        $text = $this->svc()->nextOpenText('es', $t);
        $this->assertStringContainsString('lunes', $text);
        $this->assertStringContainsString('10:00', $text);
    }

    /**
     * Sáb 28/03/2026 13:01 (CET +1, horario de invierno).
     * El dom 29/03 cae el spring-forward a CEST+2.
     * Próxima apertura: lunes 30/03 a las 10:00 (hora Madrid, ya en CEST+2).
     */
    public function test_dst_spring_forward_saturday_1301_next_open_is_monday(): void
    {
        $t = CarbonImmutable::parse('2026-03-28 13:01:00', 'Europe/Madrid');
        $this->assertFalse($this->svc()->isOpen($t));

        $text = $this->svc()->nextOpenText('es', $t);
        $this->assertStringContainsString('lunes', $text);
        $this->assertStringContainsString('10:00', $text);
    }

    // ── Festivos ─────────────────────────────────────────────────────────────

    public function test_holiday_is_closed(): void
    {
        config(['chat.holidays' => ['2026-11-16']]);
        $t = CarbonImmutable::parse('2026-11-16 11:00:00', 'Europe/Madrid');
        $this->assertFalse($this->svc()->isOpen($t));
    }

    // ── Vacaciones ───────────────────────────────────────────────────────────

    public function test_during_vacation_range_is_closed(): void
    {
        config(['chat.vacation_ranges' => [
            ['start' => '2026-12-24', 'end' => '2027-01-07', 'message' => 'Volvemos el 7 de enero.'],
        ]]);
        $t = CarbonImmutable::parse('2026-12-27 11:00:00', 'Europe/Madrid');
        $this->assertFalse($this->svc()->isOpen($t));
    }

    public function test_vacation_message_shown_in_next_open_text(): void
    {
        config(['chat.vacation_ranges' => [
            ['start' => '2026-12-24', 'end' => '2027-01-07', 'message' => 'Volvemos el 7 de enero.'],
        ]]);
        $t    = CarbonImmutable::parse('2026-12-27 11:00:00', 'Europe/Madrid');
        $text = $this->svc()->nextOpenText('es', $t);
        $this->assertStringContainsString('7 de enero', $text);
    }

    // ── Interruptor manual ───────────────────────────────────────────────────

    public function test_agent_available_by_default(): void
    {
        $this->assertTrue($this->svc()->isAgentAvailable());
    }

    public function test_set_agent_unavailable_affects_is_online(): void
    {
        $this->svc()->setAgentAvailable(false);

        // Lunes dentro de horario → isOpen() true, pero isOnline() false
        $t = CarbonImmutable::parse('2026-11-16 11:00:00', 'Europe/Madrid');
        $this->assertTrue($this->svc()->isOpen($t));
        $this->assertFalse($this->svc()->isOnline($t));
    }

    public function test_set_agent_available_restores_online(): void
    {
        $this->svc()->setAgentAvailable(false);
        $this->svc()->setAgentAvailable(true);

        $t = CarbonImmutable::parse('2026-11-16 11:00:00', 'Europe/Madrid');
        $this->assertTrue($this->svc()->isOnline($t));
    }

    // ── Status payload ───────────────────────────────────────────────────────

    public function test_status_includes_is_online_field(): void
    {
        $response = $this->getJson('/api/chat/availability');
        $response->assertStatus(200)
            ->assertJsonStructure(['is_open', 'is_online', 'next_open_text', 'timezone']);
    }

    // ── Multilang welcome messages ────────────────────────────────────────────

    public function test_welcome_message_in_english_when_locale_en(): void
    {
        $this->mock(AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isOnline')->andReturn(true);
            $mock->shouldReceive('isOpen')->andReturn(true);
            $mock->shouldReceive('isAgentAvailable')->andReturn(true);
            $mock->shouldReceive('status')->andReturn([
                'is_open' => true, 'is_online' => true, 'next_open_text' => '', 'timezone' => 'Europe/Madrid',
            ]);
        });

        $response = $this->postJson('/api/chat/conversations', [
            'visitor_name' => 'John',
            'consent'      => '1',
            'locale'       => 'en',
        ]);
        $response->assertStatus(201);

        $token = $response->json('visitor_token');
        $uuid  = $response->json('uuid');

        $messages = $this->withHeaders(['X-Chat-Visitor-Token' => $token])
            ->getJson("/api/chat/conversations/{$uuid}/messages")
            ->json('messages');

        $sysMsg = collect($messages)->firstWhere('sender', 'system');
        $this->assertStringContainsString('Thanks', $sysMsg['body']);
    }

    public function test_locale_detected_from_accept_language_header(): void
    {
        $this->mock(AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isOnline')->andReturn(true);
            $mock->shouldReceive('isOpen')->andReturn(true);
            $mock->shouldReceive('isAgentAvailable')->andReturn(true);
            $mock->shouldReceive('status')->andReturn([
                'is_open' => true, 'is_online' => true, 'next_open_text' => '', 'timezone' => 'Europe/Madrid',
            ]);
        });

        $response = $this->withHeaders(['Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8'])
            ->postJson('/api/chat/conversations', [
                'visitor_name' => 'Pierre',
                'consent'      => '1',
            ]);
        $response->assertStatus(201);

        $token = $response->json('visitor_token');
        $uuid  = $response->json('uuid');

        $messages = $this->withHeaders(['X-Chat-Visitor-Token' => $token])
            ->getJson("/api/chat/conversations/{$uuid}/messages")
            ->json('messages');

        $sysMsg = collect($messages)->firstWhere('sender', 'system');
        $this->assertStringContainsString('Bonjour', $sysMsg['body']);
    }

    public function test_away_message_sent_when_agent_unavailable_within_hours(): void
    {
        $this->mock(AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isOnline')->andReturn(false);
            $mock->shouldReceive('isOpen')->andReturn(true);
            $mock->shouldReceive('isAgentAvailable')->andReturn(false);
            $mock->shouldReceive('status')->andReturn([
                'is_open' => true, 'is_online' => false, 'next_open_text' => '', 'timezone' => 'Europe/Madrid',
            ]);
        });

        $response = $this->postJson('/api/chat/conversations', [
            'visitor_name' => 'Ana',
            'consent'      => '1',
            'locale'       => 'es',
        ]);
        $response->assertStatus(201);

        $token = $response->json('visitor_token');
        $uuid  = $response->json('uuid');

        $messages = $this->withHeaders(['X-Chat-Visitor-Token' => $token])
            ->getJson("/api/chat/conversations/{$uuid}/messages")
            ->json('messages');

        $sysMsg = collect($messages)->firstWhere('sender', 'system');
        $this->assertStringContainsString('no estamos disponibles', $sysMsg['body']);
    }
}
