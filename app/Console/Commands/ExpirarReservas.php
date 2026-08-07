<?php

namespace App\Console\Commands;

use App\Mail\NotificacionExpirarReserva;
use App\Models\User;
use App\Notifications\FotosVencidasSinAgendarDigest;
use App\Notifications\ReservaPorExpirarDigest;
use App\Support\FotosPendientesDecision;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('reservas:expirar')]
#[Description('Notifica al operador sobre fotografías en reserva que expiran hoy, y sobre fotos aprobadas sin agendar hace más de meses_reserva. No elimina nada sin intervención manual.')]
class ExpirarReservas extends Command
{
    public function handle(): int
    {
        $huboAviso = false;

        $destinatarios = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_OPERADOR])
            ->where('estatus', User::ESTATUS_ACTIVO)
            ->get();

        // ── 1. Fotos en reserva cuya vigencia termina hoy ──────────────────────
        $porExpirar = FotosPendientesDecision::reservaPorExpirar();

        if ($porExpirar->isNotEmpty()) {
            $huboAviso = true;

            foreach ($destinatarios as $usuario) {
                Mail::to($usuario->email)->send(new NotificacionExpirarReserva($porExpirar));
                $usuario->notify(new ReservaPorExpirarDigest($porExpirar));
            }

            $this->info(
                "{$porExpirar->count()} fotografía(s) en reserva expirada(s). " .
                "Notificación enviada a {$destinatarios->count()} destinatario(s). " .
                "Ninguna fue eliminada — requiere decisión manual."
            );
        }

        // ── 2. Fotos aprobadas sin agendar cuyo período venció hace más de ─────
        //      meses_reserva (rezago que nunca se puso al día). Mismo trato que
        //      el Banco de Reserva: solo aviso, no se toca el estatus.
        $mesesReserva = config('renovaciones.meses_reserva', 6);
        $vencidasSinAgendar = FotosPendientesDecision::vencidasSinAgendar();

        if ($vencidasSinAgendar->isNotEmpty()) {
            $huboAviso = true;

            foreach ($destinatarios as $usuario) {
                // FotosVencidasSinAgendarDigest ya manda correo por sí sola
                // (canal 'mail'), no se duplica con un Mail::send() aparte.
                $usuario->notify(new FotosVencidasSinAgendarDigest($vencidasSinAgendar, $mesesReserva));
            }

            $this->info(
                "{$vencidasSinAgendar->count()} fotografía(s) aprobada(s) llevan más de {$mesesReserva} " .
                "mes(es) sin agendarse. Notificación enviada a {$destinatarios->count()} destinatario(s). " .
                'Ninguna fue eliminada — requiere decisión manual.'
            );
        }

        if (!$huboAviso) {
            $this->info('No hay fotografías con expiración pendiente hoy.');
        }

        return self::SUCCESS;
    }
}
