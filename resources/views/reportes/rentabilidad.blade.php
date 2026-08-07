@extends('layouts.app')

@section('title', 'Reporte de Clientes Más Rentables')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Clientes Rentables</span>
        </div>
    </div>
@endsection

@section('content')
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Clientes Más Rentables</h1>
            <p class="page-subtitle">Rentabilidad por cliente en el periodo seleccionado</p>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('reportes.rentabilidad') }}" class="flex flex-wrap items-end gap-4">
                <div class="form-group mb-0">
                    <label for="desde" class="form-label">Desde</label>
                    <input type="date" id="desde" name="desde" class="form-input" value="{{ $desde->format('Y-m-d') }}">
                </div>
                <div class="form-group mb-0">
                    <label for="hasta" class="form-label">Hasta</label>
                    <input type="date" id="hasta" name="hasta" class="form-input" value="{{ $hasta->format('Y-m-d') }}">
                </div>
                <div class="form-group mb-0">
                    <label for="cliente_id" class="form-label">Cliente</label>
                    <select id="cliente_id" name="cliente_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" @selected($clienteId === $cliente->id)>{{ $cliente->nombre_negocio }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filtrar</button>
                @if($clienteId)
                    <a href="{{ route('reportes.rentabilidad', ['desde' => $desde->format('Y-m-d'), 'hasta' => $hasta->format('Y-m-d')]) }}" class="btn btn-secondary">Ver todos</a>
                @endif
            </form>
        </div>
    </div>

    @if(count($filas) > 0)
        @php
            $totalIngresos = collect($filas)->sum('ingresos');
            $totalGastosDirectos = collect($filas)->sum('gastos_directos');
            $totalRentabilidad = collect($filas)->sum('rentabilidad');
            $masRentable = collect($filas)->sortByDesc('rentabilidad')->first();
        @endphp

        <div class="flex justify-end mb-4">
            <button type="button" class="btn btn-secondary btn-sm" onclick="descargarReporteComoPng('reporte-exportable', 'reporte-rentabilidad.png')">Exportar PNG</button>
        </div>

        <div id="reporte-exportable">
            <div class="report-stats-grid">
                <x-report-stat color="green" :value="'$' . number_format($totalIngresos, 2)" label="Ingresos totales">
                    <x-slot:icon>
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </x-slot:icon>
                </x-report-stat>

                <x-report-stat color="red" :value="'$' . number_format($totalGastosDirectos, 2)" label="Gastos directos">
                    <x-slot:icon>
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </x-slot:icon>
                </x-report-stat>

                <x-report-stat :color="$totalRentabilidad >= 0 ? 'blue' : 'red'" :value="'$' . number_format($totalRentabilidad, 2)" label="Rentabilidad neta">
                    <x-slot:icon>
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </x-slot:icon>
                </x-report-stat>

                <x-report-stat color="amber" :value="$masRentable['cliente']->nombre_negocio" label="Cliente más rentable">
                    <x-slot:icon>
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </x-slot:icon>
                </x-report-stat>
            </div>

            <div class="card report-chart-card mb-6">
                <div class="card-header">
                    <h3 class="card-title">Rentabilidad por Cliente</h3>
                </div>
                <div class="card-body">
                    <div style="height:380px;">
                        <canvas id="graficaRentabilidad"></canvas>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalle por Cliente</h3>
                </div>
                <div class="card-body" style="padding:0;">
                    <div class="table-container">
                        <table class="table report-detail-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Ingresos</th>
                                    <th>Gastos Directos</th>
                                    <th>Gastos Generales (prorrateo)</th>
                                    <th>Rentabilidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($filas as $fila)
                                    <tr>
                                        <td class="font-medium">{{ $fila['cliente']->nombre_negocio }}</td>
                                        <td>${{ number_format($fila['ingresos'], 2) }}</td>
                                        <td>${{ number_format($fila['gastos_directos'], 2) }}</td>
                                        <td>${{ number_format($fila['gastos_generales'], 2) }}</td>
                                        <td class="font-semibold {{ $fila['rentabilidad'] >= 0 ? 'text-green-600' : 'text-red-600' }}">${{ number_format($fila['rentabilidad'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <h3 class="empty-state-title">Sin datos</h3>
                    <p class="empty-state-description">
                        {{ $clienteId ? 'Ese cliente no estuvo activo durante el periodo seleccionado.' : 'No hay clientes activos para calcular rentabilidad en ese periodo.' }}
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('graficaRentabilidad');
        if (!ctx) return;
        const valores = @json(array_map(fn($f) => $f['rentabilidad'], $filas));
        window.graficaRentabilidad = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json(array_map(fn($f) => $f['cliente']->nombre_negocio, $filas)),
                datasets: [{
                    label: 'Rentabilidad',
                    data: valores,
                    backgroundColor: valores.map(v => v >= 0 ? '#7c3aed' : '#dc2626'),
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Clientes Más Rentables', font: { size: 16 } },
                    subtitle: { display: true, text: 'Periodo: {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}', padding: { bottom: 12 } },
                    legend: { display: false },
                },
            },
        });
    });
</script>
@endsection
