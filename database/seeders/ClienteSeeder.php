<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\ClienteEstatusLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@sistema.com')->firstOrFail();

        // ── Definición de clientes ─────────────────────────────────────────────
        // Fechas fijas para que PagoClienteSeeder y GastoOperativoSeeder puedan
        // calcular correctamente los periodos de cada cliente.
        //
        // Estatus posibles: activo (5 clientes) | dado_de_baja (1 cliente)
        // El cliente dado de baja tiene fecha_alta y fecha_baja para que FN.12
        // muestre correctamente la baja en el mes correspondiente.
        $clientes = [
            [
                'email'             => 'contacto@elbuensabor.com',
                'nombres'           => 'Fernanda',
                'apellido_paterno'  => 'Ríos',
                'apellido_materno'  => 'Campos',
                'nombre_negocio'    => 'Restaurante El Buen Sabor',
                'giro'              => 'Restaurante',
                'direccion'         => 'Av. Morelos 145, Jiutepec, Morelos',
                'servicio_contratado' => 'Posicionamiento Local Mensual',
                'cantidad_fotos'    => 8,
                'precio_mensual'    => 2500.00,
                'fecha_registro'    => now()->subMonths(6)->startOfMonth(),
                'estatus_user'      => User::ESTATUS_ACTIVO,
            ],
            [
                'email'             => 'contacto@boutiqueluna.com',
                'nombres'           => 'Ricardo',
                'apellido_paterno'  => 'Vega',
                'apellido_materno'  => 'Herrera',
                'nombre_negocio'    => 'Boutique Luna',
                'giro'              => 'Ropa y accesorios',
                'direccion'         => 'Calle Reforma 22, Cuernavaca, Morelos',
                'servicio_contratado' => 'Posicionamiento Local Mensual',
                'cantidad_fotos'    => 6,
                'precio_mensual'    => 1800.00,
                'fecha_registro'    => now()->subMonths(5)->startOfMonth(),
                'estatus_user'      => User::ESTATUS_ACTIVO,
            ],
            [
                'email'             => 'contacto@tallertorres.com',
                'nombres'           => 'Jorge',
                'apellido_paterno'  => 'Torres',
                'apellido_materno'  => 'Domínguez',
                'nombre_negocio'    => 'Taller Mecánico Torres',
                'giro'              => 'Servicios automotrices',
                'direccion'         => 'Blvd. Industrial 890, Jiutepec, Morelos',
                'servicio_contratado' => 'Posicionamiento Local Mensual',
                'cantidad_fotos'    => 5,
                'precio_mensual'    => 1500.00,
                'fecha_registro'    => now()->subMonths(4)->startOfMonth(),
                'estatus_user'      => User::ESTATUS_ACTIVO,
            ],
            [
                'email'             => 'contacto@clinicasonrisas.com',
                'nombres'           => 'Alejandra',
                'apellido_paterno'  => 'Núñez',
                'apellido_materno'  => 'Salas',
                'nombre_negocio'    => 'Clínica Dental Sonrisas',
                'giro'              => 'Salud dental',
                'direccion'         => 'Av. Plan de Ayala 310, Cuernavaca, Morelos',
                'servicio_contratado' => 'Posicionamiento Local + Fotografía Premium',
                'cantidad_fotos'    => 12,
                'precio_mensual'    => 3500.00,
                'fecha_registro'    => now()->subMonths(6)->startOfMonth(),
                'estatus_user'      => User::ESTATUS_ACTIVO,
            ],
            [
                'email'             => 'contacto@farmaciasanjose.com',
                'nombres'           => 'Héctor',
                'apellido_paterno'  => 'Morales',
                'apellido_materno'  => 'Ibarra',
                'nombre_negocio'    => 'Farmacia San José',
                'giro'              => 'Farmacia',
                'direccion'         => 'Calle Hidalgo 88, Temixco, Morelos',
                'servicio_contratado' => 'Posicionamiento Local Mensual',
                'cantidad_fotos'    => 6,
                'precio_mensual'    => 1800.00,
                // Cliente reciente: solo 2 meses de pagos
                'fecha_registro'    => now()->subMonths(2)->startOfMonth(),
                'estatus_user'      => User::ESTATUS_ACTIVO,
            ],
            // ── Cliente dado de baja (para probar vista "Eliminados" y FN.12) ──
            [
                'email'             => 'contacto@peluqueriaestilo.com',
                'nombres'           => 'Sofía',
                'apellido_paterno'  => 'Ramírez',
                'apellido_materno'  => 'Cruz',
                'nombre_negocio'    => 'Peluquería Estilo',
                'giro'              => 'Estética y peluquería',
                'direccion'         => 'Calle Guerrero 55, Cuernavaca, Morelos',
                'servicio_contratado' => 'Posicionamiento Local Mensual',
                'cantidad_fotos'    => 5,
                'precio_mensual'    => 1500.00,
                'fecha_registro'    => now()->subMonths(5)->startOfMonth(),
                'estatus_user'      => User::ESTATUS_DADO_DE_BAJA,
                'fecha_baja'        => now()->subMonths(1)->startOfMonth()->addDays(10),
            ],
        ];

        foreach ($clientes as $datos) {
            $esBaja    = $datos['estatus_user'] === User::ESTATUS_DADO_DE_BAJA;
            $fechaBaja = $datos['fecha_baja'] ?? null;

            // ── Usuario ────────────────────────────────────────────────────────
            $nameParts = array_filter([
                $datos['nombres'],
                $datos['apellido_paterno'],
                $datos['apellido_materno'] ?? null,
            ]);

            $user = User::updateOrCreate(
                ['email' => $datos['email']],
                [
                    'name'             => implode(' ', $nameParts),
                    'password'         => Hash::make('password123'),
                    'role'             => User::ROLE_CLIENTE,
                    'estatus'          => $datos['estatus_user'],
                    'fecha_baja'       => $fechaBaja,
                    'nombres'          => $datos['nombres'],
                    'apellido_paterno' => $datos['apellido_paterno'],
                    'apellido_materno' => $datos['apellido_materno'],
                ]
            );

            // ── Cliente ────────────────────────────────────────────────────────
            $cliente = Cliente::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nombre_negocio'     => $datos['nombre_negocio'],
                    'giro'               => $datos['giro'],
                    'direccion'          => $datos['direccion'],
                    'servicio_contratado' => $datos['servicio_contratado'],
                    'cantidad_fotos'     => $datos['cantidad_fotos'],
                    'precio_mensual'     => $datos['precio_mensual'],
                    'fecha_registro'     => $datos['fecha_registro'],
                ]
            );

            // ── Bitácora: alta ─────────────────────────────────────────────────
            if (!$cliente->estatusLogs()->where('evento', ClienteEstatusLog::EVENTO_ALTA)->exists()) {
                ClienteEstatusLog::create([
                    'cliente_id'    => $cliente->id,
                    'user_id'       => $user->id,
                    'evento'        => ClienteEstatusLog::EVENTO_ALTA,
                    'fecha_evento'  => $datos['fecha_registro']->toDateString(),
                    'registrado_por' => $admin->id,
                    'observaciones' => 'Alta inicial registrada por el seeder.',
                ]);
            }

            // ── Bitácora: baja (solo si el cliente está dado de baja) ──────────
            if ($esBaja && $fechaBaja) {
                if (!$cliente->estatusLogs()->where('evento', ClienteEstatusLog::EVENTO_BAJA)->exists()) {
                    ClienteEstatusLog::create([
                        'cliente_id'    => $cliente->id,
                        'user_id'       => $user->id,
                        'evento'        => ClienteEstatusLog::EVENTO_BAJA,
                        'fecha_evento'  => $fechaBaja->toDateString(),
                        'registrado_por' => $admin->id,
                        'observaciones' => 'Baja registrada por el seeder.',
                    ]);
                }
            }
        }
    }
}
