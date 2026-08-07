<?php

namespace App\Notifications;

use App\Models\Cliente;
use Illuminate\Notifications\Notification;

class FotosDescartadasPorLimiteReserva extends Notification
{
    public function __construct(public Cliente $cliente, public int $cantidad)
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
            'icono'   => 'alerta',
            'titulo'  => 'Fotos descartadas por ciclo de reserva',
            'mensaje' => $this->cantidad . ' fotografía(s) de ' . $this->cliente->nombre_negocio .
                ' llevaban demasiados ciclos sin decidirse (conservada → reingresada → conservada) ' .
                'y se marcaron descartadas automáticamente.',
            // calendario.cliente-view (no clientes.show): así también funciona
            // para el operador, que no tiene acceso a la ficha de Clientes.
            'url'     => route('calendario.cliente-view', $this->cliente, false),
        ];
    }
}
