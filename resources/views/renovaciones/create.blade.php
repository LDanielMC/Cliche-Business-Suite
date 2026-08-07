@extends('layouts.app')

@section('title', 'Nuevo Ciclo de Renovación')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('renovaciones.index') }}" class="breadcrumb-link">Renovaciones</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Nuevo Ciclo</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Nuevo ciclo de renovación</h1>
            <p class="page-subtitle">El cliente deberá realizar su primer pago antes de que inicie su plan de 30 días.</p>
        </div>
    </div>

    {{-- Flujo informativo --}}
    <div class="card" style="border-left: 4px solid var(--color-warning, #d97706); margin-bottom: 1.25rem;">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <p style="font-size:.875rem; color:var(--color-text-secondary); margin:0;">
                <strong>¿Cómo funciona?</strong><br>
                Al registrar el ciclo, el cliente quedará en estado <em>Vencido</em> y tendrá
                <strong>{{ config('renovaciones.dias_gracia', 5) }} días</strong> para enviar su comprobante de pago.
                Una vez validado, su plan de 30 días comenzará a partir de la fecha de pago declarada.
                Si no paga en ese plazo, la cuenta se suspenderá automáticamente.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('renovaciones.store') }}">
                @csrf

                <div class="form-group">
                    <label for="cliente_id" class="form-label">Cliente <span style="color:var(--color-error)">*</span></label>
                    @if(isset($clienteSeleccionado))
                        {{-- Pre-selected from a suspension renewal request — locked --}}
                        <input type="hidden" name="cliente_id" value="{{ $clienteSeleccionado->id }}">
                        <div class="form-input" style="background:var(--color-background-alt,#f8fafc); color:var(--color-text-secondary); cursor:not-allowed;">
                            {{ $clienteSeleccionado->nombre_negocio }} — {{ $clienteSeleccionado->user->email }}
                        </div>
                        <p style="font-size:.8rem; color:var(--color-text-secondary); margin-top:.3rem;">Cliente pre-seleccionado desde la solicitud de renovación.</p>
                    @else
                        <select id="cliente_id" name="cliente_id" class="form-select" required>
                            <option value="">Selecciona un cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                                    {{ $cliente->nombre_negocio }} — {{ $cliente->user->email }}
                                </option>
                            @endforeach
                        </select>
                        @error('cliente_id')<p class="form-error">{{ $message }}</p>@enderror
                        @if($clientes->isEmpty())
                            <p style="font-size:.85rem; color:var(--color-warning,#92400e); margin-top:.5rem;">
                                Todos los clientes activos ya tienen un ciclo de renovación registrado.
                            </p>
                        @endif
                    @endif
                </div>

                <div class="form-group">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" class="form-input" rows="3"
                        placeholder="Notas internas opcionales...">{{ old('observaciones') }}</textarea>
                    @error('observaciones')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('renovaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Registrar ciclo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
