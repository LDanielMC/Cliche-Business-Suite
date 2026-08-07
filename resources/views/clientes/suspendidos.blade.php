@extends('layouts.app')

@section('title', 'Clientes suspendidos')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('clientes.index') }}" class="breadcrumb-link">Clientes</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Suspendidos</span>
        </div>
    </div>
@endsection

@section('content')
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Clientes suspendidos</h1>
            <p class="page-subtitle">Servicio pausado por falta de pago — se reactivan automáticamente al completar el proceso de renovación</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Volver a Clientes</a>
        </div>
    </div>

    <div class="card mb-4" style="border-left: 4px solid var(--color-warning, #f59e0b);">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <p style="margin:0; font-size:.875rem;">
                <strong>Importante:</strong> Un cliente suspendido solo puede volver a <em>Activo</em> completando el flujo de renovación y teniendo un pago validado.
                No existe reactivación manual para garantizar la trazabilidad.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <x-table-search placeholder="Buscar por negocio, contacto o email..." />

            @if($clientes->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <x-th-sort field="cliente" label="Negocio" />
                                <th>Contacto</th>
                                <th>Email</th>
                                <th>Vencimiento</th>
                                <th>Días vencido</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                                @php
                                    $renovacion  = $cliente->renovacionActiva;
                                    $diasVencido = $renovacion
                                        ? max(0, (int) now()->startOfDay()->diffInDays($renovacion->fecha_vencimiento, false) * -1)
                                        : null;
                                @endphp
                                <tr>
                                    <td class="font-medium">{{ $cliente->nombre_negocio }}</td>
                                    <td>{{ $cliente->user->name }}</td>
                                    <td>{{ $cliente->user->email }}</td>
                                    <td>
                                        @if($renovacion)
                                            {{ $renovacion->fecha_vencimiento->format('d/m/Y') }}
                                        @else
                                            <span style="color:var(--color-text-secondary);">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($diasVencido !== null && $diasVencido > 0)
                                            <span class="badge badge-error">{{ $diasVencido }} día(s)</span>
                                        @else
                                            <span style="color:var(--color-text-secondary);">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:center;">
                                            <a href="{{ route('renovaciones.index') }}?cliente={{ $cliente->id }}"
                                               class="btn btn-primary btn-sm">Ver renovación</a>
                                            <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST"
                                                  onsubmit="showConfirmModal('Dar de baja', '¿Dar de baja definitiva a {{ addslashes($cliente->nombre_negocio) }}?', () => this.submit(), {danger: true}); return false;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Dar de baja</button>
                                            </form>
                                        </div>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No hay clientes suspendidos</h3>
                    <p class="empty-state-description">Todos los clientes están al corriente</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
