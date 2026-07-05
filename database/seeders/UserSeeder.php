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
            'name' => 'Administrador Sistema',
            'email' => 'admin@sistema.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'nombres' => 'Administrador',
            'apellido_paterno' => 'Sistema',
            'apellido_materno' => null,
        ]);

        // Usuario Operador
        User::create([
            'name' => 'Operador Ejemplo',
            'email' => 'operador@sistema.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_OPERADOR,
            'nombres' => 'Operador',
            'apellido_paterno' => 'Ejemplo',
            'apellido_materno' => null,
        ]);

        // Usuario Cliente
        User::create([
            'name' => 'Cliente Demo',
            'email' => 'cliente@sistema.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_CLIENTE,
            'nombres' => 'Cliente',
            'apellido_paterno' => 'Demo',
            'apellido_materno' => null,
        ]);
    }
}
