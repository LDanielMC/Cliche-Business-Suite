@csrf

@if($edit)
    @method('PUT')
@endif

<div class="form-group">
    <label for="nombre_categoria">Nombre *</label>
    <input type="text" id="nombre_categoria" name="nombre_categoria" class="form-control @error('nombre_categoria') error @enderror" value="{{ old('nombre_categoria', $categoria->nombre_categoria ?? '') }}" maxlength="100" required>
    @error('nombre_categoria')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-actions">
    <a href="{{ route('categorias-gastos.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Categoría' : 'Guardar Categoría' }}</button>
</div>
