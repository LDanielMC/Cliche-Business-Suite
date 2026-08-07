<?php

namespace Tests\Feature;

use App\Models\FotoAprobacion;
use App\Models\PaqueteAprobacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Escenarios §9 10-11: AutoAprobarFotos (cron).
 *
 * Escenario 10: paquete vencido → auto_aprobado, selección por orden de subida.
 * Escenario 11: sin prioridad manual asignada → igual se auto-aprueba (ya no es
 * un dato requerido, el orden de subida basta).
 */
class AutoAprobacionTest extends TestCase
{
    use RefreshDatabase;

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 10 — auto-aprobación por orden de subida, sobrantes → conservada
    // ══════════════════════════════════════════════════════════════════════════

    public function test_10_auto_aprobacion_paquete_vencido_por_orden_de_subida(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(2); // requiere 2
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);

        $foto1 = $this->crearFoto($paquete); // subida primero → aprobada
        $foto2 = $this->crearFoto($paquete); // subida segundo → aprobada
        $foto3 = $this->crearFoto($paquete); // subida tercero → sobrante, conservada

        // Mover el paquete a pendiente y simular que venció
        $paquete->update([
            'estatus'      => PaqueteAprobacion::ESTATUS_PENDIENTE,
            'fecha_envio'  => now()->subDays(5)->toDateString(),
            'fecha_limite' => now()->subDay()->toDateString(), // vencido ayer
        ]);

        $this->artisan('aprobaciones:auto-aprobar')->assertSuccessful();

        $this->assertEquals(FotoAprobacion::ESTATUS_APROBADA,  $foto1->fresh()->estatus);
        $this->assertEquals(FotoAprobacion::ESTATUS_APROBADA,  $foto2->fresh()->estatus);
        $this->assertEquals(FotoAprobacion::ESTATUS_CONSERVADA, $foto3->fresh()->estatus);
        $this->assertEquals(PaqueteAprobacion::ESTATUS_AUTO_APROBADO, $paquete->fresh()->estatus);
        $this->assertEquals(
            PaqueteAprobacion::MOTIVO_APROBADO_AUTOMATICO,
            $paquete->fresh()->motivo_finalizacion
        );

        // El sobrante recibe fechas de reserva
        $this->assertNotNull($foto3->fresh()->fecha_ingreso_reserva);
        $this->assertNotNull($foto3->fresh()->fecha_expiracion_reserva);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario 11 — sin prioridad asignada (el caso normal ahora) → se
    // auto-aprueba igual, tomando las primeras fotos subidas.
    // ══════════════════════════════════════════════════════════════════════════

    public function test_11_auto_aprobacion_sin_prioridad_asignada_usa_orden_de_subida(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(1); // requiere 1
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);

        $foto1 = $this->crearFoto($paquete); // subida primero, sin prioridad → aprobada
        $foto2 = $this->crearFoto($paquete); // subida segundo, sin prioridad → sobrante

        $paquete->update([
            'estatus'      => PaqueteAprobacion::ESTATUS_PENDIENTE,
            'fecha_envio'  => now()->subDays(5)->toDateString(),
            'fecha_limite' => now()->subDay()->toDateString(),
        ]);

        $this->artisan('aprobaciones:auto-aprobar')->assertSuccessful();

        $this->assertEquals(PaqueteAprobacion::ESTATUS_AUTO_APROBADO, $paquete->fresh()->estatus);
        $this->assertEquals(FotoAprobacion::ESTATUS_APROBADA,   $foto1->fresh()->estatus);
        $this->assertEquals(FotoAprobacion::ESTATUS_CONSERVADA, $foto2->fresh()->estatus);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Escenario extra — paquete no vencido no es tocado por el cron
    // ══════════════════════════════════════════════════════════════════════════

    public function test_cron_no_toca_paquetes_no_vencidos(): void
    {
        Mail::fake();
        Storage::fake('public');

        [$cliente, $renovacion] = $this->crearClienteActivo(2);
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);

        $this->crearFoto($paquete, 1);
        $this->crearFoto($paquete, 2);

        $paquete->update([
            'estatus'      => PaqueteAprobacion::ESTATUS_PENDIENTE,
            'fecha_envio'  => now()->toDateString(),
            'fecha_limite' => now()->addDays(2)->toDateString(), // aún vigente
        ]);

        $this->artisan('aprobaciones:auto-aprobar')->assertSuccessful();

        $this->assertEquals(PaqueteAprobacion::ESTATUS_PENDIENTE, $paquete->fresh()->estatus);
    }
}
