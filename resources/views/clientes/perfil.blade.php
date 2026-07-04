@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('styles')
<style>
    .perfil-container { max-width: 800px; margin: 0 auto; }
    .perfil-header { margin-bottom: 25px; }
    .perfil-header h1 { color: #333; font-size: 28px; }
    .perfil-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .perfil-card h2 { color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #28a745; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
    .info-item { padding: 15px; background: #f8f9fa; border-radius: 8px; }
    .info-label { color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; font-weight: 600; }
    .info-value { color: #333; font-size: 16px; }
    .badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 500; text-transform: capitalize; }
    .badge-activo { background: #d4edda; color: #155724; }
    .badge-inactivo { background: #fff3cd; color: #856404; }
    .badge-suspendido { background: #f8d7da; color: #721c24; }
    .badge-baja { background: #f8d7da; color: #721c24; }
    .btn-secondary { background: #6c757d; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; display: inline-block; margin-top: 15px; }
    .btn-secondary:hover { background: #5a6268; }
</style>
@endsection

@section('content')
<div class="perfil-container">
    <div class="perfil-header">
        <h1>Mi Perfil</h1>
    </div>

    <div class="perfil-card">
        <h2>Datos del Negocio</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nombre del negocio</div>
                <div class="info-value">{{ $cliente->nombre_negocio }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Giro</div>
                <div class="info-value">{{ $cliente->giro ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Dirección</div>
                <div class="info-value">{{ $cliente->direccion ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Servicio contratado</div>
                <div class="info-value">{{ $cliente->servicio_contratado ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Fotos mensuales</div>
                <div class="info-value">{{ $cliente->cantidad_fotos }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Precio mensual</div>
                <div class="info-value">${{ number_format($cliente->precio_mensual, 2) }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Estatus</div>
                <div class="info-value">
                    @if($cliente->user->estatus === 'activo')
                        <span class="badge badge-activo">Activo</span>
                    @elseif($cliente->user->estatus === 'inactivo')
                        <span class="badge badge-inactivo">Inactivo</span>
                    @elseif($cliente->user->estatus === 'suspendido')
                        <span class="badge badge-suspendido">Suspendido</span>
                    @elseif($cliente->user->estatus === 'dado_de_baja')
                        <span class="badge badge-baja">Dado de baja</span>
                    @endif
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Fecha de registro</div>
                <div class="info-value">{{ $cliente->fecha_registro->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <div class="perfil-card">
        <h2>Datos de Contacto</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nombre</div>
                <div class="info-value">{{ $cliente->user->nombres }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Apellido paterno</div>
                <div class="info-value">{{ $cliente->user->apellido_paterno ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Apellido materno</div>
                <div class="info-value">{{ $cliente->user->apellido_materno ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Correo electrónico</div>
                <div class="info-value">{{ $cliente->user->email }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Teléfono</div>
                <div class="info-value">{{ $cliente->user->telefono ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <a href="{{ route('cliente.dashboard') }}" class="btn-secondary">Volver al dashboard</a>
</div>
@endsection
