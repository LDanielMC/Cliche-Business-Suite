<?php

namespace App\Notifications;

use App\Models\Cliente;
use Illuminate\Notifications\Notification;

class ClienteNuevo extends Notification
{
    public function __construct(public Cliente $cliente, public bool $reactivado = false)
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
            'titulo'  => $this->reactivado ? 'Cliente reactivado' : 'Cliente nuevo',
            'mensaje' => $this->cliente->nombre_negocio . ($this->reactivado
                ? ' fue reactivado — hay que retomarle su calendario y paquete de fotos.'
                : ' se registró — hay que armarle su primer paquete de fotos.'),
            // aprobaciones.create (no clientes.show): así también funciona
            // para el operador, y lleva directo a la acción pendiente.
            'url'     => route('aprobaciones.create', ['cliente_id' => $this->cliente->id], false),
        ];
    }
}
