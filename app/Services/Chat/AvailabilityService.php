<?php

namespace App\Services\Chat;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

/**
 * Lógica de disponibilidad del chat.
 *
 * isOpen()         — ¿está dentro del horario configurado?
 * isAgentAvailable() — ¿el interruptor manual está en "disponible"?
 * isOnline()       — isOpen() && isAgentAvailable() → muestra "atendemos enseguida"
 *
 * setAgentAvailable() se llama desde:
 *   - Fase 3: comandos de Telegram /disponible y /ausente
 *   - Fase 4: botón en el panel Filament
 */
class AvailabilityService
{
    private const AGENT_AVAILABLE_KEY    = 'chat.agent_available';
    private const AGENT_AVAILABLE_CACHE  = 60; // segundos

    // ── API pública ───────────────────────────────────────────────────────────

    /**
     * ¿Está dentro del horario Y el agente está disponible?
     * Este es el estado que determina el mensaje de bienvenida.
     */
    public function isOnline(?CarbonImmutable $at = null): bool
    {
        return $this->isOpen($at) && $this->isAgentAvailable();
    }

    /**
     * ¿Está dentro del horario configurado?
     * No tiene en cuenta el interruptor manual.
     */
    public function isOpen(?CarbonImmutable $at = null): bool
    {
        $now = $this->now($at);

        if ($this->isHoliday($now) || $this->isInVacationRange($now)) {
            return false;
        }

        foreach ($this->slotsForDay($now->dayOfWeekIso) as [$start, $end]) {
            if ($now->between($now->setTimeFromTimeString($start), $now->setTimeFromTimeString($end), true)) {
                return true;
            }
        }

        return false;
    }

    /** Interruptor manual: ¿está el agente marcado como disponible? */
    public function isAgentAvailable(): bool
    {
        return Cache::remember(self::AGENT_AVAILABLE_KEY, self::AGENT_AVAILABLE_CACHE, function () {
            return Setting::get(self::AGENT_AVAILABLE_KEY, '1') !== '0';
        });
    }

    /** Cambiar el interruptor manual (Telegram/Filament). */
    public function setAgentAvailable(bool $available): void
    {
        Setting::set(self::AGENT_AVAILABLE_KEY, $available ? '1' : '0');
        Cache::forget(self::AGENT_AVAILABLE_KEY);
    }

    /**
     * Texto del próximo momento de apertura en el idioma indicado.
     * Si hay un rango de vacaciones activo, devuelve su mensaje.
     */
    public function nextOpenText(string $locale = 'es', ?CarbonImmutable $at = null): string
    {
        $now = $this->now($at);

        $vacMsg = $this->currentVacationMessage($now, $locale);
        if ($vacMsg !== null) {
            return $vacMsg;
        }

        $next = $this->nextOpenTime($now);
        if ($next === null) {
            return (string) trans('chat.no_schedule', [], $locale);
        }

        $time    = $next->format('H:i');
        $dayName = (string) trans('chat.day_' . $next->dayOfWeekIso, [], $locale);
        $diffDays = (int) $now->startOfDay()->diffInDays($next->startOfDay());

        return match ($diffDays) {
            0       => (string) trans('chat.next_open_today',    ['time' => $time],                    $locale),
            1       => (string) trans('chat.next_open_tomorrow', ['time' => $time, 'day' => $dayName], $locale),
            default => (string) trans('chat.next_open_later',    ['time' => $time, 'day' => $dayName], $locale),
        };
    }

    /**
     * Payload completo para el widget.
     *
     * @return array{is_open: bool, is_online: bool, next_open_text: string, timezone: string}
     */
    public function status(string $locale = 'es', ?CarbonImmutable $at = null): array
    {
        $open   = $this->isOpen($at);
        $online = $open && $this->isAgentAvailable();

        return [
            'is_open'        => $open,
            'is_online'      => $online,
            'next_open_text' => $open ? '' : $this->nextOpenText($locale, $at),
            'timezone'       => $this->timezone(),
        ];
    }

    // ── Helpers privados ─────────────────────────────────────────────────────

    private function now(?CarbonImmutable $at): CarbonImmutable
    {
        return ($at ?? CarbonImmutable::now($this->timezone()))->setTimezone($this->timezone());
    }

    private function timezone(): string
    {
        return (string) config('chat.timezone', 'Europe/Madrid');
    }

    /**
     * Franjas horarias para el día ISO dado (1=lun … 7=dom).
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private function slotsForDay(int $isoDay): array
    {
        /** @var array<int, array<int, array{0: string, 1: string}>> $schedule */
        $schedule = config('chat.schedule', []);
        return $schedule[$isoDay] ?? [];
    }

    private function isHoliday(CarbonImmutable $date): bool
    {
        /** @var string[] $holidays */
        $holidays = config('chat.holidays', []);
        return in_array($date->toDateString(), $holidays, true);
    }

    private function isInVacationRange(CarbonImmutable $date): bool
    {
        /** @var array<int, array{start: string, end: string, message?: string}> $ranges */
        $ranges = config('chat.vacation_ranges', []);
        foreach ($ranges as $range) {
            $start = CarbonImmutable::parse($range['start'], $this->timezone())->startOfDay();
            $end   = CarbonImmutable::parse($range['end'],   $this->timezone())->endOfDay();
            if ($date->between($start, $end)) {
                return true;
            }
        }
        return false;
    }

    /** Devuelve el mensaje de vacaciones si la fecha está en un rango, o null. */
    private function currentVacationMessage(CarbonImmutable $date, string $locale): ?string
    {
        /** @var array<int, array{start: string, end: string, message?: string}> $ranges */
        $ranges = config('chat.vacation_ranges', []);
        foreach ($ranges as $range) {
            $start = CarbonImmutable::parse($range['start'], $this->timezone())->startOfDay();
            $end   = CarbonImmutable::parse($range['end'],   $this->timezone())->endOfDay();
            if ($date->between($start, $end)) {
                return $range['message'] ?? (string) trans('chat.no_schedule', [], $locale);
            }
        }
        return null;
    }

    /**
     * Calcula el próximo momento de apertura cronológicamente posterior a $from.
     * Busca hasta 60 días hacia adelante para cubrir rangos de vacaciones largos.
     */
    private function nextOpenTime(CarbonImmutable $from): ?CarbonImmutable
    {
        $cursor = $from;

        for ($i = 0; $i < 60; $i++) {
            $date = $i === 0 ? $cursor : $cursor->startOfDay();

            if (! ($this->isHoliday($date) || $this->isInVacationRange($date))) {
                foreach ($this->slotsForDay($date->dayOfWeekIso) as [$start]) {
                    $openAt = $date->setTimeFromTimeString($start);
                    if ($openAt->gt($from)) {
                        return $openAt;
                    }
                }
            }

            $cursor = $cursor->addDay()->startOfDay();
        }

        return null;
    }
}
