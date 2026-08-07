<?php

namespace Database\Seeders;

use App\Models\CategoriaGasto;
use App\Models\Cliente;
use App\Models\GastoOperativo;
use App\Models\User;
use Illuminate\Database\Seeder;

class GastoOperativoSeeder extends Seeder
{
    public function run(): void
    {
        $admin    = User::where('email', 'admin@sistema.com')->firstOrFail();
        $cats     = CategoriaGasto::all()->keyBy('nombre');
        $activos  = Cliente::activos()->get();

        // ── Gastos generales — 2 meses de historia ─────────────────────────────
        // Set reducido a propósito: solo un ejemplo por categoría principal,
        // suficiente para probar reportes sin saturar el listado de gastos.
        $gastosGeneralesPorMes = [
            ['Publicidad Digital',       'Campaña de anuncios en redes sociales',           1200.00, 'transferencia'],
            ['Suscripciones y Software',  'Suscripción Canva Pro equipo',                     350.00, 'tarjeta'],
            ['Equipo Fotográfico',        'Mantenimiento de cámara réflex y lentes',          600.00, 'efectivo'],
            ['Gastos Administrativos',   'Papelería e insumos de oficina',                   180.00, 'efectivo'],
        ];

        for ($mesesAtras = 1; $mesesAtras >= 0; $mesesAtras--) {
            $baseDate = now()->subMonths($mesesAtras)->startOfMonth();

            foreach ($gastosGeneralesPorMes as [$cat, $concepto, $monto, $formaPago]) {
                // Pequeña variación en monto mes a mes para que las gráficas no sean planas
                $montoVariado = round($monto * (1 + (($mesesAtras % 3) * 0.05)), 2);

                GastoOperativo::create([
                    'concepto_gasto'    => $concepto,
                    'categoria_gasto_id' => $cats[$cat]->id,
                    'monto'             => $montoVariado,
                    'fecha_gasto'       => $baseDate->copy()->addDays(3),
                    'cliente_id'        => null,
                    'forma_pago'        => $formaPago,
                    'observaciones'     => 'Gasto operativo general de la agencia.',
                    'registrado_por'    => $admin->id,
                ]);
            }

            // ── Gastos directos — solo el mes más reciente, uno por cliente activo ──
            // Un ejemplo simple del prorrateo de FN.11 (rentabilidad), sin llenar
            // el listado con un registro por cliente por cada mes de historia.
            if ($mesesAtras === 0) {
                foreach ($activos as $cliente) {
                    if ($baseDate->lt($cliente->fecha_registro)) {
                        continue;
                    }

                    GastoOperativo::create([
                        'concepto_gasto'    => 'Producción fotográfica mensual',
                        'categoria_gasto_id' => $cats['Equipo Fotográfico']->id,
                        'monto'             => round($cliente->precio_mensual * 0.08, 2), // 8% del precio
                        'fecha_gasto'       => $baseDate->copy()->addDays(5),
                        'cliente_id'        => $cliente->id,
                        'forma_pago'        => 'efectivo',
                        'observaciones'     => 'Gasto directo: sesión fotográfica para ' . $cliente->nombre_negocio . '.',
                        'registrado_por'    => $admin->id,
                    ]);
                }
            }
        }
    }
}
