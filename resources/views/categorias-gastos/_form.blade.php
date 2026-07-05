@csrf

@if($edit)
    @method('PUT')
@endif

<div class="form-group">
    <label for="nombre">Nombre *</label>
    <input type="text" id="nombre" name="nombre" class="form-control @error('nombre') error @enderror" value="{{ old('nombre', $categoria->nombre ?? '') }}" maxlength="100" required>
    @error('nombre')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="descripcion">Descripción</label>
    <input type="text" id="descripcion" name="descripcion" class="form-control @error('descripcion') error @enderror" value="{{ old('descripcion', $categoria->descripcion ?? '') }}" maxlength="255">
    @error('descripcion')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-actions">
    <a href="{{ route('categorias-gastos.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Categoría' : 'Guardar Categoría' }}</button>
</div>
