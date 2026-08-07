<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class NotificacionExpirarReserva extends Mailable
{
    use Queueable, SerializesModels;

    /** @param Collection<int, \App\Models\FotoAprobacion> $fotos */
    public function __construct(public Collection $fotos)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Acción requerida] Fotografías en reserva próximas a expirar',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notificacion-expirar-reserva',
        );
    }
}
