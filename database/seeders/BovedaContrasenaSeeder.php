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
        $admin = User::where('email', 'admin@sistema.com')->firstOrFail();
        $clientes = Cliente::all()->values();

        $credenciales = [
            ['Google Business Profile', 'https://business.google.com', 'GBP2026!Seguro'],
            ['Facebook Business Manager', 'https://business.facebook.com', 'FbAds#2026Seguro'],
            ['Panel de Hosting', 'https://cpanel.hosting.mx', 'HostPanel$2026'],
        ];

        foreach ($clientes as $indice => $cliente) {
            [$plataforma, $url, $password] = $credenciales[$indice % count($credenciales)];

            BovedaContrasena::create([
                'cliente_id' => $cliente->id,
                'nombre_plataforma' => $plataforma,
                'url_acceso' => $url,
                'usuario' => strtolower(str_replace(' ', '.', $cliente->nombre_negocio)) . '@acceso.com',
                'password' => $password,
                'correo_asociado' => $cliente->user->email,
                'observaciones' => 'Acceso administrado por Cliché Marketing Digital para ' . $cliente->nombre_negocio . '.',
                'creado_por' => $admin->id,
            ]);
        }
    }
}
