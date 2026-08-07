<?php

namespace Database\Seeders;

use App\Models\CalendarioFoto;
use App\Models\FotoAprobacion;
use App\Models\PaqueteAprobacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CalendarioFotoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@sistema.com')->firstOrFail();

        // Solo paquetes cerrados tienen fotos aprobadas que pueden ir al calendario.
        $paquetes = PaqueteAprobacion::whereIn('estatus', [
            PaqueteAprobacion::ESTATUS_COMPLETADO,
            PaqueteAprobacion::ESTATUS_AUTO_APROBADO,
        ])->get();

        foreach ($paquetes as $paquete) {
            $fotosAprobadas = FotoAprobacion::where('paquete_aprobacion_id', $paquete->id)
                ->where('estatus', FotoAprobacion::ESTATUS_APROBADA)
                ->orderBy('prioridad')
                ->get();

            if ($fotosAprobadas->isEmpty()) {
                continue;
            }

            // Calcular el espaciado para distribuir las fotos dentro del período.
            $inicioPeriodo = $paquete->fecha_inicio_periodo
                ? Carbon::parse($paquete->fecha_inicio_periodo)
                : Carbon::parse($paquete->fecha_envio)->subDays(15);

            $finPeriodo = $paquete->fecha_vencimiento_periodo
                ? Carbon::parse($paquete->fecha_vencimiento_periodo)
                : Carbon::parse($paquete->fecha_limite);

            $diasPeriodo  = max(1, $inicioPeriodo->diffInDays($finPeriodo));
            $espaciado    = max(1, (int) floor($diasPeriodo / $fotosAprobadas->count()));

            foreach ($fotosAprobadas as $i => $foto) {
                $fecha = $inicioPeriodo->copy()->addDays(($i * $espaciado) + 1)->setTime(10, 0);

                // No colocar fuera del período.
                if ($fecha->gt($finPeriodo)) {
                    break;
                }

                $estatus = $fecha->isPast()
                    ? CalendarioFoto::ESTATUS_PUBLICADA
                    : CalendarioFoto::ESTATUS_PROGRAMADA;

                CalendarioFoto::create([
                    'cliente_id'                   => $paquete->cliente_id,
                    'foto_aprobacion_id'           => $foto->id,
                    'fotografia_asociada'          => $foto->ruta_foto,
                    'fecha_publicacion_programada' => $fecha,
                    'estatus'                      => $estatus,
                    'observaciones'                => 'Publicación de posicionamiento local.',
                    'creado_por'                   => $admin->id,
                ]);
            }
        }
    }
}
