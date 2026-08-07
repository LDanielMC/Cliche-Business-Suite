<?php

namespace App\Notifications;

use App\Models\PaqueteAprobacion;
use Illuminate\Notifications\Notification;

class FotosAutoAprobadas extends Notification
{
    public function __construct(public PaqueteAprobacion $paquete, public int $aprobadas)
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
            'titulo'  => 'Tus fotos se aprobaron automáticamente',
            'mensaje' => 'No alcanzamos a recibir tu selección a tiempo, así que aprobamos ' .
                $this->aprobadas . ' fotografía(s) de ' . $this->paquete->mes_revision_legible . ' por ti.',
            'url'     => route('cliente.aprobaciones.index', [], false),
        ];
    }
}
