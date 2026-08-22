<?php

namespace Database\Seeders;

use App\Models\Mesa;
use Illuminate\Database\Seeder;

class MesaSeeder extends Seeder
{
    public function run(): void
    {
        $ubicaciones = ['A', 'B', 'C', 'D'];
        $nombres = [
            'A' => ['Terraza 1', 'Terraza 2', 'Terraza 3', 'Terraza 4'],
            'B' => ['Sala 1', 'Sala 2', 'Sala 3', 'Sala 4'],
            'C' => ['Privado 1', 'Privado 2', 'Privado 3', 'Privado 4'],
            'D' => ['Barra 1', 'Barra 2', 'Barra 3', 'Barra 4'],
        ];
        $capacidades = [
            'A' => [4, 6, 4, 8],
            'B' => [4, 4, 6, 10],
            'C' => [6, 8, 4, 12],
            'D' => [2, 4, 4, 6],
        ];

        foreach ($ubicaciones as $ubicacion) {
            for ($i = 0; $i < 4; $i++) {
                Mesa::create([
                    'ubicacion' => $ubicacion,
                    'numero' => $nombres[$ubicacion][$i],
                    'capacidad' => $capacidades[$ubicacion][$i],
                ]);
            }
        }
    }
}
