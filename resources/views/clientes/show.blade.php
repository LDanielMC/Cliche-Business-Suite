@extends('layouts.app')

@section('title', 'Detalle del Cliente')

@section('styles')
<style>
    .cliente-container { max-width: 800px; margin: 0 auto; padding: 25px; }
    .cliente-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .cliente-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #667eea; padding-bottom: 15px; }
    .cliente-header h1 { color: #333; font-size: 24px; margin: 0; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .info-item { margin-bottom: 15px; }
    .info-item label { display: block; color: #666; font-size: 13px; text-transform: uppercase; margin-bottom: 5px; }
    .info-item p { color: #333; font-size: 16px; font-weight: 500; margin: 0; }
    .btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; }
    .btn-primary { background: #667eea; color: white; }
    .btn-primary:hover { background: #5a6fd6; }
    .btn-secondary { background: #6c757d; color: white; }
    .btn-secondary:hover { background: #5a6268; }
    .btn-warning { background: #ffc107; color: #333; }
    .btn-warning:hover { background: #e0a800; }
    .actions { display: flex; gap: 10px; }
    @media (max-width: 768px) { .info-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="cliente-container">
    <div class="cliente-card">
        <div class="cliente-header">
            <h1>{{ $cliente->nombre_negocio }}</h1>
            <div class="actions">
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Volver</a>
                <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-warning">Editar</a>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Giro</label>
                <p>{{ $cliente->giro ?? 'No especificado' }}</p>
            </div>
            <div class="info-item">
                <label>Nombre de Contacto</label>
                <p>{{ $cliente->user->name }}</p>
            </div>
            <div class="info-item">
                <label>Correo Electrónico</label>
                <p>{{ $cliente->user->email }}</p>
            </div>
            <div class="info-item">
                <label>Servicio Contratado</label>
                <p>{{ $cliente->servicio_contratado ?? 'No especificado' }}</p>
            </div>
            <div class="info-item">
                <label>Cantidad de Fotos</label>
                <p>{{ $cliente->cantidad_fotos }}</p>
            </div>
            <div class="info-item">
                <label>Precio Mensual</label>
                <p>${{ number_format($cliente->precio_mensual, 2) }}</p>
            </div>
            <div class="info-item">
                <label>Fecha de Registro</label>
                <p>{{ $cliente->fecha_registro->format('d/m/Y') }}</p>
            </div>
            <div class="info-item">
                <label>Estado</label>
                <p><span style="background: #d4edda; color: #155724; padding: 5px 10px; border-radius: 5px; font-size: 12px;">Activo</span></p>
            </div>
        </div>

        <div class="info-item" style="margin-top: 20px;">
            <label>Dirección</label>
            <p>{{ $cliente->direccion ?? 'No especificada' }}</p>
        </div>
    </div>
</div>
@endsection
