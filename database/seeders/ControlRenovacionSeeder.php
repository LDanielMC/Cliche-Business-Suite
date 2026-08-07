<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\ControlRenovacion;
use App\Models\HistorialRenovacion;
use App\Models\PagoCliente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Builds a fully coherent renovation + payment history for every client.
 *
 * Rules that mirror the real app flow (completarRenovacion):
 *  - Each cycle is exactly 30 days  (inicio … inicio+29).
 *  - Payment is submitted 3 days before the cycle expires.
 *  - Because the payment is within the grace window, the NEXT cycle starts
 *    the very next day after the old one ends → no gaps between periods.
 *  - Each completed cycle creates ONE HistorialRenovacion + ONE PagoCliente
 *    linked by historial_renovacion_id.
 *  - The ControlRenovacion record always holds the CURRENT active cycle.
 *
 * Client scenarios (today = 2026-07-18):
 *  [0] El Buen Sabor    reg 2026-01-01  6 completed  current vigente  (ends 2026-07-29, 11 d)
 *  [1] Boutique Luna    reg 2026-02-01  5 completed  current por_vencer (ends 2026-07-22, 4 d)
 *  [2] Taller Mecánico  reg 2026-03-01  3 completed  current vencido  (ended 2026-06-28, suspendido)
 *  [3] Clínica Dental   reg 2026-01-01  6 completed  current vigente  (ends 2026-07-29, 11 d)
 *  [4] Farmacia S.José  reg 2026-05-01  2 completed  current vigente  (ends 2026-07-29, 11 d)
 *  [5] Peluquería Estilo reg 2026-02-01 4 completed  dado de baja (2026-06-11), cycle 5 never paid
 */
class ControlRenovacionSeeder extends Seeder
{
    private User $admin;

    public function run(): void
    {
        $this->admin = User::where('email', 'admin@sistema.com')->firstOrFail();
        $clientes    = Cliente::with('user')->get()->keyBy('nombre_negocio');

        // ── Restaurante El Buen Sabor ──────────────────────────────────────────
        // reg 2026-01-01 → 6 cycles paid → current vigente ends 2026-07-29
        $ctrl = $this->crearControl($clientes['Restaurante El Buen Sabor'], '2026-01-01');
        $this->completarCiclos($ctrl, $clientes['Restaurante El Buen Sabor'], '2026-01-01', 6, 'transferencia');
        // After 6 × 30d from 2026-01-01: current = 2026-06-30 → 2026-07-29 ✓

        // ── Boutique Luna ─────────────────────────────────────────────────────
        // reg 2026-02-01 → 5 cycles paid → current por_vencer ends 2026-07-22 (4 days left)
        $ctrl = $this->crearControl($clientes['Boutique Luna'], '2026-02-01');
        $this->completarCiclos($ctrl, $clientes['Boutique Luna'], '2026-02-01', 5, 'tarjeta');
        // After 5 × 30d from 2026-02-01: next would start 2026-07-01, ends 2026-07-30.
        // Override end to 2026-07-22 so the demo shows "por_vencer" today.
        $ctrl->update([
            'fecha_vencimiento'  => Carbon::parse('2026-07-22'),
            'fecha_recordatorio' => Carbon::parse('2026-07-17'),
            'estatus'            => ControlRenovacion::ESTATUS_POR_VENCER,
        ]);

        // ── Taller Mecánico Torres ────────────────────────────────────────────
        // reg 2026-03-01 → 3 cycles paid → current vencido (ended 2026-06-28, >5d ago → suspendido)
        $ctrl = $this->crearControl($clientes['Taller Mecánico Torres'], '2026-03-01');
        $this->completarCiclos($ctrl, $clientes['Taller Mecánico Torres'], '2026-03-01', 3, 'efectivo');
        // After 3 × 30d from 2026-03-01: current = 2026-05-30 → 2026-06-28.
        // 2026-07-18 - 2026-06-28 = 20 days → past 5-day grace → suspendido.
        $ctrl->update(['estatus' => ControlRenovacion::ESTATUS_VENCIDO]);
        $clientes['Taller Mecánico Torres']->user->update(['estatus' => User::ESTATUS_SUSPENDIDO]);

        // ── Clínica Dental Sonrisas ───────────────────────────────────────────
        // reg 2026-01-01 → 6 cycles paid → current vigente ends 2026-07-29
        $ctrl = $this->crearControl($clientes['Clínica Dental Sonrisas'], '2026-01-01');
        $this->completarCiclos($ctrl, $clientes['Clínica Dental Sonrisas'], '2026-01-01', 6, 'transferencia');

        // ── Farmacia San José ─────────────────────────────────────────────────
        // reg 2026-05-01 → 2 cycles paid → current vigente ends 2026-07-29
        $ctrl = $this->crearControl($clientes['Farmacia San José'], '2026-05-01');
        $this->completarCiclos($ctrl, $clientes['Farmacia San José'], '2026-05-01', 2, 'tarjeta');
        // After 2 × 30d from 2026-05-01: current = 2026-06-30 → 2026-07-29 ✓

        // ── Peluquería Estilo (dado de baja 2026-06-11) ───────────────────────
        // reg 2026-02-01 → 4 cycles paid → cycle 5 started 2026-06-01 but NEVER paid (baja)
        $ctrl = $this->crearControl($clientes['Peluquería Estilo'], '2026-02-01');
        $this->completarCiclos($ctrl, $clientes['Peluquería Estilo'], '2026-02-01', 4, 'efectivo');
        // After 4 × 30d from 2026-02-01: current = 2026-06-01 → 2026-06-30.
        // Client was given baja on 2026-06-11 during this cycle → mark as vencido, no payment.
        $ctrl->update(['estatus' => ControlRenovacion::ESTATUS_VENCIDO]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Create the initial ControlRenovacion for a client starting at $primerInicio.
     * The record will be overwritten (reset) by completarCiclos to the current period.
     */
    private function crearControl(Cliente $cliente, string $primerInicio): ControlRenovacion
    {
        $inicio = Carbon::parse($primerInicio);

        return ControlRenovacion::create([
            'cliente_id'         => $cliente->id,
            'fecha_inicio'       => $inicio,
            'fecha_vencimiento'  => $inicio->copy()->addDays(29),
            'estatus'            => ControlRenovacion::ESTATUS_VIGENTE,
            'fecha_recordatorio' => $inicio->copy()->addDays(24),
        ]);
    }

    /**
     * Simulate $cantidad completed 30-day cycles starting from $primerInicio.
     *
     * For each completed cycle:
     *   - HistorialRenovacion is created (archived period).
     *   - PagoCliente is created and linked to the historial record.
     *   - Payment date = 3 days before period end (client pays early, within grace).
     *
     * After all cycles the ControlRenovacion is reset to the NEXT period
     * (which becomes the current active cycle shown in the UI).
     */
    private function completarCiclos(
        ControlRenovacion $ctrl,
        Cliente $cliente,
        string $primerInicio,
        int $cantidad,
        string $formaPago
    ): void {
        $inicio = Carbon::parse($primerInicio);

        for ($i = 0; $i < $cantidad; $i++) {
            $fin       = $inicio->copy()->addDays(29);
            $fechaPago = $fin->copy()->subDays(3);   // paid 3 days before expiry
            $fechaVal  = $fechaPago->copy()->addDay(); // validated next day

            $historial = HistorialRenovacion::create([
                'cliente_id'            => $cliente->id,
                'control_renovacion_id' => $ctrl->id,
                'periodo_inicio'        => $inicio,
                'periodo_fin'           => $fin,
                'fecha_pago'            => $fechaPago,
                'monto'                 => $cliente->precio_mensual,
                'solicita_factura'      => false,
                'validado_por'          => $this->admin->id,
                'fecha_validacion'      => $fechaVal,
            ]);

            PagoCliente::create([
                'cliente_id'              => $cliente->id,
                'historial_renovacion_id' => $historial->id,
                'concepto_servicio'       => $cliente->servicio_contratado,
                'monto'                   => $cliente->precio_mensual,
                'fecha_pago'              => $fechaPago,
                'periodo_inicio'          => $inicio,
                'periodo_fin'             => $fin,
                'forma_pago'              => $formaPago,
                'estatus'                 => PagoCliente::ESTATUS_PAGADO,
                'validado_por'            => $this->admin->id,
                'fecha_validacion'        => $fechaVal,
                'registrado_por'          => $this->admin->id,
            ]);

            // Next cycle starts the day after this one ends (no gaps)
            $inicio = $fin->copy()->addDay();
        }

        // Reset the control record to the new (current) active period
        $nuevaFin = $inicio->copy()->addDays(29);
        $ctrl->update([
            'fecha_inicio'       => $inicio,
            'fecha_vencimiento'  => $nuevaFin,
            'fecha_recordatorio' => $nuevaFin->copy()->subDays(5),
            'estatus'            => ControlRenovacion::ESTATUS_VIGENTE,
        ]);
    }
}
