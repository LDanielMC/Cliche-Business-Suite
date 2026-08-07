@extends('layouts.app')

@section('title', 'Evolución de Cartera de Clientes')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Evolución de Cartera</span>
        </div>
    </div>
@endsection

@section('content')
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Evolución de Cartera de Clientes</h1>
            <p class="page-subtitle">Altas, bajas y crecimiento anual de la cartera</p>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('reportes.cartera') }}" class="flex flex-wrap items-end gap-4">
                <div class="form-group mb-0">
                    <label for="anio" class="form-label">Año</label>
                    <input type="number" id="anio" name="anio" class="form-input" value="{{ $anio }}">
                </div>
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>
    </div>

    <div class="flex justify-end mb-4">
        <button type="button" class="btn btn-secondary btn-sm" onclick="descargarReporteComoPng('reporte-exportable', 'reporte-cartera.png')">Exportar PNG</button>
    </div>

    <div id="reporte-exportable">
        <div class="report-stats-grid">
            <x-report-stat color="blue" :value="$datos['total_inicio']" label="Clientes al inicio">
                <x-slot:icon>
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </x-slot:icon>
            </x-report-stat>

            <x-report-stat color="purple" :value="$datos['total_cierre']" label="Clientes al cierre">
                <x-slot:icon>
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </x-slot:icon>
            </x-report-stat>

            <x-report-stat :color="$datos['crecimiento_pct'] >= 0 ? 'green' : 'red'" :value="$datos['crecimiento_pct'] . '%'" label="Crecimiento anual">
                <x-slot:icon>
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </x-slot:icon>
            </x-report-stat>
        </div>

        <div class="card report-chart-card mb-6">
            <div class="card-header">
                <h3 class="card-title">Clientes Acumulados</h3>
            </div>
            <div class="card-body">
                <div style="height:380px;">
                    <canvas id="graficaCartera"></canvas>
                </div>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">Altas y bajas por mes</h3>
            </div>
            <div class="card-body" style="padding:0;">
                <div class="table-container">
                    <table class="table report-detail-table">
                        <thead>
                            <tr><th>Mes</th><th>Altas</th><th>Bajas</th><th>Clientes activos</th></tr>
                        </thead>
                        <tbody>
                            @foreach($datos['labels'] as $i => $label)
                                <tr>
                                    <td class="font-medium">{{ $label }}</td>
                                    <td class="text-green-600">+{{ $datos['altas'][$i] }}</td>
                                    <td class="text-red-600">-{{ $datos['bajas'][$i] }}</td>
                                    <td class="font-semibold">{{ $datos['acumulado'][$i] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card" x-data='{
                abierto: false,
                busqueda: "",
                filtroEvento: "",
                eventos: @json($datos["eventos_detalle"]),
                get filtrados() {
                    return this.eventos.filter(e =>
                        (this.filtroEvento === "" || e.evento_tipo === this.filtroEvento) &&
                        (this.busqueda === "" || e.cliente.toLowerCase().includes(this.busqueda.toLowerCase()))
                    );
                },
            }'>
            <div class="card-header flex items-center justify-between" style="cursor:pointer;" @click="abierto = !abierto">
                <h3 class="card-title">Detalle de clientes ({{ count($datos['eventos_detalle']) }} movimientos)</h3>
                <button type="button" class="btn btn-secondary btn-sm" @click.stop="abierto = !abierto">
                    <span x-text="abierto ? 'Ocultar' : 'Ver detalle'"></span>
                </button>
            </div>
            <div x-show="abierto" x-cloak x-transition>
                @if(count($datos['eventos_detalle']) > 0)
                    <div class="card-body" style="padding-bottom:0;">
                        <div class="flex flex-wrap items-center gap-3">
                            <input type="text" class="form-input" style="max-width:220px;" placeholder="Buscar cliente…" x-model="busqueda">
                            <select class="form-select" style="max-width:200px;" x-model="filtroEvento">
                                <option value="">Todos los eventos</option>
                                <option value="alta">Alta</option>
                                <option value="baja">Baja</option>
                                <option value="suspension">Suspensión</option>
                                <option value="reactivacion">Reactivación</option>
                                <option value="restauracion">Restauración</option>
                            </select>
                            <span class="text-sm" style="color: var(--color-text-secondary, #666);" x-text="filtrados.length + ' de ' + eventos.length"></span>
                        </div>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <div class="table-container">
                            <table class="table report-detail-table">
                                <thead>
                                    <tr><th>Cliente</th><th>Evento</th><th>Fecha</th></tr>
                                </thead>
                                <tbody>
                                    <template x-for="evento in filtrados" :key="evento.cliente + evento.evento_tipo + evento.fecha">
                                        <tr>
                                            <td class="font-medium" x-text="evento.cliente"></td>
                                            <td>
                                                <span class="badge" :class="{
                                                    'badge-success': evento.evento_tipo === 'alta',
                                                    'badge-error': evento.evento_tipo === 'baja',
                                                    'badge-warning': evento.evento_tipo === 'suspension',
                                                    'badge-primary': evento.evento_tipo === 'reactivacion',
                                                    'badge-gray': evento.evento_tipo === 'restauracion',
                                                }" x-text="evento.evento"></span>
                                            </td>
                                            <td x-text="evento.fecha"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="filtrados.length === 0">
                                        <td colspan="3" class="text-center" style="color: var(--color-text-secondary, #666);">Sin resultados para ese filtro.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="card-body">
                        <div class="empty-state">
                            <p class="empty-state-description">No hubo movimientos de clientes en {{ $anio }}.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('graficaCartera');
        window.graficaCartera = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($datos['labels']),
                datasets: [{
                    label: 'Clientes acumulados',
                    data: @json($datos['acumulado']),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Evolución de Cartera de Clientes', font: { size: 16 } },
                    subtitle: { display: true, text: 'Año {{ $anio }}', padding: { bottom: 12 } },
                    legend: { display: false },
                },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    });
</script>
@endsection
