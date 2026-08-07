<?php

namespace App\Console\Commands;

use App\Mail\AdvertenciaFotosInsuficientes;
use App\Models\FotoAprobacion;
use App\Models\PaqueteAprobacion;
use App\Models\User;
use App\Notifications\FotosAutoAprobadas;
use App\Notifications\FotosDescartadasPorLimiteReserva;
use App\Notifications\FotosInsuficientes;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

#[Signature('aprobaciones:auto-aprobar')]
#[Description('Auto-aprueba por orden de subida los paquetes vencidos que el cliente no seleccionó a tiempo. No crea entradas de calendario.')]
class AutoAprobarFotos extends Command
{
    public function handle(): int
    {
        $paquetes = PaqueteAprobacion::with(['fotos', 'cliente.user'])
            ->where('estatus', PaqueteAprobacion::ESTATUS_PENDIENTE)
            ->whereDate('fecha_limite', '<', now()->toDateString())
            ->get();

        if ($paquetes->isEmpty()) {
            $this->info('No hay paquetes vencidos pendientes de auto-aprobación.');
            return self::SUCCESS;
        }

        $hoy            = Carbon::today();
        $mesesReserva   = config('renovaciones.meses_reserva', 6);
        $destinoSobrante = config('renovaciones.destino_sobrantes_auto', FotoAprobacion::ESTATUS_CONSERVADA);

        foreach ($paquetes as $paquete) {
            DB::transaction(function () use ($paquete, $hoy, $mesesReserva, $destinoSobrante) {
                // Lock the row to prevent concurrent runs
                PaqueteAprobacion::lockForUpdate()->find($paquete->id);

                // Re-fetch status inside the transaction in case another process got here first
                if ($paquete->fresh()->estatus !== PaqueteAprobacion::ESTATUS_PENDIENTE) {
                    return;
                }

                // ── 1. Select exactly cantidad_requerida photos by upload order ────────
                // El sistema ya no usa prioridad manual: se aprueban las primeras
                // fotos subidas (id ASC = orden de carga), como si el cliente hubiera
                // elegido las más antiguas primero.
                $candidatas = $paquete->fotos()
                    ->where('estatus', FotoAprobacion::ESTATUS_PENDIENTE)
                    ->orderBy('id')
                    ->get();

                $requeridas  = $paquete->cantidad_requerida;
                $seleccionadas = $candidatas->take($requeridas);
                $sobrantes     = $candidatas->skip($requeridas);

                $idsAprobadas = $seleccionadas->pluck('id');

                // ── 2. Approve selected photos ────────────────────────────────────────
                FotoAprobacion::whereIn('id', $idsAprobadas)
                    ->update(['estatus' => FotoAprobacion::ESTATUS_APROBADA]);

                // ── 3. Send surplus to the Reserve Bank with expiration dates ─────────
                $descartadasPorLimite = collect();

                if ($sobrantes->isNotEmpty()) {
                    $fechaIngreso    = $hoy->toDateString();
                    $fechaExpiracion = $hoy->copy()->addMonths($mesesReserva)->toDateString();

                    if ($destinoSobrante === FotoAprobacion::ESTATUS_CONSERVADA) {
                        // Con límite de ciclos: si una foto ya lleva demasiadas
                        // vueltas por reserva sin decidirse, se descarta en vez
                        // de conservarla otra vez.
                        $descartadasPorLimite = FotoAprobacion::conservarConLimite(
                            $sobrantes->pluck('id')->all(), $fechaIngreso, $fechaExpiracion
                        );
                    } else {
                        FotoAprobacion::whereIn('id', $sobrantes->pluck('id'))
                            ->update(['estatus' => $destinoSobrante]);
                    }
                }

                // ── 4. Close the package ─────────────────────────────────────────────
                $paquete->update([
                    'estatus'             => PaqueteAprobacion::ESTATUS_AUTO_APROBADO,
                    'motivo_finalizacion' => PaqueteAprobacion::MOTIVO_APROBADO_AUTOMATICO,
                ]);

                // ── 5. Warn operator if there weren't enough photos ──────────────────
                $aprobadas = $seleccionadas->count();

                if ($aprobadas < $requeridas) {
                    $admins = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_OPERADOR])
                        ->where('estatus', User::ESTATUS_ACTIVO)
                        ->get();

                    foreach ($admins as $admin) {
                        Mail::to($admin->email)->send(
                            new AdvertenciaFotosInsuficientes($paquete, $aprobadas, $requeridas)
                        );
                        $admin->notify(new FotosInsuficientes($paquete, $aprobadas, $requeridas));
                    }

                    $this->warn(
                        "Paquete #{$paquete->id} ({$paquete->cliente->nombre_negocio}): " .
                        "solo {$aprobadas}/{$requeridas} fotos disponibles — operador notificado."
                    );
                } else {
                    $paquete->cliente->user->notify(new FotosAutoAprobadas($paquete, $aprobadas));

                    $this->info(
                        "Paquete #{$paquete->id} ({$paquete->cliente->nombre_negocio}): " .
                        "{$aprobadas} fotografía(s) auto-aprobadas por orden de subida."
                    );
                }

                // ── 6. Warn operator about photos discarded for hitting the reserve-cycle limit ──
                if ($descartadasPorLimite->isNotEmpty()) {
                    $admins = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_OPERADOR])
                        ->where('estatus', User::ESTATUS_ACTIVO)
                        ->get();

                    foreach ($admins as $admin) {
                        $admin->notify(new FotosDescartadasPorLimiteReserva($paquete->cliente, $descartadasPorLimite->count()));
                    }

                    $this->warn(
                        "Paquete #{$paquete->id} ({$paquete->cliente->nombre_negocio}): " .
                        "{$descartadasPorLimite->count()} foto(s) descartadas por llegar al límite de ciclos en reserva."
                    );
                }
                // NOTE: No CalendarioFoto is created here. Scheduling is done manually in FN.04.
            });
        }

        return self::SUCCESS;
    }
}
