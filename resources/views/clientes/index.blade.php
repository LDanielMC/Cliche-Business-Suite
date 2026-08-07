@extends('layouts.app')

@section('title', 'Gestión de Clientes')

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
@php
    $badgeMap = [
        'activo'       => 'badge-success',
        'inactivo'     => 'badge-warning',
        'suspendido'   => 'badge-error',
        'dado_de_baja' => 'badge-error',
    ];
@endphp
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Gestión de Clientes</h1>
            <p class="page-subtitle">Administra los clientes del sistema</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('clientes.suspendidos') }}" class="btn btn-secondary">Suspendidos</a>
            <a href="{{ route('clientes.eliminados') }}" class="btn btn-secondary">Dados de baja</a>
            <a href="{{ route('clientes.create') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Cliente
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <x-table-search placeholder="Buscar por negocio, contacto o email..." />

            @if($clientes->count() > 0)
                <p style="font-size:.75rem; color:var(--color-text-secondary); margin-bottom:.75rem;">
                    Selecciona un cliente para ver su detalle y gestionar sus opciones.
                </p>
                <div class="table-container">
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <x-th-sort field="cliente" label="Cliente" />
                                <x-th-sort field="servicio" label="Servicio" class="hide-mobile" />
                                <x-th-sort field="precio" label="Precio / mes" class="hide-mobile" />
                                <x-th-sort field="estado" label="Estado" />
                                <th style="width:2rem;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                                <tr class="tr-link" onclick="window.location='{{ route('clientes.show', $cliente) }}'">
                                    <td data-label="Cliente">
                                        <div class="font-medium">{{ $cliente->nombre_negocio }}</div>
                                        <div style="font-size:.75rem; color:var(--color-text-secondary); margin-top:.1rem;">{{ $cliente->user->email }}</div>
                                    </td>
                                    <td data-label="Servicio" class="hide-mobile" style="font-size:.85rem;">{{ $cliente->servicio_contratado ?? '—' }}</td>
                                    <td data-label="Precio/mes" class="hide-mobile" style="font-weight:600;">${{ number_format($cliente->precio_mensual, 2) }}</td>
                                    <td data-label="Estado">
                                        <span class="badge {{ $badgeMap[$cliente->user->estatus] ?? 'badge-gray' }}">
                                            {{ ucfirst(str_replace('_', ' ', $cliente->user->estatus)) }}
                                        </span>
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
                    {{ $clientes->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 100-8 4 4 0 000 8z"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No hay clientes registrados</h3>
                    <p class="empty-state-description">Comienza creando un nuevo cliente.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
