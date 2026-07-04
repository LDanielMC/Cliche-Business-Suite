@extends('layouts.app')

@section('title', 'Detalle del Operador')

@section('styles')
<style>
    .detail-container { max-width: 700px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .detail-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .detail-header h1 { color: #333; font-size: 28px; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
    .info-item { padding: 15px; background: #f8f9fa; border-radius: 8px; }
    .info-label { color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; font-weight: 600; }
    .info-value { color: #333; font-size: 16px; }
    .badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 500; text-transform: capitalize; }
    .badge-activo { background: #d4edda; color: #155724; }
    .badge-inactivo { background: #fff3cd; color: #856404; }
    .badge-suspendido { background: #f8d7da; color: #721c24; }
    .actions { margin-top: 25px; display: flex; gap: 10px; }
    .btn-secondary { background: #6c757d; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; }
    .btn-secondary:hover { background: #5a6268; }
    .btn-warning { background: #ffc107; color: #333; padding: 10px 20px; border-radius: 6px; text-decoration: none; }
    .btn-warning:hover { background: #e0a800; }
</style>
@endsection

@section('content')
<div class="detail-container">
    <div class="detail-header">
        <h1>Detalle del Operador</h1>
        <a href="{{ route('operadores.index') }}" class="btn-secondary">Volver</a>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Nombre de usuario</div>
            <div class="info-value">{{ $operador->name }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Correo electrónico</div>
            <div class="info-value">{{ $operador->email }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Nombres</div>
            <div class="info-value">{{ $operador->nombres ?? 'N/A' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Apellido paterno</div>
            <div class="info-value">{{ $operador->apellido_paterno ?? 'N/A' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Apellido materno</div>
            <div class="info-value">{{ $operador->apellido_materno ?? 'N/A' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Teléfono</div>
            <div class="info-value">{{ $operador->telefono ?? 'N/A' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Estatus</div>
            <div class="info-value">
                @if($operador->estatus === 'activo')
                    <span class="badge badge-activo">Activo</span>
                @elseif($operador->estatus === 'inactivo')
                    <span class="badge badge-inactivo">Inactivo</span>
                @elseif($operador->estatus === 'suspendido')
                    <span class="badge badge-suspendido">Suspendido</span>
                @endif
            </div>
        </div>
        <div class="info-item">
            <div class="info-label">Fecha de registro</div>
            <div class="info-value">{{ $operador->created_at->format('d/m/Y H:i') }}</div>
        </div>
        @if($operador->fecha_baja)
            <div class="info-item">
                <div class="info-label">Fecha de baja</div>
                <div class="info-value">{{ $operador->fecha_baja->format('d/m/Y') }}</div>
            </div>
        @endif
    </div>

    <div class="actions">
        <a href="{{ route('operadores.edit', $operador) }}" class="btn-warning">Editar</a>
        <a href="{{ route('operadores.index') }}" class="btn-secondary">Volver al listado</a>
    </div>
</div>
@endsection
