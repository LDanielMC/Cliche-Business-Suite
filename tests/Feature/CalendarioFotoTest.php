<?php

namespace Tests\Feature;

use App\Models\CalendarioFoto;
use App\Models\ControlRenovacion;
use App\Models\FotoAprobacion;
use App\Models\PaqueteAprobacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Escenarios §9 12-14 + invariantes de BD del calendario (FN.04).
 *
 * "Activa" = programada | publicada en TODOS los chequeos.
 */
class CalendarioFotoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Coloca una foto aprobada en el calendario y devuelve la CalendarioFoto creada.
     * Usa el endpoint real para que pasen todas las validaciones de negocio.
     */
    private function colocarFoto(
        FotoAprobacion $foto,
        string $fecha,
        \App\Models\User $admin
    ): \Illuminate\Testing\TestResponse {
        return $this->actingAs($admin)->post(route('calendario.colocar'), [
            'foto_id' => $foto->id,
            'fecha'   => $fecha,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 12 — colocar() fuera del snapshot del paquete → rechazado
    // ══════════════════════════════════════════════════════════════════════════

    public function test_12_colocar_foto_fuera_del_periodo_snapshot_rechazado(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(2);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $foto    = $this->crearFoto($paquete, 1, FotoAprobacion::ESTATUS_APROBADA);
        $admin   = $this->crearAdmin();

        // Fecha fuera del snapshot del paquete (anterior al inicio)
        $fechaFuera = Carbon::parse($paquete->fecha_inicio_periodo)->subDay()->toDateString();

        $response = $this->colocarFoto($foto, $fechaFuera, $admin);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('calendario_fotos', ['foto_aprobacion_id' => $foto->id]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 13a — varias fotos el mismo día están permitidas (sin tope diario);
    // el único límite es cantidad_requerida en el período.
    // ══════════════════════════════════════════════════════════════════════════

    public function test_13a_varias_fotos_mismo_dia_permitidas_hasta_cuota_del_periodo(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(2);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $foto1   = $this->crearFoto($paquete, 1, FotoAprobacion::ESTATUS_APROBADA);
        $foto2   = $this->crearFoto($paquete, 2, FotoAprobacion::ESTATUS_APROBADA);
        $foto3   = $this->crearFoto($paquete, 3, FotoAprobacion::ESTATUS_APROBADA);
        $admin   = $this->crearAdmin();

        // El mínimo agendable es siempre hoy (nunca días ya pasados), aunque el
        // período haya arrancado antes.
        $fechaDentro = Carbon::today()->addDays(3)->toDateString();

        // Dos fotos el mismo día → ambas aceptadas (ya no hay tope por día)
        $this->colocarFoto($foto1, $fechaDentro, $admin)->assertRedirect();
        $this->colocarFoto($foto2, $fechaDentro, $admin)->assertRedirect();
        $this->assertDatabaseHas('calendario_fotos', ['foto_aprobacion_id' => $foto1->id]);
        $this->assertDatabaseHas('calendario_fotos', ['foto_aprobacion_id' => $foto2->id]);

        // La 3ra excede cantidad_requerida (2) del período → rechazada
        $response = $this->colocarFoto($foto3, $fechaDentro, $admin);
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('calendario_fotos', ['foto_aprobacion_id' => $foto3->id]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Invariante BD — una foto aprobada no puede estar activa dos veces
    // ══════════════════════════════════════════════════════════════════════════

    public function test_13b_foto_aprobada_no_puede_programarse_dos_veces(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(4);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $foto    = $this->crearFoto($paquete, 1, FotoAprobacion::ESTATUS_APROBADA);
        $admin   = $this->crearAdmin();

        $fecha1 = Carbon::today()->addDays(2)->toDateString();
        $fecha2 = Carbon::today()->addDays(5)->toDateString();

        // Primera colocación → ok
        $this->colocarFoto($foto, $fecha1, $admin)->assertRedirect();

        // Segunda colocación de la misma foto en diferente día → rechazada (guarda yaScheduled)
        $response = $this->colocarFoto($foto, $fecha2, $admin);
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('calendario_fotos', 1);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 14 — cascade: foto aprobada con CalendarioFoto activa se cancela
    // cuando el cliente confirma y esa foto NO queda aprobada
    // ══════════════════════════════════════════════════════════════════════════

    public function test_14_cascade_foto_no_aprobada_cancela_su_calendario(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(1);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);

        // Dos fotos: foto1 se colocará en el calendario, luego el cliente aprobará foto2
        $foto1 = $this->crearFoto($paquete, 1, FotoAprobacion::ESTATUS_APROBADA);
        $foto2 = $this->crearFoto($paquete, 2, FotoAprobacion::ESTATUS_APROBADA);
        $admin = $this->crearAdmin();

        // Colocar foto1 en el calendario
        $fechaDentro = Carbon::parse($paquete->fecha_inicio_periodo)->addDays(1)->toDateString();
        // La foto ya está aprobada pero el paquete está en borrador aún — simular colocación directa
        $entrada = CalendarioFoto::create([
            'cliente_id'                   => $cliente->id,
            'foto_aprobacion_id'           => $foto1->id,
            'fotografia_asociada'          => $foto1->ruta_foto,
            'fecha_publicacion_programada' => $fechaDentro,
            'estatus'                      => CalendarioFoto::ESTATUS_PROGRAMADA,
            'creado_por'                   => $admin->id,
        ]);

        // Ahora enviamos al cliente y el cliente elige foto2 (no foto1)
        $paquete->update([
            'estatus'      => PaqueteAprobacion::ESTATUS_PENDIENTE,
            'fecha_envio'  => now()->toDateString(),
            'fecha_limite' => now()->addDays(3)->toDateString(),
        ]);

        $this->actingAs($cliente->user)->post(
            route('cliente.aprobaciones.confirmar', $paquete),
            ['aprobadas' => [$foto2->id]]  // foto1 no queda aprobada
        )->assertRedirect();

        // Foto1 debe quedar descartada (no fue elegida ni conservada)
        $this->assertEquals(FotoAprobacion::ESTATUS_DESCARTADA, $foto1->fresh()->estatus);
        // La CalendarioFoto de foto1 debe quedar cancelada (cascade)
        $this->assertEquals(CalendarioFoto::ESTATUS_CANCELADA, $entrada->fresh()->estatus);
        // Foto2 debe quedar aprobada
        $this->assertEquals(FotoAprobacion::ESTATUS_APROBADA, $foto2->fresh()->estatus);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // publicar() no puede editar una entrada ya publicada
    // ══════════════════════════════════════════════════════════════════════════

    public function test_publicar_bloquea_edicion_posterior(): void
    {
        Storage::fake('public');
        Mail::fake();

        [$cliente, $renovacion] = $this->crearClienteActivo(2);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $foto    = $this->crearFoto($paquete, 1, FotoAprobacion::ESTATUS_APROBADA);
        $admin   = $this->crearAdmin();

        $fechaDentro = Carbon::today()->addDays(1)->toDateString();

        $this->colocarFoto($foto, $fechaDentro, $admin)->assertRedirect();

        $entrada = CalendarioFoto::where('foto_aprobacion_id', $foto->id)->first();
        $this->assertNotNull($entrada);

        // Publicar
        $this->actingAs($admin)
             ->post(route('calendario.publicar', $entrada))
             ->assertRedirect();

        $this->assertEquals(CalendarioFoto::ESTATUS_PUBLICADA, $entrada->fresh()->estatus);

        // Intentar eliminar una entrada publicada → rechazado
        $response = $this->actingAs($admin)
            ->delete(route('calendario.destroy', $entrada));

        $response->assertSessionHas('error');
        $this->assertEquals(CalendarioFoto::ESTATUS_PUBLICADA, $entrada->fresh()->estatus);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Sin tope por día: toda la cuota del período puede caer el mismo día si el
    // admin así lo decide (p. ej. ponerse al corriente al final de la vigencia).
    // ══════════════════════════════════════════════════════════════════════════

    public function test_sin_tope_diario_toda_la_cuota_puede_ir_el_mismo_dia(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(6);

        // Vigencia corta (3 días) — escenario típico de calendario armado tarde.
        $renovacion->update(['fecha_vencimiento' => Carbon::today()->addDays(2)]);
        $renovacion->refresh();

        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $admin   = $this->crearAdmin();

        $fotos = collect(range(1, 6))->map(
            fn ($i) => $this->crearFoto($paquete, $i, FotoAprobacion::ESTATUS_APROBADA)
        );

        $hoy = Carbon::today()->toDateString();

        // Las 6 fotos de la cuota caben todas el mismo día.
        foreach ($fotos as $foto) {
            $this->colocarFoto($foto, $hoy, $admin)->assertRedirect();
        }

        $this->assertEquals(6,
            CalendarioFoto::whereDate('fecha_publicacion_programada', $hoy)->count(),
            'Sin tope diario, toda la cuota del período puede agendarse el mismo día.'
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Rezago (fotos "vencidas"): un paquete cuyo período ya pasó sin agendarse
    // se puede colocar sin tope por día y aunque la renovación no esté vigente.
    // ══════════════════════════════════════════════════════════════════════════

    public function test_fotos_vencidas_se_pueden_agendar_sin_tope_y_sin_renovacion_vigente(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(3);

        // Paquete de un ciclo ya cerrado y vencido hace tiempo.
        $paquete = PaqueteAprobacion::create([
            'cliente_id'                => $cliente->id,
            'mes_revision'              => Carbon::today()->subMonths(2)->format('Y-m'),
            'cantidad_requerida'        => 3,
            'estatus'                   => PaqueteAprobacion::ESTATUS_AUTO_APROBADO,
            'fecha_inicio_periodo'      => Carbon::today()->subDays(40),
            'fecha_vencimiento_periodo' => Carbon::today()->subDays(11),
        ]);

        $fotos = collect(range(1, 3))->map(
            fn ($i) => $this->crearFoto($paquete, null, FotoAprobacion::ESTATUS_APROBADA)
        );

        // La renovación del cliente ya venció y no se ha renovado — no vigente ni por_vencer.
        $renovacion->update(['estatus' => ControlRenovacion::ESTATUS_VENCIDO]);

        $admin = $this->crearAdmin();
        $hoy   = Carbon::today()->toDateString();

        // Las 3 fotos vencidas se colocan el mismo día, sin tope, pese a que la
        // renovación no está vigente ni por_vencer.
        $this->colocarFoto($fotos[0], $hoy, $admin)->assertRedirect();
        $this->colocarFoto($fotos[1], $hoy, $admin)->assertRedirect();
        $this->colocarFoto($fotos[2], $hoy, $admin)->assertRedirect();

        $this->assertEquals(3,
            CalendarioFoto::whereDate('fecha_publicacion_programada', $hoy)->count(),
            'Las fotos vencidas deben poder agendarse todas el mismo día, sin tope dinámico.'
        );

        // Pero una fecha anterior a hoy sigue rechazada incluso para fotos vencidas.
        $fotoExtra = $this->crearFoto($paquete, null, FotoAprobacion::ESTATUS_APROBADA);
        $response  = $this->colocarFoto($fotoExtra, Carbon::yesterday()->toDateString(), $admin);
        $response->assertSessionHas('error');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // colocar() acepta un lote de fotos (foto_ids[]) en un solo envío.
    // ══════════════════════════════════════════════════════════════════════════

    public function test_colocar_acepta_varias_fotos_a_la_vez(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(4);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $fotos   = collect(range(1, 3))->map(
            fn ($i) => $this->crearFoto($paquete, $i, FotoAprobacion::ESTATUS_APROBADA)
        );
        $admin = $this->crearAdmin();
        $hoy   = Carbon::today()->toDateString();

        $response = $this->actingAs($admin)->post(route('calendario.colocar'), [
            'foto_ids' => $fotos->pluck('id')->all(),
            'fecha'    => $hoy,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(3,
            CalendarioFoto::whereDate('fecha_publicacion_programada', $hoy)->count(),
            'Las 3 fotos del lote deben quedar programadas en un solo envío.'
        );
    }

    public function test_colocar_en_lote_reporta_fallas_parciales_sin_bloquear_al_resto(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(4);
        $paquete   = $this->crearPaqueteBorrador($cliente, $renovacion);
        $fotoOk    = $this->crearFoto($paquete, 1, FotoAprobacion::ESTATUS_APROBADA);
        $fotoMala  = $this->crearFoto($paquete, 2, FotoAprobacion::ESTATUS_PENDIENTE); // no aprobada
        $admin     = $this->crearAdmin();
        $hoy       = Carbon::today()->toDateString();

        $response = $this->actingAs($admin)->post(route('calendario.colocar'), [
            'foto_ids' => [$fotoOk->id, $fotoMala->id],
            'fecha'    => $hoy,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('calendario_fotos', ['foto_aprobacion_id' => $fotoOk->id]);
        $this->assertDatabaseMissing('calendario_fotos', ['foto_aprobacion_id' => $fotoMala->id]);
    }
}
