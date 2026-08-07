<?php

namespace Database\Seeders;

use App\Models\CategoriaGasto;
use Illuminate\Database\Seeder;

class CategoriaGastoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'nombre'      => 'Publicidad Digital',
                'descripcion' => 'Anuncios en redes sociales, Google Ads y campañas de pago.',
            ],
            [
                'nombre'      => 'Herramientas SEO',
                'descripcion' => 'Licencias de software para posicionamiento y análisis de palabras clave.',
            ],
            [
                'nombre'      => 'Equipo Fotográfico',
                'descripcion' => 'Compra, mantenimiento y accesorios para equipo de fotografía.',
            ],
            [
                'nombre'      => 'Suscripciones y Software',
                'descripcion' => 'Plataformas de diseño, edición, gestión y comunicación.',
            ],
            [
                'nombre'      => 'Transporte',
                'descripcion' => 'Gasolina, peajes y traslados para visitas a clientes.',
            ],
            [
                'nombre'      => 'Gastos Administrativos',
                'descripcion' => 'Papelería, servicios de oficina y gastos generales de operación.',
            ],
        ];

        foreach ($categorias as $datos) {
            CategoriaGasto::firstOrCreate(
                ['nombre' => $datos['nombre']],
                ['descripcion' => $datos['descripcion']]
            );
        }
    }
}
