<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class RecordatorioPaquetesPendientes extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Collection $enRiesgo)
    {
        //
    }

    public function envelope(): Envelope
    {
        $criticos = $this->enRiesgo->where('nivel', 'critico')->count();

        $subject = $criticos > 0
            ? "[Urgente] {$criticos} paquete(s) de aprobación sin enviar"
            : 'Recordatorio: paquetes de aprobación por subir';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio-paquetes-pendientes',
            with: ['enRiesgo' => $this->enRiesgo],
        );
    }
}
