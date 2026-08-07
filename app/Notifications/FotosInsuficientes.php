<?php

namespace App\Notifications;

use App\Models\PaqueteAprobacion;
use App\Notifications\Concerns\EnviaCorreoSimple;
use Illuminate\Notifications\Notification;

class FotosInsuficientes extends Notification
{
    use EnviaCorreoSimple;

    public function __construct(public PaqueteAprobacion $paquete, public int $aprobadas, public int $requeridas)
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
            'icono'   => 'alerta',
            'titulo'  => 'Auto-aprobación incompleta',
            'mensaje' => $this->paquete->cliente->nombre_negocio . ': solo ' . $this->aprobadas . '/' . $this->requeridas . ' fotos disponibles.',
            'url'     => route('aprobaciones.show', $this->paquete, false),
        ];
    }
}
