<?php

namespace App\Mail;

use App\Models\PaqueteAprobacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdvertenciaFotosInsuficientes extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PaqueteAprobacion $paquete,
        public int $aprobadas,
        public int $requeridas,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Advertencia] Auto-aprobación incompleta — ' . $this->paquete->cliente->nombre_negocio,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.advertencia-fotos-insuficientes',
        );
    }
}
