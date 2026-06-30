<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Codigo de recuperacion de contraseña - Cliche Business Suite',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-code',
            with: ['code' => $this->code],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
