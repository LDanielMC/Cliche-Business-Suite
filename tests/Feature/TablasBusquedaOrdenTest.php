<?php

namespace Tests\Feature;

use App\Models\BovedaContrasena;
use App\Models\CategoriaGasto;
use App\Models\Cliente;
use App\Models\ControlRenovacion;
use App\Models\GastoOperativo;
use App\Models\PagoCliente;
use App\Models\PaqueteAprobacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Barra de búsqueda (?q=) y orden ascendente/descendente (?sort=&dir=) en
 * todas las tablas administrativas paginadas.
 */
class TablasBusquedaOrdenTest extends TestCase
{
    use RefreshDatabase;

    public function test_clientes_index_busca_y_ordena(): void
    {
        $admin = $this->crearAdmin();
        [$a] = $this->crearClienteActivo();
        $a->update(['nombre_negocio' => 'Alfa Estudio']);
        [$z] = $this->crearClienteActivo();
        $z->update(['nombre_negocio' => 'Zeta Boutique']);

        $busqueda = $this->actingAs($admin)->get(route('clientes.index', ['q' => 'Zeta']));
        $busqueda->assertOk()->assertSee('Zeta Boutique')->assertDontSee('Alfa Estudio');

        $asc = $this->actingAs($admin)->get(route('clientes.index', ['sort' => 'cliente', 'dir' => 'asc']));
        $asc->assertSeeInOrder(['Alfa Estudio', 'Zeta Boutique']);

        $desc = $this->actingAs($admin)->get(route('clientes.index', ['sort' => 'cliente', 'dir' => 'desc']));
        $desc->assertSeeInOrder(['Zeta Boutique', 'Alfa Estudio']);
    }

    public function test_clientes_suspendidos_y_eliminados_aceptan_busqueda_y_orden(): void
    {
        $admin = $this->crearAdmin();
        [$cliente] = $this->crearClienteActivo();
        $cliente->update(['nombre_negocio' => 'Suspendido Uno']);
        $cliente->user->update(['estatus' => User::ESTATUS_SUSPENDIDO]);

        $this->actingAs($admin)
            ->get(route('clientes.suspendidos', ['q' => 'Suspendido', 'sort' => 'cliente', 'dir' => 'asc']))
            ->assertOk()->assertSee('Suspendido Uno');

        $cliente->user->update(['estatus' => User::ESTATUS_DADO_DE_BAJA, 'fecha_baja' => now()]);

        $this->actingAs($admin)
            ->get(route('clientes.eliminados', ['q' => 'Suspendido', 'sort' => 'fecha_baja', 'dir' => 'desc']))
            ->assertOk()->assertSee('Suspendido Uno');
    }

    public function test_categorias_gastos_busca_y_ordena(): void
    {
        $admin = $this->crearAdmin();
        CategoriaGasto::create(['nombre' => 'Alfa Categoría']);
        CategoriaGasto::create(['nombre' => 'Zeta Categoría']);

        $this->actingAs($admin)
            ->get(route('categorias-gastos.index', ['q' => 'Zeta']))
            ->assertOk()->assertSee('Zeta Categoría')->assertDontSee('Alfa Categoría');

        $desc = $this->actingAs($admin)->get(route('categorias-gastos.index', ['sort' => 'nombre', 'dir' => 'desc']));
        $desc->assertSeeInOrder(['Zeta Categoría', 'Alfa Categoría']);
    }

    public function test_gastos_busca_y_ordena(): void
    {
        $admin = $this->crearAdmin();
        $categoria = CategoriaGasto::create(['nombre' => 'Transporte']);
        GastoOperativo::create([
            'concepto_gasto' => 'Alfa Gasolina', 'categoria_gasto_id' => $categoria->id,
            'monto' => 100, 'fecha_gasto' => now()->subDays(2), 'forma_pago' => 'efectivo', 'registrado_por' => $admin->id,
        ]);
        GastoOperativo::create([
            'concepto_gasto' => 'Zeta Peaje', 'categoria_gasto_id' => $categoria->id,
            'monto' => 200, 'fecha_gasto' => now()->subDay(), 'forma_pago' => 'efectivo', 'registrado_por' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('gastos.index', ['q' => 'Zeta']))
            ->assertOk()->assertSee('Zeta Peaje')->assertDontSee('Alfa Gasolina');

        $asc = $this->actingAs($admin)->get(route('gastos.index', ['sort' => 'monto', 'dir' => 'asc']));
        $asc->assertSeeInOrder(['Alfa Gasolina', 'Zeta Peaje']);

        $desc = $this->actingAs($admin)->get(route('gastos.index', ['sort' => 'monto', 'dir' => 'desc']));
        $desc->assertSeeInOrder(['Zeta Peaje', 'Alfa Gasolina']);
    }

    public function test_pagos_busca_y_ordena(): void
    {
        $admin = $this->crearAdmin();
        [$a] = $this->crearClienteActivo();
        $a->update(['nombre_negocio' => 'Alfa Pagos']);
        [$z] = $this->crearClienteActivo();
        $z->update(['nombre_negocio' => 'Zeta Pagos']);

        PagoCliente::create([
            'cliente_id' => $a->id, 'concepto_servicio' => 'Servicio', 'monto' => 100,
            'fecha_pago' => now()->subDays(2), 'forma_pago' => 'efectivo', 'estatus' => PagoCliente::ESTATUS_PAGADO,
        ]);
        PagoCliente::create([
            'cliente_id' => $z->id, 'concepto_servicio' => 'Servicio', 'monto' => 500,
            'fecha_pago' => now()->subDay(), 'forma_pago' => 'efectivo', 'estatus' => PagoCliente::ESTATUS_PAGADO,
        ]);

        // No se usa assertDontSee aquí: el nombre del cliente "Alfa" también
        // aparece en el <select> de filtro por cliente, que lista a todos.
        $this->actingAs($admin)
            ->get(route('pagos.index', ['q' => 'Zeta']))
            ->assertOk()->assertSee('Zeta Pagos');

        $asc = $this->actingAs($admin)->get(route('pagos.index', ['sort' => 'monto', 'dir' => 'asc']));
        $asc->assertSeeInOrder(['Alfa Pagos', 'Zeta Pagos']);
    }

    public function test_renovaciones_busca_y_ordena_sin_romper_orden_por_defecto(): void
    {
        $admin = $this->crearAdmin();
        [$a] = $this->crearClienteActivo();
        $a->update(['nombre_negocio' => 'Alfa Renovación']);
        [$z] = $this->crearClienteActivo();
        $z->update(['nombre_negocio' => 'Zeta Renovación']);

        // Orden por defecto (prioridad por estatus) sigue funcionando.
        $this->actingAs($admin)->get(route('renovaciones.index'))->assertOk();

        $this->actingAs($admin)
            ->get(route('renovaciones.index', ['q' => 'Zeta']))
            ->assertOk()->assertSee('Zeta Renovación')->assertDontSee('Alfa Renovación');

        $asc = $this->actingAs($admin)->get(route('renovaciones.index', ['sort' => 'cliente', 'dir' => 'asc']));
        $asc->assertSeeInOrder(['Alfa Renovación', 'Zeta Renovación']);
    }

    public function test_aprobaciones_busca_y_ordena(): void
    {
        $admin = $this->crearAdmin();
        [$a, $renovacionA] = $this->crearClienteActivo();
        $a->update(['nombre_negocio' => 'Alfa Aprobación']);
        // Aleja el vencimiento para que no aparezca en el widget de "en riesgo",
        // que contaminaría las aserciones de la tabla principal.
        $renovacionA->update(['fecha_vencimiento' => now()->addDays(60)]);
        [$z, $renovacionZ] = $this->crearClienteActivo();
        $z->update(['nombre_negocio' => 'Zeta Aprobación']);
        $renovacionZ->update(['fecha_vencimiento' => now()->addDays(60)]);

        $this->crearPaqueteBorrador($a, $renovacionA);
        $this->crearPaqueteBorrador($z, $renovacionZ);

        $this->actingAs($admin)
            ->get(route('aprobaciones.index', ['q' => 'Zeta']))
            ->assertOk()->assertSee('Zeta Aprobación')->assertDontSee('Alfa Aprobación');

        $asc = $this->actingAs($admin)->get(route('aprobaciones.index', ['sort' => 'cliente', 'dir' => 'asc']));
        $asc->assertSeeInOrder(['Alfa Aprobación', 'Zeta Aprobación']);

        // sort=fotos ordena por el alias de withCount('fotos'); select() debe ir
        // antes de withCount() o MySQL lanza "Unknown column 'fotos_count'".
        $this->actingAs($admin)
            ->get(route('aprobaciones.index', ['sort' => 'fotos', 'dir' => 'asc']))
            ->assertOk();
    }

    public function test_operadores_busca_y_ordena(): void
    {
        $admin = $this->crearAdmin();
        // "name" se recalcula en User::boot() a partir de nombres/apellido_paterno.
        User::factory()->create(['role' => User::ROLE_OPERADOR, 'estatus' => User::ESTATUS_ACTIVO, 'nombres' => 'Alfa', 'apellido_paterno' => 'Operador']);
        User::factory()->create(['role' => User::ROLE_OPERADOR, 'estatus' => User::ESTATUS_ACTIVO, 'nombres' => 'Zeta', 'apellido_paterno' => 'Operador']);

        $this->actingAs($admin)
            ->get(route('operadores.index', ['q' => 'Zeta']))
            ->assertOk()->assertSee('Zeta Operador')->assertDontSee('Alfa Operador');

        $asc = $this->actingAs($admin)->get(route('operadores.index', ['sort' => 'operador', 'dir' => 'asc']));
        $asc->assertSeeInOrder(['Alfa Operador', 'Zeta Operador']);
    }

    public function test_boveda_busca_y_ordena(): void
    {
        $admin = $this->crearAdmin();
        [$cliente] = $this->crearClienteActivo();

        BovedaContrasena::create([
            'cliente_id' => $cliente->id, 'nombre_plataforma' => 'Alfa Plataforma',
            'usuario' => 'user1', 'password' => 'secret1', 'creado_por' => $admin->id,
        ]);
        BovedaContrasena::create([
            'cliente_id' => $cliente->id, 'nombre_plataforma' => 'Zeta Plataforma',
            'usuario' => 'user2', 'password' => 'secret2', 'creado_por' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('boveda.index', ['q' => 'Zeta']))
            ->assertOk()->assertSee('Zeta Plataforma')->assertDontSee('Alfa Plataforma');

        $desc = $this->actingAs($admin)->get(route('boveda.index', ['sort' => 'plataforma', 'dir' => 'desc']));
        $desc->assertSeeInOrder(['Zeta Plataforma', 'Alfa Plataforma']);
    }
}
