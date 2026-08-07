<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\ControlRenovacion;
use App\Models\FotoAprobacion;
use App\Models\PaqueteAprobacion;
use Carbon\Carbon;
use Database\Seeders\Concerns\GeneraImagenesDemo;
use Illuminate\Database\Seeder;

class PaqueteAprobacionSeeder extends Seeder
{
    use GeneraImagenesDemo;

    public function run(): void
    {
        $clientes = Cliente::all()->values();
        $colores = [[124, 58, 237], [20, 184, 166], [245, 158, 11], [99, 102, 241]];

        // Cliente 0: paquete pendiente del mes actual, esperando que el cliente decida.
        $this->crearPaquete($clientes[0], now()->addDays(5), PaqueteAprobacion::ESTATUS_PENDIENTE, $colores[0], function ($paquete, $cantidadFotos) {
            for ($i = 1; $i <= $cantidadFotos + 2; $i++) {
                // Las primeras $cantidadFotos fotos tienen prioridad asignada; las sobrantes no.
                $this->crearFoto($paquete, FotoAprobacion::ESTATUS_PENDIENTE, $i, $i <= $cantidadFotos ? $i : null);
            }
        });

        // Cliente 1: paquete completado el mes pasado (aprobadas + conservada + descartadas).
        $this->crearPaquete($clientes[1], now()->subMonth()->addDays(20), PaqueteAprobacion::ESTATUS_COMPLETADO, $colores[1], function ($paquete, $cantidadFotos) {
            for ($i = 1; $i <= $cantidadFotos; $i++) {
                $this->crearFoto($paquete, FotoAprobacion::ESTATUS_APROBADA, $i, $i);
            }
            $this->crearFoto($paquete, FotoAprobacion::ESTATUS_CONSERVADA, $cantidadFotos + 1, $cantidadFotos + 1);
            $this->crearFoto($paquete, FotoAprobacion::ESTATUS_DESCARTADA, $cantidadFotos + 2, $cantidadFotos + 2);
        });

        // Cliente 2: paquete auto-aprobado del mes pasado (el cliente no respondió a tiempo).
        $this->crearPaquete($clientes[2], now()->subMonth()->addDays(20), PaqueteAprobacion::ESTATUS_AUTO_APROBADO, $colores[2], function ($paquete, $cantidadFotos) {
            for ($i = 1; $i <= $cantidadFotos; $i++) {
                $this->crearFoto($paquete, FotoAprobacion::ESTATUS_APROBADA, $i, $i);
            }
            $this->crearFoto($paquete, FotoAprobacion::ESTATUS_DESCARTADA, $cantidadFotos + 1, $cantidadFotos + 1);
        });

        // Cliente 3: paquete pendiente del mes actual con menos candidatas que la cuota.
        $this->crearPaquete($clientes[3], now()->addDays(7), PaqueteAprobacion::ESTATUS_PENDIENTE, $colores[3], function ($paquete, $cantidadFotos) {
            for ($i = 1; $i <= max($cantidadFotos - 2, 1); $i++) {
                $this->crearFoto($paquete, FotoAprobacion::ESTATUS_PENDIENTE, $i, $i);
            }
        });
    }

    /**
     * mes_revision se deriva de fecha_inicio de la renovación (igual que
     * PaqueteAprobacionController::store()) para no desalinearse del período
     * real que el paquete está cubriendo.
     */
    private function crearPaquete(Cliente $cliente, Carbon $fechaLimite, string $estatus, array $color, callable $callback): void
    {
        $renovacion = ControlRenovacion::where('cliente_id', $cliente->id)->latest('id')->first();

        $motivo = match ($estatus) {
            PaqueteAprobacion::ESTATUS_COMPLETADO    => PaqueteAprobacion::MOTIVO_APROBADO_CLIENTE,
            PaqueteAprobacion::ESTATUS_AUTO_APROBADO => PaqueteAprobacion::MOTIVO_APROBADO_AUTOMATICO,
            default                                  => null,
        };

        $paquete = PaqueteAprobacion::create([
            'cliente_id'                => $cliente->id,
            'mes_revision'              => ($renovacion?->fecha_inicio ?? now())->format('Y-m'),
            'fecha_envio'               => $fechaLimite->copy()->subDays(15),
            'fecha_limite'              => $fechaLimite,
            'cantidad_requerida'        => $cliente->cantidad_fotos,
            'estatus'                   => $estatus,
            'observaciones'             => 'Paquete de fotografías propuestas para ' . $cliente->nombre_negocio . '.',
            'fecha_inicio_periodo'      => $renovacion?->fecha_inicio,
            'fecha_vencimiento_periodo' => $renovacion?->fecha_vencimiento,
            'motivo_finalizacion'       => $motivo,
        ]);

        $this->colorActual = $color;
        $callback($paquete, $cliente->cantidad_fotos);
    }

    private array $colorActual = [124, 58, 237];

    private function crearFoto(PaqueteAprobacion $paquete, string $estatus, int $indice, ?int $prioridad = null): void
    {
        $ruta = $this->generarImagenDemo(
            'fotos-aprobacion/' . $paquete->cliente_id . '/' . $paquete->id,
            $paquete->cliente->nombre_negocio . ' #' . $indice,
            $this->colorActual
        );

        FotoAprobacion::create([
            'paquete_aprobacion_id' => $paquete->id,
            'ruta_foto'             => $ruta,
            'estatus'               => $estatus,
            'prioridad'             => $prioridad,
        ]);
    }
}
