<?php

namespace App\Console\Commands;

use App\Mail\RecordatorioRenovacion;
use App\Models\ClienteEstatusLog;
use App\Models\ControlRenovacion;
use App\Models\User;
use App\Notifications\RenovacionPorVencer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('renovaciones:verificar')]
#[Description('Marca renovaciones próximas a vencer (5 días), envía recordatorios y suspende clientes vencidos.')]
class VerificarRenovaciones extends Command
{
    public function handle(): int
    {
        $hoy = now()->startOfDay();

        // ── 1. Send reminder emails on fecha_recordatorio day ──────────────────
        $conRecordatorioHoy = ControlRenovacion::with('cliente.user')
            ->whereIn('estatus', [ControlRenovacion::ESTATUS_VIGENTE, ControlRenovacion::ESTATUS_POR_VENCER])
            ->whereDate('fecha_recordatorio', $hoy)
            ->get();

        foreach ($conRecordatorioHoy as $renovacion) {
            Mail::to($renovacion->cliente->user->email)
                ->send(new RecordatorioRenovacion($renovacion));
            $renovacion->cliente->user->notify(new RenovacionPorVencer($renovacion));
            $this->info("Recordatorio enviado a {$renovacion->cliente->nombre_negocio}.");
        }

        // ── 2. Mark as "por_vencer" when 5 days or fewer remain ───────────────
        $actualizadas = ControlRenovacion::where('estatus', ControlRenovacion::ESTATUS_VIGENTE)
            ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->addDays(5))
            ->whereDate('fecha_vencimiento', '>=', $hoy)
            ->update(['estatus' => ControlRenovacion::ESTATUS_POR_VENCER]);

        if ($actualizadas > 0) {
            $this->info("{$actualizadas} renovación(es) marcada(s) como 'por_vencer'.");
        }

        $diasGracia = config('renovaciones.dias_gracia', 5);

        // ── 3. Mark as "vencido" as soon as fecha_vencimiento passes ─────────
        // The client still has the grace window to submit a proof before being
        // suspended, but the status badge must reflect "vencido" immediately.
        $marcadasVencidas = ControlRenovacion::whereIn('estatus', [
                ControlRenovacion::ESTATUS_VIGENTE,
                ControlRenovacion::ESTATUS_POR_VENCER,
            ])
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->update(['estatus' => ControlRenovacion::ESTATUS_VENCIDO]);

        if ($marcadasVencidas > 0) {
            $this->info("{$marcadasVencidas} renovación(es) marcada(s) como 'vencido'.");
        }

        // ── 4. Suspend clients once the grace period has fully expired ────────
        $vencidas = ControlRenovacion::with('cliente.user')
            ->where('estatus', ControlRenovacion::ESTATUS_VENCIDO)
            ->whereDate('fecha_vencimiento', '<', $hoy->copy()->subDays($diasGracia))
            ->whereHas('cliente.user', fn ($q) => $q->where('estatus', '!=', User::ESTATUS_SUSPENDIDO))
            ->get();

        foreach ($vencidas as $renovacion) {
            $cliente = $renovacion->cliente;
            $cliente->user->update(['estatus' => User::ESTATUS_SUSPENDIDO]);

            // Log the suspension event for traceability
            ClienteEstatusLog::create([
                'cliente_id'     => $cliente->id,
                'user_id'        => $cliente->user_id,
                'evento'         => ClienteEstatusLog::EVENTO_SUSPENSION,
                'fecha_evento'   => $hoy->toDateString(),
                'registrado_por' => null, // System-triggered, no user actor
                'observaciones'  => "Suspensión automática: venció el período de gracia de {$diasGracia} días.",
            ]);

            $this->info("Cliente {$cliente->nombre_negocio} suspendido (gracia de {$diasGracia} días expirada).");
        }

        // ── 5. Suspend clients with a rejected proof whose resubmission window expired ──
        // When a proof is rejected, fecha_vencimiento is reset to the rejection date
        // so the client gets dias_gracia days to upload the correct document.
        // If they don't, they are suspended just like any other expired client.
        $rechazadasExpiradas = ControlRenovacion::with('cliente.user')
            ->where('estatus', ControlRenovacion::ESTATUS_PAGO_RECHAZADO)
            ->whereDate('fecha_vencimiento', '<', $hoy->copy()->subDays($diasGracia))
            ->whereHas('cliente.user', fn ($q) => $q->where('estatus', '!=', User::ESTATUS_SUSPENDIDO))
            ->get();

        foreach ($rechazadasExpiradas as $renovacion) {
            $cliente = $renovacion->cliente;
            $cliente->user->update(['estatus' => User::ESTATUS_SUSPENDIDO]);

            ClienteEstatusLog::create([
                'cliente_id'     => $cliente->id,
                'user_id'        => $cliente->user_id,
                'evento'         => ClienteEstatusLog::EVENTO_SUSPENSION,
                'fecha_evento'   => $hoy->toDateString(),
                'registrado_por' => null,
                'observaciones'  => "Suspensión automática: no se recibió comprobante válido en {$diasGracia} días tras el rechazo.",
            ]);

            $this->info("Cliente {$cliente->nombre_negocio} suspendido (comprobante rechazado sin reenvío en {$diasGracia} días).");
        }

        if ($conRecordatorioHoy->isEmpty() && $actualizadas === 0 && $marcadasVencidas === 0 && $vencidas->isEmpty() && $rechazadasExpiradas->isEmpty()) {
            $this->info('No hay renovaciones que requieran atención hoy.');
        }

        return self::SUCCESS;
    }
}
