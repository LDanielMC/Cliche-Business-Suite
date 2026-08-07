<?php

namespace App\Notifications;

use App\Models\PaqueteAprobacion;
use App\Notifications\Concerns\EnviaCorreoSimple;
use Illuminate\Notifications\Notification;

class SeleccionFotosConfirmada extends Notification
{
    use EnviaCorreoSimple;

    public function __construct(public PaqueteAprobacion $paquete, public int $aprobadas)
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
            'titulo'  => 'Cliente confirmó su selección de fotos',
            'mensaje' => $this->paquete->cliente->nombre_negocio . ' eligió sus ' . $this->aprobadas .
                ' fotografía(s) de ' . $this->paquete->mes_revision_legible . '. Ya se pueden programar en el calendario.',
            'url'     => route('calendario.cliente-view', $this->paquete->cliente_id, false),
        ];
    }
}
