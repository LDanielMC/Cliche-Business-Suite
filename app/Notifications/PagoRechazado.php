<?php

namespace App\Notifications;

use App\Models\ControlRenovacion;
use App\Notifications\Concerns\EnviaCorreoSimple;
use Illuminate\Notifications\Notification;

class PagoRechazado extends Notification
{
    use EnviaCorreoSimple;

    public function __construct(public ControlRenovacion $renovacion)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icono'   => 'pago',
            'titulo'  => 'Tu comprobante fue rechazado',
            'mensaje' => $this->renovacion->motivo_rechazo ?: 'Necesitamos que envíes un comprobante válido.',
            'url'     => route('renovaciones.cliente.index', [], false),
        ];
    }
}
