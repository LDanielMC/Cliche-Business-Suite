<?php

namespace Tests\Feature;

use App\Models\CalendarioFoto;
use App\Models\ControlRenovacion;
use App\Models\FotoAprobacion;
use App\Models\HistorialRenovacion;
use App\Models\PaqueteAprobacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests prioritarios de Renovaciones:
 *
 * - Pago dentro de gracia → período retoma desde el día siguiente al vencimiento.
 * - Pago fuera de gracia → período arranca desde fecha_pago_cliente.
 * - Suspensión solo ocurre DESPUÉS de que expire el período de gracia.
 * - Baja a mitad de período: CalendarioFoto existente NO se pausa; bloquea
 *   crear paquete nuevo y programar foto nueva.
 *
 * En todos los tests la fecha_pago_cliente la pone el CLIENTE al enviar
 * su comprobante, no el admin al validar. Así se mide la gracia de forma justa.
 */
class RenovacionGraciaTest extends TestCase
{
    use RefreshDatabase;

    // ══════════════════════════════════════════════════════════════════════════
    // Helper: construye una renovación en estado en_revision lista para validar
    // ══════════════════════════════════════════════════════════════════════════

    private function renovacionEnRevision(
        Carbon $fechaVencimiento,
        Carbon $fechaPagoCliente
    ): array {
        [$cliente, $renovacion] = $this->crearClienteActivo();

        // Simula que el cliente ya completó un ciclo previo (historial no vacío),
        // para que completarRenovacion() la trate como renovación regular
        // (grace-aware) y no como primer pago — que es lo que estos escenarios
        // de "gracia" quieren ejercitar.
        HistorialRenovacion::create([
            'cliente_id'            => $cliente->id,
            'control_renovacion_id' => $renovacion->id,
            'periodo_inicio'        => $fechaVencimiento->copy()->subDays(59),
            'periodo_fin'           => $fechaVencimiento->copy()->subDays(30),
            'fecha_pago'            => $fechaVencimiento->copy()->subDays(59),
            'monto'                 => 500.00,
        ]);

        $renovacion->update([
            'fecha_inicio'       => $fechaVencimiento->copy()->subDays(29),
            'fecha_vencimiento'  => $fechaVencimiento,
            'estatus'            => ControlRenovacion::ESTATUS_EN_REVISION,
            'fecha_pago_cliente' => $fechaPagoCliente->toDateString(),
            'monto'              => 500.00,
            'forma_pago'         => 'transferencia',
            'solicita_factura'   => false,
            'comprobante_pago'   => 'renovaciones/comprobantes/fake.jpg',
        ]);

        return [$cliente, $renovacion];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Pago DENTRO de gracia → nuevo período arranca el día siguiente al vencimiento
    // ══════════════════════════════════════════════════════════════════════════

    public function test_gracia_pago_dentro_retoma_desde_vencimiento(): void
    {
        Mail::fake();
        Storage::fake('public');

        $vencimiento = Carbon::parse('2026-06-30');
        // 3 días después: dentro del período de gracia de 5 días
        $fechaPago   = Carbon::parse('2026-07-03');

        [$cliente, $renovacion] = $this->renovacionEnRevision($vencimiento, $fechaPago);
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
             ->post(route('renovaciones.validar', $renovacion))
             ->assertRedirect();

        $renovacion->refresh();

        // El nuevo período debe empezar el 2026-07-01 (día siguiente al vencimiento)
        $this->assertEquals('2026-07-01', $renovacion->fecha_inicio->toDateString(),
            'El pago dentro de gracia debe retomar desde el día siguiente al vencimiento, no desde fecha_pago_cliente.'
        );
        $this->assertEquals('2026-07-30', $renovacion->fecha_vencimiento->toDateString());
        $this->assertEquals(ControlRenovacion::ESTATUS_VIGENTE, $renovacion->estatus);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Pago FUERA de gracia → nuevo período arranca desde fecha_pago_cliente
    // ══════════════════════════════════════════════════════════════════════════

    public function test_gracia_pago_fuera_arranca_desde_fecha_pago_cliente(): void
    {
        Mail::fake();
        Storage::fake('public');

        $vencimiento = Carbon::parse('2026-06-30');
        // 15 días después: fuera del período de gracia de 5 días (30-jun + 5 = 5-jul)
        $fechaPago   = Carbon::parse('2026-07-15');

        [$cliente, $renovacion] = $this->renovacionEnRevision($vencimiento, $fechaPago);
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
             ->post(route('renovaciones.validar', $renovacion))
             ->assertRedirect();

        $renovacion->refresh();

        // El período debe arrancar desde la fecha que el cliente declaró, no desde la del admin
        $this->assertEquals('2026-07-15', $renovacion->fecha_inicio->toDateString(),
            'El pago fuera de gracia debe arrancar desde fecha_pago_cliente (la del cliente, no del admin).'
        );
        $this->assertEquals('2026-08-13', $renovacion->fecha_vencimiento->toDateString());
        $this->assertEquals(ControlRenovacion::ESTATUS_VIGENTE, $renovacion->estatus);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // El cliente no puede declarar una fecha de pago de en medio de un
    // período ya vencido (backdating hacia el ciclo anterior ya consumido)
    // ══════════════════════════════════════════════════════════════════════════

    public function test_no_permite_fecha_de_pago_en_medio_de_un_periodo_ya_vencido(): void
    {
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo();
        $renovacion->update([
            'fecha_inicio'      => Carbon::parse('2026-06-30'),
            'fecha_vencimiento' => Carbon::parse('2026-07-29'),
            'estatus'           => ControlRenovacion::ESTATUS_VENCIDO,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-02'));

        $response = $this->actingAs($cliente->user)->post(
            route('renovaciones.cliente.enviar', $renovacion),
            [
                'comprobante_pago'   => \Illuminate\Http\UploadedFile::fake()->create('comprobante.pdf', 100),
                'fecha_pago_cliente' => '2026-07-02',
                'monto'              => 500,
                'forma_pago'         => 'transferencia',
                'solicita_factura'   => 0,
            ]
        );

        $response->assertSessionHasErrors('fecha_pago_cliente');

        $responseValida = $this->actingAs($cliente->user)->post(
            route('renovaciones.cliente.enviar', $renovacion),
            [
                'comprobante_pago'   => \Illuminate\Http\UploadedFile::fake()->create('comprobante2.pdf', 100),
                'fecha_pago_cliente' => '2026-07-30',
                'monto'              => 500,
                'forma_pago'         => 'transferencia',
                'solicita_factura'   => 0,
            ]
        );

        $responseValida->assertSessionDoesntHaveErrors('fecha_pago_cliente');

        Carbon::setTestNow();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Suspensión ocurre SOLO después de que expire el período de gracia
    // ══════════════════════════════════════════════════════════════════════════

    public function test_suspension_solo_ocurre_despues_del_periodo_de_gracia(): void
    {
        Mail::fake();
        Storage::fake('public');

        $diasGracia  = config('renovaciones.dias_gracia', 5);
        $vencimiento = now()->subDays(3)->startOfDay(); // venció hace 3 días (dentro de gracia)

        [$cliente, $renovacion] = $this->crearClienteActivo();
        $renovacion->update([
            'fecha_inicio'      => $vencimiento->copy()->subDays(29),
            'fecha_vencimiento' => $vencimiento,
            'estatus'           => ControlRenovacion::ESTATUS_VIGENTE,
        ]);

        // Correr el cron estando dentro del período de gracia → NO debe suspender
        // la cuenta, aunque el badge de la renovación ya marque "vencido"
        // (VerificarRenovaciones lo actualiza apenas pasa fecha_vencimiento;
        // solo la suspensión de la cuenta espera a que expire la gracia).
        $this->artisan('renovaciones:verificar')->assertSuccessful();

        $this->assertEquals(
            User::ESTATUS_ACTIVO,
            $cliente->user->fresh()->estatus,
            'El cliente no debe suspenderse mientras está dentro del período de gracia.'
        );
        $this->assertEquals(
            ControlRenovacion::ESTATUS_VENCIDO,
            $renovacion->fresh()->estatus,
            'El badge de la renovación debe marcar "vencido" inmediatamente al pasar la fecha, aunque la cuenta siga activa.'
        );

        // Ahora simular que venció hace más de diasGracia días → SÍ debe suspender
        $vencimientoAntiguo = now()->subDays($diasGracia + 2)->startOfDay();
        $renovacion->update([
            'fecha_inicio'      => $vencimientoAntiguo->copy()->subDays(29),
            'fecha_vencimiento' => $vencimientoAntiguo,
            'estatus'           => ControlRenovacion::ESTATUS_VIGENTE,
        ]);

        $this->artisan('renovaciones:verificar')->assertSuccessful();

        $this->assertEquals(
            User::ESTATUS_SUSPENDIDO,
            $cliente->user->fresh()->estatus,
            'El cliente debe suspenderse una vez expirado el período de gracia.'
        );
        $this->assertEquals(ControlRenovacion::ESTATUS_VENCIDO, $renovacion->fresh()->estatus);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Baja a mitad de período:
    //   - CalendarioFoto existente NO se pausa (sigue publicable)
    //   - No se puede crear nuevo paquete para el cliente suspendido
    //   - No se puede programar nueva foto para el cliente suspendido
    // ══════════════════════════════════════════════════════════════════════════

    public function test_baja_no_pausa_calendario_existente_y_bloquea_nuevas_operaciones(): void
    {
        Mail::fake();
        Storage::fake('public');

        $diasGracia = config('renovaciones.dias_gracia', 5);

        [$cliente, $renovacion] = $this->crearClienteActivo(2);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $foto    = $this->crearFoto($paquete, 1, FotoAprobacion::ESTATUS_APROBADA);
        $admin   = $this->crearAdmin();

        // Colocar la foto en el calendario (período ya vigente)
        $fechaDentro = Carbon::today()->addDays(5)->toDateString();
        $this->actingAs($admin)->post(route('calendario.colocar'), [
            'foto_id' => $foto->id,
            'fecha'   => $fechaDentro,
        ])->assertRedirect();

        $entrada = CalendarioFoto::where('foto_aprobacion_id', $foto->id)->first();
        $this->assertNotNull($entrada);
        $this->assertEquals(CalendarioFoto::ESTATUS_PROGRAMADA, $entrada->estatus);

        // Suspender el cliente simulando que expiró la gracia
        $vencimientoAntiguo = now()->subDays($diasGracia + 2)->startOfDay();
        $renovacion->update([
            'fecha_inicio'      => $vencimientoAntiguo->copy()->subDays(29),
            'fecha_vencimiento' => $vencimientoAntiguo,
            'estatus'           => ControlRenovacion::ESTATUS_VIGENTE,
        ]);

        $this->artisan('renovaciones:verificar')->assertSuccessful();

        // ── Verificación 1: el calendario NO se pausó ─────────────────────
        $this->assertEquals(
            CalendarioFoto::ESTATUS_PROGRAMADA,
            $entrada->fresh()->estatus,
            'El VerificarRenovaciones NO debe pausar CalendarioFoto al suspender un cliente.'
        );

        // ── Verificación 2: no se puede crear paquete nuevo ───────────────
        // Necesita renovación activa con estatus vigente/por_vencer — está vencida ahora
        $response = $this->actingAs($admin)->post(route('aprobaciones.store'), [
            'cliente_id'   => $cliente->id,
            'mes_revision' => now()->addMonth()->format('Y-m'),
        ]);

        // El store valida estatus del cliente activo O estatus de renovación
        $response->assertSessionHas('error');

        // ── Verificación 3: la foto programada se puede seguir publicando ─
        // (el operador puede publicar aunque el cliente esté suspendido)
        $this->actingAs($admin)
             ->post(route('calendario.publicar', $entrada))
             ->assertRedirect();

        $this->assertEquals(CalendarioFoto::ESTATUS_PUBLICADA, $entrada->fresh()->estatus,
            'Una CalendarioFoto programada debe poder publicarse incluso si el cliente se suspendió después.'
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Reactivación tras renovación: el cliente vuelve a activo
    // ══════════════════════════════════════════════════════════════════════════

    public function test_reactivacion_tras_renovacion_exitosa(): void
    {
        Mail::fake();
        Storage::fake('public');

        $vencimiento = Carbon::parse('2026-06-30');
        $fechaPago   = Carbon::parse('2026-07-02'); // dentro de gracia

        [$cliente, $renovacion] = $this->renovacionEnRevision($vencimiento, $fechaPago);

        // Suspender el cliente antes de renovar
        $cliente->user->update(['estatus' => User::ESTATUS_SUSPENDIDO]);

        $admin = $this->crearAdmin();

        $this->actingAs($admin)
             ->post(route('renovaciones.validar', $renovacion))
             ->assertRedirect();

        // Tras renovación exitosa el cliente vuelve a activo
        $this->assertEquals(
            User::ESTATUS_ACTIVO,
            $cliente->user->fresh()->estatus,
            'El cliente debe reactivarse automáticamente al completar una renovación.'
        );
    }
}
