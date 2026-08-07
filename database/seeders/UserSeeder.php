<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ──────────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@sistema.com'],
            [
                'name'              => 'Administrador Sistema',
                'password'          => Hash::make('password123'),
                'role'              => User::ROLE_ADMIN,
                'estatus'           => User::ESTATUS_ACTIVO,
                'nombres'           => 'Administrador',
                'apellido_paterno'  => 'Sistema',
                'apellido_materno'  => null,
            ]
        );

        // ── Operadores ─────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'operador@sistema.com'],
            [
                'name'              => 'Carlos Mendoza López',
                'password'          => Hash::make('password123'),
                'role'              => User::ROLE_OPERADOR,
                'estatus'           => User::ESTATUS_ACTIVO,
                'nombres'           => 'Carlos',
                'apellido_paterno'  => 'Mendoza',
                'apellido_materno'  => 'López',
            ]
        );

        User::updateOrCreate(
            ['email' => 'operador2@sistema.com'],
            [
                'name'              => 'María García Ruiz',
                'password'          => Hash::make('password123'),
                'role'              => User::ROLE_OPERADOR,
                'estatus'           => User::ESTATUS_ACTIVO,
                'nombres'           => 'María',
                'apellido_paterno'  => 'García',
                'apellido_materno'  => 'Ruiz',
            ]
        );
    }
}
