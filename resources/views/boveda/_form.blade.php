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
                <option value="{{ $cliente->id }}" @selected(old('cliente_id', $credencial->cliente_id ?? '') == $cliente->id)>
                    {{ $cliente->nombre_negocio }}
                </option>
            @endforeach
        </select>
        @error('cliente_id')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="nombre_plataforma">Plataforma / Servicio *</label>
        <input type="text" id="nombre_plataforma" name="nombre_plataforma" class="form-control @error('nombre_plataforma') error @enderror" value="{{ old('nombre_plataforma', $credencial->nombre_plataforma ?? '') }}" maxlength="100" required>
        @error('nombre_plataforma')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-2">
        <label for="url_acceso">URL de Acceso</label>
        <input type="text" id="url_acceso" name="url_acceso" class="form-control @error('url_acceso') error @enderror" value="{{ old('url_acceso', $credencial->url_acceso ?? '') }}" maxlength="255" placeholder="https://...">
        @error('url_acceso')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="correo_asociado">Correo Asociado</label>
        <input type="email" id="correo_asociado" name="correo_asociado" class="form-control @error('correo_asociado') error @enderror" value="{{ old('correo_asociado', $credencial->correo_asociado ?? '') }}" maxlength="150">
        @error('correo_asociado')
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
    <label for="observaciones">Observaciones</label>
    <textarea id="observaciones" name="observaciones" class="form-control @error('observaciones') error @enderror" rows="3">{{ old('observaciones', $credencial->observaciones ?? '') }}</textarea>
    @error('observaciones')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-actions">
    <a href="{{ route('boveda.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Credencial' : 'Guardar Credencial' }}</button>
</div>
