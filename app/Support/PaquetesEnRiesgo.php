<?php

namespace App\Support;

use App\Models\Cliente;
use App\Models\ControlRenovacion;
use App\Models\PaqueteAprobacion;
use Illuminate\Support\Collection;

/**
 * Detecta clientes cuyo período de renovación se acerca a su fin sin que
 * exista todavía un paquete de aprobación enviado al cliente (borrador
 * sin enviar, o ni siquiera creado). Usado tanto por el listado de
 * Aprobaciones como por el recordatorio diario por correo.
 */
class PaquetesEnRiesgo
{
    const NIVEL_AVISO   = 'aviso';
    const NIVEL_CRITICO = 'critico';

    /**
     * @return Collection<int, array{cliente: Cliente, paquete: ?PaqueteAprobacion, dias_restantes: int, etiqueta: string, nivel: string}>
     */
    public static function detectar(): Collection
    {
        $diasTemprano = config('renovaciones.dias_aviso_temprano', 25);
        $diasCritico  = config('renovaciones.dias_aviso_critico', 15);

        return Cliente::activos()
            ->with('user', 'renovacionActiva')
            ->get()
            ->filter(fn (Cliente $cliente) => $cliente->renovacionActiva
                && in_array($cliente->renovacionActiva->estatus, [
                    ControlRenovacion::ESTATUS_VIGENTE,
                    ControlRenovacion::ESTATUS_POR_VENCER,
                    // 'vencido' incluido a propósito: el cron diario puede tardar en
                    // reflejarlo, y dentro del período de gracia el cliente sigue activo
                    // — si su paquete sigue sin enviar, es el caso MÁS urgente, no uno
                    // que deba desaparecer de la lista.
                    ControlRenovacion::ESTATUS_VENCIDO,
                ]))
            ->map(function (Cliente $cliente) use ($diasTemprano, $diasCritico) {
                $renovacion    = $cliente->renovacionActiva;
                $diasRestantes = $renovacion->diasRestantes();

                // Solo se descarta por estar demasiado lejos en el futuro.
                // Los valores negativos (ya vencido) siempre califican.
                if ($diasRestantes > $diasTemprano) {
                    return null;
                }

                // El paquete de este ciclo es el que copió el período vigente
                // al crearse (fecha_inicio_periodo = renovación.fecha_inicio).
                $paquete = PaqueteAprobacion::where('cliente_id', $cliente->id)
                    ->whereDate('fecha_inicio_periodo', $renovacion->fecha_inicio)
                    ->latest()
                    ->first();

                $sinEnviar = !$paquete || $paquete->estatus === PaqueteAprobacion::ESTATUS_BORRADOR;

                if (!$sinEnviar) {
                    return null;
                }

                if ($diasRestantes < 0) {
                    $dias     = abs($diasRestantes);
                    $etiqueta = 'Vencido hace ' . $dias . ' día' . ($dias === 1 ? '' : 's');
                } elseif ($diasRestantes === 0) {
                    $etiqueta = 'Vence hoy';
                } else {
                    $etiqueta = $diasRestantes . ' día' . ($diasRestantes === 1 ? '' : 's');
                }

                return [
                    'cliente'        => $cliente,
                    'paquete'        => $paquete,
                    'dias_restantes' => $diasRestantes,
                    'etiqueta'       => $etiqueta,
                    'nivel'          => $diasRestantes <= $diasCritico ? self::NIVEL_CRITICO : self::NIVEL_AVISO,
                ];
            })
            ->filter()
            ->sortBy('dias_restantes')
            ->values();
    }
}
