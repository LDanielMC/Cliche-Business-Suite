<?php

namespace Tests\Feature;

use App\Models\CategoriaGasto;
use App\Models\ClienteEstatusLog;
use App\Models\GastoOperativo;
use App\Models\PagoCliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FN.11 — Reporte de clientes más rentables: ingresos vs gastos directos +
 * prorrateo de gastos generales, filtrable por periodo o por cliente
 * específico, exportable a PNG.
 */
class ReporteRentabilidadTest extends TestCase
{
    use RefreshDatabase;

    private function crearClienteConAlta(string $fechaAlta = '2026-01-01'): array
    {
        [$cliente, $renovacion] = $this->crearClienteActivo();

        ClienteEstatusLog::create([
            'cliente_id'    => $cliente->id,
            'user_id'       => $cliente->user_id,
            'evento'        => ClienteEstatusLog::EVENTO_ALTA,
            'fecha_evento'  => $fechaAlta,
        ]);

        return [$cliente, $renovacion];
    }

    private function crearPago($cliente, string $fecha, float $monto): void
    {
        PagoCliente::create([
            'cliente_id' => $cliente->id,
            'concepto_servicio' => 'Servicio mensual',
            'monto' => $monto,
            'fecha_pago' => $fecha,
            'forma_pago' => 'transferencia',
            'estatus' => PagoCliente::ESTATUS_PAGADO,
        ]);
    }

    private function crearGastoDirecto($cliente, string $fecha, float $monto): void
    {
        $admin = $this->crearAdmin();
        $categoria = CategoriaGasto::firstOrCreate(['nombre' => 'Directo']);

        GastoOperativo::create([
            'concepto_gasto' => 'Gasto directo', 'categoria_gasto_id' => $categoria->id,
            'cliente_id' => $cliente->id, 'monto' => $monto, 'fecha_gasto' => $fecha,
            'forma_pago' => 'efectivo', 'registrado_por' => $admin->id,
        ]);
    }

    private function crearGastoGeneral(string $fecha, float $monto): void
    {
        $admin = $this->crearAdmin();
        $categoria = CategoriaGasto::firstOrCreate(['nombre' => 'General']);

        GastoOperativo::create([
            'concepto_gasto' => 'Gasto general', 'categoria_gasto_id' => $categoria->id,
            'cliente_id' => null, 'monto' => $monto, 'fecha_gasto' => $fecha,
            'forma_pago' => 'efectivo', 'registrado_por' => $admin->id,
        ]);
    }

    public function test_calcula_ingresos_gastos_directos_y_prorrateo_por_cliente(): void
    {
        $admin = $this->crearAdmin();
        [$clienteA] = $this->crearClienteConAlta('2026-01-01');
        [$clienteB] = $this->crearClienteConAlta('2026-01-01');

        $this->crearPago($clienteA, '2026-03-10', 1000);
        $this->crearGastoDirecto($clienteA, '2026-03-05', 100);

        $this->crearPago($clienteB, '2026-03-15', 2000);

        // Gasto general de $400 se reparte entre los 2 clientes activos: $200 c/u.
        $this->crearGastoGeneral('2026-03-01', 400);

        $response = $this->actingAs($admin)->get(route('reportes.rentabilidad', [
            'desde' => '2026-03-01', 'hasta' => '2026-03-31',
        ]));

        $response->assertOk();
        // Cliente A: 1000 - 100 - 200 = 700
        $response->assertSee('700.00', false);
        // Cliente B: 2000 - 0 - 200 = 1800
        $response->assertSee('1,800.00', false);
    }

    public function test_filtrar_por_cliente_especifico_no_altera_el_prorrateo(): void
    {
        $admin = $this->crearAdmin();
        [$clienteA] = $this->crearClienteConAlta('2026-01-01');
        [$clienteB] = $this->crearClienteConAlta('2026-01-01');

        $this->crearPago($clienteA, '2026-03-10', 1000);
        $this->crearPago($clienteB, '2026-03-15', 2000);
        $this->crearGastoGeneral('2026-03-01', 400); // $200 c/u entre 2 clientes activos

        $response = $this->actingAs($admin)->get(route('reportes.rentabilidad', [
            'desde' => '2026-03-01', 'hasta' => '2026-03-31',
            'cliente_id' => $clienteA->id,
        ]));

        $response->assertOk();
        // El nombre de B sigue en el <select> (para poder cambiar de cliente),
        // pero no debe tener fila propia en la tabla de resultados.
        $filas = substr_count($response->getContent(), 'font-medium">' . $clienteB->nombre_negocio);
        $this->assertSame(0, $filas);

        // El prorrateo de A sigue siendo 200 (entre 2 activos), no 400 (si fuera el único).
        $response->assertSee('800.00', false); // 1000 - 0 - 200
    }

    public function test_selector_de_cliente_incluye_clientes_sin_importar_estatus(): void
    {
        $admin = $this->crearAdmin();
        [$cliente] = $this->crearClienteConAlta('2026-01-01');
        $cliente->user->update(['estatus' => User::ESTATUS_SUSPENDIDO]);

        $response = $this->actingAs($admin)->get(route('reportes.rentabilidad'));

        $response->assertOk();
        $response->assertSee($cliente->nombre_negocio);
    }

    public function test_cliente_sin_actividad_en_el_periodo_muestra_mensaje_especifico(): void
    {
        $admin = $this->crearAdmin();
        [$clienteA] = $this->crearClienteConAlta('2026-06-01'); // se dio de alta después del periodo filtrado

        $response = $this->actingAs($admin)->get(route('reportes.rentabilidad', [
            'desde' => '2026-01-01', 'hasta' => '2026-01-31',
            'cliente_id' => $clienteA->id,
        ]));

        $response->assertOk();
        $response->assertSee('no estuvo activo durante el periodo');
    }

    public function test_cliente_no_puede_ver_el_reporte_de_rentabilidad(): void
    {
        [$cliente] = $this->crearClienteActivo();

        $this->actingAs($cliente->user)->get(route('reportes.rentabilidad'))->assertForbidden();
    }

    public function test_operador_no_puede_ver_el_reporte_de_rentabilidad(): void
    {
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);

        $this->actingAs($operador)->get(route('reportes.rentabilidad'))->assertForbidden();
    }
}
