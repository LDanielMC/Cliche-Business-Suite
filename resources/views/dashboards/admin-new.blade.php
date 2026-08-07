@extends('layouts.app-new')

@section('title', 'Dashboard Administrador')

@section('breadcrumbs')
    <x-breadcrumbs :items="[
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')]
    ]" />
@endsection

@section('content')
<div class="admin-dashboard animate-fade-in">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard Administrador</h1>
            <p class="page-subtitle">Bienvenido de vuelta, {{ auth()->user()->name }}</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-secondary" onclick="showKeyboardShortcuts()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
                Atajos
            </button>
            <button class="btn btn-primary" onclick="refreshDashboard()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Actualizar
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card animate-slide-in" style="animation-delay: 0.1s">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Clientes Activos</div>
                    <div class="stat-value">{{ $activeClients ?? 0 }}</div>
                    <div class="stat-change positive">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7 14l5-5 5 5z"></path>
                        </svg>
                        +12% este mes
                    </div>
                </div>
                <div class="stat-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-slide-in" style="animation-delay: 0.2s">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Ingresos Mensuales</div>
                    <div class="stat-value">${{ number_format($monthlyRevenue ?? 0, 2) }}</div>
                    <div class="stat-change positive">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7 14l5-5 5 5z"></path>
                        </svg>
                        +8% vs mes anterior
                    </div>
                </div>
                <div class="stat-icon accent">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-slide-in" style="animation-delay: 0.3s">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Fotos Pendientes</div>
                    <div class="stat-value">{{ $pendingPhotos ?? 0 }}</div>
                    <div class="stat-change negative">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7 10l5 5 5-5z"></path>
                        </svg>
                        -5% esta semana
                    </div>
                </div>
                <div class="stat-icon warning">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card animate-slide-in" style="animation-delay: 0.4s">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Tasa de Conversión</div>
                    <div class="stat-value">{{ $conversionRate ?? 0 }}%</div>
                    <div class="stat-change positive">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7 14l5-5 5 5z"></path>
                        </svg>
                        +3% mejora
                    </div>
                </div>
                <div class="stat-icon success">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-grid">
        <!-- Revenue Chart -->
        <div class="dashboard-card animate-slide-in" style="animation-delay: 0.5s">
            <div class="card-header">
                <h3 class="card-title">Evolución de Ingresos</h3>
                <div class="card-actions">
                    <select class="form-select form-select-sm" id="revenue-period">
                        <option value="7">Últimos 7 días</option>
                        <option value="30" selected>Últimos 30 días</option>
                        <option value="90">Últimos 90 días</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" id="revenue-chart">
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="dashboard-card animate-slide-in" style="animation-delay: 0.6s">
            <div class="card-header">
                <h3 class="card-title">Actividad Reciente</h3>
                <a href="{{ route('clientes.index') }}" class="btn btn-ghost btn-sm">Ver todo</a>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    @if(isset($recentActivity) && count($recentActivity) > 0)
                        @foreach($recentActivity as $activity)
                            <div class="activity-item">
                                <div class="activity-icon {{ $activity['type'] }}">
                                    @switch($activity['type'])
                                        @case('client')
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                            </svg>
                                        @break
                                        @case('payment')
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                                            </svg>
                                        @break
                                        @case('photo')
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                                            </svg>
                                        @break
                                    @endswitch
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">{{ $activity['title'] }}</div>
                                    <div class="activity-time">{{ $activity['time'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="empty-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="empty-title">Sin actividad reciente</p>
                            <p class="empty-description">Las actividades recientes aparecerán aquí</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Tasks -->
        <div class="dashboard-card animate-slide-in" style="animation-delay: 0.7s">
            <div class="card-header">
                <h3 class="card-title">Próximas Tareas</h3>
                <button class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('calendario.index') }}'">
                    Ver Calendario
                </button>
            </div>
            <div class="card-body">
                <div class="task-list">
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
                                        <span class="task-priority {{ $task['priority'] }}">{{ $task['priority'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="empty-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="empty-title">Sin tareas pendientes</p>
                            <p class="empty-description">¡Buen trabajo! No tienes tareas próximas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-card animate-slide-in" style="animation-delay: 0.8s">
            <div class="card-header">
                <h3 class="card-title">Acciones Rápidas</h3>
            </div>
            <div class="card-body">
                <div class="quick-actions-grid">
                    <a href="{{ route('clientes.create') }}" class="quick-action">
                        <div class="quick-action-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <span>Nuevo Cliente</span>
                    </a>
                    
                    <a href="{{ route('gastos.create') }}" class="quick-action">
                        <div class="quick-action-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span>Registrar Gasto</span>
                    </a>
                    
                    <a href="{{ route('pagos.create') }}" class="quick-action">
                        <div class="quick-action-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <span>Nuevo Pago</span>
                    </a>
                    
                    <a href="{{ route('calendario.create') }}" class="quick-action">
                        <div class="quick-action-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <span>Agendar Foto</span>
                    </a>
                    
                    <a href="{{ route('aprobaciones.create') }}" class="quick-action">
                        <div class="quick-action-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <span>Nueva Aprobación</span>
                    </a>
                    
                    <a href="{{ route('reportes.financiero') }}" class="quick-action">
                        <div class="quick-action-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <span>Ver Reportes</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="dashboard-card animate-slide-in" style="animation-delay: 0.9s">
        <div class="card-header">
            <h3 class="card-title">Métricas de Rendimiento</h3>
            <div class="card-actions">
                <button class="btn btn-ghost btn-sm" onclick="exportMetrics()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Exportar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="metrics-grid">
                <div class="metric-item">
                    <div class="metric-label">Tiempo de Respuesta</div>
                    <div class="metric-value">2.3s</div>
                    <div class="metric-progress">
                        <div class="progress-bar" style="width: 85%"></div>
                    </div>
                </div>
                
                <div class="metric-item">
                    <div class="metric-label">Satisfacción Cliente</div>
                    <div class="metric-value">94%</div>
                    <div class="metric-progress">
                        <div class="progress-bar success" style="width: 94%"></div>
                    </div>
                </div>
                
                <div class="metric-item">
                    <div class="metric-label">Tasa de Retención</div>
                    <div class="metric-value">87%</div>
                    <div class="metric-progress">
                        <div class="progress-bar warning" style="width: 87%"></div>
                    </div>
                </div>
                
                <div class="metric-item">
                    <div class="metric-label">Eficiencia Operativa</div>
                    <div class="metric-value">91%</div>
                    <div class="metric-progress">
                        <div class="progress-bar accent" style="width: 91%"></div>
                    </div>
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
    initializeCharts();
    setupDashboardInteractions();
});

function initializeCharts() {
    // Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            datasets: [{
                label: 'Ingresos',
                data: [12000, 19000, 15000, 25000, 22000, 30000],
                borderColor: '#7c3aed',
                backgroundColor: 'rgba(124, 58, 237, 0.1)',
                tension: 0.4,
                fill: true
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
}

function setupDashboardInteractions() {
    // Period selector for revenue chart
    document.getElementById('revenue-period').addEventListener('change', function(e) {
        updateRevenueChart(e.target.value);
    });
}

function updateRevenueChart(period) {
    // Update chart based on selected period
    window.uxSystem.showLoading('Actualizando gráfico...');
    
    setTimeout(() => {
        window.uxSystem.hideLoading();
        window.uxSystem.showToast('Gráfico actualizado', 'success');
    }, 1000);
}

function refreshDashboard() {
    window.uxSystem.showLoading('Actualizando dashboard...');
    
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

function toggleTask(taskId) {
    window.uxSystem.showToast('Tarea actualizada', 'success');
}

function exportMetrics() {
    window.uxSystem.showToast('Exportando métricas...', 'info');
    
    setTimeout(() => {
        window.uxSystem.showToast('Métricas exportadas', 'success');
    }, 1500);
}
</script>
@endsection

<style>
/* Dashboard Specific Styles */
.admin-dashboard {
    max-width: 100%;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--space-8);
    flex-wrap: wrap;
    gap: var(--space-4);
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--color-gray-900);
    margin: 0 0 var(--space-2) 0;
}

.page-subtitle {
    color: var(--color-gray-600);
    margin: 0;
    font-size: 1rem;
}

.page-actions {
    display: flex;
    gap: var(--space-3);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--space-6);
    margin-bottom: var(--space-8);
}

.stat-card {
    background: white;
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    transition: all var(--transition-base);
    box-shadow: var(--shadow-sm);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    border-color: var(--color-primary-200);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.stat-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--color-gray-600);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: var(--space-2);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--color-gray-900);
    margin-bottom: var(--space-2);
}

.stat-change {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    font-size: 0.875rem;
    font-weight: 500;
}

.stat-change.positive {
    color: var(--color-success);
}

.stat-change.negative {
    color: var(--color-error);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(124, 58, 237, 0.1);
    color: var(--color-primary-600);
}

.stat-icon.accent {
    background: rgba(20, 184, 166, 0.1);
    color: var(--color-accent-600);
}

.stat-icon.warning {
    background: rgba(245, 158, 11, 0.1);
    color: var(--color-warning);
}

.stat-icon.success {
    background: rgba(16, 185, 129, 0.1);
    color: var(--color-success);
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: var(--space-6);
    margin-bottom: var(--space-8);
}

.dashboard-card {
    background: white;
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.dashboard-card:hover {
    box-shadow: var(--shadow-md);
}

.card-actions {
    display: flex;
    gap: var(--space-2);
}

.form-select-sm {
    padding: var(--space-1) var(--space-2);
    font-size: 0.75rem;
}

/* Chart Container */
.chart-container {
    position: relative;
    height: 200px;
}

/* Activity List */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: var(--space-3);
    padding: var(--space-3);
    border-radius: var(--radius-md);
    transition: background-color var(--transition-fast);
}

.activity-item:hover {
    background-color: var(--color-gray-50);
}

.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: white;
}

.activity-icon.client {
    background: var(--color-primary-500);
}

.activity-icon.payment {
    background: var(--color-success);
}

.activity-icon.photo {
    background: var(--color-accent-500);
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 500;
    color: var(--color-gray-900);
    margin-bottom: var(--space-1);
}

.activity-time {
    font-size: 0.75rem;
    color: var(--color-gray-500);
}

/* Task List */
.task-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.task-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3);
    border-radius: var(--radius-md);
    transition: background-color var(--transition-fast);
}

.task-item:hover {
    background-color: var(--color-gray-50);
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
    width: 20px;
    height: 20px;
    border: 2px solid var(--color-gray-300);
    border-radius: var(--radius-sm);
    display: block;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.task-checkbox input[type="checkbox"]:checked + label {
    background: var(--color-primary-500);
    border-color: var(--color-primary-500);
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
    font-weight: 500;
    color: var(--color-gray-900);
    margin-bottom: var(--space-1);
}

.task-meta {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: 0.75rem;
}

.task-date {
    color: var(--color-gray-500);
}

.task-priority {
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-full);
    font-weight: 500;
    text-transform: uppercase;
}

.task-priority.high {
    background: #fee2e2;
    color: #991b1b;
}

.task-priority.medium {
    background: #fef3c7;
    color: #92400e;
}

.task-priority.low {
    background: #dcfce7;
    color: #166534;
}

/* Quick Actions */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-3);
}

.quick-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-4);
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-lg);
    text-decoration: none;
    color: var(--color-gray-700);
    transition: all var(--transition-fast);
    text-align: center;
}

.quick-action:hover {
    background-color: var(--color-primary-50);
    border-color: var(--color-primary-200);
    color: var(--color-primary-700);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.quick-action-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-lg);
    background: var(--color-gray-100);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-gray-600);
    transition: all var(--transition-fast);
}

.quick-action:hover .quick-action-icon {
    background: var(--color-primary-100);
    color: var(--color-primary-600);
}

/* Metrics Grid */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-6);
}

.metric-item {
    text-align: center;
}

.metric-label {
    font-size: 0.875rem;
    color: var(--color-gray-600);
    margin-bottom: var(--space-2);
}

.metric-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-gray-900);
    margin-bottom: var(--space-3);
}

.metric-progress {
    height: 8px;
    background: var(--color-gray-200);
    border-radius: var(--radius-full);
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: var(--color-primary-500);
    transition: width var(--transition-slow);
    border-radius: var(--radius-full);
}

.progress-bar.success {
    background: var(--color-success);
}

.progress-bar.warning {
    background: var(--color-warning);
}

.progress-bar.accent {
    background: var(--color-accent-500);
}

/* Empty States */
.empty-state {
    text-align: center;
    padding: var(--space-8) var(--space-4);
    color: var(--color-gray-500);
}

.empty-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto var(--space-4);
    color: var(--color-gray-300);
}

.empty-title {
    font-weight: 600;
    color: var(--color-gray-700);
    margin-bottom: var(--space-2);
}

.empty-description {
    font-size: 0.875rem;
    margin: 0;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .page-actions {
        justify-content: stretch;
    }
    
    .page-actions button {
        flex: 1;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
}
</style>
