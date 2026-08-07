@extends('layouts.app')

@section('title', 'Reporte Financiero General')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Reporte Financiero</span>
        </div>
    </div>
@endsection

@section('content')
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Reporte Financiero General</h1>
            <p class="page-subtitle">Ingresos, gastos y utilidad del periodo</p>
        </div>
    </div>

    <div class="card mb-6" x-data="{ modo: '{{ request()->filled('anio') ? 'anio' : (request()->filled('mes') ? 'mes' : 'rango') }}' }">
        <div class="card-body">
            <div class="flex gap-2 mb-4">
                <button type="button" class="btn btn-sm" :class="modo === 'rango' ? 'btn-primary' : 'btn-secondary'" @click="modo = 'rango'">Rango de fechas</button>
                <button type="button" class="btn btn-sm" :class="modo === 'mes' ? 'btn-primary' : 'btn-secondary'" @click="modo = 'mes'">Mes</button>
                <button type="button" class="btn btn-sm" :class="modo === 'anio' ? 'btn-primary' : 'btn-secondary'" @click="modo = 'anio'">Año</button>
            </div>

            <form method="GET" action="{{ route('reportes.financiero') }}" class="flex flex-wrap items-end gap-4">
                <div class="form-group mb-0" x-show="modo === 'rango'">
                    <label for="desde" class="form-label">Desde</label>
                    <input type="date" id="desde" name="desde" class="form-input" value="{{ $desde->format('Y-m-d') }}" :disabled="modo !== 'rango'">
                </div>
                <div class="form-group mb-0" x-show="modo === 'rango'">
                    <label for="hasta" class="form-label">Hasta</label>
                    <input type="date" id="hasta" name="hasta" class="form-input" value="{{ $hasta->format('Y-m-d') }}" :disabled="modo !== 'rango'">
                </div>

                <div class="form-group mb-0" x-show="modo === 'mes'" x-cloak>
                    <label for="mes" class="form-label">Mes</label>
                    <input type="month" id="mes" name="mes" class="form-input"
                           value="{{ request('mes', $desde->format('Y-m')) }}" :disabled="modo !== 'mes'">
                </div>

                <div class="form-group mb-0" x-show="modo === 'anio'" x-cloak>
                    <label for="anio" class="form-label">Año</label>
                    <select id="anio" name="anio" class="form-select" :disabled="modo !== 'anio'">
                        @php $anioSeleccionado = (int) request('anio', $desde->year); @endphp
                        @for($a = now()->year; $a >= now()->year - 5; $a--)
                            <option value="{{ $a }}" @selected($a === $anioSeleccionado)>{{ $a }}</option>
                        @endfor
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>
    </div>

    <div class="flex justify-end gap-2 mb-4">
        <button type="button" class="btn btn-secondary btn-sm" onclick="descargarReporteComoPng('reporte-exportable', 'reporte-financiero.png')">Exportar PNG</button>
        <button type="button" class="btn btn-primary btn-sm" onclick="exportarGraficaComoPdf(window.graficaFinanciero, 'formExportarPdf')">Exportar PDF</button>
        <form id="formExportarPdf" method="POST" action="{{ route('reportes.financiero.pdf') }}" style="display:none;">
            @csrf
            <input type="hidden" name="desde" value="{{ $desde->format('Y-m-d') }}">
            <input type="hidden" name="hasta" value="{{ $hasta->format('Y-m-d') }}">
            <input type="hidden" name="chart_image">
        </form>
    </div>

    <div id="reporte-exportable">
        <div class="report-stats-grid">
            <x-report-stat color="green" :value="'$' . number_format($datos['total_ingresos'], 2)" label="Ingresos">
                <x-slot:icon>
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </x-slot:icon>
            </x-report-stat>

            <x-report-stat color="red" :value="'$' . number_format($datos['total_gastos'], 2)" label="Gastos">
                <x-slot:icon>
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </x-slot:icon>
            </x-report-stat>

            <x-report-stat :color="$datos['utilidad'] >= 0 ? 'blue' : 'red'" :value="'$' . number_format($datos['utilidad'], 2)" label="Utilidad">
                <x-slot:icon>
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </x-slot:icon>
            </x-report-stat>
        </div>

        <div class="card report-chart-card mb-6">
            <div class="card-header">
                <h3 class="card-title">Ingresos vs Gastos</h3>
            </div>
            <div class="card-body">
                <div style="height:380px;">
                    <canvas id="graficaFinanciero"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detalle por mes</h3>
            </div>
            <div class="card-body" style="padding:0;">
                <div class="table-container">
                    <table class="table report-detail-table">
                        <thead>
                            <tr><th>Periodo</th><th>Ingresos</th><th>Gastos</th><th>Utilidad</th></tr>
                        </thead>
                        <tbody>
                            @foreach($datos['labels'] as $i => $label)
                                @php $utilidadMes = $datos['ingresos'][$i] - $datos['gastos'][$i]; @endphp
                                <tr>
                                    <td class="font-medium">{{ $label }}</td>
                                    <td>${{ number_format($datos['ingresos'][$i], 2) }}</td>
                                    <td>${{ number_format($datos['gastos'][$i], 2) }}</td>
                                    <td class="{{ $utilidadMes >= 0 ? 'text-green-600' : 'text-red-600' }}">${{ number_format($utilidadMes, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('graficaFinanciero');
        window.graficaFinanciero = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($datos['labels']),
                datasets: [
                    { label: 'Ingresos', data: @json($datos['ingresos']), backgroundColor: '#14b8a6', borderRadius: 4 },
                    { label: 'Gastos', data: @json($datos['gastos']), backgroundColor: '#dc3545', borderRadius: 4 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Reporte Financiero General', font: { size: 16 } },
                    subtitle: { display: true, text: 'Periodo: {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}', padding: { bottom: 12 } },
                },
            },
        });
    });
</script>
@endsection
