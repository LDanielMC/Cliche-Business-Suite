@extends('layouts.app')

@section('title', 'Nuevo Ciclo de Renovación')

@section('styles')
<style>
    .form-container { max-width: 700px; margin: 0 auto; padding: 25px; }
    .form-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .form-card h1 { color: #333; margin-bottom: 25px; font-size: 24px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; color: #555; font-weight: 500; }
    .form-control { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; }
    .form-control.error { border-color: #dc3545; }
    .error-message { color: #dc3545; font-size: 13px; margin-top: 5px; }
    .form-actions { display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; }
    .btn { padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: 500; border: none; cursor: pointer; font-size: 15px; }
    .btn-primary { background: #667eea; color: white; }
    .btn-secondary { background: #6c757d; color: white; }
    @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="form-container">
    <div class="form-card">
        <h1>Nuevo Ciclo de Renovación</h1>

        <form method="POST" action="{{ route('renovaciones.store') }}">
            @csrf

            <div class="form-group">
                <label for="cliente_id">Cliente *</label>
                <select id="cliente_id" name="cliente_id" class="form-control @error('cliente_id') error @enderror" required>
                    <option value="">Selecciona un cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>{{ $cliente->nombre_negocio }}</option>
                    @endforeach
                </select>
                @error('cliente_id')
                    <span class="error-message">{{ $message }}</span>
                @enderror
                @if($clientes->isEmpty())
                    <p style="color:#856404; font-size:13px; margin-top:6px;">Todos los clientes activos ya tienen un ciclo de renovación vigente.</p>
                @endif
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_inicio">Fecha de Inicio *</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control @error('fecha_inicio') error @enderror" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}" required>
                    @error('fecha_inicio')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="duracion_meses">Duración (meses) *</label>
                    <input type="number" id="duracion_meses" name="duracion_meses" min="1" max="12" class="form-control @error('duracion_meses') error @enderror" value="{{ old('duracion_meses', 1) }}" required>
                    @error('duracion_meses')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('renovaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Ciclo</button>
            </div>
        </form>
    </div>
</div>
@endsection
