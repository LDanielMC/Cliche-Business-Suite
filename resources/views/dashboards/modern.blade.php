@extends('layouts.modern')

@section('title', 'Dashboard')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
        </div>
    </div>
@endsection

@section('content')
<div class="dashboard-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Bienvenido de vuelta, {{ auth()->user()->name }}</p>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-secondary" onclick="window.uxSystem.showKeyboardShortcuts()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Atajos
            </button>
            <button type="button" class="btn btn-primary" onclick="refreshDashboard()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Actualizar
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon stat-icon-blue">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="stat-trend stat-trend-up">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7 14l5-5 5 5z"/>
                    </svg>
                    <span>12%</span>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $totalClientes ?? 0 }}</div>
                <div class="stat-label">Clientes Totales</div>
            </div>
            <div class="stat-footer">
                <span class="stat-period">vs. mes anterior</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon stat-icon-green">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="stat-trend stat-trend-up">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7 14l5-5 5 5z"/>
                    </svg>
                    <span>8%</span>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-value">${{ number_format($ingresosMes ?? 0, 0) }}</div>
                <div class="stat-label">Ingresos del Mes</div>
            </div>
            <div class="stat-footer">
                <span class="stat-period">vs. mes anterior</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon stat-icon-orange">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="stat-trend stat-trend-down">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7 10l5 5 5-5z"/>
                    </svg>
                    <span>3%</span>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $gastosMes ?? 0 }}</div>
                <div class="stat-label">Gastos del Mes</div>
            </div>
            <div class="stat-footer">
                <span class="stat-period">vs. mes anterior</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon stat-icon-purple">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="stat-trend stat-trend-up">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7 14l5-5 5 5z"/>
                    </svg>
                    <span>15%</span>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $aprobacionesPendientes ?? 0 }}</div>
                <div class="stat-label">Aprobaciones Pendientes</div>
            </div>
            <div class="stat-footer">
                <span class="stat-period">requieren atención</span>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-grid">
        <!-- Revenue Chart -->
        <div class="card card-elevated">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Ingresos vs Gastos</h3>
                    <p class="card-subtitle">Últimos 6 meses</p>
                </div>
                <div class="card-actions">
                    <select class="form-select form-select-sm" onchange="updateChart(this.value)">
                        <option value="6">Últimos 6 meses</option>
                        <option value="12">Últimos 12 meses</option>
                        <option value="30">Último mes</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenue-chart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Actividad Reciente</h3>
                    <p class="card-subtitle">Últimas 24 horas</p>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" onclick="loadMoreActivity()">
                    Ver todo
                </button>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    @if(isset($recentActivity) && count($recentActivity) > 0)
                        @foreach($recentActivity as $activity)
                            <div class="activity-item">
                                <div class="activity-icon activity-icon-{{ $activity['type'] }}">
                                    @switch($activity['type'])
                                        @case('client')
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                            </svg>
                                        @break
                                        @case('payment')
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                                            </svg>
                                        @break
                                        @case('expense')
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h3l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                                            </svg>
                                        @break
                                        @default
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                                            </svg>
                                        @endswitch
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">{{ $activity['title'] }}</div>
                                    <div class="activity-description">{{ $activity['description'] }}</div>
                                    <div class="activity-time">{{ $activity['time'] }}</div>
                                </div>
                                <div class="activity-action">
                                    @if($activity['action_url'])
                                        <a href="{{ $activity['action_url'] }}" class="btn btn-ghost btn-sm">
                                            Ver
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="empty-state-title">Sin actividad reciente</div>
                            <div class="empty-state-description">No ha habido actividad en las últimas 24 horas</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Acciones Rápidas</h3>
                    <p class="card-subtitle">Tareas comunes</p>
                </div>
            </div>
            <div class="card-body">
                <div class="quick-actions-grid">
                    <button type="button" class="quick-action-btn" onclick="window.location.href='{{ route('clientes.create') }}'">
                        <div class="quick-action-icon quick-action-icon-blue">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Nuevo Cliente</div>
                            <div class="quick-action-description">Agregar cliente</div>
                        </div>
                    </button>

                    <button type="button" class="quick-action-btn" onclick="window.location.href='{{ route('gastos.create') }}'">
                        <div class="quick-action-icon quick-action-icon-orange">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Registrar Gasto</div>
                            <div class="quick-action-description">Nuevo gasto</div>
                        </div>
                    </button>

                    <button type="button" class="quick-action-btn" onclick="window.location.href='{{ route('pagos.index') }}'">
                        <div class="quick-action-icon quick-action-icon-green">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Historial de Pagos</div>
                            <div class="quick-action-description">Ver pagos</div>
                        </div>
                    </button>

                    <button type="button" class="quick-action-btn" onclick="window.location.href='{{ route('aprobaciones.index') }}'">
                        <div class="quick-action-icon quick-action-icon-purple">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Aprobaciones</div>
                            <div class="quick-action-description">Revisar pendientes</div>
                        </div>
                    </button>

                    <button type="button" class="quick-action-btn" onclick="window.location.href='{{ route('reportes.financiero') }}'">
                        <div class="quick-action-icon quick-action-icon-indigo">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Reportes</div>
                            <div class="quick-action-description">Ver reportes</div>
                        </div>
                    </button>

                    <button type="button" class="quick-action-btn" onclick="window.location.href='{{ route('calendario.index') }}'">
                        <div class="quick-action-icon quick-action-icon-pink">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="quick-action-content">
                            <div class="quick-action-title">Calendario</div>
                            <div class="quick-action-description">Ver calendario</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Top Clients -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Clientes Principales</h3>
                    <p class="card-subtitle">Por ingresos este mes</p>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" onclick="window.location.href='{{ route('clientes.index') }}'">
                    Ver todos
                </button>
            </div>
            <div class="card-body">
                <div class="top-clients-list">
                    @if(isset($topClients) && count($topClients) > 0)
                        @foreach($topClients as $index => $client)
                            <div class="top-client-item">
                                <div class="top-client-rank">{{ $index + 1 }}</div>
                                <div class="top-client-avatar">
                                    {{ strtoupper(substr($client['name'], 0, 2)) }}
                                </div>
                                <div class="top-client-info">
                                    <div class="top-client-name">{{ $client['name'] }}</div>
                                    <div class="top-client-service">{{ $client['service'] }}</div>
                                </div>
                                <div class="top-client-revenue">
                                    <div class="top-client-amount">${{ number_format($client['revenue'], 0) }}</div>
                                    <div class="top-client-status badge badge-success">Activo</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="empty-state-title">Sin clientes</div>
                            <div class="empty-state-description">No hay clientes registrados</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Tasks -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Próximas Tareas</h3>
                    <p class="card-subtitle">Próximos 7 días</p>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" onclick="window.location.href='{{ route('calendario.index') }}'">
                    Ver calendario
                </button>
            </div>
            <div class="card-body">
                <div class="tasks-list">
                    @if(isset($upcomingTasks) && count($upcomingTasks) > 0)
                        @foreach($upcomingTasks as $task)
                            <div class="task-item">
                                <div class="task-checkbox">
                                    <input type="checkbox" id="task-{{ $task['id'] }}" onchange="toggleTask({{ $task['id'] }})">
                                    <label for="task-{{ $task['id'] }}"></label>
                                </div>
                                <div class="task-content">
                                    <div class="task-title">{{ $task['title'] }}</div>
                                    <div class="task-meta">
                                        <span class="task-date">{{ $task['date'] }}</span>
                                        <span class="task-client">{{ $task['client'] }}</span>
                                    </div>
                                </div>
                                <div class="task-priority task-priority-{{ $task['priority'] }}">
                                    {{ ucfirst($task['priority']) }}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <div class="empty-state-title">Sin tareas pendientes</div>
                            <div class="empty-state-description">No hay tareas programadas</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeRevenueChart();
    setupDashboardInteractions();
});

function initializeRevenueChart() {
    const ctx = document.getElementById('revenue-chart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            datasets: [
                {
                    label: 'Ingresos',
                    data: [45000, 52000, 48000, 61000, 58000, 67000],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Gastos',
                    data: [32000, 35000, 31000, 38000, 36000, 41000],
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += '$' + context.parsed.y.toLocaleString('es-MX');
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString('es-MX');
                        }
                    }
                }
            }
        }
    });
}

function setupDashboardInteractions() {
    // Auto-refresh every 30 seconds
    setInterval(() => {
        refreshDashboard(false);
    }, 30000);
}

function refreshDashboard(showLoading = true) {
    if (showLoading) {
        window.uxSystem.showLoading('Actualizando dashboard...');
    }
    
    // Simulate API call
    setTimeout(() => {
        if (showLoading) {
            window.uxSystem.hideLoading();
            window.uxSystem.showToast('Dashboard actualizado', 'success');
        }
    }, 1000);
}

function updateChart(period) {
    // Update chart based on selected period
    console.log('Updating chart for period:', period);
    window.uxSystem.showToast('Gráfico actualizado', 'success');
}

function loadMoreActivity() {
    window.location.href = '/activity';
}

function toggleTask(taskId) {
    // Toggle task completion
    console.log('Toggling task:', taskId);
    window.uxSystem.showToast('Tarea actualizada', 'success');
}
</script>
@endsection

<style>
/* Dashboard Specific Styles */
.dashboard-page {
    max-width: 100%;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--space-6);
    margin-bottom: var(--space-8);
}

.stat-card {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    transition: all var(--transition-fast);
}

.stat-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-4);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.stat-icon-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.stat-icon-green { background: linear-gradient(135deg, #10b981, #059669); }
.stat-icon-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
.stat-icon-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

.stat-trend {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
}

.stat-trend-up {
    color: var(--color-success);
}

.stat-trend-down {
    color: var(--color-error);
}

.stat-content {
    margin-bottom: var(--space-3);
}

.stat-value {
    font-size: var(--font-size-3xl);
    font-weight: var(--font-weight-bold);
    color: var(--color-text-primary);
    line-height: 1;
    margin-bottom: var(--space-1);
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
}

.stat-footer {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.stat-period {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: var(--space-6);
}

.dashboard-grid .card:first-child {
    grid-column: 1 / -1;
}

/* Activity List */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.activity-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4);
    border-radius: var(--radius-lg);
    transition: background-color var(--transition-fast);
}

.activity-item:hover {
    background-color: var(--color-surface-hover);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.activity-icon-client { background: var(--color-brand); }
.activity-icon-payment { background: var(--color-success); }
.activity-icon-expense { background: var(--color-warning); }
.activity-icon-default { background: var(--color-text-tertiary); }

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: var(--font-weight-medium);
    color: var(--color-text-primary);
    margin-bottom: var(--space-1);
}

.activity-description {
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
    margin-bottom: var(--space-1);
}

.activity-time {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
}

/* Quick Actions */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-4);
}

.quick-action-btn {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4);
    background: none;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    text-align: left;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.quick-action-btn:hover {
    background-color: var(--color-surface-hover);
    border-color: var(--color-border-hover);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.quick-action-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.quick-action-icon-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.quick-action-icon-green { background: linear-gradient(135deg, #10b981, #059669); }
.quick-action-icon-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
.quick-action-icon-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.quick-action-icon-indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }
.quick-action-icon-pink { background: linear-gradient(135deg, #ec4899, #db2777); }

.quick-action-content {
    flex: 1;
}

.quick-action-title {
    font-weight: var(--font-weight-medium);
    color: var(--color-text-primary);
    margin-bottom: var(--space-1);
}

.quick-action-description {
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
}

/* Top Clients */
.top-clients-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.top-client-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4);
    border-radius: var(--radius-lg);
    transition: background-color var(--transition-fast);
}

.top-client-item:hover {
    background-color: var(--color-surface-hover);
}

.top-client-rank {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-full);
    background: var(--color-brand-light);
    color: var(--color-brand);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: var(--font-weight-bold);
    font-size: var(--font-size-sm);
    flex-shrink: 0;
}

.top-client-avatar {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-full);
    background: linear-gradient(135deg, var(--color-brand), var(--color-brand-hover));
    color: var(--color-text-inverse);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-sm);
    flex-shrink: 0;
}

.top-client-info {
    flex: 1;
}

.top-client-name {
    font-weight: var(--font-weight-medium);
    color: var(--color-text-primary);
    margin-bottom: var(--space-1);
}

.top-client-service {
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
}

.top-client-revenue {
    text-align: right;
}

.top-client-amount {
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-primary);
    margin-bottom: var(--space-1);
}

/* Tasks List */
.tasks-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.task-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4);
    border-radius: var(--radius-lg);
    transition: background-color var(--transition-fast);
}

.task-item:hover {
    background-color: var(--color-surface-hover);
}

.task-checkbox {
    position: relative;
}

.task-checkbox input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.task-checkbox label {
    display: block;
    width: 20px;
    height: 20px;
    border: 2px solid var(--color-border);
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.task-checkbox input[type="checkbox"]:checked + label {
    background: var(--color-brand);
    border-color: var(--color-brand);
}

.task-checkbox input[type="checkbox"]:checked + label::after {
    content: '✓';
    color: white;
    display: block;
    text-align: center;
    line-height: 16px;
    font-size: 12px;
}

.task-content {
    flex: 1;
}

.task-title {
    font-weight: var(--font-weight-medium);
    color: var(--color-text-primary);
    margin-bottom: var(--space-1);
}

.task-meta {
    display: flex;
    gap: var(--space-3);
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
}

.task-priority {
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
}

.task-priority-high {
    background: var(--color-error-light);
    color: var(--color-error);
}

.task-priority-medium {
    background: var(--color-warning-light);
    color: var(--color-warning);
}

.task-priority-low {
    background: var(--color-success-light);
    color: var(--color-success);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-grid .card:first-child {
        grid-column: 1;
    }
    
    .quick-actions-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-4);
    }
    
    .page-actions {
        width: 100%;
        justify-content: stretch;
    }
    
    .page-actions button {
        flex: 1;
    }
}
</style>
