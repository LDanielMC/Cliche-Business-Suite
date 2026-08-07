<?php

namespace App\Notifications\Concerns;

use Illuminate\Notifications\Messages\MailMessage;

/**
 * Genera el correo a partir de los mismos datos de toArray() (icono/titulo/
 * mensaje/url), para no duplicar el texto entre la campana y el correo.
 * Requiere que la clase implemente toArray(object $notifiable): array con
 * esas llaves — 'url' debe ser relativa (route(..., false)), aquí se vuelve
 * absoluta con url() para que funcione dentro del correo.
 */
trait EnviaCorreoSimple
{
    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->toArray($notifiable);

        return (new MailMessage)
            ->subject($data['titulo'])
            ->line($data['mensaje'])
            ->action('Ver en Cliche Suite', url($data['url']));
    }
}
