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

        $conRecordatorioHoy = ControlRenovacion::with('cliente.user')
            ->whereIn('estatus', [ControlRenovacion::ESTATUS_VIGENTE, ControlRenovacion::ESTATUS_POR_VENCER])
            ->whereDate('fecha_recordatorio', $hoy)
            ->get();

        foreach ($conRecordatorioHoy as $renovacion) {
            Mail::to($renovacion->cliente->user->email)->send(new RecordatorioRenovacion($renovacion));
            $this->info("Recordatorio enviado a {$renovacion->cliente->nombre_negocio}.");
        }

        $porVencer = ControlRenovacion::where('estatus', ControlRenovacion::ESTATUS_VIGENTE)
            ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->addDays(7))
            ->whereDate('fecha_vencimiento', '>=', $hoy)
            ->update(['estatus' => ControlRenovacion::ESTATUS_POR_VENCER]);

        $vencidas = ControlRenovacion::with('cliente.user')
            ->whereIn('estatus', [ControlRenovacion::ESTATUS_VIGENTE, ControlRenovacion::ESTATUS_POR_VENCER])
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->get();

        foreach ($vencidas as $renovacion) {
            $renovacion->update(['estatus' => ControlRenovacion::ESTATUS_VENCIDO]);
            $renovacion->cliente->user->update(['estatus' => User::ESTATUS_SUSPENDIDO]);
            $this->info("Cliente {$renovacion->cliente->nombre_negocio} suspendido por falta de renovación.");
        }

        if ($conRecordatorioHoy->isEmpty() && $porVencer === 0 && $vencidas->isEmpty()) {
            $this->info('No hay renovaciones que requieran atención hoy.');
        }

        return self::SUCCESS;
    }
}
