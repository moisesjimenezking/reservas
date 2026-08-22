<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $nombres = [
            ['name' => 'Carlos Mendoza', 'email' => 'carlos@email.com'],
            ['name' => 'María García', 'email' => 'maria@email.com'],
            ['name' => 'José López', 'email' => 'jose@email.com'],
            ['name' => 'Ana Hernández', 'email' => 'ana@email.com'],
            ['name' => 'Luis Martínez', 'email' => 'luis@email.com'],
            ['name' => 'Sofía Ramírez', 'email' => 'sofia@email.com'],
            ['name' => 'Diego Torres', 'email' => 'diego@email.com'],
            ['name' => 'Valentina Cruz', 'email' => 'valentina@email.com'],
            ['name' => 'Andrés Morales', 'email' => 'andres@email.com'],
            ['name' => 'Camila Flores', 'email' => 'camila@email.com'],
        ];

        foreach ($nombres as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make('password'),
            ]);
        }
    }
}
