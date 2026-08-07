<?php

namespace App\Mail;

use App\Models\PaqueteAprobacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaqueteEnviadoCliente extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PaqueteAprobacion $paquete)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tienes fotografías pendientes de selección — ' . $this->paquete->mes_revision_legible,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.paquete-enviado-cliente',
        );
    }
}
