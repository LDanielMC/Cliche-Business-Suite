@extends('layouts.app')

@section('title', 'Bóveda de Contraseñas')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Bóveda</span>
        </div>
    </div>
@endsection

@section('content')
@php
    $estatusBadge = [
        'activo' => 'badge-success',
        'inactivo' => 'badge-warning',
        'suspendido' => 'badge-error',
        'dado_de_baja' => 'badge-error',
    ];
@endphp
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Bóveda de Contraseñas</h1>
            <p class="page-subtitle">Accesos y credenciales de tus clientes</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('boveda.create') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nueva Credencial
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <x-table-search placeholder="Buscar por plataforma, usuario o cliente..." />

            @if($credenciales->count() > 0)
                <p style="font-size:.75rem; color:var(--color-text-secondary); margin-bottom:.75rem;">
                    Selecciona un registro para ver las credenciales completas y gestionar sus opciones.
                </p>
                <div class="table-container">
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <x-th-sort field="plataforma" label="Cliente · Plataforma" />
                                <x-th-sort field="usuario" label="Usuario" class="hide-mobile" />
                                <th class="hide-mobile">URL</th>
                                <th style="width:2rem;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($credenciales as $credencial)
                                <tr class="tr-link" onclick="window.location='{{ route('boveda.edit', $credencial) }}'">
                                    <td data-label="Plataforma">
                                        <div class="font-medium" style="display:flex; align-items:center; gap:.4rem;">
                                            {{ $credencial->cliente->nombre_negocio }}
                                            @if($credencial->cliente->user->estatus !== 'activo')
                                                <span class="badge {{ $estatusBadge[$credencial->cliente->user->estatus] ?? 'badge-gray' }}" style="font-size:.65rem;">
                                                    {{ ucfirst(str_replace('_', ' ', $credencial->cliente->user->estatus)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div style="font-size:.75rem; color:var(--color-text-secondary); margin-top:.1rem;">{{ $credencial->nombre_plataforma }}</div>
                                    </td>
                                    <td data-label="Usuario" class="hide-mobile" style="font-size:.85rem;">{{ $credencial->usuario }}</td>
                                    <td data-label="URL" class="hide-mobile" style="font-size:.75rem; color:var(--color-text-secondary); max-width:14rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                        {{ $credencial->url_acceso ?? '—' }}
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
                    {{ $credenciales->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No hay credenciales guardadas</h3>
                    <p class="empty-state-description">Comienza guardando una nueva credencial.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
