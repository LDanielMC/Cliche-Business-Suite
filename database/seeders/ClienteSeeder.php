<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'nombres' => 'Fernanda', 'apellido_paterno' => 'Ríos', 'apellido_materno' => 'Campos',
                'email' => 'contacto@elbuensabor.com',
                'nombre_negocio' => 'Restaurante El Buen Sabor', 'giro' => 'Restaurante',
                'direccion' => 'Av. Morelos 145, Jiutepec, Morelos',
                'servicio_contratado' => 'Posicionamiento Local Mensual', 'cantidad_fotos' => 8, 'precio_mensual' => 2500,
            ],
            [
                'nombres' => 'Ricardo', 'apellido_paterno' => 'Vega', 'apellido_materno' => 'Herrera',
                'email' => 'contacto@boutiqueluna.com',
                'nombre_negocio' => 'Boutique Luna', 'giro' => 'Ropa y accesorios',
                'direccion' => 'Calle Reforma 22, Cuernavaca, Morelos',
                'servicio_contratado' => 'Posicionamiento Local Mensual', 'cantidad_fotos' => 6, 'precio_mensual' => 1800,
            ],
            [
                'nombres' => 'Jorge', 'apellido_paterno' => 'Torres', 'apellido_materno' => 'Domínguez',
                'email' => 'contacto@tallertorres.com',
                'nombre_negocio' => 'Taller Mecánico Torres', 'giro' => 'Servicios automotrices',
                'direccion' => 'Blvd. Industrial 890, Jiutepec, Morelos',
                'servicio_contratado' => 'Posicionamiento Local Mensual', 'cantidad_fotos' => 5, 'precio_mensual' => 1500,
            ],
            [
                'nombres' => 'Alejandra', 'apellido_paterno' => 'Núñez', 'apellido_materno' => 'Salas',
                'email' => 'contacto@clinicasonrisas.com',
                'nombre_negocio' => 'Clínica Dental Sonrisas', 'giro' => 'Salud dental',
                'direccion' => 'Av. Plan de Ayala 310, Cuernavaca, Morelos',
                'servicio_contratado' => 'Posicionamiento Local + Fotografía', 'cantidad_fotos' => 10, 'precio_mensual' => 3200,
            ],
        ];

        foreach ($clientes as $datos) {
            $user = User::create([
                'name' => trim($datos['nombres'] . ' ' . $datos['apellido_paterno'] . ' ' . $datos['apellido_materno']),
                'email' => $datos['email'],
                'password' => Hash::make('password123'),
                'role' => User::ROLE_CLIENTE,
                'estatus' => User::ESTATUS_ACTIVO,
                'nombres' => $datos['nombres'],
                'apellido_paterno' => $datos['apellido_paterno'],
                'apellido_materno' => $datos['apellido_materno'],
            ]);

            Cliente::create([
                'user_id' => $user->id,
                'nombre_negocio' => $datos['nombre_negocio'],
                'giro' => $datos['giro'],
                'direccion' => $datos['direccion'],
                'servicio_contratado' => $datos['servicio_contratado'],
                'cantidad_fotos' => $datos['cantidad_fotos'],
                'precio_mensual' => $datos['precio_mensual'],
                'fecha_registro' => now()->subMonths(random_int(2, 8)),
            ]);
        }
    }
}
