<?php

namespace Tests\Feature;

use App\Models\ControlRenovacion;
use App\Models\FotoAprobacion;
use App\Models\PaqueteAprobacion;
use App\Models\User;
use App\Notifications\FotosInsuficientes;
use App\Notifications\PagoRechazado;
use App\Notifications\PaqueteEnviado;
use App\Notifications\SeleccionFotosConfirmada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Los avisos más importantes (confirmados con el usuario) ahora también se
 * mandan por correo, no solo por la campana. Se verifica que via() declare
 * el canal 'mail' y que toMail() arme el correo sin errores.
 */
class NotificacionesCorreoTest extends TestCase
{
    use RefreshDatabase;

    public function test_seleccion_fotos_confirmada_manda_correo(): void
    {
        [$cliente, $renovacion] = $this->crearClienteActivo();
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $admin = $this->crearAdmin();

        $notificacion = new SeleccionFotosConfirmada($paquete, 5);

        $this->assertContains('mail', $notificacion->via($admin));
        $this->assertInstanceOf(MailMessage::class, $notificacion->toMail($admin));
    }

    public function test_fotos_insuficientes_manda_correo(): void
    {
        [$cliente, $renovacion] = $this->crearClienteActivo();
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $admin = $this->crearAdmin();

        $notificacion = new FotosInsuficientes($paquete, 2, 5);

        $this->assertContains('mail', $notificacion->via($admin));
        $this->assertInstanceOf(MailMessage::class, $notificacion->toMail($admin));
    }

    public function test_pago_rechazado_manda_correo(): void
    {
        [$cliente, $renovacion] = $this->crearClienteActivo();
        $renovacion->update(['motivo_rechazo' => 'El comprobante es ilegible.']);

        $notificacion = new PagoRechazado($renovacion->fresh());

        $this->assertContains('mail', $notificacion->via($cliente->user));
        $this->assertInstanceOf(MailMessage::class, $notificacion->toMail($cliente->user));
    }

    public function test_paquete_enviado_manda_correo(): void
    {
        [$cliente, $renovacion] = $this->crearClienteActivo();
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);

        $notificacion = new PaqueteEnviado($paquete);

        $this->assertContains('mail', $notificacion->via($cliente->user));
        $this->assertInstanceOf(MailMessage::class, $notificacion->toMail($cliente->user));
    }

    public function test_seleccion_fotos_confirmada_se_dispara_realmente_por_correo_al_confirmar(): void
    {
        Notification::fake();

        [$cliente, $renovacion] = $this->crearClienteActivo(1);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $admin = $this->crearAdmin();
        $foto = $this->crearFoto($paquete, 1);
        $this->enviarPaqueteAlCliente($paquete, $admin);

        $this->actingAs($cliente->user)->post(route('cliente.aprobaciones.confirmar', $paquete), [
            'aprobadas' => [$foto->id],
        ])->assertRedirect();

        Notification::assertSentTo($admin, SeleccionFotosConfirmada::class, function ($n, $channels) {
            return in_array('mail', $channels);
        });
    }
}
