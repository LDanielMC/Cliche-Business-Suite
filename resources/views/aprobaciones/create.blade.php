@extends('layouts.app')

@section('title', 'Nuevo Paquete de Aprobación')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('aprobaciones.index') }}" class="breadcrumb-link">Aprobaciones</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Nuevo Paquete</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Nuevo Paquete de Aprobación</h1>
            <p class="page-subtitle">Crea un paquete mensual de fotos para un cliente</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($clientes->isEmpty())
                <div class="empty-state">
                    <p class="empty-state-description">
                        No hay clientes elegibles en este momento. Un cliente aparece aquí solo si está activo,
                        tiene una renovación vigente o por vencer, y no tiene ya un paquete abierto (borrador o en revisión).
                    </p>
                </div>
            @else
            <form method="POST" action="{{ route('aprobaciones.store') }}">
                @csrf

                <div class="form-group">
                    <label for="cliente_id" class="form-label">Cliente <span class="required">*</span></label>
                    <select id="cliente_id" name="cliente_id" class="form-select @error('cliente_id') error @enderror" required>
                        <option value="">Selecciona un cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}"
                                data-mes-revision="{{ $cliente->renovacionActiva->fecha_inicio->format('Y-m') }}"
                                data-periodo="{{ $cliente->renovacionActiva->fecha_inicio->format('d/m/Y') }} — {{ $cliente->renovacionActiva->fecha_vencimiento->format('d/m/Y') }}"
                                @selected(old('cliente_id', request('cliente_id')) == $cliente->id)>
                                {{ $cliente->nombre_negocio }} (cuota: {{ $cliente->cantidad_fotos }} fotos/mes)
                            </option>
                        @endforeach
                    </select>
                    @error('cliente_id')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                    <p id="periodo-hint" style="display:none;margin-top:.35rem;font-size:.82rem;color:#6b7280;"></p>
                </div>

                <div class="form-group">
                    <label for="mes_revision" class="form-label">Mes de Revisión <span class="required">*</span></label>
                    <input type="month" id="mes_revision" name="mes_revision" class="form-input @error('mes_revision') error @enderror" value="{{ old('mes_revision') }}" readonly required style="background-color:#f3f4f6;cursor:not-allowed;">
                    <p style="margin-top:.35rem;font-size:.82rem;color:#6b7280;">Se calcula automáticamente a partir del período de renovación vigente del cliente.</p>
                    @error('mes_revision')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" class="form-textarea @error('observaciones') error @enderror" rows="3">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <a href="{{ route('aprobaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Crear Paquete</button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>

@section('scripts')
<script>
(function () {
    const select = document.getElementById('cliente_id');
    const mesInput = document.getElementById('mes_revision');
    const hint = document.getElementById('periodo-hint');
    if (!select || !mesInput) return;

    function actualizar() {
        const opt = select.options[select.selectedIndex];
        const mes = opt ? opt.dataset.mesRevision : '';
        const periodo = opt ? opt.dataset.periodo : '';

        mesInput.value = mes || '';

        if (periodo) {
            hint.textContent = 'Período de renovación: ' + periodo;
            hint.style.display = 'block';
        } else {
            hint.style.display = 'none';
        }
    }

    select.addEventListener('change', actualizar);
    actualizar();
})();
</script>
@endsection
@endsection
