<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class FotografiasProgramadas extends Notification
{
    public function __construct(public int $cantidad, public Carbon $fecha)
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
            'titulo'  => $this->cantidad === 1 ? 'Nueva fotografía en tu calendario' : 'Nuevas fotografías en tu calendario',
            'mensaje' => $this->cantidad === 1
                ? 'Se programó una fotografía para publicarse el ' . $this->fecha->format('d/m/Y') . '.'
                : "Se programaron {$this->cantidad} fotografías para publicarse el " . $this->fecha->format('d/m/Y') . '.',
            'url'     => route('calendario.cliente', ['mes' => $this->fecha->month, 'anio' => $this->fecha->year], false),
        ];
    }
}
