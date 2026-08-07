<?php

namespace Tests\Feature;

use App\Models\CategoriaGasto;
use App\Models\GastoOperativo;
use App\Models\PagoCliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * FN.10 — Reporte financiero general: ingresos vs gastos, con filtro por
 * rango de fechas, mes o año, y exportación a PDF.
 */
class ReporteFinancieroTest extends TestCase
{
    use RefreshDatabase;

    private function crearPago(string $fecha, float $monto): void
    {
        [$cliente] = $this->crearClienteActivo();

        PagoCliente::create([
            'cliente_id'  => $cliente->id,
            'concepto_servicio' => 'Servicio mensual',
            'monto'       => $monto,
            'fecha_pago'  => $fecha,
            'forma_pago'  => 'transferencia',
            'estatus'     => PagoCliente::ESTATUS_PAGADO,
        ]);
    }

    private function crearGasto(string $fecha, float $monto): void
    {
        $admin = $this->crearAdmin();
        $categoria = CategoriaGasto::firstOrCreate(['nombre' => 'General']);

        GastoOperativo::create([
            'concepto_gasto' => 'Gasto de prueba',
            'categoria_gasto_id' => $categoria->id,
            'monto' => $monto,
            'fecha_gasto' => $fecha,
            'forma_pago' => 'efectivo',
            'registrado_por' => $admin->id,
        ]);
    }

    public function test_calcula_ingresos_gastos_y_utilidad_del_mes_seleccionado(): void
    {
        $admin = $this->crearAdmin();
        $this->crearPago('2026-07-10', 1000);
        $this->crearPago('2026-07-20', 500);
        $this->crearGasto('2026-07-15', 300);
        // Fuera del periodo filtrado — no debe contarse.
        $this->crearPago('2026-06-15', 9999);

        $response = $this->actingAs($admin)->get(route('reportes.financiero', ['mes' => '2026-07']));

        $response->assertOk();
        $response->assertSee('1,500.00', false); // total ingresos
        $response->assertSee('300.00', false);   // total gastos
        $response->assertSee('1,200.00', false); // utilidad
        $response->assertDontSee('9,999.00', false);
    }

    public function test_filtro_por_anio_cubre_todo_el_anio(): void
    {
        $admin = $this->crearAdmin();
        $this->crearPago('2026-01-10', 100);
        $this->crearPago('2026-12-20', 200);
        $this->crearPago('2025-12-31', 9999); // año anterior, no debe contarse
        $this->crearPago('2027-01-01', 9999); // año siguiente, no debe contarse

        $response = $this->actingAs($admin)->get(route('reportes.financiero', ['anio' => '2026']));

        $response->assertOk();
        $response->assertSee('300.00', false);
        $response->assertDontSee('9,999.00', false);
    }

    public function test_filtro_por_rango_de_fechas_personalizado(): void
    {
        $admin = $this->crearAdmin();
        $this->crearPago('2026-03-05', 700);
        $this->crearPago('2026-03-25', 9999); // fuera del rango

        $response = $this->actingAs($admin)->get(route('reportes.financiero', [
            'desde' => '2026-03-01',
            'hasta' => '2026-03-10',
        ]));

        $response->assertOk();
        $response->assertSee('700.00', false);
        $response->assertDontSee('9,999.00', false);
    }

    public function test_anio_tiene_precedencia_sobre_mes_y_rango(): void
    {
        $admin = $this->crearAdmin();
        $this->crearPago('2026-05-15', 111);

        $response = $this->actingAs($admin)->get(route('reportes.financiero', [
            'anio' => '2026',
            'mes'  => '2026-01', // se ignora porque anio gana
            'desde' => '2020-01-01',
            'hasta' => '2020-01-02',
        ]));

        $response->assertOk();
        $response->assertSee('111.00', false);
    }

    public function test_sin_filtros_usa_el_mes_actual_por_defecto(): void
    {
        $admin = $this->crearAdmin();
        Carbon::setTestNow(Carbon::parse('2026-08-15'));
        $this->crearPago('2026-08-05', 250);

        $response = $this->actingAs($admin)->get(route('reportes.financiero'));

        $response->assertOk();
        $response->assertSee('250.00', false);

        Carbon::setTestNow();
    }

    public function test_exportar_pdf_devuelve_un_pdf_descargable(): void
    {
        $admin = $this->crearAdmin();
        $this->crearPago('2026-07-10', 500);

        $response = $this->actingAs($admin)->post(route('reportes.financiero.pdf'), [
            'mes' => '2026-07',
            'chart_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_cliente_no_puede_ver_el_reporte_financiero(): void
    {
        [$cliente] = $this->crearClienteActivo();

        $this->actingAs($cliente->user)->get(route('reportes.financiero'))->assertForbidden();
    }

    public function test_operador_no_puede_ver_el_reporte_financiero(): void
    {
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);

        $this->actingAs($operador)->get(route('reportes.financiero'))->assertForbidden();
    }
}
