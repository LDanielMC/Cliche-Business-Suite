<?php

namespace Database\Seeders;

use App\Models\BovedaContrasena;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Seeder;

class BovedaContrasenaSeeder extends Seeder
{
    public function run(): void
    {
        $admin   = User::where('email', 'admin@sistema.com')->firstOrFail();
        // Solo clientes activos — la bóveda es operacional, no histórica
        $clientes = Cliente::activos()->with('user')->get()->values();

        // Dos credenciales por cliente para probar el CRUD de FN.07
        $credenciales = [
            [
                'plataforma' => 'Google Business Profile',
                'url'        => 'https://business.google.com',
                'password'   => 'GBP@2026!Seguro',
            ],
            [
                'plataforma' => 'Facebook Business Manager',
                'url'        => 'https://business.facebook.com',
                'password'   => 'FbAds#2026Cliche',
            ],
        ];

        foreach ($clientes as $cliente) {
            foreach ($credenciales as $cred) {
                BovedaContrasena::create([
                    'cliente_id'        => $cliente->id,
                    'nombre_plataforma' => $cred['plataforma'],
                    'url_acceso'        => $cred['url'],
                    'usuario'           => strtolower(str_replace(' ', '', $cliente->nombre_negocio)) . '@gbp.com',
                    'password'          => $cred['password'],
                    'correo_asociado'   => $cliente->user->email,
                    'observaciones'     => 'Acceso administrado por Cliché Marketing Digital.',
                    'creado_por'        => $admin->id,
                ]);
            }
        }
    }
}
