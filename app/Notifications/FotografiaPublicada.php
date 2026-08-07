<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class FotografiaPublicada extends Notification
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
            'icono'   => 'foto',
            'titulo'  => '¡Ya se publicó tu fotografía!',
            'mensaje' => 'La fotografía programada para el ' . $this->fecha->format('d/m/Y') . ' ya está publicada.',
            'url'     => route('calendario.cliente', ['mes' => $this->fecha->month, 'anio' => $this->fecha->year], false),
        ];
    }
}
