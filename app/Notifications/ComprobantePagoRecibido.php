<?php

namespace App\Notifications;

use App\Models\ControlRenovacion;
use Illuminate\Notifications\Notification;

class ComprobantePagoRecibido extends Notification
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
            'titulo'  => 'Comprobante de pago recibido',
            'mensaje' => $this->renovacion->cliente->nombre_negocio .
                ' envió un comprobante de pago por $' . number_format((float) $this->renovacion->monto, 2) .
                '. Pendiente de revisión.',
            'url'     => route('renovaciones.show', $this->renovacion, false),
        ];
    }
}
