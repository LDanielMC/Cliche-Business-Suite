<?php

namespace Tests\Feature;

use App\Models\CalendarioFoto;
use App\Models\CategoriaGasto;
use App\Models\GastoOperativo;
use App\Models\PaqueteAprobacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OperadorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_muestra_datos_reales_no_de_relleno(): void
    {
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);
        [$cliente, $renovacion] = $this->crearClienteActivo();
        $paquete = $this->crearPaqueteBorrador($cliente, $renovacion);
        $paquete->update(['estatus' => PaqueteAprobacion::ESTATUS_PENDIENTE]);

        $categoria = CategoriaGasto::create(['nombre' => 'Transporte']);
        GastoOperativo::create([
            'concepto_gasto' => 'Uber a sesión', 'categoria_gasto_id' => $categoria->id,
            'monto' => 150, 'fecha_gasto' => now(), 'forma_pago' => 'efectivo', 'registrado_por' => $operador->id,
        ]);

        // 3 fotos programadas el mismo día para el mismo cliente: no deben
        // generar 3 filas repetidas en el feed, sino una sola agrupada.
        $fechaPublicacion = now()->addDays(2);
        foreach (range(1, 3) as $i) {
            $foto = $this->crearFoto($paquete);
            CalendarioFoto::create([
                'cliente_id' => $cliente->id,
                'foto_aprobacion_id' => $foto->id,
                'fotografia_asociada' => $foto->ruta_foto,
                'fecha_publicacion_programada' => $fechaPublicacion,
                'estatus' => CalendarioFoto::ESTATUS_PROGRAMADA,
                'creado_por' => $operador->id,
            ]);
        }

        $response = $this->actingAs($operador)->get(route('operador.dashboard'));

        $response->assertOk();
        $response->assertSee($cliente->nombre_negocio);
        $response->assertSee('$150');
        $response->assertSee('Qué hacer hoy');
        $response->assertSee('3 fotos — publicar el');

        // El nombre del cliente debe aparecer una sola vez en el feed (agrupado),
        // no 3 veces por cada foto.
        $cuerpo = $response->getContent();
        $this->assertSame(1, substr_count($cuerpo, '3 fotos — publicar el'));

        // Nada de datos falsos del dashboard viejo.
        $response->assertDontSee('Tareas Asignadas');
        $response->assertDontSee('Incidentes Hoy');
        $response->assertDontSee('Se completo la tarea #1234');
    }

    public function test_dashboard_no_revienta_sin_datos(): void
    {
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);

        $response = $this->actingAs($operador)->get(route('operador.dashboard'));

        $response->assertOk();
        $response->assertSee('0'); // paquetes en riesgo / pendientes en 0
    }

    public function test_dashboard_muestra_reserva_por_expirar_y_vencidas_sin_agendar_sin_depender_del_cron(): void
    {
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);

        // Foto en reserva ya vencida.
        [$clienteA, $renovacionA] = $this->crearClienteActivo();
        $paqueteA = $this->crearPaqueteBorrador($clienteA, $renovacionA);
        $fotoReserva = $this->crearFoto($paqueteA, null, \App\Models\FotoAprobacion::ESTATUS_CONSERVADA);
        $fotoReserva->update([
            'cliente_id'               => $clienteA->id,
            'fecha_expiracion_reserva' => now()->subDay(),
        ]);

        // Foto aprobada nunca agendada, con período vencido hace +6 meses.
        [$clienteB, $renovacionB] = $this->crearClienteActivo();
        $mesesReserva = config('renovaciones.meses_reserva', 6);
        $paqueteB = PaqueteAprobacion::create([
            'cliente_id'                => $clienteB->id,
            'mes_revision'              => now()->format('Y-m'),
            'cantidad_requerida'        => 3,
            'estatus'                   => PaqueteAprobacion::ESTATUS_AUTO_APROBADO,
            'fecha_inicio_periodo'      => now()->subMonths($mesesReserva)->subDays(30),
            'fecha_vencimiento_periodo' => now()->subMonths($mesesReserva)->subDay(),
        ]);
        $this->crearFoto($paqueteB, null, \App\Models\FotoAprobacion::ESTATUS_APROBADA);

        // Sin correr ningún comando artisan: esto debe verse solo por cargar la página.
        $response = $this->actingAs($operador)->get(route('operador.dashboard'));

        $response->assertOk();
        $response->assertSee('en reserva por expirar');
        $response->assertSee('aprobadas sin agendar hace meses');
    }
}
