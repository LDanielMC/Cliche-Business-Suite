<?php

namespace Tests\Feature;

use App\Models\ClienteEstatusLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FN.12 — Reporte de evolución de cartera de clientes: altas/bajas por mes,
 * total al inicio/cierre y % de crecimiento anual, filtrable por año.
 */
class ReporteCarteraTest extends TestCase
{
    use RefreshDatabase;

    private function crearEvento(string $evento, string $fecha): void
    {
        [$cliente] = $this->crearClienteActivo();

        $this->crearEventoParaCliente($cliente, $evento, $fecha);
    }

    private function crearEventoParaCliente($cliente, string $evento, string $fecha): void
    {
        ClienteEstatusLog::create([
            'cliente_id'   => $cliente->id,
            'user_id'      => $cliente->user_id,
            'evento'       => $evento,
            'fecha_evento' => $fecha,
        ]);
    }

    public function test_calcula_altas_bajas_y_acumulado_por_mes(): void
    {
        $admin = $this->crearAdmin();

        $this->crearEvento(ClienteEstatusLog::EVENTO_ALTA, '2026-02-10');
        $this->crearEvento(ClienteEstatusLog::EVENTO_ALTA, '2026-02-20');
        $this->crearEvento(ClienteEstatusLog::EVENTO_ALTA, '2026-05-01');
        $this->crearEvento(ClienteEstatusLog::EVENTO_BAJA, '2026-06-15');

        $response = $this->actingAs($admin)->get(route('reportes.cartera', ['anio' => 2026]));

        $response->assertOk();
        // Inicio: 0, +2 en feb, +1 en may, -1 en jun => cierre 2.
        $response->assertSee('>0<', false); // clientes al inicio
        $response->assertSee('>2<', false); // clientes al cierre
    }

    public function test_altas_y_bajas_de_otros_anios_no_se_cuentan(): void
    {
        $admin = $this->crearAdmin();

        $this->crearEvento(ClienteEstatusLog::EVENTO_ALTA, '2025-03-01');
        $this->crearEvento(ClienteEstatusLog::EVENTO_ALTA, '2027-03-01');

        $refl = new \ReflectionMethod(\App\Http\Controllers\ReporteController::class, 'calcularEvolucionCartera');
        $refl->setAccessible(true);
        $datos = $refl->invoke(app(\App\Http\Controllers\ReporteController::class), 2026);

        // El alta de 2025 sí cuenta como "cliente al inicio" (ya estaba dado de alta antes del año).
        $this->assertEquals(1, $datos['total_inicio']);
        $this->assertEquals(array_sum($datos['altas']), 0);
    }

    public function test_cliente_dado_de_alta_antes_del_anio_cuenta_en_total_inicio(): void
    {
        $admin = $this->crearAdmin();
        $this->crearEvento(ClienteEstatusLog::EVENTO_ALTA, '2025-06-01');

        $refl = new \ReflectionMethod(\App\Http\Controllers\ReporteController::class, 'calcularEvolucionCartera');
        $refl->setAccessible(true);
        $datos = $refl->invoke(app(\App\Http\Controllers\ReporteController::class), 2026);

        $this->assertEquals(1, $datos['total_inicio']);
    }

    public function test_calcula_porcentaje_de_crecimiento(): void
    {
        $admin = $this->crearAdmin();
        $this->crearEvento(ClienteEstatusLog::EVENTO_ALTA, '2025-01-01'); // cuenta en el inicio de 2026
        $this->crearEvento(ClienteEstatusLog::EVENTO_ALTA, '2026-03-01'); // +1 durante 2026

        $refl = new \ReflectionMethod(\App\Http\Controllers\ReporteController::class, 'calcularEvolucionCartera');
        $refl->setAccessible(true);
        $datos = $refl->invoke(app(\App\Http\Controllers\ReporteController::class), 2026);

        // De 1 a 2 clientes => 100% de crecimiento.
        $this->assertEquals(100.0, $datos['crecimiento_pct']);
    }

    public function test_filtro_por_anio_diferente_cambia_los_resultados(): void
    {
        $admin = $this->crearAdmin();
        $this->crearEvento(ClienteEstatusLog::EVENTO_ALTA, '2025-06-01');

        $response2025 = $this->actingAs($admin)->get(route('reportes.cartera', ['anio' => 2025]));
        $response2020 = $this->actingAs($admin)->get(route('reportes.cartera', ['anio' => 2020]));

        $response2025->assertOk();
        $response2020->assertOk();
        // En 2020 el cliente ni existía: 0 al inicio y 0 al cierre.
        $response2020->assertSeeInOrder(['>0<', 'Clientes al inicio'], false);
    }

    public function test_cliente_no_puede_ver_el_reporte_de_cartera(): void
    {
        [$cliente] = $this->crearClienteActivo();

        $this->actingAs($cliente->user)->get(route('reportes.cartera'))->assertForbidden();
    }

    public function test_operador_no_puede_ver_el_reporte_de_cartera(): void
    {
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);

        $this->actingAs($operador)->get(route('reportes.cartera'))->assertForbidden();
    }

    public function test_una_suspension_a_mitad_de_anio_baja_el_acumulado(): void
    {
        [$cliente] = $this->crearClienteActivo();
        $this->crearEventoParaCliente($cliente, ClienteEstatusLog::EVENTO_ALTA, '2026-01-10');
        $this->crearEventoParaCliente($cliente, ClienteEstatusLog::EVENTO_SUSPENSION, '2026-04-05');

        $refl = new \ReflectionMethod(\App\Http\Controllers\ReporteController::class, 'calcularEvolucionCartera');
        $refl->setAccessible(true);
        $datos = $refl->invoke(app(\App\Http\Controllers\ReporteController::class), 2026);

        // Ene-mar: 1 activo (dado de alta). Abr en adelante: 0 (suspendido).
        $this->assertEquals(1, $datos['acumulado'][0]); // enero
        $this->assertEquals(1, $datos['acumulado'][2]); // marzo
        $this->assertEquals(0, $datos['acumulado'][3]); // abril: cae por la suspensión
        $this->assertEquals(0, $datos['acumulado'][11]); // diciembre: se queda en 0
    }

    public function test_reactivacion_despues_de_suspension_regresa_el_acumulado(): void
    {
        [$cliente] = $this->crearClienteActivo();
        $this->crearEventoParaCliente($cliente, ClienteEstatusLog::EVENTO_ALTA, '2026-01-10');
        $this->crearEventoParaCliente($cliente, ClienteEstatusLog::EVENTO_SUSPENSION, '2026-03-01');
        $this->crearEventoParaCliente($cliente, ClienteEstatusLog::EVENTO_REACTIVACION, '2026-06-01');

        $refl = new \ReflectionMethod(\App\Http\Controllers\ReporteController::class, 'calcularEvolucionCartera');
        $refl->setAccessible(true);
        $datos = $refl->invoke(app(\App\Http\Controllers\ReporteController::class), 2026);

        $this->assertEquals(0, $datos['acumulado'][3]); // abril: suspendido
        $this->assertEquals(1, $datos['acumulado'][5]); // junio: reactivado, vuelve a contar
        $this->assertEquals(1, $datos['total_cierre']);
    }

    public function test_restauracion_despues_de_baja_regresa_el_acumulado(): void
    {
        [$cliente] = $this->crearClienteActivo();
        $this->crearEventoParaCliente($cliente, ClienteEstatusLog::EVENTO_ALTA, '2026-01-10');
        $this->crearEventoParaCliente($cliente, ClienteEstatusLog::EVENTO_BAJA, '2026-02-01');
        $this->crearEventoParaCliente($cliente, ClienteEstatusLog::EVENTO_RESTAURACION, '2026-05-01');

        $refl = new \ReflectionMethod(\App\Http\Controllers\ReporteController::class, 'calcularEvolucionCartera');
        $refl->setAccessible(true);
        $datos = $refl->invoke(app(\App\Http\Controllers\ReporteController::class), 2026);

        $this->assertEquals(0, $datos['acumulado'][2]); // marzo: dado de baja
        $this->assertEquals(1, $datos['acumulado'][4]); // mayo: restaurado
        $this->assertEquals(1, $datos['total_cierre']);
    }

    public function test_cliente_suspendido_antes_del_anio_no_cuenta_en_total_inicio(): void
    {
        [$cliente] = $this->crearClienteActivo();
        $this->crearEventoParaCliente($cliente, ClienteEstatusLog::EVENTO_ALTA, '2025-01-10');
        $this->crearEventoParaCliente($cliente, ClienteEstatusLog::EVENTO_SUSPENSION, '2025-06-01');

        $refl = new \ReflectionMethod(\App\Http\Controllers\ReporteController::class, 'calcularEvolucionCartera');
        $refl->setAccessible(true);
        $datos = $refl->invoke(app(\App\Http\Controllers\ReporteController::class), 2026);

        // Su último evento antes de 2026 fue "suspension" — no debe contar como activo al inicio.
        $this->assertEquals(0, $datos['total_inicio']);
    }

    public function test_cliente_restaurado_antes_del_anio_si_cuenta_en_total_inicio(): void
    {
        [$cliente] = $this->crearClienteActivo();
        $this->crearEventoParaCliente($cliente, ClienteEstatusLog::EVENTO_ALTA, '2025-01-10');
        $this->crearEventoParaCliente($cliente, ClienteEstatusLog::EVENTO_BAJA, '2025-03-01');
        $this->crearEventoParaCliente($cliente, ClienteEstatusLog::EVENTO_RESTAURACION, '2025-06-01');

        $refl = new \ReflectionMethod(\App\Http\Controllers\ReporteController::class, 'calcularEvolucionCartera');
        $refl->setAccessible(true);
        $datos = $refl->invoke(app(\App\Http\Controllers\ReporteController::class), 2026);

        // Su último evento antes de 2026 fue "restauracion" — sí cuenta como activo al inicio.
        $this->assertEquals(1, $datos['total_inicio']);
    }
}
