<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\PagoCliente;
use App\Models\User;
use Illuminate\Database\Seeder;

class PagoClienteSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@sistema.com')->firstOrFail();
        $formasPago = ['transferencia', 'tarjeta', 'efectivo'];

        foreach (Cliente::all() as $indice => $cliente) {
            foreach (range(2, 0) as $mesesAtras) {
                $vencimiento = now()->subMonths($mesesAtras)->startOfMonth()->addDays(4);
                $periodo = $vencimiento->translatedFormat('F Y');

                if ($mesesAtras > 0) {
                    // Meses anteriores: ya pagados.
                    $estatus = PagoCliente::ESTATUS_PAGADO;
                    $fechaPago = $vencimiento->copy()->subDays(random_int(0, 3));
                } else {
                    // Mes actual: variar el estatus según el cliente para tener ejemplos de cada caso.
                    $estatus = match ($indice % 3) {
                        0 => PagoCliente::ESTATUS_PAGADO,
                        1 => PagoCliente::ESTATUS_PENDIENTE,
                        default => PagoCliente::ESTATUS_VENCIDO,
                    };
                    $fechaPago = $estatus === PagoCliente::ESTATUS_PAGADO ? $vencimiento->copy()->subDay() : null;
                }

                PagoCliente::create([
                    'cliente_id' => $cliente->id,
                    'concepto_servicio' => $cliente->servicio_contratado,
                    'monto' => $cliente->precio_mensual,
                    'fecha_pago' => $fechaPago,
                    'periodo_facturado' => ucfirst($periodo),
                    'forma_pago' => $estatus === PagoCliente::ESTATUS_PAGADO ? $formasPago[$indice % 3] : null,
                    'estatus' => $estatus,
                    'fecha_vencimiento' => $vencimiento,
                    'registrado_por' => $admin->id,
                ]);
            }
        }
    }
}
