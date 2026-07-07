<?php

namespace App\Http\Controllers;

use App\Models\CategoriaGasto;
use App\Models\Cliente;
use App\Models\GastoOperativo;
use App\Models\PagoCliente;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

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
        [$desde, $hasta] = $this->rangoFechas($request);
        $filas = $this->calcularRentabilidad($desde, $hasta);

        return view('reportes.rentabilidad', ['filas' => $filas, 'desde' => $desde, 'hasta' => $hasta]);
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
        $categorias = CategoriaGasto::orderBy('nombre_categoria')->get();

        return view('reportes.gastos-operativos', compact('filas', 'desde', 'hasta', 'categorias', 'categoriaId'));
    }

    public function gastosOperativosPdf(Request $request)
    {
        $request->validate(['chart_image' => ['required', 'string']]);

        [$desde, $hasta] = $this->rangoFechas($request);
        $categoriaId = $request->input('categoria_gasto_id');
        $filas = $this->calcularGastosPorCategoria($desde, $hasta, $categoriaId);

        $pdf = Pdf::loadView('reportes.pdf.gastos-operativos', [
            'filas' => $filas,
            'desde' => $desde,
            'hasta' => $hasta,
            'chartImage' => $request->input('chart_image'),
        ]);

        return $pdf->download('reporte-gastos-' . $desde->format('Ymd') . '-' . $hasta->format('Ymd') . '.pdf');
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    private function rangoFechas(Request $request): array
    {
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
        $ingresosPorMes = PagoCliente::pagados()->whereBetween('fecha_pago', [$desde, $hasta])
            ->selectRaw("DATE_FORMAT(fecha_pago, '%Y-%m') as periodo, SUM(monto) as total")
            ->groupBy('periodo')->pluck('total', 'periodo');

        $gastosPorMes = GastoOperativo::whereBetween('fecha_gasto', [$desde, $hasta])
            ->selectRaw("DATE_FORMAT(fecha_gasto, '%Y-%m') as periodo, SUM(monto) as total")
            ->groupBy('periodo')->pluck('total', 'periodo');

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

    private function calcularRentabilidad(\Carbon\Carbon $desde, \Carbon\Carbon $hasta): array
    {
        $clientes = Cliente::activos()->with('user')->get();
        $totalClientesActivos = max($clientes->count(), 1);

        $gastosGenerales = (float) GastoOperativo::generales()
            ->whereBetween('fecha_gasto', [$desde, $hasta])
            ->sum('monto');

        $prorrateo = $gastosGenerales / $totalClientesActivos;

        $filas = [];

        foreach ($clientes as $cliente) {
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
        $finAnio = \Carbon\Carbon::create($anio, 12, 31)->endOfDay();

        $totalInicio = Cliente::where('fecha_registro', '<', $inicioAnio)
            ->whereHas('user', function ($q) use ($inicioAnio) {
                $q->where(function ($q2) use ($inicioAnio) {
                    $q2->whereNull('fecha_baja')->orWhere('fecha_baja', '>=', $inicioAnio);
                });
            })->count();

        $altasPorMes = Cliente::whereBetween('fecha_registro', [$inicioAnio, $finAnio])
            ->selectRaw('MONTH(fecha_registro) as mes, COUNT(*) as total')
            ->groupBy('mes')->pluck('total', 'mes');

        $bajasPorMes = User::where('estatus', User::ESTATUS_DADO_DE_BAJA)
            ->whereBetween('fecha_baja', [$inicioAnio, $finAnio])
            ->selectRaw('MONTH(fecha_baja) as mes, COUNT(*) as total')
            ->groupBy('mes')->pluck('total', 'mes');

        $labels = [];
        $acumulado = [];
        $altas = [];
        $bajas = [];
        $totalAcumulado = $totalInicio;

        for ($mes = 1; $mes <= 12; $mes++) {
            $altaMes = (int) ($altasPorMes[$mes] ?? 0);
            $bajaMes = (int) ($bajasPorMes[$mes] ?? 0);
            $totalAcumulado += $altaMes - $bajaMes;

            $labels[] = \Carbon\Carbon::create($anio, $mes, 1)->translatedFormat('M');
            $altas[] = $altaMes;
            $bajas[] = $bajaMes;
            $acumulado[] = $totalAcumulado;
        }

        $totalCierre = $totalAcumulado;
        $crecimiento = $totalInicio > 0 ? (($totalCierre - $totalInicio) / $totalInicio) * 100 : ($totalCierre > 0 ? 100 : 0);

        return [
            'labels' => $labels,
            'altas' => $altas,
            'bajas' => $bajas,
            'acumulado' => $acumulado,
            'total_inicio' => $totalInicio,
            'total_cierre' => $totalCierre,
            'crecimiento_pct' => round($crecimiento, 1),
        ];
    }

    private function calcularGastosPorCategoria(\Carbon\Carbon $desde, \Carbon\Carbon $hasta, ?string $categoriaId): array
    {
        $query = GastoOperativo::with('categoria')
            ->whereBetween('fecha_gasto', [$desde, $hasta]);

        if (!empty($categoriaId)) {
            $query->where('categoria_gasto_id', $categoriaId);
        }

        $porCategoria = $query->get()->groupBy(fn ($g) => $g->categoria->nombre_categoria);

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
}
