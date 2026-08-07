<?php

namespace Tests\Feature;

use App\Models\CategoriaGasto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El formulario de categorías de gastos usaba el campo "nombre_categoria"
 * mientras el controlador/modelo/BD usan "nombre" — esto rompía crear y
 * editar categorías (validación siempre fallaba) y dejaba el nombre en
 * blanco en el listado.
 */
class CategoriaGastoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_crear_una_categoria_de_gasto(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('categorias-gastos.store'), [
            'nombre' => 'Mantenimiento de Equipo',
        ]);

        $response->assertRedirect(route('categorias-gastos.index'));
        $this->assertDatabaseHas('categorias_gastos', ['nombre' => 'Mantenimiento de Equipo']);
    }

    public function test_admin_puede_editar_una_categoria_de_gasto(): void
    {
        $admin = $this->crearAdmin();
        $categoria = CategoriaGasto::create(['nombre' => 'Original']);

        $response = $this->actingAs($admin)->put(route('categorias-gastos.update', $categoria), [
            'nombre' => 'Renombrada',
        ]);

        $response->assertRedirect(route('categorias-gastos.index'));
        $this->assertDatabaseHas('categorias_gastos', ['id' => $categoria->id, 'nombre' => 'Renombrada']);
    }

    public function test_el_listado_muestra_el_nombre_de_la_categoria(): void
    {
        $admin = $this->crearAdmin();
        CategoriaGasto::create(['nombre' => 'Publicidad Digital']);

        $response = $this->actingAs($admin)->get(route('categorias-gastos.index'));

        $response->assertOk();
        $response->assertSee('Publicidad Digital');
    }

    public function test_el_filtro_de_gastos_muestra_el_nombre_de_la_categoria(): void
    {
        $admin = $this->crearAdmin();
        CategoriaGasto::create(['nombre' => 'Equipo Fotográfico']);

        $response = $this->actingAs($admin)->get(route('gastos.index'));

        $response->assertOk();
        $response->assertSee('Equipo Fotográfico');
    }

    public function test_admin_puede_eliminar_una_categoria_sin_gastos_asociados(): void
    {
        $admin = $this->crearAdmin();
        $categoria = CategoriaGasto::create(['nombre' => 'Sin uso']);

        $response = $this->actingAs($admin)->delete(route('categorias-gastos.destroy', $categoria));

        $response->assertRedirect(route('categorias-gastos.index'));
        $this->assertDatabaseMissing('categorias_gastos', ['id' => $categoria->id]);
    }

    public function test_no_permite_eliminar_una_categoria_con_gastos_asociados(): void
    {
        $admin = $this->crearAdmin();
        $categoria = CategoriaGasto::create(['nombre' => 'En uso']);
        \App\Models\GastoOperativo::create([
            'concepto_gasto'      => 'Renta de estudio',
            'categoria_gasto_id'  => $categoria->id,
            'monto'               => 100,
            'fecha_gasto'         => now(),
            'forma_pago'          => 'efectivo',
            'registrado_por'      => $admin->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('categorias-gastos.destroy', $categoria));

        $response->assertRedirect(route('categorias-gastos.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categorias_gastos', ['id' => $categoria->id]);
    }

    public function test_el_boton_eliminar_se_deshabilita_si_la_categoria_tiene_gastos(): void
    {
        $admin = $this->crearAdmin();
        $categoria = CategoriaGasto::create(['nombre' => 'En uso']);
        \App\Models\GastoOperativo::create([
            'concepto_gasto'      => 'Renta de estudio',
            'categoria_gasto_id'  => $categoria->id,
            'monto'               => 100,
            'fecha_gasto'         => now(),
            'forma_pago'          => 'efectivo',
            'registrado_por'      => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('categorias-gastos.edit', $categoria));

        $response->assertOk();
        $response->assertSee('disabled', false);
    }
}
