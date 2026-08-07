<?php

namespace App\Http\Controllers;

use App\Models\CategoriaGasto;
use App\Models\Cliente;
use App\Models\ClienteEstatusLog;
use App\Models\GastoOperativo;
use App\Models\PagoCliente;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function financiero(Request $request)
    {
        [$desde, $hasta] = $this->rangoFechas($request);
        $datos = $this->calcularFinanciero($desde, $hasta);

        return view('reportes.financiero', compact('datos', 'desde', 'hasta'));
    }

    public function financieroPdf(Request $request)
    {
        $request->validate(['chart_image' => ['required', 'string']]);

        [$desde, $hasta] = $this->rangoFechas($request);
        $datos = $this->calcularFinanciero($desde, $hasta);

        $pdf = Pdf::loadView('reportes.pdf.financiero', [
            'datos' => $datos,
            'desde' => $desde,
            'hasta' => $hasta,
            'chartImage' => $request->input('chart_image'),
        ]);

        return $pdf->download('reporte-financiero-' . $desde->format('Ymd') . '-' . $hasta->format('Ymd') . '.pdf');
    }

    public function clientesRentables(Request $request)
    {
        $request->validate(['cliente_id' => ['nullable', 'integer', 'exists:clientes,id']]);

        [$desde, $hasta] = $this->rangoFechas($request);
        $clienteId = $request->filled('cliente_id') ? (int) $request->input('cliente_id') : null;
        $filas = $this->calcularRentabilidad($desde, $hasta, $clienteId);
        $clientes = Cliente::orderBy('nombre_negocio')->get();

        return view('reportes.rentabilidad', [
            'filas' => $filas,
            'desde' => $desde,
            'hasta' => $hasta,
            'clientes' => $clientes,
            'clienteId' => $clienteId,
        ]);
    }

    public function evolucionCartera(Request $request)
    {
        $anio = (int) $request->input('anio', now()->year);
        $datos = $this->calcularEvolucionCartera($anio);

        return view('reportes.cartera', ['datos' => $datos, 'anio' => $anio]);
    }

    public function gastosOperativos(Request $request)
    {
        [$desde, $hasta] = $this->rangoFechas($request);
        $categoriaId = $request->input('categoria_gasto_id');
        $filas = $this->calcularGastosPorCategoria($desde, $hasta, $categoriaId);
        $categorias = CategoriaGasto::orderBy('nombre')->get();

        $gastosDetalle = !empty($categoriaId)
            ? $this->obtenerGastosDeCategoria($desde, $hasta, (int) $categoriaId)
            : null;

        return view('reportes.gastos-operativos', compact('filas', 'desde', 'hasta', 'categorias', 'categoriaId', 'gastosDetalle'));
    }

    public function gastosOperativosPdf(Request $request)
    {
        $request->validate(['chart_image' => ['required', 'string']]);

        [$desde, $hasta] = $this->rangoFechas($request);
        $categoriaId = $request->input('categoria_gasto_id');
        $filas = $this->calcularGastosPorCategoria($desde, $hasta, $categoriaId);

        $gastosDetalle = !empty($categoriaId)
            ? $this->obtenerGastosDeCategoria($desde, $hasta, (int) $categoriaId)
            : null;

        $pdf = Pdf::loadView('reportes.pdf.gastos-operativos', [
            'filas' => $filas,
            'desde' => $desde,
            'hasta' => $hasta,
            'chartImage' => $request->input('chart_image'),
            'gastosDetalle' => $gastosDetalle,
        ]);

        return $pdf->download('reporte-gastos-' . $desde->format('Ymd') . '-' . $hasta->format('Ymd') . '.pdf');
    }

    /**
     * Resuelve el periodo del reporte en tres modos posibles — rango de
     * fechas libre, mes, o año — según lo que venga en la query string.
     * Precedencia: año > mes > rango > default (mes actual). Compartido
     * por los reportes financiero y de gastos operativos.
     *
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    private function rangoFechas(Request $request): array
    {
        // Bug #4 fix: validate before parsing to prevent Carbon exceptions on malformed input
        $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
            'mes'   => ['nullable', 'date_format:Y-m'],
            'anio'  => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        if ($request->filled('anio')) {
            $anio = (int) $request->input('anio');

            return [
                \Carbon\Carbon::create($anio, 1, 1)->startOfDay(),
                \Carbon\Carbon::create($anio, 12, 31)->endOfDay(),
            ];
        }

        if ($request->filled('mes')) {
            $inicioMes = \Carbon\Carbon::createFromFormat('Y-m', $request->input('mes'))->startOfMonth();

            return [$inicioMes->copy()->startOfDay(), $inicioMes->copy()->endOfMonth()->endOfDay()];
        }

        $desde = $request->filled('desde')
            ? \Carbon\Carbon::parse($request->input('desde'))->startOfDay()
            : now()->startOfMonth();

        $hasta = $request->filled('hasta')
            ? \Carbon\Carbon::parse($request->input('hasta'))->endOfDay()
            : now()->endOfMonth();

        return [$desde, $hasta];
    }

    private function calcularFinanciero(\Carbon\Carbon $desde, \Carbon\Carbon $hasta): array
    {
        // Agrupado en PHP (no con DATE_FORMAT/strftime en SQL) para que la
        // consulta funcione igual en MySQL y SQLite.
        $ingresosPorMes = PagoCliente::pagados()->whereBetween('fecha_pago', [$desde, $hasta])
            ->get(['fecha_pago', 'monto'])
            ->groupBy(fn ($p) => $p->fecha_pago->format('Y-m'))
            ->map(fn ($grupo) => $grupo->sum('monto'));

        $gastosPorMes = GastoOperativo::whereBetween('fecha_gasto', [$desde, $hasta])
            ->get(['fecha_gasto', 'monto'])
            ->groupBy(fn ($g) => $g->fecha_gasto->format('Y-m'))
            ->map(fn ($grupo) => $grupo->sum('monto'));

        $periodos = collect();
        $cursor = $desde->copy()->startOfMonth();
        while ($cursor->lte($hasta)) {
            $periodos->push($cursor->format('Y-m'));
            $cursor->addMonth();
        }

        $labels = [];
        $ingresos = [];
        $gastos = [];

        foreach ($periodos as $periodo) {
            $labels[] = \Carbon\Carbon::createFromFormat('Y-m', $periodo)->translatedFormat('M Y');
            $ingresos[] = (float) ($ingresosPorMes[$periodo] ?? 0);
            $gastos[] = (float) ($gastosPorMes[$periodo] ?? 0);
        }

        $totalIngresos = array_sum($ingresos);
        $totalGastos = array_sum($gastos);

        return [
            'labels' => $labels,
            'ingresos' => $ingresos,
            'gastos' => $gastos,
            'total_ingresos' => $totalIngresos,
            'total_gastos' => $totalGastos,
            'utilidad' => $totalIngresos - $totalGastos,
        ];
    }

    private function calcularRentabilidad(\Carbon\Carbon $desde, \Carbon\Carbon $hasta, ?int $clienteId = null): array
    {
        // Bug #1 fix: use clients active DURING the period, not currently active clients.
        //
        // A client is "active during the period" if:
        //   (a) their last log event BEFORE $desde was alta/reactivacion (they were already active)
        //   OR
        //   (b) they had an alta/reactivacion event DURING the period (they joined mid-period)
        //
        // This ensures historical reports include clients who generated revenue/costs even
        // if they are now deactivated, and excludes clients who only joined after the period.

        // (a) Clients active at the start of the period
        $ultimoEventoAntes = DB::table('cliente_estatus_logs')
            ->where('fecha_evento', '<', $desde)
            ->select('cliente_id', DB::raw('MAX(id) as last_id'))
            ->groupBy('cliente_id');

        $activosAlInicio = DB::table('cliente_estatus_logs')
            ->joinSub($ultimoEventoAntes, 'ultima', fn ($j) => $j->on('cliente_estatus_logs.id', '=', 'ultima.last_id'))
            ->whereIn('cliente_estatus_logs.evento', [ClienteEstatusLog::EVENTO_ALTA, ClienteEstatusLog::EVENTO_REACTIVACION])
            ->pluck('cliente_estatus_logs.cliente_id');

        // (b) Clients who became active during the period
        $activadosDurante = ClienteEstatusLog::whereBetween('fecha_evento', [$desde, $hasta])
            ->whereIn('evento', [ClienteEstatusLog::EVENTO_ALTA, ClienteEstatusLog::EVENTO_REACTIVACION])
            ->pluck('cliente_id');

        $activeClientIds = $activosAlInicio->merge($activadosDurante)->unique()->values();

        $clientes = Cliente::whereIn('id', $activeClientIds)->with('user')->get();
        $totalClientesActivos = max($clientes->count(), 1);

        $gastosGenerales = (float) GastoOperativo::generales()
            ->whereBetween('fecha_gasto', [$desde, $hasta])
            ->sum('monto');

        $prorrateo = $gastosGenerales / $totalClientesActivos;

        // El prorrateo siempre se calcula sobre TODOS los clientes activos del
        // periodo (arriba). Filtrar a un cliente específico solo acota qué
        // filas se muestran, sin alterar esa base de cálculo.
        $clientesAMostrar = $clienteId
            ? $clientes->where('id', $clienteId)
            : $clientes;

        $filas = [];

        foreach ($clientesAMostrar as $cliente) {
            $ingresos = (float) PagoCliente::pagados()->where('cliente_id', $cliente->id)
                ->whereBetween('fecha_pago', [$desde, $hasta])
                ->sum('monto');

            $gastosDirectos = (float) GastoOperativo::delCliente($cliente->id)
                ->whereBetween('fecha_gasto', [$desde, $hasta])
                ->sum('monto');

            $filas[] = [
                'cliente' => $cliente,
                'ingresos' => $ingresos,
                'gastos_directos' => $gastosDirectos,
                'gastos_generales' => $prorrateo,
                'rentabilidad' => $ingresos - $gastosDirectos - $prorrateo,
            ];
        }

        usort($filas, fn ($a, $b) => $b['rentabilidad'] <=> $a['rentabilidad']);

        return $filas;
    }

    private function calcularEvolucionCartera(int $anio): array
    {
        $inicioAnio = \Carbon\Carbon::create($anio, 1, 1)->startOfDay();
        $finAnio    = \Carbon\Carbon::create($anio, 12, 31)->endOfDay();

        // Bug #2 fix: use the bitácora log table instead of querying users directly.
        // The old approach used User::where('estatus', DADO_DE_BAJA) which:
        //   - Counted deactivated operators/admins as client losses
        //   - Lost baja history when a client was restored (fecha_baja set to null)

        // Un cliente cuenta como "activo" mientras su ÚLTIMO evento sea uno de
        // estos tres — no solo alta. Suspensión y baja lo sacan del conteo;
        // reactivación (desde suspendido) y restauración (desde dado de baja)
        // lo regresan. Sin este criterio completo, un cliente suspendido a
        // mitad de año seguiría contando como activo en la gráfica.
        $eventosActivan   = [ClienteEstatusLog::EVENTO_ALTA, ClienteEstatusLog::EVENTO_REACTIVACION, ClienteEstatusLog::EVENTO_RESTAURACION];
        $eventosDesactivan = [ClienteEstatusLog::EVENTO_BAJA, ClienteEstatusLog::EVENTO_SUSPENSION];

        // Total de clientes activos al inicio del año: el último evento de
        // cada cliente antes de esa fecha debe ser uno "activador".
        $ultimoEventoAntesDeAnio = DB::table('cliente_estatus_logs')
            ->where('fecha_evento', '<', $inicioAnio)
            ->select('cliente_id', DB::raw('MAX(id) as last_id'))
            ->groupBy('cliente_id');

        $totalInicio = DB::table('cliente_estatus_logs')
            ->joinSub($ultimoEventoAntesDeAnio, 'ultima', fn ($j) => $j->on('cliente_estatus_logs.id', '=', 'ultima.last_id'))
            ->whereIn('cliente_estatus_logs.evento', $eventosActivan)
            ->count();

        // Todos los eventos del año, agrupados en PHP (no con MONTH() en SQL,
        // para que funcione igual en MySQL y SQLite — mismo criterio que FN.10).
        $eventosDelAnioLista = ClienteEstatusLog::whereBetween('fecha_evento', [$inicioAnio, $finAnio])
            ->with('cliente')
            ->orderByDesc('fecha_evento')
            ->get(['id', 'cliente_id', 'evento', 'fecha_evento']);

        $eventosDelAnio = $eventosDelAnioLista->groupBy(fn ($e) => $e->fecha_evento->month);

        // "Altas" y "bajas" que se muestran son específicamente registros
        // nuevos y cancelaciones (para saber cuántos clientes nuevos se
        // captaron/perdieron de verdad) — el acumulado de abajo, en cambio,
        // usa los 5 tipos de evento para reflejar quién estaba activo de
        // verdad mes a mes, incluyendo suspensiones y reactivaciones.
        $altasPorMes = $eventosDelAnio->map(
            fn ($grupo) => $grupo->where('evento', ClienteEstatusLog::EVENTO_ALTA)->count()
        );
        $bajasPorMes = $eventosDelAnio->map(
            fn ($grupo) => $grupo->where('evento', ClienteEstatusLog::EVENTO_BAJA)->count()
        );
        $deltaPorMes = $eventosDelAnio->map(
            fn ($grupo) => $grupo->sum(fn ($e) => in_array($e->evento, $eventosActivan) ? 1 : -1)
        );

        $labels = [];
        $acumulado = [];
        $altas = [];
        $bajas = [];
        $totalAcumulado = $totalInicio;

        for ($mes = 1; $mes <= 12; $mes++) {
            $totalAcumulado += (int) ($deltaPorMes[$mes] ?? 0);

            $labels[] = \Carbon\Carbon::create($anio, $mes, 1)->translatedFormat('M');
            $altas[] = (int) ($altasPorMes[$mes] ?? 0);
            $bajas[] = (int) ($bajasPorMes[$mes] ?? 0);
            $acumulado[] = $totalAcumulado;
        }

        $totalCierre = $totalAcumulado;
        $crecimiento = $totalInicio > 0
            ? (($totalCierre - $totalInicio) / $totalInicio) * 100
            : ($totalCierre > 0 ? 100 : 0);

        $etiquetasEvento = [
            ClienteEstatusLog::EVENTO_ALTA         => 'Alta',
            ClienteEstatusLog::EVENTO_BAJA         => 'Baja',
            ClienteEstatusLog::EVENTO_SUSPENSION   => 'Suspensión',
            ClienteEstatusLog::EVENTO_REACTIVACION => 'Reactivación',
            ClienteEstatusLog::EVENTO_RESTAURACION => 'Restauración',
        ];

        // Detalle individual de cada movimiento del año (qué cliente, qué
        // evento y cuándo), para el bloque plegable "Ver detalle de
        // clientes" — no altera el cálculo del acumulado de arriba.
        $eventosDetalle = $eventosDelAnioLista->map(fn ($e) => [
            'cliente'     => $e->cliente->nombre_negocio,
            'evento'      => $etiquetasEvento[$e->evento] ?? $e->evento,
            'evento_tipo' => $e->evento,
            'fecha'       => $e->fecha_evento->format('d/m/Y'),
        ])->values()->all();

        return [
            'labels'         => $labels,
            'altas'          => $altas,
            'bajas'          => $bajas,
            'acumulado'      => $acumulado,
            'total_inicio'   => $totalInicio,
            'total_cierre'   => $totalCierre,
            'crecimiento_pct' => round($crecimiento, 1),
            'eventos_detalle' => $eventosDetalle,
        ];
    }

    private function calcularGastosPorCategoria(\Carbon\Carbon $desde, \Carbon\Carbon $hasta, ?string $categoriaId): array
    {
        $query = GastoOperativo::with('categoria')
            ->whereBetween('fecha_gasto', [$desde, $hasta]);

        if (!empty($categoriaId)) {
            $query->where('categoria_gasto_id', $categoriaId);
        }

        $porCategoria = $query->get()->groupBy(fn ($g) => $g->categoria->nombre);

        $total = $porCategoria->flatten()->sum('monto');

        $filas = [];
        foreach ($porCategoria as $nombreCategoria => $gastos) {
            $subtotal = $gastos->sum('monto');
            $filas[] = [
                'categoria' => $nombreCategoria,
                'total' => (float) $subtotal,
                'porcentaje' => $total > 0 ? round(($subtotal / $total) * 100, 1) : 0,
            ];
        }

        usort($filas, fn ($a, $b) => $b['total'] <=> $a['total']);

        return $filas;
    }

    /**
     * Desglose de los gastos individuales que componen el total de una
     * categoría — para cuando el admin filtra el reporte por categoría y
     * quiere ver qué movimientos concretos la conforman.
     */
    private function obtenerGastosDeCategoria(\Carbon\Carbon $desde, \Carbon\Carbon $hasta, int $categoriaId): array
    {
        return GastoOperativo::with('cliente')
            ->where('categoria_gasto_id', $categoriaId)
            ->whereBetween('fecha_gasto', [$desde, $hasta])
            ->orderByDesc('fecha_gasto')
            ->get()
            ->map(fn ($gasto) => [
                'fecha'       => $gasto->fecha_gasto->format('d/m/Y'),
                'concepto'    => $gasto->concepto_gasto,
                'cliente'     => $gasto->cliente?->nombre_negocio,
                'forma_pago'  => ucfirst($gasto->forma_pago),
                'monto'       => (float) $gasto->monto,
            ])
            ->values()
            ->all();
    }
}
