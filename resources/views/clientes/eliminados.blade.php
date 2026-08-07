@extends('layouts.app')

@section('title', 'Clientes dados de baja')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('clientes.index') }}" class="breadcrumb-link">Clientes</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Dados de baja</span>
        </div>
    </div>
@endsection

@section('content')
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Clientes dados de baja</h1>
            <p class="page-subtitle">Clientes archivados — al reactivarlos quedarán activos y podrán iniciar un nuevo ciclo de renovación</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Volver a Clientes</a>
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
                                <x-th-sort field="fecha_baja" label="Fecha de baja" />
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                                <tr>
                                    <td class="font-medium">{{ $cliente->nombre_negocio }}</td>
                                    <td>{{ $cliente->user->name }}</td>
                                    <td>{{ $cliente->user->email }}</td>
                                    <td>{{ $cliente->user->fecha_baja ? $cliente->user->fecha_baja->format('d/m/Y') : 'N/A' }}</td>
                                    <td><span class="badge badge-error">Dado de baja</span></td>
                                    <td>
                                        <form action="{{ route('clientes.restaurar', $cliente->id) }}" method="POST"
                                              onsubmit="showConfirmModal('Reactivar cliente', 'El cliente quedará activo y podrá iniciar un nuevo ciclo de renovación. ¿Continuar?', () => this.submit()); return false;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">Reactivar</button>
                                        </form>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No hay clientes dados de baja</h3>
                    <p class="empty-state-description">Todos los clientes están activos</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
