<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class FotografiaReprogramada extends Notification
{
    public function __construct(public Carbon $fechaAnterior, public Carbon $fechaNueva)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icono'   => 'foto',
            'titulo'  => 'Se cambió la fecha de una publicación',
            'mensaje' => 'Una fotografía programada para el ' . $this->fechaAnterior->format('d/m/Y') .
                ' se movió al ' . $this->fechaNueva->format('d/m/Y') . '.',
            'url'     => route('calendario.cliente', ['mes' => $this->fechaNueva->month, 'anio' => $this->fechaNueva->year], false),
        ];
    }
}
