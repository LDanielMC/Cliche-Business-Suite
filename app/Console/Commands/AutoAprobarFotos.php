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
            ->where('estado', PaqueteAprobacion::ESTADO_PENDIENTE)
            ->whereDate('fecha_limite', '<', now()->toDateString())
            ->get();

        foreach ($paquetes as $paquete) {
            $seleccionadas = $paquete->fotos()
                ->orderBy('orden')
                ->limit($paquete->cantidad_requerida)
                ->pluck('id');

            FotoAprobacion::where('paquete_aprobacion_id', $paquete->id)
                ->whereIn('id', $seleccionadas)
                ->update(['estado' => FotoAprobacion::ESTADO_APROBADA]);

            FotoAprobacion::where('paquete_aprobacion_id', $paquete->id)
                ->whereNotIn('id', $seleccionadas)
                ->update(['estado' => FotoAprobacion::ESTADO_DESCARTADA]);

            $paquete->update(['estado' => PaqueteAprobacion::ESTADO_AUTO_APROBADO]);
            $paquete->publicarEnCalendario();

            $this->info("Paquete #{$paquete->id} (cliente {$paquete->cliente_id}) auto-aprobado con {$seleccionadas->count()} fotografías.");
        }

        if ($paquetes->isEmpty()) {
            $this->info('No hay paquetes vencidos pendientes de auto-aprobación.');
        }

        return self::SUCCESS;
    }
}
