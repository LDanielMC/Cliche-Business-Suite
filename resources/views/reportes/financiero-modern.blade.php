@extends('layouts.modern')

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
<div class="reporte-financiero-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Reporte Financiero General</h1>
            <p class="page-subtitle">Análisis completo del rendimiento financiero</p>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-secondary" onclick="exportReport()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Exportar Reporte
            </button>
            <button type="button" class="btn btn-primary" onclick="refreshData()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Actualizar Datos
            </button>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="form-group">
                    <label for="periodo" class="form-label">Período</label>
                    <select id="periodo" class="form-control" onchange="changePeriod()">
                        <option value="month">Este Mes</option>
                        <option value="quarter">Este Trimestre</option>
                        <option value="year" selected>Este Año</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>

                <div class="form-group" id="fecha_inicio_group" style="display: none;">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" id="fecha_inicio" class="form-control">
                </div>

                <div class="form-group" id="fecha_fin_group" style="display: none;">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" id="fecha_fin" class="form-control">
                </div>

                <div class="form-group">
                    <label for="tipo_reporte" class="form-label">Tipo de Reporte</label>
                    <select id="tipo_reporte" class="form-control">
                        <option value="completo">Reporte Completo</option>
                        <option value="ingresos">Solo Ingresos</option>
                        <option value="gastos">Solo Gastos</option>
                        <option value="rentabilidad">Análisis de Rentabilidad</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">Ingresos Totales</div>
                        <div class="text-2xl font-bold text-green-600">${{ number_format($ingresosTotales ?? 0, 2) }}</div>
                        <div class="text-sm text-gray-500 mt-1">
                            @if(isset($ingresosCrecimiento) && $ingresosCrecimiento > 0)
                                <span class="text-green-600">↑ {{ $ingresosCrecimiento }}%</span>
                            @else
                                <span class="text-red-600">↓ {{ abs($ingresosCrecimiento ?? 0) }}%</span>
                            @endif
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">Gastos Totales</div>
                        <div class="text-2xl font-bold text-red-600">${{ number_format($gastosTotales ?? 0, 2) }}</div>
                        <div class="text-sm text-gray-500 mt-1">
                            @if(isset($gastosCrecimiento) && $gastosCrecimiento < 0)
                                <span class="text-green-600">↓ {{ abs($gastosCrecimiento) }}%</span>
                            @else
                                <span class="text-red-600">↑ {{ $gastosCrecimiento ?? 0 }}%</span>
                            @endif
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">Utilidad Neta</div>
                        <div class="text-2xl font-bold {{ ($utilidadNeta ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ${{ number_format($utilidadNeta ?? 0, 2) }}
                        </div>
                        <div class="text-sm text-gray-500 mt-1">
                            {{ round(($utilidadNeta ?? 0) / max($ingresosTotales ?? 1, 1) * 100, 1) }}% margen
                        </div>
                    </div>
                    <div class="w-12 h-12 {{ ($utilidadNeta ?? 0) >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">Clientes Activos</div>
                        <div class="text-2xl font-bold">{{ $clientesActivos ?? 0 }}</div>
                        <div class="text-sm text-gray-500 mt-1">
                            @if(isset($clientesNuevos) && $clientesNuevos > 0)
                                <span class="text-green-600">+{{ $clientesNuevos }} nuevos</span>
                            @endif
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Revenue Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Evolución de Ingresos</h3>
                <div class="card-actions">
                    <select class="form-control form-control-sm" onchange="updateRevenueChart(this.value)">
                        <option value="daily">Diario</option>
                        <option value="weekly" selected>Semanal</option>
                        <option value="monthly">Mensual</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Expense Breakdown -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Desglose de Gastos</h3>
                <div class="card-actions">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleExpenseView()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="expenseChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Clients -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Clientes Principales</h3>
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary btn-sm">Ver Todos</a>
            </div>
            <div class="card-body">
                @if(isset($topClientes) && $topClientes->count() > 0)
                    <div class="space-y-4">
                        @foreach($topClientes->take(5) as $cliente)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-blue-600">
                                            {{ strtoupper(substr($cliente->nombre ?? $cliente->user->name ?? 'U', 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium">{{ $cliente->nombre ?? $cliente->user->name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $cliente->servicio_contratado ?? 'Servicio estándar' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold">${{ number_format($cliente->total_ingresos ?? 0, 2) }}</div>
                                    <div class="text-sm text-gray-600">{{ $cliente->total_sesiones ?? 0 }} sesiones</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <p class="text-gray-600">No hay datos disponibles</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Transacciones Recientes</h3>
                <a href="#" class="btn btn-secondary btn-sm">Ver Todas</a>
            </div>
            <div class="card-body">
                @if(isset($transaccionesRecientes) && $transaccionesRecientes->count() > 0)
                    <div class="space-y-3">
                        @foreach($transaccionesRecientes->take(5) as $transaccion)
                            <div class="flex items-center justify-between p-3 border-l-4 {{ $transaccion->tipo === 'ingreso' ? 'border-green-500' : 'border-red-500' }} bg-gray-50 rounded">
                                <div>
                                    <h4 class="font-medium">{{ $transaccion->descripcion }}</h4>
                                    <p class="text-sm text-gray-600">{{ $transaccion->fecha->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold {{ $transaccion->tipo === 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaccion->tipo === 'ingreso' ? '+' : '-' }}${{ number_format($transaccion->monto, 2) }}
                                    </div>
                                    <span class="text-xs text-gray-600">{{ $transaccion->categoria ?? 'General' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <p class="text-gray-600">No hay transacciones recientes</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Financial report functionality
let revenueChart = null;
let expenseChart = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setupEventListeners();
});

function initializeCharts() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'Ingresos',
                data: [12000, 15000, 18000, 14000, 22000, 25000, 28000, 26000, 30000, 32000, 35000, 38000],
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Expense Chart
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    expenseChart = new Chart(expenseCtx, {
        type: 'doughnut',
        data: {
            labels: ['Operación', 'Marketing', 'Personal', 'Tecnología', 'Otros'],
            datasets: [{
                data: [35, 25, 20, 15, 5],
                backgroundColor: [
                    'rgb(239, 68, 68)',
                    'rgb(245, 158, 11)',
                    'rgb(59, 130, 246)',
                    'rgb(139, 92, 246)',
                    'rgb(156, 163, 175)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function setupEventListeners() {
    // Period change handler
    document.getElementById('periodo').addEventListener('change', changePeriod);
}

function changePeriod() {
    const periodo = document.getElementById('periodo').value;
    const fechaInicioGroup = document.getElementById('fecha_inicio_group');
    const fechaFinGroup = document.getElementById('fecha_fin_group');

    if (periodo === 'custom') {
        fechaInicioGroup.style.display = 'block';
        fechaFinGroup.style.display = 'block';
    } else {
        fechaInicioGroup.style.display = 'none';
        fechaFinGroup.style.display = 'none';
    }

    refreshData();
}

function updateRevenueChart(period) {
    // Update chart based on period
    let labels, data;
    
    switch(period) {
        case 'daily':
            labels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
            data = [800, 1200, 900, 1500, 1800, 2000, 1600];
            break;
        case 'monthly':
            labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            data = [12000, 15000, 18000, 14000, 22000, 25000, 28000, 26000, 30000, 32000, 35000, 38000];
            break;
        default: // weekly
            labels = ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'];
            data = [8000, 9500, 11000, 12500];
    }

    revenueChart.data.labels = labels;
    revenueChart.data.datasets[0].data = data;
    revenueChart.update();
}

function toggleExpenseView() {
    // Toggle between pie and bar chart
    expenseChart.config.type = expenseChart.config.type === 'doughnut' ? 'bar' : 'doughnut';
    expenseChart.update();
}

function refreshData() {
    window.uxSystem.showLoading('Actualizando datos...');
    
    // Simulate data refresh
    setTimeout(() => {
        window.uxSystem.hideLoading();
        window.uxSystem.showToast('Datos actualizados correctamente', 'success');
    }, 1500);
}

function exportReport() {
    window.uxSystem.showLoading('Generando reporte...');
    
    // Simulate report generation
    setTimeout(() => {
        window.uxSystem.hideLoading();
        window.uxSystem.showToast('Reporte descargado', 'success');
    }, 2000);
}
</script>
@endsection
