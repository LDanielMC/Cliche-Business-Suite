<?php

namespace App\Notifications;

use App\Models\ControlRenovacion;
use Illuminate\Notifications\Notification;

class SolicitudRenovacionCliente extends Notification
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
            'titulo'  => 'Solicitud de renovación',
            'mensaje' => $this->renovacion->cliente->nombre_negocio . ' solicitó reactivar su cuenta.',
            'url'     => route('renovaciones.show', $this->renovacion, false),
        ];
    }
}
