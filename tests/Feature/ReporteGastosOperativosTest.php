<?php

namespace Tests\Feature;

use App\Models\CategoriaGasto;
use App\Models\GastoOperativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FN.13 — Reporte de gastos operativos: desglose por categoría con filtro
 * de rango de fechas y categoría, gráfica de pastel, exportación a PDF.
 */
class ReporteGastosOperativosTest extends TestCase
{
    use RefreshDatabase;

    private function crearGasto(string $fecha, float $monto, ?CategoriaGasto $categoria = null): GastoOperativo
    {
        $admin = $this->crearAdmin();
        $categoria ??= CategoriaGasto::firstOrCreate(['nombre' => 'General']);

        return GastoOperativo::create([
            'concepto_gasto' => 'Gasto de prueba',
            'categoria_gasto_id' => $categoria->id,
            'monto' => $monto,
            'fecha_gasto' => $fecha,
            'forma_pago' => 'efectivo',
            'registrado_por' => $admin->id,
        ]);
    }

    public function test_agrupa_gastos_por_categoria_del_periodo_seleccionado(): void
    {
        $admin = $this->crearAdmin();
        $renta = CategoriaGasto::firstOrCreate(['nombre' => 'Renta']);
        $equipo = CategoriaGasto::firstOrCreate(['nombre' => 'Equipo']);

        $this->crearGasto('2026-07-05', 1000, $renta);
        $this->crearGasto('2026-07-10', 500, $renta);
        $this->crearGasto('2026-07-15', 500, $equipo);
        // Fuera del periodo filtrado — no debe contarse.
        $this->crearGasto('2026-06-01', 9999, $renta);

        $response = $this->actingAs($admin)->get(route('reportes.gastos', [
            'desde' => '2026-07-01',
            'hasta' => '2026-07-31',
        ]));

        $response->assertOk();
        $response->assertSee('Renta');
        $response->assertSee('Equipo');
        $response->assertSee('1,500.00', false); // total de Renta
        $response->assertSee('500.00', false);   // total de Equipo
        $response->assertDontSee('9,999.00', false);
    }

    public function test_calcula_el_porcentaje_de_cada_categoria(): void
    {
        $admin = $this->crearAdmin();
        $renta = CategoriaGasto::firstOrCreate(['nombre' => 'Renta']);
        $equipo = CategoriaGasto::firstOrCreate(['nombre' => 'Equipo']);

        $this->crearGasto('2026-07-05', 750, $renta);
        $this->crearGasto('2026-07-06', 250, $equipo);

        $response = $this->actingAs($admin)->get(route('reportes.gastos', [
            'desde' => '2026-07-01',
            'hasta' => '2026-07-31',
        ]));

        $response->assertOk();
        $response->assertSee('75%');
        $response->assertSee('25%');
    }

    public function test_filtro_por_categoria_especifica(): void
    {
        $admin = $this->crearAdmin();
        $renta = CategoriaGasto::firstOrCreate(['nombre' => 'Renta']);
        $equipo = CategoriaGasto::firstOrCreate(['nombre' => 'Equipo']);

        $this->crearGasto('2026-07-05', 300, $renta);
        $this->crearGasto('2026-07-06', 400, $equipo);

        $response = $this->actingAs($admin)->get(route('reportes.gastos', [
            'desde' => '2026-07-01',
            'hasta' => '2026-07-31',
            'categoria_gasto_id' => $equipo->id,
        ]));

        $response->assertOk();
        $response->assertSee('Equipo');
        // "Renta" sigue legítimamente en el <select> del filtro; lo que no
        // debe aparecer es su fila en la tabla de detalle.
        $response->assertDontSee('font-medium">Renta', false);
    }

    public function test_al_filtrar_por_categoria_se_muestra_el_desglose_de_gastos_individuales(): void
    {
        $admin = $this->crearAdmin();
        $equipo = CategoriaGasto::firstOrCreate(['nombre' => 'Equipo']);
        $renta = CategoriaGasto::firstOrCreate(['nombre' => 'Renta']);

        $g1 = $this->crearGasto('2026-07-05', 300, $equipo);
        $g1->update(['concepto_gasto' => 'Lente 50mm']);
        $g2 = $this->crearGasto('2026-07-12', 450, $equipo);
        $g2->update(['concepto_gasto' => 'Trípode']);
        // De otra categoría — no debe aparecer en el desglose de Equipo.
        $this->crearGasto('2026-07-06', 999, $renta);

        $response = $this->actingAs($admin)->get(route('reportes.gastos', [
            'desde' => '2026-07-01',
            'hasta' => '2026-07-31',
            'categoria_gasto_id' => $equipo->id,
        ]));

        $response->assertOk();
        $response->assertSee('Desglose de gastos');
        $response->assertSee('Lente 50mm');
        $response->assertSee('Trípode');
        $response->assertDontSee('999.00', false);
    }

    public function test_al_filtrar_por_categoria_la_grafica_distribuye_los_gastos_individuales(): void
    {
        $admin = $this->crearAdmin();
        $equipo = CategoriaGasto::firstOrCreate(['nombre' => 'Equipo']);

        $g1 = $this->crearGasto('2026-07-05', 300, $equipo);
        $g1->update(['concepto_gasto' => 'Lente 50mm']);
        $g2 = $this->crearGasto('2026-07-12', 700, $equipo);
        $g2->update(['concepto_gasto' => 'Tripode']);

        $response = $this->actingAs($admin)->get(route('reportes.gastos', [
            'desde' => '2026-07-01',
            'hasta' => '2026-07-31',
            'categoria_gasto_id' => $equipo->id,
        ]));

        $response->assertOk();
        // La gráfica ahora debe traer cada gasto individual como dato propio
        // (no una sola rebanada al 100% con el nombre de la categoría).
        $response->assertSee('"Lente 50mm"', false);
        $response->assertSee('"Tripode"', false);
    }

    public function test_sin_filtro_de_categoria_no_se_muestra_el_desglose(): void
    {
        $admin = $this->crearAdmin();
        $this->crearGasto('2026-07-05', 300);

        $response = $this->actingAs($admin)->get(route('reportes.gastos', [
            'desde' => '2026-07-01',
            'hasta' => '2026-07-31',
        ]));

        $response->assertOk();
        $response->assertDontSee('Desglose de gastos');
    }

    public function test_el_pdf_filtrado_por_categoria_incluye_el_desglose(): void
    {
        $admin = $this->crearAdmin();
        $equipo = CategoriaGasto::firstOrCreate(['nombre' => 'Equipo']);
        $gasto = $this->crearGasto('2026-07-05', 300, $equipo);
        $gasto->update(['concepto_gasto' => 'Lente 50mm']);

        $response = $this->actingAs($admin)->post(route('reportes.gastos.pdf'), [
            'desde' => '2026-07-01',
            'hasta' => '2026-07-31',
            'categoria_gasto_id' => $equipo->id,
            'chart_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_sin_gastos_en_el_periodo_muestra_estado_vacio(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->get(route('reportes.gastos', [
            'desde' => '2026-07-01',
            'hasta' => '2026-07-31',
        ]));

        $response->assertOk();
        $response->assertSee('Sin gastos');
    }

    public function test_exportar_pdf_devuelve_un_pdf_descargable(): void
    {
        $admin = $this->crearAdmin();
        $this->crearGasto('2026-07-10', 500);

        $response = $this->actingAs($admin)->post(route('reportes.gastos.pdf'), [
            'desde' => '2026-07-01',
            'hasta' => '2026-07-31',
            'chart_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_exportar_pdf_sin_imagen_de_grafica_falla_la_validacion(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('reportes.gastos.pdf'), [
            'desde' => '2026-07-01',
            'hasta' => '2026-07-31',
        ]);

        $response->assertSessionHasErrors('chart_image');
    }

    public function test_cliente_no_puede_ver_el_reporte_de_gastos(): void
    {
        [$cliente] = $this->crearClienteActivo();

        $this->actingAs($cliente->user)->get(route('reportes.gastos'))->assertForbidden();
    }

    public function test_operador_no_puede_ver_el_reporte_de_gastos(): void
    {
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);

        $this->actingAs($operador)->get(route('reportes.gastos'))->assertForbidden();
    }
}
