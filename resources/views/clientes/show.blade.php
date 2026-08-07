@extends('layouts.app')

@section('title', 'Detalle del Cliente')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('clientes.index') }}" class="breadcrumb-link">Clientes</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">{{ $cliente->nombre_negocio }}</span>
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
    ][$cliente->user->estatus] ?? 'badge-gray';
@endphp
<div class="max-w-4xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">{{ $cliente->nombre_negocio }}</h1>
            <p class="page-subtitle">{{ $cliente->giro ?? 'Sin giro especificado' }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Volver</a>
            <a href="{{ route('calendario.cliente-view', $cliente) }}" class="btn btn-secondary">Ver calendario</a>
            <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary">Editar</a>
            @if($cliente->user->estatus === \App\Models\User::ESTATUS_ACTIVO)
            <form action="{{ route('clientes.destroy', $cliente) }}" method="POST"
                  onsubmit="showConfirmModal('Dar de baja', '¿Dar de baja definitiva a {{ addslashes($cliente->nombre_negocio) }}?', () => this.submit(), {danger: true}); return false;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Dar de baja</button>
            </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Nombre de Contacto</div>
                    <div class="text-base font-medium">{{ $cliente->user->name }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Correo Electrónico</div>
                    <div class="text-base font-medium">{{ $cliente->user->email }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Servicio Contratado</div>
                    <div class="text-base font-medium">{{ $cliente->servicio_contratado ?? 'No especificado' }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Cantidad de Fotos</div>
                    <div class="text-base font-medium">{{ $cliente->cantidad_fotos }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Precio Mensual</div>
                    <div class="text-base font-medium">${{ number_format($cliente->precio_mensual, 2) }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Fecha de Registro</div>
                    <div class="text-base font-medium">{{ $cliente->fecha_registro->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Estado</div>
                    <span class="badge {{ $estatusBadge }}">{{ ucfirst(str_replace('_', ' ', $cliente->user->estatus)) }}</span>
                </div>
            </div>

            <div class="mt-6">
                <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Dirección</div>
                <div class="text-base font-medium">{{ $cliente->direccion ?? 'No especificada' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
