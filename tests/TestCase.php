<?php

namespace Tests;

use App\Models\CalendarioFoto;
use App\Models\Cliente;
use App\Models\ControlRenovacion;
use App\Models\FotoAprobacion;
use App\Models\PaqueteAprobacion;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    // ── Helpers de construcción de escenarios ─────────────────────────────────

    /**
     * Crea un usuario admin/operador para usar como actor en las requests.
     */
    protected function crearAdmin(string $role = User::ROLE_ADMIN): User
    {
        return User::factory()->create([
            'role'    => $role,
            'estatus' => User::ESTATUS_ACTIVO,
            'nombres' => 'Admin',
            'apellido_paterno' => 'Sistema',
        ]);
    }

    /**
     * Crea un cliente activo con su usuario y su renovación vigente.
     * Devuelve [$cliente, $renovacion].
     */
    protected function crearClienteActivo(int $cantidadFotos = 4, array $userOverrides = []): array
    {
        $user = User::factory()->create(array_merge([
            'role'             => User::ROLE_CLIENTE,
            'estatus'          => User::ESTATUS_ACTIVO,
            'nombres'          => fake()->firstName(),
            'apellido_paterno' => fake()->lastName(),
        ], $userOverrides));

        $cliente = Cliente::create([
            'user_id'             => $user->id,
            'nombre_negocio'      => fake()->company(),
            'cantidad_fotos'      => $cantidadFotos,
            'precio_mensual'      => 500.00,
            'fecha_registro'      => now(),
            'servicio_contratado' => 'Fotografía mensual',
        ]);

        $inicio = Carbon::today()->subDays(10);
        $renovacion = ControlRenovacion::create([
            'cliente_id'         => $cliente->id,
            'fecha_inicio'       => $inicio,
            'fecha_vencimiento'  => $inicio->copy()->addDays(29),
            'estatus'            => ControlRenovacion::ESTATUS_VIGENTE,
            'fecha_recordatorio' => $inicio->copy()->addDays(24),
        ]);

        return [$cliente, $renovacion];
    }

    /**
     * Crea un paquete en borrador para el cliente dado, con el snapshot de la renovación.
     */
    protected function crearPaqueteBorrador(Cliente $cliente, ControlRenovacion $renovacion): PaqueteAprobacion
    {
        return PaqueteAprobacion::create([
            'cliente_id'                => $cliente->id,
            'mes_revision'              => now()->format('Y-m'),
            'cantidad_requerida'        => $cliente->cantidad_fotos,
            'estatus'                   => PaqueteAprobacion::ESTATUS_BORRADOR,
            'fecha_inicio_periodo'      => $renovacion->fecha_inicio,
            'fecha_vencimiento_periodo' => $renovacion->fecha_vencimiento,
        ]);
    }

    /**
     * Crea una FotoAprobacion con una ruta única de prueba.
     */
    protected function crearFoto(PaqueteAprobacion $paquete, ?int $prioridad = null, string $estatus = FotoAprobacion::ESTATUS_PENDIENTE): FotoAprobacion
    {
        $ruta = "fotos-aprobacion/{$paquete->cliente_id}/{$paquete->id}/foto_" . uniqid() . '.jpg';

        return FotoAprobacion::create([
            'paquete_aprobacion_id' => $paquete->id,
            'cliente_id'            => $paquete->cliente_id,
            'ruta_foto'             => $ruta,
            'estatus'               => $estatus,
            'prioridad'             => $prioridad,
        ]);
    }

    /**
     * Envía el paquete al cliente (borrador → pendiente).
     * Asigna prioridades a las fotos que aún no la tienen y llama al endpoint.
     */
    protected function enviarPaqueteAlCliente(PaqueteAprobacion $paquete, User $admin): void
    {
        $fotos = $paquete->fotos()->where('estatus', FotoAprobacion::ESTATUS_PENDIENTE)->orderBy('id')->get();
        foreach ($fotos as $i => $foto) {
            if ($foto->prioridad === null) {
                $foto->update(['prioridad' => $i + 1]);
            }
        }

        $this->actingAs($admin)
             ->post(route('aprobaciones.enviar-cliente', $paquete))
             ->assertRedirect();

        $paquete->refresh();
    }
}
