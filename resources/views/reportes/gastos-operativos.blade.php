@extends('layouts.app')

@section('title', 'Reporte de Gastos Operativos')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Gastos por Categoría</span>
        </div>
    </div>
@endsection

@section('content')
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Gastos Operativos por Categoría</h1>
            <p class="page-subtitle">Distribución de gastos en el periodo seleccionado</p>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('reportes.gastos') }}" class="flex flex-wrap items-end gap-4">
                <div class="form-group mb-0">
                    <label for="desde" class="form-label">Desde</label>
                    <input type="date" id="desde" name="desde" class="form-input" value="{{ $desde->format('Y-m-d') }}">
                </div>
                <div class="form-group mb-0">
                    <label for="hasta" class="form-label">Hasta</label>
                    <input type="date" id="hasta" name="hasta" class="form-input" value="{{ $hasta->format('Y-m-d') }}">
                </div>
                <div class="form-group mb-0">
                    <label for="categoria_gasto_id" class="form-label">Categoría</label>
                    <select id="categoria_gasto_id" name="categoria_gasto_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected($categoriaId == $categoria->id)>{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>
    </div>

    @if(count($filas) > 0)
        @php
            $totalGastos = collect($filas)->sum('total');
            $categoriaPrincipal = collect($filas)->sortByDesc('total')->first();
            // Debe coincidir en orden con el arreglo backgroundColor del script de abajo.
            $colores = ['#7c3aed', '#14b8a6', '#f59e0b', '#dc3545', '#6366f1', '#0d9488', '#b45309', '#004085'];

            // Con una categoría filtrada, la gráfica distribuye los gastos
            // individuales que la componen en vez de una sola rebanada al
            // 100% (que era lo único que se podía mostrar agrupando por
            // categoría cuando ya solo queda una).
            if ($gastosDetalle !== null) {
                $chartTitulo = 'Desglose de gastos — ' . $filas[0]['categoria'];
                $chartEtiquetas = collect($gastosDetalle)->map(fn ($g) => $g['concepto'])->all();
                $chartValores = collect($gastosDetalle)->map(fn ($g) => $g['monto'])->all();
                $chartPorcentajes = collect($gastosDetalle)->map(
                    fn ($g) => $totalGastos > 0 ? round($g['monto'] / $totalGastos * 100, 1) : 0
                )->all();
            } else {
                $chartTitulo = 'Gastos Operativos por Categoría';
                $chartEtiquetas = array_column($filas, 'categoria');
                $chartValores = array_column($filas, 'total');
                $chartPorcentajes = array_column($filas, 'porcentaje');
            }
        @endphp

        <div class="flex justify-end gap-2 mb-4">
            <button type="button" class="btn btn-secondary btn-sm" onclick="descargarReporteComoPng('reporte-exportable', 'reporte-gastos.png')">Exportar PNG</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="exportarGraficaComoPdf(window.graficaGastos, 'formExportarPdfGastos')">Exportar PDF</button>
            <form id="formExportarPdfGastos" method="POST" action="{{ route('reportes.gastos.pdf') }}" style="display:none;">
                @csrf
                <input type="hidden" name="desde" value="{{ $desde->format('Y-m-d') }}">
                <input type="hidden" name="hasta" value="{{ $hasta->format('Y-m-d') }}">
                <input type="hidden" name="categoria_gasto_id" value="{{ $categoriaId }}">
                <input type="hidden" name="chart_image">
            </form>
        </div>

        <div id="reporte-exportable">
            <div class="report-stats-grid">
                <x-report-stat color="red" :value="'$' . number_format($totalGastos, 2)" label="Gastos totales">
                    <x-slot:icon>
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </x-slot:icon>
                </x-report-stat>

                <x-report-stat color="blue" :value="count($filas)" label="Categorías con gasto">
                    <x-slot:icon>
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 8V3a2 2 0 012-2z"></path></svg>
                    </x-slot:icon>
                </x-report-stat>

                <x-report-stat color="amber" :value="$categoriaPrincipal['categoria']" :label="'Categoría principal (' . $categoriaPrincipal['porcentaje'] . '%)'">
                    <x-slot:icon>
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    </x-slot:icon>
                </x-report-stat>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="card report-chart-card">
                    <div class="card-header">
                        <h3 class="card-title">Distribución</h3>
                    </div>
                    <div class="card-body">
                        <div class="flex flex-wrap items-center gap-6">
                            <div style="flex: 1 1 240px; min-width: 240px; height:380px;">
                                <canvas id="graficaGastos"></canvas>
                            </div>
                            <ul class="chart-legend">
                                @foreach($chartEtiquetas as $i => $etiqueta)
                                    <li class="chart-legend-item">
                                        <span class="chart-legend-swatch" style="background: {{ $colores[$i % count($colores)] }};"></span>
                                        <span class="chart-legend-label">{{ $etiqueta }}</span>
                                        <span class="chart-legend-value">{{ $chartPorcentajes[$i] }}%</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detalle por Categoría</h3>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <div class="table-container">
                            <table class="table report-detail-table">
                                <thead>
                                    <tr><th>Categoría</th><th>Total</th><th>Porcentaje</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($filas as $fila)
                                        <tr>
                                            <td class="font-medium">{{ $fila['categoria'] }}</td>
                                            <td>${{ number_format($fila['total'], 2) }}</td>
                                            <td><span class="badge badge-primary">{{ $fila['porcentaje'] }}%</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @if($gastosDetalle !== null)
                <div class="card mt-6">
                    <div class="card-header">
                        <h3 class="card-title">Desglose de gastos — {{ $filas[0]['categoria'] }}</h3>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <div class="table-container">
                            <table class="table report-detail-table">
                                <thead>
                                    <tr><th>Fecha</th><th>Concepto</th><th>Cliente</th><th>Forma de pago</th><th>Monto</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($gastosDetalle as $gasto)
                                        <tr>
                                            <td>{{ $gasto['fecha'] }}</td>
                                            <td class="font-medium">{{ $gasto['concepto'] }}</td>
                                            <td>{{ $gasto['cliente'] ?? '—' }}</td>
                                            <td>{{ $gasto['forma_pago'] }}</td>
                                            <td>${{ number_format($gasto['monto'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <h3 class="empty-state-title">Sin gastos</h3>
                    <p class="empty-state-description">No hay gastos registrados en el periodo seleccionado.</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('graficaGastos');
        if (!ctx) return;
        window.graficaGastos = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: @json($chartEtiquetas ?? []),
                datasets: [{
                    data: @json($chartValores ?? []),
                    backgroundColor: ['#7c3aed', '#14b8a6', '#f59e0b', '#dc3545', '#6366f1', '#0d9488', '#b45309', '#004085'],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: @json($chartTitulo ?? 'Gastos Operativos por Categoría'), font: { size: 16 } },
                    subtitle: { display: true, text: 'Periodo: {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}', padding: { bottom: 12 } },
                    legend: { display: false },
                },
            },
        });
    });
</script>
@endsection
