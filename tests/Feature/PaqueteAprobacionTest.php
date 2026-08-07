<?php

namespace Tests\Feature;

use App\Models\ControlRenovacion;
use App\Models\FotoAprobacion;
use App\Models\PaqueteAprobacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Escenarios §9 1-9 del plan: creación de paquetes, gestión de fotos,
 * envío al cliente y selección exacta del cliente.
 */
class PaqueteAprobacionTest extends TestCase
{
    use RefreshDatabase;

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 1 — store() con cliente activo y renovación vigente → borrador
    // ══════════════════════════════════════════════════════════════════════════

    public function test_01_store_con_cliente_activo_y_renovacion_vigente_crea_borrador(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(4);
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('aprobaciones.store'), [
            'cliente_id'   => $cliente->id,
            'mes_revision' => now()->format('Y-m'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('paquetes_aprobacion', [
            'cliente_id'         => $cliente->id,
            'estatus'            => PaqueteAprobacion::ESTATUS_BORRADOR,
            'cantidad_requerida' => 4,
        ]);

        $paquete = PaqueteAprobacion::where('cliente_id', $cliente->id)->first();
        $this->assertEquals($renovacion->fecha_inicio->toDateString(), $paquete->fecha_inicio_periodo->toDateString());
        $this->assertEquals($renovacion->fecha_vencimiento->toDateString(), $paquete->fecha_vencimiento_periodo->toDateString());
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 2 — store() con renovación en estatus no operable → rechazado
    // ══════════════════════════════════════════════════════════════════════════

    public function test_02_store_con_renovacion_vencida_es_rechazado(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo();
        $renovacion->update(['estatus' => ControlRenovacion::ESTATUS_VENCIDO]);

        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('aprobaciones.store'), [
            'cliente_id'   => $cliente->id,
            'mes_revision' => now()->format('Y-m'),
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('paquetes_aprobacion', ['cliente_id' => $cliente->id]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 3 — store() con paquete activo existente → rechazado
    // ══════════════════════════════════════════════════════════════════════════

    public function test_03_store_con_paquete_activo_existente_es_rechazado_por_codigo(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo();
        $this->crearPaqueteBorrador($cliente, $renovacion); // ya existe uno activo

        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('aprobaciones.store'), [
            'cliente_id'   => $cliente->id,
            'mes_revision' => now()->subMonth()->format('Y-m'),
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('paquetes_aprobacion', 1);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 4a — uploadFotos() en borrador → ok
    // ══════════════════════════════════════════════════════════════════════════

    public function test_04a_subir_fotos_a_borrador_ok(): void
    {
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo();
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $admin   = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(
            route('aprobaciones.fotos.store', $paquete),
            ['fotos' => [UploadedFile::fake()->image('foto1.jpg')]]
        );

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('fotos_aprobacion', 1);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 4b — uploadFotos() en paquete pendiente → rechazado
    // ══════════════════════════════════════════════════════════════════════════

    public function test_04b_subir_fotos_a_paquete_pendiente_es_rechazado(): void
    {
        Storage::fake('public');
        Mail::fake();

        [$cliente, $renovacion] = $this->crearClienteActivo(2);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);

        // Crear fotos suficientes y enviar al cliente
        $this->crearFoto($paquete, 1);
        $this->crearFoto($paquete, 2);
        $admin = $this->crearAdmin();
        $this->enviarPaqueteAlCliente($paquete, $admin);

        $this->assertEquals(PaqueteAprobacion::ESTATUS_PENDIENTE, $paquete->estatus);

        $response = $this->actingAs($admin)->post(
            route('aprobaciones.fotos.store', $paquete),
            ['fotos' => [UploadedFile::fake()->image('nueva.jpg')]]
        );

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('fotos_aprobacion', 2); // sigue en 2
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 5 — asignarPrioridad() con valor duplicado → rechazado
    // ══════════════════════════════════════════════════════════════════════════

    public function test_05_prioridad_duplicada_es_rechazada(): void
    {
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo();
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $foto1   = $this->crearFoto($paquete, 1);
        $foto2   = $this->crearFoto($paquete);      // sin prioridad
        $admin   = $this->crearAdmin();

        // Intentar asignar prioridad 1 a foto2 (ya está en foto1)
        $response = $this->actingAs($admin)->patch(
            route('aprobaciones.fotos.prioridad', [$paquete, $foto2]),
            ['prioridad' => 1]
        );

        $response->assertSessionHas('error');
        $this->assertNull($foto2->fresh()->prioridad);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 6 — enviarACliente() con fotos < cantidad_requerida → rechazado
    // ══════════════════════════════════════════════════════════════════════════

    public function test_06_enviar_cliente_con_fotos_insuficientes_rechazado(): void
    {
        Storage::fake('public');
        Mail::fake();

        [$cliente, $renovacion] = $this->crearClienteActivo(4); // requiere 4
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);

        // Solo 2 fotos con prioridad (menos que cantidad_requerida=4)
        $this->crearFoto($paquete, 1);
        $this->crearFoto($paquete, 2);
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)
            ->post(route('aprobaciones.enviar-cliente', $paquete));

        $response->assertSessionHas('error');
        $this->assertEquals(PaqueteAprobacion::ESTATUS_BORRADOR, $paquete->fresh()->estatus);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 7 — enviarACliente() no requiere prioridad asignada
    // ══════════════════════════════════════════════════════════════════════════

    public function test_07_enviar_cliente_sin_prioridad_asignada_permitido(): void
    {
        Storage::fake('public');
        Mail::fake();

        [$cliente, $renovacion] = $this->crearClienteActivo(2);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);

        $this->crearFoto($paquete);   // sin prioridad — ya no es requisito
        $this->crearFoto($paquete);   // sin prioridad — ya no es requisito
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)
            ->post(route('aprobaciones.enviar-cliente', $paquete));

        $response->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertEquals(PaqueteAprobacion::ESTATUS_PENDIENTE, $paquete->fresh()->estatus);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 8 — confirmarSeleccion() con count ≠ cantidad_requerida → rechazado
    // ══════════════════════════════════════════════════════════════════════════

    public function test_08_confirmar_seleccion_count_incorrecto_rechazado(): void
    {
        Storage::fake('public');
        Mail::fake();

        [$cliente, $renovacion] = $this->crearClienteActivo(3); // requiere 3
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);

        $foto1 = $this->crearFoto($paquete, 1);
        $foto2 = $this->crearFoto($paquete, 2);
        $foto3 = $this->crearFoto($paquete, 3);
        $admin = $this->crearAdmin();
        $this->enviarPaqueteAlCliente($paquete, $admin);

        // El cliente manda solo 2 (menos que los 3 requeridos)
        $response = $this->actingAs($cliente->user)->post(
            route('cliente.aprobaciones.confirmar', $paquete),
            ['aprobadas' => [$foto1->id, $foto2->id]]  // solo 2, se requieren 3
        );

        $response->assertSessionHas('error');
        $this->assertEquals(PaqueteAprobacion::ESTATUS_PENDIENTE, $paquete->fresh()->estatus);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 9 — confirmarSeleccion() IDOR: IDs de otro paquete son filtrados
    // ══════════════════════════════════════════════════════════════════════════

    public function test_09_confirmar_seleccion_idor_ids_externos_son_filtrados(): void
    {
        Storage::fake('public');
        Mail::fake();

        // Cliente A con su paquete
        [$clienteA, $renovA] = $this->crearClienteActivo(2);
        $paqueteA = $this->crearPaqueteBorrador($clienteA, $renovA);
        $fotoA1   = $this->crearFoto($paqueteA, 1);
        $fotoA2   = $this->crearFoto($paqueteA, 2);

        // Cliente B con otro paquete
        [$clienteB, $renovB] = $this->crearClienteActivo(2);
        $paqueteB = $this->crearPaqueteBorrador($clienteB, $renovB);
        $fotoB1   = $this->crearFoto($paqueteB, 1);
        $fotoB2   = $this->crearFoto($paqueteB, 2);

        $admin = $this->crearAdmin();
        $this->enviarPaqueteAlCliente($paqueteA, $admin);

        // El cliente A intenta aprobar fotos del cliente B (IDOR)
        $response = $this->actingAs($clienteA->user)->post(
            route('cliente.aprobaciones.confirmar', $paqueteA),
            ['aprobadas' => [$fotoB1->id, $fotoB2->id]]  // IDs de otro paquete
        );

        // El backend filtra los IDs externos; queda aprobadas=[] (count 0 ≠ 2) → error de count
        $response->assertSessionHas('error');

        // Las fotos del cliente B siguen intactas
        $this->assertEquals(FotoAprobacion::ESTATUS_PENDIENTE, $fotoB1->fresh()->estatus);
        $this->assertEquals(FotoAprobacion::ESTATUS_PENDIENTE, $fotoB2->fresh()->estatus);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // confirmarSeleccion() happy path: fotos aprobadas, conservadas, descartadas
    // ══════════════════════════════════════════════════════════════════════════

    public function test_confirmar_seleccion_completo_distribuye_correctamente(): void
    {
        Storage::fake('public');
        Mail::fake();

        [$cliente, $renovacion] = $this->crearClienteActivo(1);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);

        $foto1 = $this->crearFoto($paquete, 1); // aprobada (es la única requerida)
        $foto2 = $this->crearFoto($paquete, 2); // conservada (sobrante)
        $foto3 = $this->crearFoto($paquete, 3); // descartada (sobrante)
        $admin = $this->crearAdmin();
        $this->enviarPaqueteAlCliente($paquete, $admin);

        $this->actingAs($cliente->user)->post(
            route('cliente.aprobaciones.confirmar', $paquete),
            [
                'aprobadas' => [$foto1->id],
                'conservar' => [$foto2->id],
            ]
        )->assertRedirect();

        $this->assertEquals(FotoAprobacion::ESTATUS_APROBADA,   $foto1->fresh()->estatus);
        $this->assertEquals(FotoAprobacion::ESTATUS_CONSERVADA,  $foto2->fresh()->estatus);
        $this->assertEquals(FotoAprobacion::ESTATUS_DESCARTADA,  $foto3->fresh()->estatus);
        $this->assertEquals(PaqueteAprobacion::ESTATUS_COMPLETADO, $paquete->fresh()->estatus);
        $this->assertNotNull($foto2->fresh()->fecha_ingreso_reserva);
        $this->assertNotNull($foto2->fresh()->fecha_expiracion_reserva);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // store() reingresa automáticamente las fotos del Banco de Reserva del
    // cliente como candidatas del nuevo paquete.
    // ══════════════════════════════════════════════════════════════════════════

    public function test_store_reingresa_automaticamente_fotos_del_banco_de_reserva(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(4);

        // Paquete anterior ya cerrado, con una foto que quedó conservada.
        $paqueteViejo = PaqueteAprobacion::create([
            'cliente_id'                => $cliente->id,
            'mes_revision'              => now()->subMonth()->format('Y-m'),
            'cantidad_requerida'        => 4,
            'estatus'                   => PaqueteAprobacion::ESTATUS_COMPLETADO,
            'fecha_inicio_periodo'      => $renovacion->fecha_inicio->copy()->subDays(30),
            'fecha_vencimiento_periodo' => $renovacion->fecha_inicio->copy()->subDay(),
        ]);

        $fotoReserva = FotoAprobacion::create([
            'paquete_aprobacion_id'    => $paqueteViejo->id,
            'cliente_id'               => $cliente->id,
            'ruta_foto'                => 'fotos-aprobacion/reserva/foto.jpg',
            'estatus'                  => FotoAprobacion::ESTATUS_CONSERVADA,
            'fecha_ingreso_reserva'    => now()->subDays(10),
            'fecha_expiracion_reserva' => now()->addMonths(5),
        ]);

        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('aprobaciones.store'), [
            'cliente_id'   => $cliente->id,
            'mes_revision' => now()->format('Y-m'),
        ]);

        $response->assertRedirect();

        $paqueteNuevo = PaqueteAprobacion::where('cliente_id', $cliente->id)
            ->where('estatus', PaqueteAprobacion::ESTATUS_BORRADOR)
            ->firstOrFail();

        $fotoReserva->refresh();
        $this->assertEquals($paqueteNuevo->id, $fotoReserva->paquete_aprobacion_id,
            'La foto de reserva debe quedar asociada al paquete nuevo.');
        $this->assertEquals(FotoAprobacion::ESTATUS_PENDIENTE, $fotoReserva->estatus);
        $this->assertNull($fotoReserva->fecha_expiracion_reserva);
        $this->assertNotNull($fotoReserva->fecha_ingreso_reserva,
            'Debe conservar la marca de origen para poder regresarla a reserva si se quita.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // destroyFoto(): una foto que vino de reserva regresa al Banco de Reserva;
    // una foto subida directa al paquete se elimina permanentemente.
    // ══════════════════════════════════════════════════════════════════════════

    public function test_destroyFoto_regresa_foto_de_reserva_al_banco_en_vez_de_borrarla(): void
    {
        Mail::fake();
        Storage::fake('public');
        Storage::fake('gcs');

        [$cliente, $renovacion] = $this->crearClienteActivo(4);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);

        $fotoReserva = FotoAprobacion::create([
            'paquete_aprobacion_id'    => $paquete->id,
            'cliente_id'               => $cliente->id,
            'ruta_foto'                => 'fotos-aprobacion/reserva/foto.jpg',
            'estatus'                  => FotoAprobacion::ESTATUS_PENDIENTE,
            'fecha_ingreso_reserva'    => now()->subDays(10),
            'fecha_expiracion_reserva' => null,
        ]);

        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)
            ->delete(route('aprobaciones.fotos.destroy', [$paquete, $fotoReserva]));

        $response->assertRedirect();
        $this->assertDatabaseHas('fotos_aprobacion', [
            'id'      => $fotoReserva->id,
            'estatus' => FotoAprobacion::ESTATUS_CONSERVADA,
        ]);
        $this->assertNotNull($fotoReserva->fresh()->fecha_expiracion_reserva);
    }

    public function test_destroyFoto_elimina_permanentemente_foto_subida_directa(): void
    {
        Mail::fake();
        Storage::fake('public');
        Storage::fake('gcs');

        [$cliente, $renovacion] = $this->crearClienteActivo(4);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $foto    = $this->crearFoto($paquete); // sin fecha_ingreso_reserva: subida directa

        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)
            ->delete(route('aprobaciones.fotos.destroy', [$paquete, $foto]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('fotos_aprobacion', ['id' => $foto->id]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // destroy() del paquete completo: la FK paquete_aprobacion_id tiene
    // cascadeOnDelete, así que una foto de reserva atrapada en un borrador
    // debe desprenderse ANTES de borrar el paquete, o la cascada se la lleva
    // aunque su estatus ya sea 'conservada'. Este es el bug reportado.
    // ══════════════════════════════════════════════════════════════════════════

    public function test_destroy_paquete_no_borra_fotos_de_reserva_atrapadas_en_el_borrador(): void
    {
        Mail::fake();
        Storage::fake('public');
        Storage::fake('gcs');

        [$cliente, $renovacion] = $this->crearClienteActivo(4);

        // Foto que ya estaba en el Banco de Reserva antes de este borrador.
        $paqueteViejo = PaqueteAprobacion::create([
            'cliente_id'                => $cliente->id,
            'mes_revision'              => now()->subMonth()->format('Y-m'),
            'cantidad_requerida'        => 4,
            'estatus'                   => PaqueteAprobacion::ESTATUS_COMPLETADO,
            'fecha_inicio_periodo'      => $renovacion->fecha_inicio->copy()->subDays(30),
            'fecha_vencimiento_periodo' => $renovacion->fecha_inicio->copy()->subDay(),
        ]);

        $fotoReserva = FotoAprobacion::create([
            'paquete_aprobacion_id'    => $paqueteViejo->id,
            'cliente_id'               => $cliente->id,
            'ruta_foto'                => 'fotos-aprobacion/reserva/foto.jpg',
            'estatus'                  => FotoAprobacion::ESTATUS_CONSERVADA,
            'fecha_ingreso_reserva'    => now()->subDays(10),
            'fecha_expiracion_reserva' => now()->addMonths(5),
        ]);

        // Un paquete nuevo en borrador la reingresa como candidata (store()
        // ya lo hace solo, pero se simula igual para no depender de otro test).
        $paqueteNuevo = $this->crearPaqueteBorrador($cliente, $renovacion);
        $fotoReserva->update([
            'paquete_aprobacion_id'    => $paqueteNuevo->id,
            'estatus'                  => FotoAprobacion::ESTATUS_PENDIENTE,
            'fecha_expiracion_reserva' => null,
        ]);

        // Otra foto subida directa a este mismo borrador, sin relación con reserva.
        $fotoDirecta = $this->crearFoto($paqueteNuevo);

        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->delete(route('aprobaciones.destroy', $paqueteNuevo));
        $response->assertRedirect();

        // La foto de reserva sobrevive, regresa a conservada y se desprende del paquete borrado.
        $this->assertDatabaseHas('fotos_aprobacion', [
            'id'                    => $fotoReserva->id,
            'estatus'               => FotoAprobacion::ESTATUS_CONSERVADA,
            'paquete_aprobacion_id' => null,
        ]);

        // La foto subida directa sí se elimina junto con el paquete.
        $this->assertDatabaseMissing('fotos_aprobacion', ['id' => $fotoDirecta->id]);
        $this->assertDatabaseMissing('paquetes_aprobacion', ['id' => $paqueteNuevo->id]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Límite de ciclos en reserva: una foto que ya lleva (límite - 1) veces
    // conservada, al volver a marcarse "conservar" llega al límite y se
    // descarta en vez de reiniciar su reloj de reserva otra vez.
    // ══════════════════════════════════════════════════════════════════════════

    public function test_confirmar_seleccion_descarta_foto_al_llegar_al_limite_de_veces_conservada(): void
    {
        Storage::fake('public');
        Mail::fake();

        $limite = config('renovaciones.max_veces_conservada', 3);

        [$cliente, $renovacion] = $this->crearClienteActivo(1);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);

        $foto1 = $this->crearFoto($paquete, 1); // será la aprobada
        $foto2 = $this->crearFoto($paquete, 2); // ya viene de reserva, a un ciclo del límite
        $foto2->update(['veces_conservada' => $limite - 1]);

        $admin = $this->crearAdmin();
        $this->enviarPaqueteAlCliente($paquete, $admin);

        $this->actingAs($cliente->user)->post(
            route('cliente.aprobaciones.confirmar', $paquete),
            [
                'aprobadas' => [$foto1->id],
                'conservar' => [$foto2->id],
            ]
        )->assertRedirect();

        // Llegó al límite: se descarta en vez de conservarse una vez más.
        $this->assertEquals(FotoAprobacion::ESTATUS_DESCARTADA, $foto2->fresh()->estatus);
        $this->assertEquals($limite, $foto2->fresh()->veces_conservada);
    }

    public function test_confirmar_seleccion_conserva_normal_si_no_llega_al_limite(): void
    {
        Storage::fake('public');
        Mail::fake();

        [$cliente, $renovacion] = $this->crearClienteActivo(1);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);

        $foto1 = $this->crearFoto($paquete, 1);
        $foto2 = $this->crearFoto($paquete, 2); // primera vez que se conserva

        $admin = $this->crearAdmin();
        $this->enviarPaqueteAlCliente($paquete, $admin);

        $this->actingAs($cliente->user)->post(
            route('cliente.aprobaciones.confirmar', $paquete),
            [
                'aprobadas' => [$foto1->id],
                'conservar' => [$foto2->id],
            ]
        )->assertRedirect();

        $this->assertEquals(FotoAprobacion::ESTATUS_CONSERVADA, $foto2->fresh()->estatus);
        $this->assertEquals(1, $foto2->fresh()->veces_conservada);
    }
}
