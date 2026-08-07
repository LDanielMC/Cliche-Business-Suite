@extends('layouts.modern')

@section('title', 'Gastos Operativos')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Gastos Operativos</span>
        </div>
    </div>
@endsection

@section('content')
<div class="gastos-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Gastos Operativos</h1>
            <p class="page-subtitle">Gestiona los gastos operativos del negocio</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('gastos.create') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Gasto
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">Gastos del Mes</div>
                        <div class="text-2xl font-bold">${{ number_format($gastosMes ?? 0, 2) }}</div>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
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
                        <div class="text-sm text-gray-600">Promedio Mensual</div>
                        <div class="text-2xl font-bold">${{ number_format($promedioMensual ?? 0, 2) }}</div>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">Total Gastos</div>
                        <div class="text-2xl font-bold">{{ $totalGastos ?? 0 }}</div>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">Categorías</div>
                        <div class="text-2xl font-bold">{{ $totalCategorias ?? 0 }}</div>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="form-group">
                    <label for="search" class="form-label">Buscar</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <input type="text" id="search" class="form-control" placeholder="Buscar gastos...">
                    </div>
                </div>

                <div class="form-group">
                    <label for="categoria" class="form-label">Categoría</label>
                    <select id="categoria" class="form-control">
                        <option value="">Todas las categorías</option>
                        @if(isset($categorias))
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" id="fecha_inicio" class="form-control">
                </div>

                <div class="form-group">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" id="fecha_fin" class="form-control">
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                    Limpiar Filtros
                </button>
                <button type="button" class="btn btn-primary" onclick="applyFilters()">
                    Aplicar Filtros
                </button>
            </div>
        </div>
    </div>

    <!-- Gastos Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Gastos</h3>
            <div class="card-actions">
                <button type="button" class="btn btn-secondary btn-sm" onclick="exportData()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Exportar
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(isset($gastos) && $gastos->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Descripción</th>
                                <th>Categoría</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gastos as $gasto)
                                <tr>
                                    <td>{{ $gasto->fecha->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="font-medium">{{ $gasto->descripcion }}</div>
                                        @if($gasto->notas)
                                            <div class="text-sm text-gray-600">{{ $gasto->notas }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            {{ $gasto->categoria->nombre ?? 'Sin categoría' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-semibold text-red-600">
                                            ${{ number_format($gasto->monto, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $gasto->estado === 'pagado' ? 'success' : 'warning' }}">
                                            {{ $gasto->estado }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('gastos.show', $gasto) }}" class="btn btn-ghost btn-sm">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <a href="{{ route('gastos.edit', $gasto) }}" class="btn btn-secondary btn-sm">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $gasto->id }}, '{{ $gasto->descripcion }}')">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span>Página {{ $gastos->currentPage() }} de {{ $gastos->lastPage() }}</span>
                    </div>
                    {{ $gastos->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No hay gastos registrados</h3>
                    <p class="empty-state-description">Comienza agregando un nuevo gasto operativo</p>
                    <a href="{{ route('gastos.create') }}" class="btn btn-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nuevo Gasto
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Gastos functionality
document.addEventListener('DOMContentLoaded', function() {
    setupTableInteractions();
    setupSearch();
});

function setupTableInteractions() {
    // Row click handlers
    document.querySelectorAll('.table tbody tr').forEach(row => {
        row.addEventListener('click', function(e) {
            if (!e.target.closest('button') && !e.target.closest('a')) {
                const link = this.querySelector('a[href*="show"]');
                if (link) link.click();
            }
        });
    });
}

function setupSearch() {
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
}

function clearFilters() {
    document.getElementById('search').value = '';
    document.getElementById('categoria').value = '';
    document.getElementById('fecha_inicio').value = '';
    document.getElementById('fecha_fin').value = '';
    setupSearch();
}

function applyFilters() {
    // Implement filter logic
    window.uxSystem.showToast('Filtros aplicados', 'success');
}

function exportData() {
    // Implement export logic
    window.uxSystem.showToast('Exportando datos...', 'info');
}

function confirmDelete(gastoId, gastoDescripcion) {
    window.uxSystem.showConfirmModal(
        'Eliminar Gasto',
        `¿Estás seguro de que deseas eliminar el gasto "${gastoDescripcion}"? Esta acción no se puede deshacer.`,
        () => {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/gastos/${gastoId}`;
            form.innerHTML = `
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
            `;
            document.body.appendChild(form);
            form.submit();
        },
        { danger: true }
    );
}
</script>
@endsection
