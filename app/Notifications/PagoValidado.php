<?php

namespace App\Notifications;

use App\Models\ControlRenovacion;
use Illuminate\Notifications\Notification;

class PagoValidado extends Notification
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
            'icono'   => 'pago',
            'titulo'  => 'Tu pago fue validado',
            'mensaje' => 'Tu renovación quedó vigente hasta el ' . $this->renovacion->fecha_vencimiento->format('d/m/Y') . '.',
            'url'     => route('renovaciones.cliente.index', [], false),
        ];
    }
}
