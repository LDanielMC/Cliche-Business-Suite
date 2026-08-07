@extends('layouts.app')

@section('title', 'Control de Renovaciones')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Renovaciones</span>
        </div>
    </div>
@endsection

@php
    $badgeMap = [
        'vigente'           => ['class' => 'badge-success',  'label' => 'Vigente'],
        'por_vencer'        => ['class' => 'badge-warning',  'label' => 'Próximo a vencer'],
        'en_revision'       => ['class' => 'badge-primary',  'label' => 'En revisión'],
        'pago_rechazado'    => ['class' => 'badge-error',    'label' => 'Pago rechazado'],
        'pago_validado'     => ['class' => 'badge-success',  'label' => 'Pago validado'],
        'factura_pendiente' => ['class' => 'badge-warning',  'label' => 'Factura pendiente'],
        'vencido'           => ['class' => 'badge-error',    'label' => 'Vencido'],
    ];
@endphp

@section('content')
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Control de Renovaciones</h1>
            <p class="page-subtitle">Monitorea y valida los pagos de renovación de tus clientes</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('renovaciones.create') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo ciclo
            </a>
        </div>
    </div>

    {{-- Counter cards --}}
    <div class="grid gap-4 mb-6" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
        <a href="{{ route('renovaciones.index', ['filtro' => 'solicitudes']) }}"
           class="card text-center {{ $filtro === 'solicitudes' ? 'ring-2 ring-purple-500' : '' }}" style="cursor:pointer; text-decoration:none;">
            <div class="card-body" style="padding: 1rem;">
                <div style="font-size:2rem; font-weight:700; color: #7c3aed;">{{ $counters->solicitudes ?? 0 }}</div>
                <div style="font-size:.8rem; color: var(--color-text-secondary); margin-top:.25rem;">Solicitudes</div>
            </div>
        </a>
        <a href="{{ route('renovaciones.index', ['filtro' => 'pendientes']) }}"
           class="card text-center {{ $filtro === 'pendientes' ? 'ring-2 ring-blue-500' : '' }}" style="cursor:pointer; text-decoration:none;">
            <div class="card-body" style="padding: 1rem;">
                <div style="font-size:2rem; font-weight:700; color: var(--color-brand);">{{ $counters->pendientes ?? 0 }}</div>
                <div style="font-size:.8rem; color: var(--color-text-secondary); margin-top:.25rem;">Pendientes revisión</div>
            </div>
        </a>
        <a href="{{ route('renovaciones.index', ['filtro' => 'por_vencer']) }}"
           class="card text-center {{ $filtro === 'por_vencer' ? 'ring-2 ring-yellow-500' : '' }}" style="cursor:pointer; text-decoration:none;">
            <div class="card-body" style="padding: 1rem;">
                <div style="font-size:2rem; font-weight:700; color: var(--color-warning, #f59e0b);">{{ $counters->por_vencer ?? 0 }}</div>
                <div style="font-size:.8rem; color: var(--color-text-secondary); margin-top:.25rem;">Próximos a vencer</div>
            </div>
        </a>
        <a href="{{ route('renovaciones.index', ['filtro' => 'vigentes']) }}"
           class="card text-center {{ $filtro === 'vigentes' ? 'ring-2 ring-green-500' : '' }}" style="cursor:pointer; text-decoration:none;">
            <div class="card-body" style="padding: 1rem;">
                <div style="font-size:2rem; font-weight:700; color: var(--color-success);">{{ $counters->vigentes ?? 0 }}</div>
                <div style="font-size:.8rem; color: var(--color-text-secondary); margin-top:.25rem;">Vigentes</div>
            </div>
        </a>
        <a href="{{ route('renovaciones.index', ['filtro' => 'vencidos']) }}"
           class="card text-center {{ $filtro === 'vencidos' ? 'ring-2 ring-red-500' : '' }}" style="cursor:pointer; text-decoration:none;">
            <div class="card-body" style="padding: 1rem;">
                <div style="font-size:2rem; font-weight:700; color: var(--color-error);">{{ $counters->vencidos ?? 0 }}</div>
                <div style="font-size:.8rem; color: var(--color-text-secondary); margin-top:.25rem;">Vencidos</div>
            </div>
        </a>
        <a href="{{ route('renovaciones.index') }}"
           class="card text-center {{ $filtro === 'todas' ? 'ring-2 ring-gray-400' : '' }}" style="cursor:pointer; text-decoration:none;">
            <div class="card-body" style="padding: 1rem;">
                <div style="font-size:2rem; font-weight:700; color: var(--color-text-secondary);">Todas</div>
                <div style="font-size:.8rem; color: var(--color-text-secondary); margin-top:.25rem;">Sin filtro</div>
            </div>
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <x-table-search placeholder="Buscar por cliente..." />

            @if($renovaciones->count() > 0)
                <p style="font-size:.75rem; color:var(--color-text-secondary); margin-bottom:.75rem;">
                    Selecciona un registro para revisar el comprobante y tomar acciones.
                </p>
                <div class="table-container">
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <x-th-sort field="cliente" label="Cliente" />
                                <x-th-sort field="vencimiento" label="Vencimiento" class="hide-mobile" />
                                <x-th-sort field="dias" label="Días" />
                                <x-th-sort field="estatus" label="Estatus" />
                                <th style="width:2rem;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($renovaciones as $r)
                            @php
                                $dias  = $r->diasRestantes();
                                $badge = $badgeMap[$r->estatus] ?? ['class'=>'badge-gray','label'=>$r->estatus];
                            @endphp
                            <tr class="tr-link" onclick="window.location='{{ route('renovaciones.show', $r) }}'">
                                <td data-label="Cliente">
                                    <div class="font-medium">{{ $r->cliente->nombre_negocio }}</div>
                                    <div style="font-size:.75rem; color:var(--color-text-secondary); margin-top:.1rem;">{{ $r->cliente->user->email }}</div>
                                </td>
                                <td data-label="Vencimiento" class="hide-mobile">{{ $r->fecha_vencimiento->format('d/m/Y') }}</td>
                                <td data-label="Días">
                                    @if($dias < 0)
                                        <span style="color:var(--color-error); font-weight:600;">Vencido</span>
                                    @elseif($dias <= 5)
                                        <span style="color:var(--color-warning,#f59e0b); font-weight:600;">{{ $dias }}d</span>
                                    @else
                                        <span style="color:var(--color-text-secondary);">{{ $dias }}d</span>
                                    @endif
                                </td>
                                <td data-label="Estatus">
                                    <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                    @if($r->tieneSolicitudPendiente())
                                        <span class="badge" style="background:#ede9fe; color:#6d28d9; margin-left:.25rem;">Solicitud</span>
                                    @endif
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
                    {{ $renovaciones->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No hay renovaciones en esta categoría</h3>
                    <p class="empty-state-description">Prueba con otro filtro o registra un nuevo ciclo.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
