@extends('layouts.modern')

@section('title', 'Clientes')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Clientes</span>
        </div>
    </div>
@endsection

@section('content')
<div class="clientes-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Clientes</h1>
            <p class="page-subtitle">{{ $clientes->total() }} cliente{{ $clientes->total() != 1 ? 's' : '' }} en total</p>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-secondary" onclick="showBulkActions()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Acciones Masivas
            </button>
            <a href="{{ route('clientes.eliminados') }}" class="btn btn-secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Papelera
            </a>
            <a href="{{ route('clientes.create') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nuevo Cliente
            </a>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card">
        <div class="card-body">
            <div class="filters-container">
                <div class="search-container">
                    <div class="search-input-wrapper">
                        <svg class="search-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input 
                            type="search" 
                            id="search-input"
                            placeholder="Buscar por nombre, email o negocio..." 
                            class="form-input"
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>
                
                <div class="filters-controls">
                    <select class="form-select" id="status-filter" onchange="applyFilters()">
                        <option value="">Todos los estados</option>
                        <option value="activo" {{ request('status') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ request('status') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        <option value="suspendido" {{ request('status') == 'suspendido' ? 'selected' : '' }}>Suspendido</option>
                    </select>
                    
                    <select class="form-select" id="service-filter" onchange="applyFilters()">
                        <option value="">Todos los servicios</option>
                        <option value="Básico" {{ request('service') == 'Básico' ? 'selected' : '' }}>Básico</option>
                        <option value="Premium" {{ request('service') == 'Premium' ? 'selected' : '' }}>Premium</option>
                        <option value="Enterprise" {{ request('service') == 'Enterprise' ? 'selected' : '' }}>Enterprise</option>
                    </select>
                    
                    <button type="button" class="btn btn-ghost" onclick="resetFilters()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Limpiar
                    </button>
                </div>
            </div>
            
            <!-- Active Filters -->
            @if(request()->hasAny(['search', 'status', 'service']))
                <div class="active-filters">
                    <span class="active-filters-label">Filtros activos:</span>
                    @if(request('search'))
                        <span class="filter-tag">
                            Búsqueda: {{ request('search') }}
                            <button type="button" onclick="removeFilter('search')">×</button>
                        </span>
                    @endif
                    @if(request('status'))
                        <span class="filter-tag">
                            Estado: {{ request('status') }}
                            <button type="button" onclick="removeFilter('status')">×</button>
                        </span>
                    @endif
                    @if(request('service'))
                        <span class="filter-tag">
                            Servicio: {{ request('service') }}
                            <button type="button" onclick="removeFilter('service')">×</button>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Results Summary -->
    <div class="results-summary">
        <div class="results-info">
            <span>Mostrando {{ $clientes->firstItem() }}-{{ $clientes->lastItem() }} de {{ $clientes->total() }} resultados</span>
        </div>
        <div class="view-controls">
            <button class="view-btn active" data-view="table" onclick="changeView('table')" title="Vista de tabla">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            </button>
            <button class="view-btn" data-view="cards" onclick="changeView('cards')" title="Vista de tarjetas">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Table View -->
    <div id="table-view" class="table-view">
        @if($clientes->count() > 0)
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="data-table" id="clientes-table">
                        <thead>
                            <tr>
                                <th class="table-checkbox">
                                    <input type="checkbox" id="select-all" onchange="toggleSelectAll()">
                                    <label for="select-all"></label>
                                </th>
                                <th class="sortable" onclick="sortTable('negocio')">
                                    Negocio
                                    <svg class="sort-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </th>
                                <th class="sortable" onclick="sortTable('contacto')">
                                    Contacto
                                    <svg class="sort-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </th>
                                <th class="hide-mobile sortable" onclick="sortTable('servicio')">
                                    Servicio
                                    <svg class="sort-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </th>
                                <th class="hide-mobile sortable" onclick="sortTable('fotos')">
                                    Fotos
                                    <svg class="sort-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </th>
                                <th class="sortable" onclick="sortTable('mensualidad')">
                                    Mensualidad
                                    <svg class="sort-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </th>
                                <th class="hide-mobile sortable" onclick="sortTable('registro')">
                                    Registro
                                    <svg class="sort-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </th>
                                <th class="sortable" onclick="sortTable('estado')">
                                    Estado
                                    <svg class="sort-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </th>
                                <th class="table-actions">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                                <tr class="table-row" data-id="{{ $cliente->id }}">
                                    <td class="table-checkbox">
                                        <input type="checkbox" class="row-checkbox" value="{{ $cliente->id }}">
                                        <label></label>
                                    </td>
                                    <td class="table-cell">
                                        <div class="client-info">
                                            <div class="client-avatar">
                                                {{ strtoupper(substr($cliente->nombre_negocio, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="client-name">{{ $cliente->nombre_negocio }}</div>
                                                <div class="client-email hide-mobile">{{ $cliente->user->email ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="table-cell">
                                        <div class="contact-info">
                                            <div class="contact-name">{{ $cliente->user->name ?? 'N/A' }}</div>
                                            <div class="contact-phone hide-mobile">{{ $cliente->telefono ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="table-cell hide-mobile">
                                        <span class="badge badge-primary">{{ $cliente->servicio_contratado ?? 'N/A' }}</span>
                                    </td>
                                    <td class="table-cell hide-mobile">
                                        <div class="fotos-info">
                                            <span class="fotos-count">{{ $cliente->cantidad_fotos ?? 0 }}</span>
                                            <span class="fotos-label">fotos</span>
                                        </div>
                                    </td>
                                    <td class="table-cell">
                                        <div class="price-info">
                                            <span class="price-amount">${{ number_format($cliente->precio_mensual ?? 0, 2) }}</span>
                                            <span class="price-period">/mes</span>
                                        </div>
                                    </td>
                                    <td class="table-cell hide-mobile">
                                        <div class="date-info">
                                            <div class="date-value">{{ $cliente->fecha_registro ? $cliente->fecha_registro->format('d/m/Y') : 'N/A' }}</div>
                                            <div class="date-relative">{{ $cliente->fecha_registro ? $cliente->fecha_registro->diffForHumans() : 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="table-cell">
                                        @php
                                            $status = $cliente->user->estatus;
                                            $statusClass = match($status) {
                                                'activo' => 'success',
                                                'inactivo' => 'warning',
                                                'suspendido' => 'error',
                                                'dado_de_baja' => 'error',
                                                default => 'gray'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $statusClass }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="table-cell">
                                        <div class="actions-dropdown">
                                            <button class="actions-trigger" onclick="toggleActions({{ $cliente->id }})" aria-label="Más acciones">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                                </svg>
                                            </button>
                                            <div class="actions-menu" id="actions-{{ $cliente->id }}">
                                                <a href="{{ route('clientes.show', $cliente) }}" class="action-item">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    Ver detalles
                                                </a>
                                                <a href="{{ route('clientes.edit', $cliente) }}" class="action-item">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    Editar
                                                </a>
                                                <button type="button" class="action-item action-danger" onclick="confirmDelete({{ $cliente->id }}, '{{ $cliente->nombre_negocio }}')">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info">
                    <span>Página {{ $clientes->currentPage() }} de {{ $clientes->lastPage() }}</span>
                </div>
                {{ $clientes->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="empty-state-title">No se encontraron clientes</h3>
                <p class="empty-state-description">
                    @if(request()->hasAny(['search', 'status', 'service']))
                        Intenta ajustar los filtros de búsqueda o 
                        <a href="{{ route('clientes.index') }}" class="link">limpiar todos los filtros</a>.
                    @else
                        Comienza agregando tu primer cliente para gestionar tu negocio.
                    @endif
                </p>
                <div class="empty-state-actions">
                    @if(request()->hasAny(['search', 'status', 'service']))
                        <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Limpiar Filtros
                        </button>
                    @endif
                    <a href="{{ route('clientes.create') }}" class="btn btn-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nuevo Cliente
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Cards View (Mobile Friendly) -->
    <div id="cards-view" class="cards-view" style="display: none;">
        <div class="cards-grid">
            @foreach($clientes as $cliente)
                <div class="client-card">
                    <div class="card-header">
                        <div class="client-avatar large">
                            {{ strtoupper(substr($cliente->nombre_negocio, 0, 2)) }}
                        </div>
                        <div class="card-status">
                            @php
                                $status = $cliente->user->estatus;
                                $statusClass = match($status) {
                                    'activo' => 'success',
                                    'inactivo' => 'warning',
                                    'suspendido' => 'error',
                                    'dado_de_baja' => 'error',
                                    default => 'gray'
                                };
                            @endphp
                            <span class="badge badge-{{ $statusClass }}">
                                {{ ucfirst($status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <h3 class="client-name">{{ $cliente->nombre_negocio }}</h3>
                        <p class="client-contact">{{ $cliente->user->name }}</p>
                        <p class="client-email">{{ $cliente->user->email }}</p>
                        
                        <div class="card-details">
                            <div class="detail-item">
                                <span class="detail-label">Servicio:</span>
                                <span class="detail-value">{{ $cliente->servicio_contratado ?? 'N/A' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Fotos:</span>
                                <span class="detail-value">{{ $cliente->cantidad_fotos }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Mensualidad:</span>
                                <span class="detail-value">${{ number_format($cliente->precio_mensual, 2) }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Registro:</span>
                                <span class="detail-value">{{ $cliente->fecha_registro->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-ghost btn-sm">
                            Ver
                        </a>
                        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary btn-sm">
                            Editar
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Mobile Pagination -->
        <div class="mobile-pagination">
            {{ $clientes->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    setupTableInteractions();
    setupSearch();
    setupBulkActions();
    setupKeyboardShortcuts();
});

function setupTableInteractions() {
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.actions-dropdown')) {
            document.querySelectorAll('.actions-menu').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    });
}

function setupSearch() {
    const searchInput = document.getElementById('search-input');
    let searchTimeout;
    
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 300);
    });
}

function setupBulkActions() {
    // Update checkbox listeners
    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionsButton);
    });
}

function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl+N for new client
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            window.location.href = '{{ route('clientes.create') }}';
        }
        
        // Ctrl+F for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            document.getElementById('search-input').focus();
        }
    });
}

function toggleActions(clienteId) {
    const menu = document.getElementById(`actions-${clienteId}`);
    
    // Close all other menus
    document.querySelectorAll('.actions-menu').forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

function toggleSelectAll() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkActionsButton();
}

function updateBulkActionsButton() {
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    const bulkButton = document.querySelector('[onclick="showBulkActions()"]');
    
    if (checkedBoxes.length > 0) {
        bulkButton.classList.add('active');
        bulkButton.innerHTML = `
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Acciones (${checkedBoxes.length})
        `;
    } else {
        bulkButton.classList.remove('active');
        bulkButton.innerHTML = `
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Acciones Masivas
        `;
    }
}

function showBulkActions() {
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
        window.uxSystem.showToast('Selecciona al menos un cliente', 'warning');
        return;
    }
    
    const ids = Array.from(checkedBoxes).map(cb => cb.value);
    
    window.uxSystem.showConfirmModal(
        'Acciones Masivas',
        `Has seleccionado ${checkedBoxes.length} cliente${checkedBoxes.length > 1 ? 's' : ''}. ¿Qué acción deseas realizar?`,
        () => {
            // TODO: Implement bulk actions
            window.uxSystem.showToast('Acción masiva ejecutada', 'success');
        }
    );
}

function changeView(view) {
    const tableView = document.getElementById('table-view');
    const cardsView = document.getElementById('cards-view');
    const viewButtons = document.querySelectorAll('.view-btn');
    
    viewButtons.forEach(btn => {
        btn.classList.toggle('active', btn.dataset.view === view);
    });
    
    if (view === 'table') {
        tableView.style.display = 'block';
        cardsView.style.display = 'none';
    } else {
        tableView.style.display = 'none';
        cardsView.style.display = 'block';
    }
    
    localStorage.setItem('preferredView', view);
}

function applyFilters() {
    const search = document.getElementById('search-input').value;
    const status = document.getElementById('status-filter').value;
    const service = document.getElementById('service-filter').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (service) params.append('service', service);
    
    const url = params.toString() ? `?${params.toString()}` : '';
    window.location.href = url;
}

function resetFilters() {
    window.location.href = '{{ route('clientes.index') }}';
}

function removeFilter(filter) {
    const url = new URL(window.location);
    url.searchParams.delete(filter);
    window.location.href = url.toString();
}

function sortTable(column) {
    const url = new URL(window.location);
    const currentSort = url.searchParams.get('sort');
    const currentOrder = url.searchParams.get('order') || 'asc';
    
    if (currentSort === column) {
        url.searchParams.set('order', currentOrder === 'asc' ? 'desc' : 'asc');
    } else {
        url.searchParams.set('sort', column);
        url.searchParams.set('order', 'asc');
    }
    
    window.location.href = url.toString();
}

function confirmDelete(clienteId, clienteName) {
    window.uxSystem.showConfirmModal(
        'Eliminar Cliente',
        `¿Estás seguro de que deseas eliminar a "${clienteName}"? Esta acción se puede deshacer desde la papelera.`,
        () => {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('/admin/clientes') }}/${clienteId}`;
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

// Load preferred view
document.addEventListener('DOMContentLoaded', function() {
    const preferredView = localStorage.getItem('preferredView');
    if (preferredView === 'cards' && window.innerWidth < 768) {
        changeView('cards');
    }
});

// Handle responsive view switching
window.addEventListener('resize', function() {
    if (window.innerWidth < 768) {
        const cardsView = document.getElementById('cards-view');
        const tableView = document.getElementById('table-view');
        
        if (tableView.style.display !== 'none') {
            changeView('cards');
        }
    }
});
</script>
@endsection

<style>
/* Clientes Page Specific Styles */
.clientes-page {
    max-width: 100%;
}

/* Filters Container */
.filters-container {
    display: flex;
    gap: var(--space-4);
    align-items: center;
    flex-wrap: wrap;
}

.search-container {
    flex: 1;
    min-width: 300px;
}

.search-input-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    left: var(--space-3);
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-text-quaternary);
    pointer-events: none;
}

.filters-controls {
    display: flex;
    gap: var(--space-3);
    align-items: center;
}

.active-filters {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-top: var(--space-4);
    flex-wrap: wrap;
}

.active-filters-label {
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
    font-weight: var(--font-weight-medium);
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-1) var(--space-3);
    background: var(--color-brand-light);
    color: var(--color-brand);
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
}

.filter-tag button {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 1rem;
    line-height: 1;
    padding: 0;
}

/* Results Summary */
.results-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-6);
    flex-wrap: wrap;
    gap: var(--space-4);
}

.results-info {
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
}

.view-controls {
    display: flex;
    gap: var(--space-2);
}

.view-btn {
    padding: var(--space-2);
    border: 1px solid var(--color-border);
    background: var(--color-surface);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.view-btn:hover {
    background: var(--color-surface-hover);
}

.view-btn.active {
    background: var(--color-brand);
    border-color: var(--color-brand);
    color: var(--color-text-inverse);
}

/* Enhanced Table Styles */
.table-container {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.table-wrapper {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: var(--font-size-sm);
}

.data-table th {
    text-align: left;
    padding: var(--space-4);
    background: var(--color-background-secondary);
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-primary);
    border-bottom: 2px solid var(--color-border);
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
}

.data-table th.sortable {
    cursor: pointer;
    user-select: none;
    transition: background-color var(--transition-fast);
}

.data-table th.sortable:hover {
    background: var(--color-surface-hover);
}

.sort-icon {
    display: inline-block;
    margin-left: var(--space-1);
    opacity: 0.5;
    transition: opacity var(--transition-fast);
}

.data-table th.sortable:hover .sort-icon {
    opacity: 1;
}

.data-table td {
    padding: var(--space-4);
    border-bottom: 1px solid var(--color-border);
    vertical-align: middle;
}

.table-row:hover {
    background: var(--color-surface-hover);
}

.table-checkbox {
    width: 40px;
}

.table-checkbox input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.table-checkbox label {
    display: block;
    width: 20px;
    height: 20px;
    border: 2px solid var(--color-border);
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.table-checkbox input[type="checkbox"]:checked + label {
    background: var(--color-brand);
    border-color: var(--color-brand);
}

.table-checkbox input[type="checkbox"]:checked + label::after {
    content: '✓';
    color: var(--color-text-inverse);
    display: block;
    text-align: center;
    line-height: 16px;
    font-size: 12px;
}

/* Client Info Styles */
.client-info {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.client-avatar {
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
}

.client-avatar.large {
    width: 48px;
    height: 48px;
    font-size: var(--font-size-base);
}

.client-name {
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-primary);
    margin-bottom: var(--space-1);
}

.client-email {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.contact-name {
    font-weight: var(--font-weight-medium);
    color: var(--color-text-primary);
}

.contact-phone {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
}

.fotos-info {
    text-align: center;
}

.fotos-count {
    display: block;
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-primary);
}

.fotos-label {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
}

.price-info {
    display: flex;
    align-items: baseline;
    gap: var(--space-1);
}

.price-amount {
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-primary);
}

.price-period {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
}

.date-info {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.date-value {
    font-weight: var(--font-weight-medium);
    color: var(--color-text-primary);
}

.date-relative {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
}

/* Actions Dropdown */
.actions-dropdown {
    position: relative;
}

.actions-trigger {
    padding: var(--space-2);
    border: 1px solid var(--color-border);
    background: var(--color-surface);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.actions-trigger:hover {
    background: var(--color-surface-hover);
}

.actions-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    min-width: 180px;
    z-index: var(--z-dropdown);
    display: none;
}

.action-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    width: 100%;
    padding: var(--space-3);
    background: none;
    border: none;
    text-align: left;
    color: var(--color-text-primary);
    text-decoration: none;
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    cursor: pointer;
    transition: background-color var(--transition-fast);
}

.action-item:hover {
    background: var(--color-surface-hover);
}

.action-danger {
    color: var(--color-error);
}

.action-danger:hover {
    background: var(--color-error-light);
}

/* Cards View */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: var(--space-6);
}

.client-card {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    overflow: hidden;
    transition: all var(--transition-base);
    box-shadow: var(--shadow-sm);
}

.client-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.client-card .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-6);
    border-bottom: 1px solid var(--color-border);
}

.client-card .card-body {
    padding: var(--space-6);
}

.client-card .card-footer {
    display: flex;
    gap: var(--space-3);
    padding: var(--space-6);
    border-top: 1px solid var(--color-border);
    background: var(--color-background-secondary);
}

.client-card h3 {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-primary);
    margin: 0 0 var(--space-2) 0;
}

.client-contact {
    font-weight: var(--font-weight-medium);
    color: var(--color-text-primary);
    margin: 0 0 var(--space-1) 0;
}

.client-email {
    color: var(--color-text-secondary);
    font-size: var(--font-size-sm);
    margin: 0 0 var(--space-4) 0;
}

.card-details {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-2) 0;
    border-bottom: 1px solid var(--color-border);
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
}

.detail-value {
    font-weight: var(--font-weight-medium);
    color: var(--color-text-primary);
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: var(--space-6);
    padding: var(--space-4);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
}

.pagination-info {
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
}

.mobile-pagination {
    margin-top: var(--space-6);
    text-align: center;
}

/* Bulk Actions Button Active State */
.btn.active {
    background: var(--color-brand);
    border-color: var(--color-brand);
    color: var(--color-text-inverse);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .filters-container {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-container {
        min-width: auto;
    }
    
    .filters-controls {
        justify-content: stretch;
    }
    
    .filters-controls .form-select {
        flex: 1;
        min-width: auto;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
        gap: var(--space-4);
    }
    
    .page-actions {
        justify-content: stretch;
    }
    
    .page-actions button,
    .page-actions a {
        flex: 1;
        justify-content: center;
    }
    
    .results-summary {
        flex-direction: column;
        align-items: stretch;
        gap: var(--space-3);
    }
    
    .data-table {
        font-size: var(--font-size-xs);
    }
    
    .data-table th,
    .data-table td {
        padding: var(--space-2) var(--space-3);
    }
    
    .hide-mobile {
        display: none;
    }
    
    .cards-grid {
        grid-template-columns: 1fr;
    }
    
    .pagination-container {
        flex-direction: column;
        gap: var(--space-4);
        text-align: center;
    }
}

@media (max-width: 480px) {
    .client-info {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-2);
    }
    
    .client-avatar {
        width: 32px;
        height: 32px;
        font-size: var(--font-size-xs);
    }
    
    .actions-menu {
        min-width: 160px;
        right: -50px;
    }
}
</style>
