@csrf

@if($edit)
    @method('PUT')
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="form-group">
        <label for="categoria_gasto_id" class="form-label">Categoría <span class="required">*</span></label>
        <select id="categoria_gasto_id" name="categoria_gasto_id" class="form-select @error('categoria_gasto_id') error @enderror" required>
            <option value="">Selecciona una categoría</option>
            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected(old('categoria_gasto_id', $gasto->categoria_gasto_id ?? '') == $categoria->id)>
                    {{ $categoria->nombre }}
                </option>
            @endforeach
        </select>
        @error('categoria_gasto_id')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="cliente_id" class="form-label">Cliente (opcional)</label>
        <select id="cliente_id" name="cliente_id" class="form-select @error('cliente_id') error @enderror">
            <option value="">Gasto general (no asociado a un cliente)</option>
            @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}" @selected(old('cliente_id', $gasto->cliente_id ?? '') == $cliente->id)>
                    {{ $cliente->nombre_negocio }}
                </option>
            @endforeach
        </select>
        @error('cliente_id')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="concepto_gasto" class="form-label">Concepto <span class="required">*</span></label>
    <input type="text" id="concepto_gasto" name="concepto_gasto" class="form-input @error('concepto_gasto') error @enderror" value="{{ old('concepto_gasto', $gasto->concepto_gasto ?? '') }}" maxlength="200" required>
    @error('concepto_gasto')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="form-group">
        <label for="monto" class="form-label">Monto <span class="required">*</span></label>
        <input type="number" id="monto" name="monto" step="0.01" min="0" class="form-input @error('monto') error @enderror" value="{{ old('monto', $gasto->monto ?? '') }}" required>
        @error('monto')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="fecha_gasto" class="form-label">Fecha del Gasto <span class="required">*</span></label>
        <input type="date" id="fecha_gasto" name="fecha_gasto" class="form-input @error('fecha_gasto') error @enderror" value="{{ old('fecha_gasto', isset($gasto) ? $gasto->fecha_gasto->format('Y-m-d') : '') }}" required>
        @error('fecha_gasto')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="form-group">
        <label for="forma_pago" class="form-label">Forma de Pago <span class="required">*</span></label>
        <select id="forma_pago" name="forma_pago" class="form-select @error('forma_pago') error @enderror" required>
            <option value="">Selecciona una opción</option>
            @foreach(\App\Models\GastoOperativo::FORMA_PAGO as $forma)
                <option value="{{ $forma }}" @selected(old('forma_pago', $gasto->forma_pago ?? '') == $forma)>{{ ucfirst($forma) }}</option>
            @endforeach
        </select>
        @error('forma_pago')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="comprobante" class="form-label">Comprobante (imagen o PDF)</label>
        <input type="file" id="comprobante" name="comprobante" class="form-input @error('comprobante') error @enderror" accept=".jpg,.jpeg,.png,.pdf">
        @if(isset($gasto) && $gasto->comprobante)
            <p class="text-sm mt-2"><a href="{{ $gasto->comprobante_url }}" target="_blank" class="text-blue-600 underline">Ver comprobante actual</a></p>
        @endif
        @error('comprobante')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="observaciones" class="form-label">Observaciones</label>
    <textarea id="observaciones" name="observaciones" class="form-textarea @error('observaciones') error @enderror" rows="3">{{ old('observaciones', $gasto->observaciones ?? '') }}</textarea>
    @error('observaciones')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="flex justify-end gap-3 mt-8">
    <a href="{{ route('gastos.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Gasto' : 'Guardar Gasto' }}</button>
</div>
