@extends('layouts.app')

@section('title', 'Mi Perfil')

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
            <h1 class="page-title">Mi Perfil</h1>
            <p class="page-subtitle">Consulta la información de tu cuenta y negocio</p>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-header">
            <h2 class="card-title">Datos del Negocio</h2>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Nombre del negocio</div>
                    <div class="text-base font-medium">{{ $cliente->nombre_negocio }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Giro</div>
                    <div class="text-base font-medium">{{ $cliente->giro ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Dirección</div>
                    <div class="text-base font-medium">{{ $cliente->direccion ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Servicio contratado</div>
                    <div class="text-base font-medium">{{ $cliente->servicio_contratado ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Fotos mensuales</div>
                    <div class="text-base font-medium">{{ $cliente->cantidad_fotos }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Precio mensual</div>
                    <div class="text-base font-medium">${{ number_format($cliente->precio_mensual, 2) }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Estatus</div>
                    <span class="badge {{ $estatusBadge }}">{{ ucfirst(str_replace('_', ' ', $cliente->user->estatus)) }}</span>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Fecha de registro</div>
                    <div class="text-base font-medium">{{ $cliente->fecha_registro->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-header">
            <h2 class="card-title">Datos de Contacto</h2>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Nombre</div>
                    <div class="text-base font-medium">{{ $cliente->user->nombres }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Apellido paterno</div>
                    <div class="text-base font-medium">{{ $cliente->user->apellido_paterno ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Apellido materno</div>
                    <div class="text-base font-medium">{{ $cliente->user->apellido_materno ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Correo electrónico</div>
                    <div class="text-base font-medium">{{ $cliente->user->email }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Teléfono</div>
                    <div class="text-base font-medium">{{ $cliente->user->telefono ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-header">
            <h2 class="card-title">Calendario de Publicaciones</h2>
        </div>
        <div class="card-body" style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
            <p class="text-sm" style="color:var(--color-text-secondary); margin:0;">
                Consulta qué fotografías están programadas o ya publicadas este mes.
            </p>
            <a href="{{ route('calendario.cliente') }}" class="btn btn-primary">Ver mi calendario</a>
        </div>
    </div>

    <a href="{{ route('cliente.dashboard') }}" class="btn btn-secondary">Volver al dashboard</a>
</div>
@endsection
