<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class PaquetesEnRiesgoDigest extends Notification
{
    /** @param Collection<int, array{cliente: \App\Models\Cliente, dias_restantes: int, etiqueta: string, nivel: string}> $enRiesgo */
    public function __construct(public Collection $enRiesgo)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $criticos = $this->enRiesgo->where('nivel', 'critico')->count();
        $nombres  = $this->enRiesgo->take(3)->map(fn ($item) => $item['cliente']->nombre_negocio)->implode(', ');

        return [
            'icono'   => 'alerta',
            'titulo'  => $this->enRiesgo->count() . ' paquete(s) de aprobación sin enviar',
            'mensaje' => $criticos . ' urgente(s). ' . $nombres . ($this->enRiesgo->count() > 3 ? '…' : ''),
            'url'     => route('aprobaciones.index', [], false),
        ];
    }
}
