<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class ReservaPorExpirarDigest extends Notification
{
    /** @param Collection<int, \App\Models\FotoAprobacion> $fotos */
    public function __construct(public Collection $fotos)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $nombres = $this->fotos->pluck('cliente.nombre_negocio')->unique()->take(3)->implode(', ');

        return [
            'icono'   => 'alerta',
            'titulo'  => $this->fotos->count() . ' foto(s) del Banco de Reserva por expirar',
            'mensaje' => 'Requieren decisión (conservar o descartar): ' . $nombres .
                ($this->fotos->pluck('cliente.nombre_negocio')->unique()->count() > 3 ? '…' : ''),
            'url'     => route('aprobaciones.index', [], false),
        ];
    }
}
