<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class FotografiaCancelada extends Notification
{
    public function __construct(public Carbon $fecha)
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
            'icono'   => 'alerta',
            'titulo'  => 'Se canceló una publicación programada',
            'mensaje' => 'La fotografía que tenías programada para el ' . $this->fecha->format('d/m/Y') . ' se quitó del calendario.',
            'url'     => route('calendario.cliente', ['mes' => $this->fecha->month, 'anio' => $this->fecha->year], false),
        ];
    }
}
