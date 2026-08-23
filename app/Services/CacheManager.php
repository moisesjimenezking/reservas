<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheManager
{
    /**
     * Check if a location is available for the given time range on a date.
     * Cache key: loc:{ubicacion}:{fecha}
     */
    public function isLocationAvailable(
        string $fecha,
        string $ubicacion,
        int $startMinutes,
        int $endMinutes,
    ): bool {
        $key = "loc:{$ubicacion}:{$fecha}";
        $reservations = Cache::get($key, []);

        foreach ($reservations as $res) {
            if ($startMinutes < $res['end'] && $res['start'] < $endMinutes) {
                return false;
            }
        }

        return true;
    }

    /**
     * Book a location in cache for the given time range.
     */
    public function bookLocation(
        string $fecha,
        string $ubicacion,
        int $startMinutes,
        int $endMinutes,
    ): void {
        $key = "loc:{$ubicacion}:{$fecha}";
        $reservations = Cache::get($key, []);
        $reservations[] = [
            'start' => $startMinutes,
            'end' => $endMinutes,
        ];

        $ttl = (
            (new \Carbon\Carbon($fecha))
                ->addDays(2)
                ->startOfDay()
                ->timestamp - now()
                ->timestamp
        );

        Cache::put($key, $reservations, max($ttl, 3600));
    }

    /**
     * Remove all cache entries for a location on a date.
     */
    public function cancelLocation(string $fecha, string $ubicacion): void
    {
        $key = "loc:{$ubicacion}:{$fecha}";
        Cache::forget($key);
    }
}
