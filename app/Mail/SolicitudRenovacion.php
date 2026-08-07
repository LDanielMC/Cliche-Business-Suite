<?php

namespace App\Mail;

use App\Models\ControlRenovacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudRenovacion extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ControlRenovacion $renovacion)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva solicitud de renovación — ' . $this->renovacion->cliente->nombre_negocio,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud-renovacion',
            with: ['renovacion' => $this->renovacion],
        );
    }
}
