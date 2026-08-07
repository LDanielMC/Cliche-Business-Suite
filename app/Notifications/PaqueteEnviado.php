<?php

namespace App\Notifications;

use App\Models\PaqueteAprobacion;
use App\Notifications\Concerns\EnviaCorreoSimple;
use Illuminate\Notifications\Notification;

class PaqueteEnviado extends Notification
{
    use EnviaCorreoSimple;

    public function __construct(public PaqueteAprobacion $paquete)
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
            'icono'   => 'foto',
            'titulo'  => 'Tienes fotos para revisar',
            'mensaje' => 'Ya puedes elegir tus fotografías de ' . $this->paquete->mes_revision_legible . '.',
            'url'     => route('cliente.aprobaciones.show', $this->paquete, false),
        ];
    }
}
