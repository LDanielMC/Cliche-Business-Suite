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
                    {{ $categoria->nombre }}
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
    <label for="concepto">Concepto *</label>
    <input type="text" id="concepto" name="concepto" class="form-control @error('concepto') error @enderror" value="{{ old('concepto', $gasto->concepto ?? '') }}" maxlength="150" required>
    @error('concepto')
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

<div class="form-group">
    <label for="notas">Notas</label>
    <input type="text" id="notas" name="notas" class="form-control @error('notas') error @enderror" value="{{ old('notas', $gasto->notas ?? '') }}" maxlength="255">
    @error('notas')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-actions">
    <a href="{{ route('gastos.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Gasto' : 'Guardar Gasto' }}</button>
</div>
