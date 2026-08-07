@extends('layouts.app')

@section('title', 'Categorías de Gastos')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Categorías de Gastos</span>
        </div>
    </div>
@endsection

@section('content')
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Categorías de Gastos</h1>
            <p class="page-subtitle">Clasifica tus gastos operativos</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('categorias-gastos.create') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nueva Categoría
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <x-table-search placeholder="Buscar categoría..." />

            @if($categorias->count() > 0)
                <p style="font-size:.75rem; color:var(--color-text-secondary); margin-bottom:.75rem;">
                    Selecciona una categoría para editarla o eliminarla.
                </p>
                <div class="table-container">
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <x-th-sort field="nombre" label="Nombre" />
                                <x-th-sort field="gastos" label="Gastos" />
                                <th style="width:2rem;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categorias as $categoria)
                                <tr class="tr-link" onclick="window.location='{{ route('categorias-gastos.edit', $categoria) }}'">
                                    <td data-label="Nombre" class="font-medium">{{ $categoria->nombre }}</td>
                                    <td data-label="Gastos"><span class="badge badge-gray">{{ $categoria->gastos_count }}</span></td>
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
                    {{ $categorias->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 8V3a2 2 0 012-2z"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No hay categorías de gastos registradas</h3>
                    <p class="empty-state-description">Comienza creando una nueva categoría.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
