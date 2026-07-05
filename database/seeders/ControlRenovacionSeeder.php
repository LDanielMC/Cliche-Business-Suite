<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\ControlRenovacion;
use App\Models\User;
use Illuminate\Database\Seeder;

class ControlRenovacionSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all()->values();

        // Cliente 0: ciclo vigente, vencimiento a futuro.
        $this->crearCiclo($clientes[0], now()->subMonths(2), now()->addDays(20), ControlRenovacion::ESTATUS_VIGENTE);

        // Cliente 1: por vencer en los próximos días.
        $this->crearCiclo($clientes[1], now()->subMonth(), now()->addDays(4), ControlRenovacion::ESTATUS_POR_VENCER);

        // Cliente 2: vencido; se suspende la cuenta del cliente, tal como lo haría el comando automático.
        $this->crearCiclo($clientes[2], now()->subMonths(2), now()->subDays(3), ControlRenovacion::ESTATUS_VENCIDO);
        $clientes[2]->user->update(['estatus' => User::ESTATUS_SUSPENDIDO]);

        // Cliente 3: ciclo recién renovado, vigente por un mes más.
        $this->crearCiclo($clientes[3], now()->subDays(2), now()->addMonth(), ControlRenovacion::ESTATUS_VIGENTE);
    }

    private function crearCiclo(Cliente $cliente, \Carbon\Carbon $inicio, \Carbon\Carbon $vencimiento, string $estatus): void
    {
        ControlRenovacion::create([
            'cliente_id' => $cliente->id,
            'fecha_inicio' => $inicio,
            'fecha_vencimiento' => $vencimiento,
            'estatus' => $estatus,
            'fecha_recordatorio' => $vencimiento->copy()->subDays(7),
            'observaciones' => 'Ciclo de servicio de posicionamiento local.',
        ]);
    }
}
