<?php

namespace Tests\Feature;

use App\Models\CalendarioFoto;
use App\Models\Cliente;
use App\Models\ControlRenovacion;
use App\Models\FotoAprobacion;
use App\Models\User;
use App\Notifications\ClienteNuevo;
use App\Notifications\ComprobantePagoRecibido;
use App\Notifications\FotografiaCancelada;
use App\Notifications\FotografiaPublicada;
use App\Notifications\FotografiaReprogramada;
use App\Notifications\FotografiasProgramadas;
use App\Notifications\FotosDescartadasPorLimiteReserva;
use App\Notifications\SeleccionFotosConfirmada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Notificaciones nuevas de la campana: comprobante de pago recibido y
 * selección de fotos confirmada — avisan a admins/operadores de acciones
 * del cliente que quedan pendientes de revisar.
 */
class NotificacionesTest extends TestCase
{
    use RefreshDatabase;

    public function test_enviar_comprobante_notifica_solo_a_admins_no_a_operadores(): void
    {
        Notification::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo();
        $renovacion->update(['estatus' => ControlRenovacion::ESTATUS_POR_VENCER]);

        $admin    = $this->crearAdmin();
        $operador = $this->crearAdmin(role: \App\Models\User::ROLE_OPERADOR);

        $response = $this->actingAs($cliente->user)->post(
            route('renovaciones.cliente.enviar', $renovacion),
            [
                'comprobante_pago'   => UploadedFile::fake()->create('comprobante.pdf', 100),
                'fecha_pago_cliente' => now()->toDateString(),
                'monto'              => 500,
                'forma_pago'         => 'transferencia',
                'solicita_factura'   => 0,
            ]
        );

        $response->assertRedirect();

        // Renovaciones es admin-only: el operador no tiene acceso a esa
        // ruta, así que no debe recibir una notificación que solo lo
        // llevaría a un 403.
        Notification::assertSentTo($admin, ComprobantePagoRecibido::class);
        Notification::assertNotSentTo($operador, ComprobantePagoRecibido::class);
    }

    public function test_confirmar_seleccion_notifica_a_admins_que_el_cliente_ya_eligio(): void
    {
        Notification::fake();
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(1);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $foto    = $this->crearFoto($paquete, 1);

        $admin = $this->crearAdmin();
        $this->enviarPaqueteAlCliente($paquete, $admin);

        $this->actingAs($cliente->user)->post(
            route('cliente.aprobaciones.confirmar', $paquete),
            ['aprobadas' => [$foto->id]]
        )->assertRedirect();

        Notification::assertSentTo($admin, SeleccionFotosConfirmada::class, function ($notification) {
            return $notification->aprobadas === 1;
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Cambios en el calendario avisan al cliente dueño de esa entrada.
    // ══════════════════════════════════════════════════════════════════════════

    public function test_colocar_en_lote_notifica_al_cliente_una_sola_vez(): void
    {
        Notification::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(4);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $fotos   = collect(range(1, 3))->map(
            fn ($i) => $this->crearFoto($paquete, $i, FotoAprobacion::ESTATUS_APROBADA)
        );
        $admin = $this->crearAdmin();
        $hoy   = Carbon::today()->toDateString();

        $this->actingAs($admin)->post(route('calendario.colocar'), [
            'foto_ids' => $fotos->pluck('id')->all(),
            'fecha'    => $hoy,
        ])->assertRedirect();

        Notification::assertSentToTimes($cliente->user, FotografiasProgramadas::class, 1);
        Notification::assertSentTo($cliente->user, FotografiasProgramadas::class, function ($notification) {
            return $notification->cantidad === 3;
        });
    }

    public function test_mover_notifica_al_cliente_con_fecha_anterior_y_nueva(): void
    {
        Notification::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(2);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $foto    = $this->crearFoto($paquete, 1, FotoAprobacion::ESTATUS_APROBADA);
        $admin   = $this->crearAdmin();

        $fechaOriginal = Carbon::today()->addDays(2)->toDateString();
        $fechaNueva    = Carbon::today()->addDays(4)->toDateString();

        $this->actingAs($admin)->post(route('calendario.colocar'), [
            'foto_id' => $foto->id,
            'fecha'   => $fechaOriginal,
        ])->assertRedirect();

        $entrada = CalendarioFoto::where('foto_aprobacion_id', $foto->id)->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('calendario.mover', $entrada), ['fecha' => $fechaNueva])
            ->assertRedirect();

        Notification::assertSentTo($cliente->user, FotografiaReprogramada::class, function ($notification) use ($fechaOriginal, $fechaNueva) {
            return $notification->fechaAnterior->toDateString() === $fechaOriginal
                && $notification->fechaNueva->toDateString() === $fechaNueva;
        });
    }

    public function test_publicar_notifica_al_cliente(): void
    {
        Notification::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(2);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $foto    = $this->crearFoto($paquete, 1, FotoAprobacion::ESTATUS_APROBADA);
        $admin   = $this->crearAdmin();
        $hoy     = Carbon::today()->toDateString();

        $this->actingAs($admin)->post(route('calendario.colocar'), [
            'foto_id' => $foto->id,
            'fecha'   => $hoy,
        ])->assertRedirect();

        $entrada = CalendarioFoto::where('foto_aprobacion_id', $foto->id)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('calendario.publicar', $entrada))
            ->assertRedirect();

        Notification::assertSentTo($cliente->user, FotografiaPublicada::class);
    }

    public function test_eliminar_entrada_programada_notifica_al_cliente(): void
    {
        Notification::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(2);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $foto    = $this->crearFoto($paquete, 1, FotoAprobacion::ESTATUS_APROBADA);
        $admin   = $this->crearAdmin();
        $hoy     = Carbon::today()->toDateString();

        $this->actingAs($admin)->post(route('calendario.colocar'), [
            'foto_id' => $foto->id,
            'fecha'   => $hoy,
        ])->assertRedirect();

        $entrada = CalendarioFoto::where('foto_aprobacion_id', $foto->id)->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('calendario.destroy', $entrada))
            ->assertRedirect();

        Notification::assertSentTo($cliente->user, FotografiaCancelada::class);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Eliminar notificaciones (individual y en lote las ya leídas).
    // ══════════════════════════════════════════════════════════════════════════

    public function test_destroy_elimina_una_notificacion_propia(): void
    {
        Storage::fake('public');
        [$cliente] = $this->crearClienteActivo();

        $cliente->user->notify(new FotografiaPublicada(now()));
        $notifId = $cliente->user->notifications()->first()->id;

        $this->actingAs($cliente->user)
            ->delete(route('notificaciones.destroy', $notifId))
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', ['id' => $notifId]);
    }

    public function test_destroy_no_permite_borrar_notificacion_de_otro_usuario(): void
    {
        Storage::fake('public');
        [$cliente1] = $this->crearClienteActivo();
        [$cliente2] = $this->crearClienteActivo();

        $cliente1->user->notify(new FotografiaPublicada(now()));
        $notifId = $cliente1->user->notifications()->first()->id;

        $this->actingAs($cliente2->user)
            ->delete(route('notificaciones.destroy', $notifId))
            ->assertNotFound();

        $this->assertDatabaseHas('notifications', ['id' => $notifId]);
    }

    public function test_eliminar_leidas_solo_borra_las_ya_leidas(): void
    {
        Storage::fake('public');
        [$cliente] = $this->crearClienteActivo();

        $cliente->user->notify(new FotografiaPublicada(now()));
        $cliente->user->notify(new FotografiaCancelada(now()));

        $notifs  = $cliente->user->notifications;
        $leida   = $notifs->first();
        $noLeida = $notifs->last();
        $leida->markAsRead();

        $this->actingAs($cliente->user)
            ->delete(route('notificaciones.eliminar-leidas'))
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', ['id' => $leida->id]);
        $this->assertDatabaseHas('notifications', ['id' => $noLeida->id]);
    }

    public function test_notificacion_de_fotos_descartadas_apunta_a_una_ruta_que_el_operador_puede_abrir(): void
    {
        [$cliente] = $this->crearClienteActivo();
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);

        $notificacion = new FotosDescartadasPorLimiteReserva($cliente, 2);
        $url = $notificacion->toArray($operador)['url'];

        $this->assertSame(route('calendario.cliente-view', $cliente, false), $url);
        $this->assertStringNotContainsString('/clientes/', $url);

        $this->actingAs($operador)->get($url)->assertOk();
    }

    public function test_crear_cliente_notifica_a_admin_y_operador(): void
    {
        Notification::fake();
        $admin    = $this->crearAdmin();
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);

        $response = $this->actingAs($admin)->post(route('clientes.store'), [
            'nombre_negocio'      => 'Nuevo Negocio SA',
            'nombres'             => 'Ana',
            'apellido_paterno'    => 'García',
            'email'               => 'nuevo.negocio@example.com',
            'cantidad_fotos'      => 5,
            'precio_mensual'      => 500,
        ]);

        $response->assertRedirect(route('clientes.index'));

        Notification::assertSentTo($admin, ClienteNuevo::class, fn ($n) => !$n->reactivado);
        Notification::assertSentTo($operador, ClienteNuevo::class, fn ($n) => !$n->reactivado);
    }

    public function test_notificacion_de_cliente_nuevo_apunta_a_una_ruta_que_el_operador_puede_abrir(): void
    {
        [$cliente] = $this->crearClienteActivo();
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);

        $notificacion = new ClienteNuevo($cliente);
        $url = $notificacion->toArray($operador)['url'];

        $this->assertSame(route('aprobaciones.create', ['cliente_id' => $cliente->id], false), $url);
        $this->actingAs($operador)->get($url)->assertOk();
    }

    public function test_reactivar_cliente_notifica_con_bandera_de_reactivado(): void
    {
        Notification::fake();
        $admin = $this->crearAdmin();
        [$cliente] = $this->crearClienteActivo();
        $cliente->user->update(['estatus' => User::ESTATUS_DADO_DE_BAJA, 'fecha_baja' => now()]);

        $response = $this->actingAs($admin)->post(route('clientes.restaurar', $cliente->id));

        $response->assertRedirect();

        Notification::assertSentTo($admin, ClienteNuevo::class, fn ($n) => $n->reactivado === true);
    }
}
