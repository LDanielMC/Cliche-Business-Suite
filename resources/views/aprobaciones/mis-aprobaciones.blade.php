@extends('layouts.app')

@section('title', 'Mis Aprobaciones de Fotos')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('cliente.dashboard') }}" class="breadcrumb-link">Inicio</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Mis Aprobaciones</span>
        </div>
    </div>
@endsection

@section('content')
@php
    $badgeMap = ['pendiente' => 'badge-primary', 'completado' => 'badge-success', 'auto_aprobado' => 'badge-warning'];
@endphp
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Mis Aprobaciones de Fotos</h1>
            <p class="page-subtitle">Revisa y selecciona las fotografías de cada periodo</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($paquetes->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Periodo</th>
                                <th>Cuota</th>
                                <th>Fotos Disponibles</th>
                                <th>Fecha Límite</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paquetes as $paquete)
                                <tr>
                                    <td class="font-medium">{{ ucfirst($paquete->mes_revision_legible) }}</td>
                                    <td>{{ $paquete->cantidad_requerida }}</td>
                                    <td>{{ $paquete->fotos_count }}</td>
                                    <td>{{ $paquete->fecha_limite?->format('d/m/Y') ?? '—' }}</td>
                                    <td><span class="badge {{ $badgeMap[$paquete->estatus] ?? 'badge-gray' }}">{{ str_replace('_', ' ', $paquete->estatus) }}</span></td>
                                    <td>
                                        <a href="{{ route('cliente.aprobaciones.show', $paquete) }}" class="btn btn-primary btn-sm">
                                            {{ $paquete->estatus === 'pendiente' ? 'Seleccionar Fotos' : 'Ver Detalle' }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pagination-container">
                    {{ $paquetes->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No tienes paquetes de aprobación pendientes</h3>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
