@extends('layouts.app')

@section('title', 'Paquetes de Aprobación')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Aprobaciones</span>
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
            <h1 class="page-title">Paquetes de Aprobación de Fotografía</h1>
            <p class="page-subtitle">Gestiona la carga y aprobación de fotos por cliente</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('aprobaciones.create') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Paquete
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    @if($enRiesgo->isNotEmpty())
        <div class="card mb-6" style="border-color:#fca5a5;">
            <div class="card-header" style="display:flex;align-items:center;gap:.5rem;">
                <svg width="18" height="18" fill="none" stroke="#dc2626" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <h3 class="card-title" style="margin:0;">Paquetes que necesitan atención ({{ $enRiesgo->count() }})</h3>
            </div>
            <div class="card-body" style="padding:0;">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Vence el</th>
                                <th>Días restantes</th>
                                <th>Estado del paquete</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enRiesgo as $item)
                                <tr>
                                    <td class="font-medium">{{ $item['cliente']->nombre_negocio }}</td>
                                    <td>{{ $item['cliente']->renovacionActiva->fecha_vencimiento->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge {{ $item['nivel'] === 'critico' ? 'badge-error' : 'badge-warning' }}">
                                            {{ $item['etiqueta'] }}
                                            {{ $item['nivel'] === 'critico' ? '— urgente' : '' }}
                                        </span>
                                    </td>
                                    <td>{{ $item['paquete'] ? 'Borrador sin enviar' : 'Sin crear' }}</td>
                                    <td>
                                        @if($item['paquete'])
                                            <a href="{{ route('aprobaciones.show', $item['paquete']) }}" class="btn btn-secondary btn-sm">Subir fotos</a>
                                        @else
                                            <a href="{{ route('aprobaciones.create', ['cliente_id' => $item['cliente']->id]) }}" class="btn btn-primary btn-sm">Crear paquete</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <x-table-search placeholder="Buscar por cliente..." />

            @if($paquetes->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <x-th-sort field="cliente" label="Cliente" />
                                <x-th-sort field="periodo" label="Periodo" />
                                <x-th-sort field="cuota" label="Cuota" />
                                <x-th-sort field="fotos" label="Fotos Cargadas" />
                                <x-th-sort field="limite" label="Fecha Límite" />
                                <x-th-sort field="estado" label="Estado" />
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paquetes as $paquete)
                                <tr>
                                    <td class="font-medium">{{ $paquete->cliente->nombre_negocio }}</td>
                                    <td>{{ ucfirst($paquete->mes_revision_legible) }}</td>
                                    <td>{{ $paquete->cantidad_requerida }}</td>
                                    <td>{{ $paquete->fotos_count }}</td>
                                    <td>{{ $paquete->fecha_limite?->format('d/m/Y') ?? '—' }}</td>
                                    <td><span class="badge {{ $badgeMap[$paquete->estatus] ?? 'badge-gray' }}">{{ str_replace('_', ' ', $paquete->estatus) }}</span></td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="{{ route('aprobaciones.show', $paquete) }}" class="btn btn-secondary btn-sm">Ver / Subir Fotos</a>
                                            @if($paquete->estatus === \App\Models\PaqueteAprobacion::ESTATUS_BORRADOR)
                                                <form action="{{ route('aprobaciones.destroy', $paquete) }}" method="POST" onsubmit="showConfirmModal('Eliminar paquete', '¿Eliminar este paquete y sus fotografías?', () => this.submit(), {danger: true}); return false;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                                </form>
                                            @endif
                                        </div>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No hay paquetes de aprobación creados</h3>
                    <p class="empty-state-description">Comienza creando uno nuevo para un cliente.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
