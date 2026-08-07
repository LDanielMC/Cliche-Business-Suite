<?php

namespace App\Notifications;

use App\Models\ControlRenovacion;
use Illuminate\Notifications\Notification;

class RenovacionPorVencer extends Notification
{
    public function __construct(public ControlRenovacion $renovacion)
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
            'icono'   => 'renovacion',
            'titulo'  => 'Tu servicio está por vencer',
            'mensaje' => 'Vence el ' . $this->renovacion->fecha_vencimiento->format('d/m/Y') . '. Renueva a tiempo para no perder continuidad.',
            'url'     => route('renovaciones.cliente.index', [], false),
        ];
    }
}
