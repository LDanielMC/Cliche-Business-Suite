<?php

namespace App\Console\Commands;

use App\Models\FotoAprobacion;
use App\Models\PaqueteAprobacion;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('aprobaciones:auto-aprobar')]
#[Description('Auto-aprueba las fotografías de los paquetes vencidos que el cliente no seleccionó a tiempo.')]
class AutoAprobarFotos extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $paquetes = PaqueteAprobacion::with('fotos')
            ->where('estatus', PaqueteAprobacion::ESTATUS_PENDIENTE)
            ->whereDate('fecha_limite', '<', now()->toDateString())
            ->get();

        foreach ($paquetes as $paquete) {
            $seleccionadas = $paquete->fotos()
                ->where('estatus', FotoAprobacion::ESTATUS_PENDIENTE)
                ->orderBy('id')
                ->limit($paquete->cantidad_requerida)
                ->pluck('id');

            FotoAprobacion::where('paquete_aprobacion_id', $paquete->id)
                ->whereIn('id', $seleccionadas)
                ->update(['estatus' => FotoAprobacion::ESTATUS_APROBADA]);

            FotoAprobacion::where('paquete_aprobacion_id', $paquete->id)
                ->where('estatus', FotoAprobacion::ESTATUS_PENDIENTE)
                ->whereNotIn('id', $seleccionadas)
                ->update(['estatus' => FotoAprobacion::ESTATUS_DESCARTADA]);

            $paquete->update(['estatus' => PaqueteAprobacion::ESTATUS_AUTO_APROBADO]);
            $paquete->publicarEnCalendario();

            $this->info("Paquete #{$paquete->id} (cliente {$paquete->cliente_id}) auto-aprobado con {$seleccionadas->count()} fotografías.");
        }

        if ($paquetes->isEmpty()) {
            $this->info('No hay paquetes vencidos pendientes de auto-aprobación.');
        }

        return self::SUCCESS;
    }
}
