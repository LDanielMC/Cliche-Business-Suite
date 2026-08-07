@extends('layouts.app')

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
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Gastos Operativos</h1>
            <p class="page-subtitle">Registra y controla los gastos del negocio</p>
        </div>
        <div class="page-actions">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('categorias-gastos.index') }}" class="btn btn-secondary">Categorías</a>
            @endif
            <a href="{{ route('gastos.create') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Gasto
            </a>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('gastos.index') }}" class="flex flex-wrap items-end gap-4">
                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="dir" value="{{ request('dir') }}">
                @endif
                <div class="form-group mb-0">
                    <label for="q" class="form-label">Buscar</label>
                    <input type="text" id="q" name="q" data-live-search class="form-input" placeholder="Concepto, categoría, cliente..." value="{{ request('q') }}">
                </div>
                <div class="form-group mb-0">
                    <label for="categoria_gasto_id" class="form-label">Categoría</label>
                    <select id="categoria_gasto_id" name="categoria_gasto_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(request('categoria_gasto_id') == $categoria->id)>{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user()->isAdmin())
                    <div class="form-group mb-0">
                        <label for="registrado_por" class="form-label">Operador</label>
                        <select id="registrado_por" name="registrado_por" class="form-select">
                            <option value="">Todos</option>
                            @foreach($registradores as $registrador)
                                <option value="{{ $registrador->id }}" @selected(request('registrado_por') == $registrador->id)>{{ $registrador->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="form-group mb-0">
                    <label for="desde" class="form-label">Desde</label>
                    <input type="date" id="desde" name="desde" class="form-input" value="{{ request('desde') }}">
                </div>
                <div class="form-group mb-0">
                    <label for="hasta" class="form-label">Hasta</label>
                    <input type="date" id="hasta" name="hasta" class="form-input" value="{{ request('hasta') }}">
                </div>
                <button type="submit" class="btn btn-primary">Filtrar</button>
                @if(request()->hasAny(['q', 'categoria_gasto_id', 'registrado_por', 'desde', 'hasta']))
                    <a href="{{ route('gastos.index') }}" class="btn btn-secondary">Limpiar filtros</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($gastos->count() > 0)
                <p style="font-size:.75rem; color:var(--color-text-secondary); margin-bottom:.75rem;">
                    Selecciona un registro para editarlo o eliminarlo.
                </p>
                <div class="table-container">
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <x-th-sort field="concepto" label="Concepto · Categoría" />
                                <th class="hide-mobile">Cliente</th>
                                <x-th-sort field="monto" label="Monto" />
                                <x-th-sort field="fecha" label="Fecha" class="hide-mobile" />
                                @if(auth()->user()->isAdmin())
                                    <x-th-sort field="registrado" label="Registrado por" class="hide-mobile" />
                                @endif
                                <th style="width:2rem;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gastos as $gasto)
                                <tr class="tr-link" onclick="window.location='{{ route('gastos.edit', $gasto) }}'">
                                    <td data-label="Concepto">
                                        <div class="font-medium">{{ $gasto->concepto_gasto }}</div>
                                        <div style="margin-top:.2rem;">
                                            <span class="badge badge-primary" style="font-size:.7rem;">{{ $gasto->categoria->nombre }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Cliente" class="hide-mobile" style="font-size:.85rem;">{{ $gasto->cliente->nombre_negocio ?? 'General' }}</td>
                                    <td data-label="Monto" style="font-weight:600; color:var(--color-error);">${{ number_format($gasto->monto, 2) }}</td>
                                    <td data-label="Fecha" class="hide-mobile">{{ $gasto->fecha_gasto->format('d/m/Y') }}</td>
                                    @if(auth()->user()->isAdmin())
                                        <td data-label="Registrado por" class="hide-mobile" style="font-size:.85rem;">{{ $gasto->registradoPor->name ?? '—' }}</td>
                                    @endif
                                    <td>
                                        <svg class="tr-chevron" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:block; margin-left:auto;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    {{ $gastos->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No hay gastos registrados</h3>
                    <p class="empty-state-description">Comienza registrando un nuevo gasto.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
