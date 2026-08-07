@extends('layouts.app')

@section('title', 'Detalle del Operador')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('operadores.index') }}" class="breadcrumb-link">Operadores</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">{{ $operador->name }}</span>
        </div>
    </div>
@endsection

@section('content')
@php
    $badgeMap = ['activo' => 'badge-success', 'inactivo' => 'badge-warning', 'suspendido' => 'badge-error'];
@endphp
<div class="max-w-3xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Detalle del Operador</h1>
            <p class="page-subtitle">{{ $operador->name }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('operadores.index') }}" class="btn btn-secondary">Volver</a>
            <a href="{{ route('operadores.edit', $operador) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Nombre de usuario</div>
                    <div class="text-base font-medium">{{ $operador->name }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Correo electrónico</div>
                    <div class="text-base font-medium">{{ $operador->email }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Nombres</div>
                    <div class="text-base font-medium">{{ $operador->nombres ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Apellido paterno</div>
                    <div class="text-base font-medium">{{ $operador->apellido_paterno ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Apellido materno</div>
                    <div class="text-base font-medium">{{ $operador->apellido_materno ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Teléfono</div>
                    <div class="text-base font-medium">{{ $operador->telefono ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Estatus</div>
                    <span class="badge {{ $badgeMap[$operador->estatus] ?? 'badge-gray' }}">{{ ucfirst($operador->estatus) }}</span>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Fecha de registro</div>
                    <div class="text-base font-medium">{{ $operador->created_at->format('d/m/Y H:i') }}</div>
                </div>
                @if($operador->fecha_baja)
                    <div>
                        <div class="text-xs uppercase text-gray-500 mb-1 tracking-wide">Fecha de baja</div>
                        <div class="text-base font-medium">{{ $operador->fecha_baja->format('d/m/Y') }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
