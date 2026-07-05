<?php

namespace App\Console\Commands;

use App\Mail\RecordatorioRenovacion;
use App\Models\ControlRenovacion;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('renovaciones:verificar')]
#[Description('Marca renovaciones por vencer/vencidas, envía recordatorios y suspende clientes vencidos.')]
class VerificarRenovaciones extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hoy = now()->startOfDay();

        $porVencer = ControlRenovacion::with('cliente.user')
            ->whereIn('estado', [ControlRenovacion::ESTADO_VIGENTE, ControlRenovacion::ESTADO_POR_VENCER])
            ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->addDays(7))
            ->whereDate('fecha_vencimiento', '>=', $hoy)
            ->get();

        foreach ($porVencer as $renovacion) {
            $renovacion->update(['estado' => ControlRenovacion::ESTADO_POR_VENCER]);

            if (is_null($renovacion->notificado_at)) {
                Mail::to($renovacion->cliente->user->email)->send(new RecordatorioRenovacion($renovacion));
                $renovacion->update(['notificado_at' => now()]);
                $this->info("Recordatorio enviado a {$renovacion->cliente->nombre_negocio}.");
            }
        }

        $vencidas = ControlRenovacion::with('cliente.user')
            ->whereIn('estado', [ControlRenovacion::ESTADO_VIGENTE, ControlRenovacion::ESTADO_POR_VENCER])
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->get();

        foreach ($vencidas as $renovacion) {
            $renovacion->update(['estado' => ControlRenovacion::ESTADO_VENCIDO]);
            $renovacion->cliente->user->update(['estatus' => User::ESTATUS_SUSPENDIDO]);
            $this->info("Cliente {$renovacion->cliente->nombre_negocio} suspendido por falta de renovación.");
        }

        if ($porVencer->isEmpty() && $vencidas->isEmpty()) {
            $this->info('No hay renovaciones que requieran atención hoy.');
        }

        return self::SUCCESS;
    }
}
