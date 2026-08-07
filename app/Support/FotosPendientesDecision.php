<?php

namespace App\Support;

use App\Models\CalendarioFoto;
use App\Models\FotoAprobacion;
use Illuminate\Support\Collection;

/**
 * Fotos que requieren una decisión manual del equipo y que, sin este
 * chequeo, solo se detectaban vía el comando `reservas:expirar` — si el
 * cron no corre, nadie se entera. Se usa tanto desde ese comando (aviso
 * por correo + campana) como desde el dashboard del operador (cálculo en
 * vivo, no depende de que el comando se haya ejecutado).
 */
class FotosPendientesDecision
{
    /**
     * Fotos del Banco de Reserva cuya vigencia ya venció (o vence hoy).
     */
    public static function reservaPorExpirar(): Collection
    {
        return FotoAprobacion::with('cliente')
            ->where('estatus', FotoAprobacion::ESTATUS_CONSERVADA)
            ->whereDate('fecha_expiracion_reserva', '<=', now()->startOfDay())
            ->get();
    }

    /**
     * Fotos aprobadas que nunca se colocaron en el calendario y cuyo
     * período venció hace más de `renovaciones.meses_reserva` meses.
     */
    public static function vencidasSinAgendar(): Collection
    {
        $mesesReserva = config('renovaciones.meses_reserva', 6);
        $hoy = now()->startOfDay();

        $idsAgendadas = CalendarioFoto::whereIn('estatus', [
                CalendarioFoto::ESTATUS_PROGRAMADA,
                CalendarioFoto::ESTATUS_PUBLICADA,
            ])
            ->whereNotNull('foto_aprobacion_id')
            ->pluck('foto_aprobacion_id');

        return FotoAprobacion::with('paquete.cliente')
            ->where('estatus', FotoAprobacion::ESTATUS_APROBADA)
            ->whereNotIn('id', $idsAgendadas)
            ->whereHas('paquete', fn ($q) => $q
                ->whereDate('fecha_vencimiento_periodo', '<=', $hoy->copy()->subMonths($mesesReserva))
            )
            ->get();
    }
}
