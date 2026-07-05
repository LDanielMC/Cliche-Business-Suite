<?php

namespace App\Mail;

use App\Models\ControlRenovacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecordatorioRenovacion extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ControlRenovacion $renovacion)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu servicio de posicionamiento local está por vencer',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio-renovacion',
            with: ['renovacion' => $this->renovacion],
        );
    }
}
