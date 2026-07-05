@csrf

@if($edit)
    @method('PUT')
@endif

<div class="form-row">
    <div class="form-group col-2">
        <label for="categoria_gasto_id">Categoría *</label>
        <select id="categoria_gasto_id" name="categoria_gasto_id" class="form-control @error('categoria_gasto_id') error @enderror" required>
            <option value="">Selecciona una categoría</option>
            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected(old('categoria_gasto_id', $gasto->categoria_gasto_id ?? '') == $categoria->id)>
                    {{ $categoria->nombre_categoria }}
                </option>
            @endforeach
        </select>
        @error('categoria_gasto_id')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="cliente_id">Cliente (opcional)</label>
        <select id="cliente_id" name="cliente_id" class="form-control @error('cliente_id') error @enderror">
            <option value="">Gasto general (no asociado a un cliente)</option>
            @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}" @selected(old('cliente_id', $gasto->cliente_id ?? '') == $cliente->id)>
                    {{ $cliente->nombre_negocio }}
                </option>
            @endforeach
        </select>
        @error('cliente_id')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="concepto_gasto">Concepto *</label>
    <input type="text" id="concepto_gasto" name="concepto_gasto" class="form-control @error('concepto_gasto') error @enderror" value="{{ old('concepto_gasto', $gasto->concepto_gasto ?? '') }}" maxlength="200" required>
    @error('concepto_gasto')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-row">
    <div class="form-group col-2">
        <label for="monto">Monto *</label>
        <input type="number" id="monto" name="monto" step="0.01" min="0" class="form-control @error('monto') error @enderror" value="{{ old('monto', $gasto->monto ?? '') }}" required>
        @error('monto')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="fecha_gasto">Fecha del Gasto *</label>
        <input type="date" id="fecha_gasto" name="fecha_gasto" class="form-control @error('fecha_gasto') error @enderror" value="{{ old('fecha_gasto', isset($gasto) ? $gasto->fecha_gasto->format('Y-m-d') : '') }}" required>
        @error('fecha_gasto')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-2">
        <label for="forma_pago">Forma de Pago *</label>
        <select id="forma_pago" name="forma_pago" class="form-control @error('forma_pago') error @enderror" required>
            <option value="">Selecciona una opción</option>
            @foreach(\App\Models\GastoOperativo::FORMA_PAGO as $forma)
                <option value="{{ $forma }}" @selected(old('forma_pago', $gasto->forma_pago ?? '') == $forma)>{{ ucfirst($forma) }}</option>
            @endforeach
        </select>
        @error('forma_pago')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="comprobante">Comprobante (imagen o PDF)</label>
        <input type="file" id="comprobante" name="comprobante" class="form-control @error('comprobante') error @enderror" accept=".jpg,.jpeg,.png,.pdf">
        @if(isset($gasto) && $gasto->comprobante)
            <p style="font-size:13px; margin-top:6px;"><a href="{{ $gasto->comprobante_url }}" target="_blank">Ver comprobante actual</a></p>
        @endif
        @error('comprobante')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="observaciones">Observaciones</label>
    <textarea id="observaciones" name="observaciones" class="form-control @error('observaciones') error @enderror" rows="3">{{ old('observaciones', $gasto->observaciones ?? '') }}</textarea>
    @error('observaciones')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-actions">
    <a href="{{ route('gastos.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Gasto' : 'Guardar Gasto' }}</button>
</div>
