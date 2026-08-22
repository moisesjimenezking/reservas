<?php

namespace Database\Seeders;

use App\Models\Mesa;
use App\Models\Reserva;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReservaSeeder extends Seeder
{
    public function run(): void
    {
        $users = \App\Models\User::all()->pluck('id')->toArray();
        if (empty($users)) {
            $users = [1];
        }

        $today = Carbon::now();

        // Reservas para hoy y mañana distribuidas por ubicación
        $dateOffsets = [-1, 0, 0, 0, 1, 1, 1];
        $dates = array_map(fn($offset) => $today->copy()->addDays($offset), $dateOffsets);

        foreach ($dates as $date) {
            $fecha = $date->format('Y-m-d');

            // Ubicación A - Terraza
            Reserva::create([
                'user_id' => $users[array_rand($users)],
                'mesa_ids' => [Mesa::where('ubicacion', 'A')->inRandomOrder()->first()->id],
                'fecha_reserva' => $fecha,
                'hora_inicio' => '12:00',
                'hora_fin' => '14:00',
                'cantidad_personas' => 4,
                'estado' => 'confirmada',
            ]);

            Reserva::create([
                'user_id' => $users[array_rand($users)],
                'mesa_ids' => [Mesa::where('ubicacion', 'A')->inRandomOrder()->first()->id],
                'fecha_reserva' => $fecha,
                'hora_inicio' => '19:00',
                'hora_fin' => '21:00',
                'cantidad_personas' => 6,
                'estado' => 'confirmada',
            ]);

            // Ubicación B - Sala Principal
            Reserva::create([
                'user_id' => $users[array_rand($users)],
                'mesa_ids' => [Mesa::where('ubicacion', 'B')->inRandomOrder()->first()->id],
                'fecha_reserva' => $fecha,
                'hora_inicio' => '13:00',
                'hora_fin' => '15:00',
                'cantidad_personas' => 8,
                'estado' => 'confirmada',
            ]);

            Reserva::create([
                'user_id' => $users[array_rand($users)],
                'mesa_ids' => [Mesa::where('ubicacion', 'B')->inRandomOrder()->first()->id],
                'fecha_reserva' => $fecha,
                'hora_inicio' => '20:00',
                'hora_fin' => '22:00',
                'cantidad_personas' => 3,
                'estado' => 'confirmada',
            ]);

            // Ubicación C - Salón Privado
            Reserva::create([
                'user_id' => $users[array_rand($users)],
                'mesa_ids' => json_encode([
                    Mesa::where('ubicacion', 'C')->orderBy('capacidad', 'desc')->first()->id,
                ]),
                'fecha_reserva' => $fecha,
                'hora_inicio' => '18:00',
                'hora_fin' => '20:00',
                'cantidad_personas' => 10,
                'estado' => 'confirmada',
            ]);

            // Ubicación D - Barra & Lounge
            Reserva::create([
                'user_id' => $users[array_rand($users)],
                'mesa_ids' => [Mesa::where('ubicacion', 'D')->inRandomOrder()->first()->id],
                'fecha_reserva' => $fecha,
                'hora_inicio' => '17:00',
                'hora_fin' => '19:00',
                'cantidad_personas' => 2,
                'estado' => 'confirmada',
            ]);

            // Algunas mesas combinadas (2 mesas en misma ubicación)
            if ($date->day % 2 === 0) {
                $mesasUnidas = Mesa::where('ubicacion', 'B')
                    ->take(2)
                    ->pluck('id')
                    ->toArray();
                Reserva::create([
                    'user_id' => $users[array_rand($users)],
                    'mesa_ids' => $mesasUnidas,
                    'fecha_reserva' => $fecha,
                    'hora_inicio' => '15:00',
                    'hora_fin' => '17:00',
                    'cantidad_personas' => 12,
                    'estado' => 'confirmada',
                ]);
            }
        }
    }
}
