<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario Administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@sistema.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        // Usuario Operador
        User::create([
            'name' => 'Operador',
            'email' => 'operador@sistema.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_OPERADOR,
        ]);

        // Usuario Cliente
        User::create([
            'name' => 'Cliente',
            'email' => 'cliente@sistema.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_CLIENTE,
        ]);
    }
}
