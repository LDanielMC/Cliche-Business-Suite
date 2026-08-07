<?php

namespace App\Notifications;

use App\Notifications\Concerns\EnviaCorreoSimple;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class FotosVencidasSinAgendarDigest extends Notification
{
    use EnviaCorreoSimple;

    /** @param Collection<int, \App\Models\FotoAprobacion> $fotos */
    public function __construct(public Collection $fotos, public int $mesesReserva)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $nombres = $this->fotos->pluck('paquete.cliente.nombre_negocio')->unique()->take(3)->implode(', ');

        return [
            'icono'   => 'alerta',
            'titulo'  => $this->fotos->count() . ' foto(s) aprobada(s) nunca agendada(s)',
            'mensaje' => "Llevan más de {$this->mesesReserva} mes(es) sin colocarse en el calendario: " . $nombres .
                ($this->fotos->pluck('paquete.cliente.nombre_negocio')->unique()->count() > 3 ? '…' : ''),
            'url'     => route('calendario.index', [], false),
        ];
    }
}
