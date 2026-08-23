<?php

namespace App\Services;

use App\Models\Mesa;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LocationSelector
{
    public function __construct(
        private CacheManager $cache,
    ) {
    }

    /**
     * Auto-assign location and tables for a reservation request.
     * The system selects the first available location (A→D) and greedily
     * assigns up to 3 tables starting from the smallest until capacity is met.
     *
     * @return Reserva|null
     */
    public function autoAssign(
        int $userId,
        string $fecha,
        string $horaInicio,
        int $personas,
    ): ?Reserva {
        $horaFin = $this->calculateEndTime($fecha, $horaInicio);
        $start = $this->toMinutes($fecha, $horaInicio);
        $end = $this->toMinutes($fecha, $horaFin);

        $this->validateBusinessHours($fecha, $start, $end);
        $today = now()->format('Y-m-d');
        if ($fecha === $today) {
            $reservaTime = Carbon::parse("{$fecha} {$horaInicio}");
            if ($reservaTime->hour < 2) {
                $reservaTime->addDay();
            }
            if ($reservaTime->lessThan(now()->copy()->addMinutes(15))) {
                throw new \InvalidArgumentException('Las reservas deben hacerse con al menos 15 minutos de anticipación.');
            }
        }

        return DB::transaction(function () use ($userId, $fecha, $horaInicio, $horaFin, $start, $end, $personas) {
            foreach (['A', 'B', 'C', 'D'] as $ubicacion) {
                $result = $this->findTablesInLocation($ubicacion, $fecha, $start, $end, (int) $personas);
                if ($result === null) {
                    continue;
                }

                $mesaIds = $result['mesa_ids'];
                $this->cache->bookLocation($fecha, $ubicacion, $start, $end);
                return Reserva::create([
                    'user_id' => $userId,
                    'mesa_ids' => $mesaIds,
                    'fecha_reserva' => $fecha,
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                    'cantidad_personas' => (int) $personas,
                    'estado' => 'confirmada',
                ]);
            }

            return null;
        });
    }

    /**
     * Get assigned table details for a reservation (for PDF receipt).
     */
    public function getTableDetails(Reserva $reserva): array
    {
        $mesaIds = is_array($reserva->mesa_ids)
            ? $reserva->mesa_ids
            : json_decode($reserva->mesa_ids, true);

        if (empty($mesaIds)) {
            return [];
        }

        $tables = Mesa::whereIn('id', $mesaIds)->get()->keyBy('id');
        $details = [];

        foreach ($mesaIds as $id) {
            $mesa = $tables->get($id);
            if ($mesa) {
                $details[] = [
                    'id' => $mesa->id,
                    'numero' => $mesa->numero,
                    'ubicacion' => $mesa->ubicacion,
                    'ubicacion_label' => $mesa->ubicacion_label,
                    'capacidad' => $mesa->capacidad,
                ];
            }
        }

        return $details;
    }

    /**
     * Find available tables in a location.
     * Greedily picks smallest tables first until capacity met or max 3 is reached.
     * Conflict checking respects the actual reservation duration (2h or 1h for last slot).
     */
    private function findTablesInLocation(
        string $ubicacion,
        string $fecha,
        int $start,
        int $end,
        int $personas,
    ): ?array {
        $tables = Mesa::where('ubicacion', $ubicacion)
            ->orderBy('capacidad')
            ->lockForUpdate()
            ->get();

        if ($tables->isEmpty()) {
            return null;
        }

        $conflictingReservas = Reserva::whereDate('fecha_reserva', $fecha)
            ->confirmed()
            ->get();

        $available = [];
        foreach ($tables as $mesa) {
            $isFree = true;
            foreach ($conflictingReservas as $r) {
                if (! in_array($mesa->id, $r->mesa_ids)) {
                    continue;
                }

                $dbStart = $this->toMinutes($r->fecha_reserva->format('Y-m-d'), $r->hora_inicio);
                $dbEnd = $this->toMinutes($r->fecha_reserva->format('Y-m-d'), $r->hora_fin);
                if ($start < $dbEnd && $dbStart < $end) {
                    $isFree = false;
                    break;
                }
            }

            if ($isFree) {
                $available[] = $mesa;
            }
        }

        $selected = [];
        $combinedCapacity = 0;
        foreach ($available as $mesa) {
            $selected[] = $mesa->id;
            $combinedCapacity += $mesa->capacidad;
            if ($combinedCapacity >= $personas || count($selected) >= 3) {
                break;
            }
        }

        if (empty($selected) || $combinedCapacity < $personas) {
            return null;
        }

        return ['mesa_ids' => $selected];
    }

    private function validateBusinessHours(string $fecha, int $start, int $end): void
    {
        $dayOfWeek = (int) Carbon::parse($fecha)->format('N');

        $open = null;
        $close = null;

        switch ($dayOfWeek) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 1:
                $open = 10 * 60;
                $close = 23 * 60 + 59;
                break;

            case 6:
                $open = 22 * 60;
                $close = 26 * 60;
                break;

            case 7:
                $open = 12 * 60;
                $close = 16 * 60;
                break;

            default:
                throw new \RuntimeException('Día inválido.');
        }

        if ($start < $open || $start >= $close) {
            throw new \InvalidArgumentException(
                'La hora de inicio está fuera del horario de atención.'
            );
        }

        if ($end <= $start) {
            throw new \InvalidArgumentException(
                'La hora de fin debe ser posterior a la hora de inicio.'
            );
        }
    }

    /**
     * Calculate end time (start + 2h, or +1h if 2h would exceed business hours).
     */
    private function calculateEndTime(string $fecha, string $horaInicio): string
    {
        $startTime = Carbon::parse("{$fecha} {$horaInicio}");
        $dayOfWeek = (int) $startTime->format('N');

        $closeMinutes = match ($dayOfWeek) {
            1, 2, 3, 4, 5 => 23 * 60 + 59,
            6    => 26 * 60,
            7    => 16 * 60,
            default => throw new \RuntimeException('Día inválido.'),
        };

        $startMinutes = $this->toMinutes($fecha, $horaInicio);
        $endMinutes = $startMinutes + 120;

        if ($endMinutes > $closeMinutes) {
            $endMinutes = $startMinutes + 60;
        }

        $hours = intdiv($endMinutes, 60) % 24;
        $mins = $endMinutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

    /**
     * Get the valid time slots for a given date (for frontend display).
     * Each slot ensures the +2h reservation ends before closing.
     */
    public function getValidSlots(string $fecha): array
    {
        $dayOfWeek = (int) Carbon::parse($fecha)->format('N');
        $slots = [];

        $range = match ($dayOfWeek) {
            1, 2, 3, 4, 5 => [10, 23],
            6    => [22, 1],
            7    => [12, 15],
            default => [],
        };

        [$startH, $endH] = $range;

        if ($startH <= $endH) {
            for ($h = $startH; $h <= $endH; $h++) {
                $slots[] = sprintf('%02d:00', $h);
            }
        } else {
            for ($h = $startH; $h <= 23; $h++) {
                $slots[] = sprintf('%02d:00', $h);
            }
            for ($h = 0; $h <= $endH; $h++) {
                $slots[] = sprintf('%02d:00', $h);
            }
        }

        return $slots;
    }

    /**
     * Check which slots are fully booked across ALL locations.
     * Returns a list of time strings that are blocked (no availability in any location).
     * Uses 2-hour duration for all slots except the last one, which uses 1h
     * (since the last slot only allows 1-hour reservations).
     */
    public function getBlockedSlots(string $fecha): array
    {
        $slots = $this->getValidSlots($fecha);
        if (empty($slots)) {
            return [];
        }

        $blocked = [];
        $lastSlot = end($slots);

        foreach ($slots as $slot) {
            $slotStart = $this->toMinutes($fecha, $slot);
            $duration = ($slot === $lastSlot) ? 60 : 120;
            $slotEnd = $slotStart + $duration;
            $anyAvailable = false;
            foreach (['A', 'B', 'C', 'D'] as $ubicacion) {
                if ($this->cache->isLocationAvailable($fecha, $ubicacion, $slotStart, $slotEnd)) {
                    $anyAvailable = true;
                    break;
                }
            }

            if (!$anyAvailable) {
                $blocked[] = $slot;
            }
        }

        return $blocked;
    }

    /**
     * Convert date + time to minutes from date 00:00.
     * Handles Saturday midnight crossing: 00:00 next day = 1440 min.
     */
    private function toMinutes(string $fecha, string $hora): int
    {
        $h = (int) substr($hora, 0, 2);
        $m = (int) substr($hora, 3, 2);
        if ($h < 3) {
            return ($h + 24) * 60 + $m;
        }

        return $h * 60 + $m;
    }
}
