@extends('layouts.app')

@section('title', 'Gestión de Operadores')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Operadores</span>
        </div>
    </div>
@endsection

@section('content')
@php
    $badgeMap = ['activo' => 'badge-success', 'inactivo' => 'badge-warning', 'suspendido' => 'badge-error'];
@endphp
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Gestión de Operadores</h1>
            <p class="page-subtitle">Administra los operadores del sistema</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('operadores.create') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Operador
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <x-table-search placeholder="Buscar por nombre, email o teléfono..." />

            @if($operadores->count() > 0)
                <p style="font-size:.75rem; color:var(--color-text-secondary); margin-bottom:.75rem;">
                    Selecciona un operador para ver su detalle y gestionar sus opciones.
                </p>
                <div class="table-container">
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <x-th-sort field="operador" label="Operador" />
                                <x-th-sort field="telefono" label="Teléfono" class="hide-mobile" />
                                <x-th-sort field="estatus" label="Estatus" />
                                <th style="width:2rem;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($operadores as $operador)
                                <tr class="tr-link" onclick="window.location='{{ route('operadores.show', $operador) }}'">
                                    <td data-label="Operador">
                                        <div class="font-medium">{{ $operador->name }}</div>
                                        <div style="font-size:.75rem; color:var(--color-text-secondary); margin-top:.1rem;">{{ $operador->email }}</div>
                                    </td>
                                    <td data-label="Teléfono" class="hide-mobile">{{ $operador->telefono ?? '—' }}</td>
                                    <td data-label="Estatus">
                                        <span class="badge {{ $badgeMap[$operador->estatus] ?? 'badge-gray' }}">{{ ucfirst($operador->estatus) }}</span>
                                    </td>
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
                    {{ $operadores->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 100-8 4 4 0 000 8z"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No hay operadores registrados</h3>
                    <p class="empty-state-description">Comienza creando un nuevo operador.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
