@csrf

@if($edit)
    @method('PUT')
@endif

<div class="form-group">
    <label for="nombre" class="form-label">Nombre <span class="required">*</span></label>
    <input type="text" id="nombre" name="nombre" class="form-input @error('nombre') error @enderror" value="{{ old('nombre', $categoria->nombre ?? '') }}" maxlength="100" required>
    @error('nombre')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="flex justify-end gap-3 mt-8">
    <a href="{{ route('categorias-gastos.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Categoría' : 'Guardar Categoría' }}</button>
</div>
