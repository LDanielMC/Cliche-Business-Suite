<?php

namespace Tests\Feature;

use App\Models\CategoriaGasto;
use App\Models\GastoOperativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GastoOperativoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_eliminar_un_gasto(): void
    {
        $admin = $this->crearAdmin();
        $categoria = CategoriaGasto::create(['nombre' => 'Transporte']);
        $gasto = GastoOperativo::create([
            'concepto_gasto'     => 'Gasolina',
            'categoria_gasto_id' => $categoria->id,
            'monto'              => 500,
            'fecha_gasto'        => now(),
            'forma_pago'         => 'efectivo',
            'registrado_por'     => $admin->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('gastos.destroy', $gasto));

        $response->assertRedirect(route('gastos.index'));
        $this->assertDatabaseMissing('gastos_operativos', ['id' => $gasto->id]);
    }

    public function test_la_pagina_de_editar_gasto_muestra_boton_eliminar(): void
    {
        $admin = $this->crearAdmin();
        $categoria = CategoriaGasto::create(['nombre' => 'Transporte']);
        $gasto = GastoOperativo::create([
            'concepto_gasto'     => 'Gasolina',
            'categoria_gasto_id' => $categoria->id,
            'monto'              => 500,
            'fecha_gasto'        => now(),
            'forma_pago'         => 'efectivo',
            'registrado_por'     => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('gastos.edit', $gasto));

        $response->assertOk();
        $response->assertSee(route('gastos.destroy', $gasto), false);
    }

    public function test_operador_puede_registrar_un_gasto(): void
    {
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);
        $categoria = CategoriaGasto::create(['nombre' => 'Transporte']);

        $response = $this->actingAs($operador)->post(route('gastos.store'), [
            'categoria_gasto_id' => $categoria->id,
            'concepto_gasto'     => 'Uber a sesión de fotos',
            'monto'              => 250,
            'fecha_gasto'        => now()->toDateString(),
            'forma_pago'         => 'efectivo',
        ]);

        $response->assertRedirect(route('gastos.index'));
        $this->assertDatabaseHas('gastos_operativos', [
            'concepto_gasto' => 'Uber a sesión de fotos',
            'registrado_por' => $operador->id,
        ]);
    }

    public function test_operador_puede_editar_su_propio_gasto(): void
    {
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);
        $categoria = CategoriaGasto::create(['nombre' => 'Transporte']);
        $gasto = GastoOperativo::create([
            'concepto_gasto'     => 'Gasolina',
            'categoria_gasto_id' => $categoria->id,
            'monto'              => 300,
            'fecha_gasto'        => now(),
            'forma_pago'         => 'efectivo',
            'registrado_por'     => $operador->id,
        ]);

        $response = $this->actingAs($operador)->get(route('gastos.edit', $gasto));
        $response->assertOk();

        $response = $this->actingAs($operador)->put(route('gastos.update', $gasto), [
            'categoria_gasto_id' => $categoria->id,
            'concepto_gasto'     => 'Gasolina actualizada',
            'monto'              => 300,
            'fecha_gasto'        => now()->toDateString(),
            'forma_pago'         => 'efectivo',
        ]);

        $response->assertRedirect(route('gastos.index'));
        $this->assertDatabaseHas('gastos_operativos', ['id' => $gasto->id, 'concepto_gasto' => 'Gasolina actualizada']);
    }

    public function test_operador_no_puede_editar_ni_eliminar_gasto_de_otro(): void
    {
        $admin = $this->crearAdmin();
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);
        $categoria = CategoriaGasto::create(['nombre' => 'Transporte']);
        $gasto = GastoOperativo::create([
            'concepto_gasto'     => 'Gasolina del admin',
            'categoria_gasto_id' => $categoria->id,
            'monto'              => 300,
            'fecha_gasto'        => now(),
            'forma_pago'         => 'efectivo',
            'registrado_por'     => $admin->id,
        ]);

        $this->actingAs($operador)->get(route('gastos.edit', $gasto))->assertForbidden();

        $this->actingAs($operador)->put(route('gastos.update', $gasto), [
            'categoria_gasto_id' => $categoria->id,
            'concepto_gasto'     => 'Intento de edición',
            'monto'              => 999,
            'fecha_gasto'        => now()->toDateString(),
            'forma_pago'         => 'efectivo',
        ])->assertForbidden();

        $this->actingAs($operador)->delete(route('gastos.destroy', $gasto))->assertForbidden();

        $this->assertDatabaseHas('gastos_operativos', ['id' => $gasto->id, 'concepto_gasto' => 'Gasolina del admin']);
    }

    public function test_operador_puede_ver_el_listado_pero_no_categorias(): void
    {
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);

        $this->actingAs($operador)->get(route('gastos.index'))->assertOk();
        $this->actingAs($operador)->get(route('categorias-gastos.index'))->assertForbidden();
    }

    public function test_operador_en_el_listado_solo_ve_sus_propios_gastos(): void
    {
        $admin = $this->crearAdmin();
        $operadorA = $this->crearAdmin(role: User::ROLE_OPERADOR);
        $operadorB = $this->crearAdmin(role: User::ROLE_OPERADOR);
        $categoria = CategoriaGasto::create(['nombre' => 'Transporte']);

        GastoOperativo::create([
            'concepto_gasto' => 'Gasto del admin', 'categoria_gasto_id' => $categoria->id,
            'monto' => 100, 'fecha_gasto' => now(), 'forma_pago' => 'efectivo', 'registrado_por' => $admin->id,
        ]);
        GastoOperativo::create([
            'concepto_gasto' => 'Gasto de operador B', 'categoria_gasto_id' => $categoria->id,
            'monto' => 100, 'fecha_gasto' => now(), 'forma_pago' => 'efectivo', 'registrado_por' => $operadorB->id,
        ]);
        GastoOperativo::create([
            'concepto_gasto' => 'Gasto de operador A', 'categoria_gasto_id' => $categoria->id,
            'monto' => 100, 'fecha_gasto' => now(), 'forma_pago' => 'efectivo', 'registrado_por' => $operadorA->id,
        ]);

        $response = $this->actingAs($operadorA)->get(route('gastos.index'));

        $response->assertOk();
        $response->assertSee('Gasto de operador A');
        $response->assertDontSee('Gasto del admin');
        $response->assertDontSee('Gasto de operador B');

        // El admin sigue viendo todo.
        $response = $this->actingAs($admin)->get(route('gastos.index'));
        $response->assertSee('Gasto del admin');
        $response->assertSee('Gasto de operador A');
        $response->assertSee('Gasto de operador B');
    }

    public function test_admin_filtra_gastos_por_operador(): void
    {
        $admin = $this->crearAdmin();
        $operadorA = User::factory()->create(['role' => User::ROLE_OPERADOR, 'estatus' => User::ESTATUS_ACTIVO, 'nombres' => 'Alfa', 'apellido_paterno' => 'Operador']);
        $operadorB = User::factory()->create(['role' => User::ROLE_OPERADOR, 'estatus' => User::ESTATUS_ACTIVO, 'nombres' => 'Zeta', 'apellido_paterno' => 'Operador']);
        $categoria = CategoriaGasto::create(['nombre' => 'Transporte']);

        GastoOperativo::create([
            'concepto_gasto' => 'Uber de Alfa', 'categoria_gasto_id' => $categoria->id,
            'monto' => 100, 'fecha_gasto' => now(), 'forma_pago' => 'efectivo', 'registrado_por' => $operadorA->id,
        ]);
        GastoOperativo::create([
            'concepto_gasto' => 'Uber de Zeta', 'categoria_gasto_id' => $categoria->id,
            'monto' => 100, 'fecha_gasto' => now(), 'forma_pago' => 'efectivo', 'registrado_por' => $operadorB->id,
        ]);

        // Filtro por dropdown de operador.
        $response = $this->actingAs($admin)->get(route('gastos.index', ['registrado_por' => $operadorA->id]));
        $response->assertSee('Uber de Alfa');
        $response->assertDontSee('Uber de Zeta');

        // Búsqueda de texto también encuentra por nombre del operador.
        $response = $this->actingAs($admin)->get(route('gastos.index', ['q' => 'Zeta']));
        $response->assertSee('Uber de Zeta');
        $response->assertDontSee('Uber de Alfa');

        // Orden por "Registrado por".
        $response = $this->actingAs($admin)->get(route('gastos.index', ['sort' => 'registrado', 'dir' => 'asc']));
        $response->assertSeeInOrder(['Alfa Operador', 'Zeta Operador']);
    }

    public function test_operador_no_ve_el_filtro_de_operador(): void
    {
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);

        $response = $this->actingAs($operador)->get(route('gastos.index'));

        $response->assertOk();
        $response->assertDontSee('name="registrado_por"', false);
    }
}
