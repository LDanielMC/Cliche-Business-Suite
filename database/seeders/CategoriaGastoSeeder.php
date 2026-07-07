<?php

namespace Database\Seeders;

use App\Models\CategoriaGasto;
use Illuminate\Database\Seeder;

class CategoriaGastoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Publicidad Digital', 'Herramientas SEO', 'Transporte', 'Suscripciones y Software', 'Equipo Fotográfico'] as $nombre) {
            CategoriaGasto::create(['nombre_categoria' => $nombre]);
        }
    }
}
