<?php

namespace Database\Seeders;

use App\Models\CalendarioFoto;
use App\Models\Cliente;
use App\Models\User;
use Database\Seeders\Concerns\GeneraImagenesDemo;
use Illuminate\Database\Seeder;

class CalendarioFotoSeeder extends Seeder
{
    use GeneraImagenesDemo;

    public function run(): void
    {
        $admin = User::where('email', 'admin@sistema.com')->firstOrFail();
        $colores = [[124, 58, 237], [20, 184, 166], [245, 158, 11], [99, 102, 241]];

        foreach (Cliente::all() as $indice => $cliente) {
            $color = $colores[$indice % count($colores)];

            // Una publicación ya pasada (publicada) y dos próximas (programadas) por cliente.
            $fechas = [
                now()->subDays(random_int(3, 10))->setTime(9, 0),
                now()->addDays(random_int(1, 6))->setTime(10, 0),
                now()->addDays(random_int(8, 15))->setTime(11, 0),
            ];

            foreach ($fechas as $posicion => $fecha) {
                $ruta = $this->generarImagenDemo(
                    'calendario-fotos/' . $cliente->id,
                    $cliente->nombre_negocio,
                    $color
                );

                CalendarioFoto::create([
                    'cliente_id' => $cliente->id,
                    'fotografia_asociada' => $ruta,
                    'fecha_publicacion_programada' => $fecha,
                    'estatus' => $posicion === 0 ? CalendarioFoto::ESTATUS_PUBLICADA : CalendarioFoto::ESTATUS_PROGRAMADA,
                    'observaciones' => 'Publicación de ficha Google Business para ' . $cliente->nombre_negocio . '.',
                    'creado_por' => $admin->id,
                ]);
            }
        }
    }
}
