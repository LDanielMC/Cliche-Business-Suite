@csrf

@if($edit)
    @method('PUT')
@endif

<div class="form-row">
    <div class="form-group col-2">
        <label for="nombre_servicio">Servicio / Plataforma *</label>
        <input type="text" id="nombre_servicio" name="nombre_servicio" class="form-control @error('nombre_servicio') error @enderror" value="{{ old('nombre_servicio', $credencial->nombre_servicio ?? '') }}" maxlength="150" required>
        @error('nombre_servicio')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="url">URL</label>
        <input type="text" id="url" name="url" class="form-control @error('url') error @enderror" value="{{ old('url', $credencial->url ?? '') }}" maxlength="255" placeholder="https://...">
        @error('url')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-2">
        <label for="usuario">Usuario *</label>
        <input type="text" id="usuario" name="usuario" class="form-control @error('usuario') error @enderror" value="{{ old('usuario', $credencial->usuario ?? '') }}" maxlength="150" required>
        @error('usuario')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2" x-data="{ visible: false }">
        <label for="password">Contraseña {{ $edit ? '(dejar en blanco para no cambiar)' : '*' }}</label>
        <div style="display:flex; gap:8px;">
            <input :type="visible ? 'text' : 'password'" id="password" name="password" class="form-control @error('password') error @enderror" autocomplete="new-password" @if(!$edit) required @endif>
            <button type="button" class="btn btn-secondary" style="padding: 12px 16px;" @click="visible = !visible" x-text="visible ? 'Ocultar' : 'Mostrar'"></button>
        </div>
        @error('password')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="notas">Notas</label>
    <input type="text" id="notas" name="notas" class="form-control @error('notas') error @enderror" value="{{ old('notas', $credencial->notas ?? '') }}" maxlength="255">
    @error('notas')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-actions">
    <a href="{{ route('boveda.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Credencial' : 'Guardar Credencial' }}</button>
</div>
