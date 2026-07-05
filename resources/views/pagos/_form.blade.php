@csrf

@if($edit)
    @method('PUT')
@endif

<div class="form-row">
    <div class="form-group col-2">
        <label for="cliente_id">Cliente *</label>
        <select id="cliente_id" name="cliente_id" class="form-control @error('cliente_id') error @enderror" required>
            <option value="">Selecciona un cliente</option>
            @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}" @selected(old('cliente_id', $pago->cliente_id ?? '') == $cliente->id)>
                    {{ $cliente->nombre_negocio }}
                </option>
            @endforeach
        </select>
        @error('cliente_id')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="monto">Monto *</label>
        <input type="number" id="monto" name="monto" step="0.01" min="0" class="form-control @error('monto') error @enderror" value="{{ old('monto', $pago->monto ?? '') }}" required>
        @error('monto')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-2">
        <label for="fecha_pago">Fecha de Pago *</label>
        <input type="date" id="fecha_pago" name="fecha_pago" class="form-control @error('fecha_pago') error @enderror" value="{{ old('fecha_pago', isset($pago) ? $pago->fecha_pago->format('Y-m-d') : '') }}" required>
        @error('fecha_pago')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="metodo_pago">Método de Pago</label>
        <input type="text" id="metodo_pago" name="metodo_pago" class="form-control @error('metodo_pago') error @enderror" value="{{ old('metodo_pago', $pago->metodo_pago ?? '') }}" maxlength="50" placeholder="Efectivo, transferencia, tarjeta...">
        @error('metodo_pago')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="concepto">Concepto</label>
    <input type="text" id="concepto" name="concepto" class="form-control @error('concepto') error @enderror" value="{{ old('concepto', $pago->concepto ?? '') }}" maxlength="255">
    @error('concepto')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-actions">
    <a href="{{ route('pagos.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Pago' : 'Guardar Pago' }}</button>
</div>
