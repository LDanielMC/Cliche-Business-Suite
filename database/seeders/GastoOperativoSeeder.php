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
        $admin = User::where('email', 'admin@sistema.com')->firstOrFail();
        $categorias = CategoriaGasto::all()->keyBy('nombre_categoria');
        $clientes = Cliente::all();

        $gastosGenerales = [
            ['Publicidad Digital', 'Campaña de anuncios en redes sociales', 1200.00, 'transferencia'],
            ['Herramientas SEO', 'Licencia mensual SEMrush', 890.00, 'tarjeta'],
            ['Suscripciones y Software', 'Suscripción Canva Pro equipo', 350.00, 'tarjeta'],
            ['Equipo Fotográfico', 'Mantenimiento de cámara réflex', 600.00, 'efectivo'],
            ['Transporte', 'Gasolina para visitas a clientes', 450.00, 'efectivo'],
        ];

        foreach (range(0, 2) as $mesesAtras) {
            $fecha = now()->subMonths($mesesAtras)->startOfMonth()->addDays(random_int(2, 20));

            foreach ($gastosGenerales as [$categoriaNombre, $concepto, $monto, $formaPago]) {
                GastoOperativo::create([
                    'concepto_gasto' => $concepto,
                    'categoria_gasto_id' => $categorias[$categoriaNombre]->id,
                    'monto' => $monto,
                    'fecha_gasto' => $fecha->copy()->addDays(random_int(0, 5)),
                    'cliente_id' => null,
                    'forma_pago' => $formaPago,
                    'observaciones' => 'Gasto operativo general de la agencia.',
                    'registrado_por' => $admin->id,
                ]);
            }

            // Gastos directos asociados a un cliente específico (para el reporte de rentabilidad).
            foreach ($clientes as $cliente) {
                GastoOperativo::create([
                    'concepto_gasto' => 'Impresión de material promocional',
                    'categoria_gasto_id' => $categorias['Publicidad Digital']->id,
                    'monto' => random_int(150, 400),
                    'fecha_gasto' => $fecha->copy()->addDays(random_int(0, 10)),
                    'cliente_id' => $cliente->id,
                    'forma_pago' => 'efectivo',
                    'observaciones' => 'Gasto directo del cliente ' . $cliente->nombre_negocio . '.',
                    'registrado_por' => $admin->id,
                ]);
            }
        }
    }
}
