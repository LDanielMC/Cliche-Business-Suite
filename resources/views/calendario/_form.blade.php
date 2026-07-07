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
                <option value="{{ $cliente->id }}" @selected(old('cliente_id', $publicacion->cliente_id ?? '') == $cliente->id)>
                    {{ $cliente->nombre_negocio }} ({{ $cliente->user->name }})
                </option>
            @endforeach
        </select>
        @error('cliente_id')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="fecha_publicacion_programada">Fecha y Hora de Publicación *</label>
        <input type="datetime-local" id="fecha_publicacion_programada" name="fecha_publicacion_programada" class="form-control @error('fecha_publicacion_programada') error @enderror" value="{{ old('fecha_publicacion_programada', isset($publicacion) ? $publicacion->fecha_publicacion_programada->format('Y-m-d\TH:i') : '') }}" required>
        @error('fecha_publicacion_programada')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="fotografia_asociada">Fotografía {{ $edit ? '(deja en blanco para conservar la actual)' : '*' }}</label>
    <input type="file" id="fotografia_asociada" name="fotografia_asociada" class="form-control @error('fotografia_asociada') error @enderror" accept="image/*" @if(!$edit) required @endif>
    @if($edit && $publicacion->fotografia_asociada)
        <img src="{{ $publicacion->fotografia_url }}" alt="Foto actual" style="max-width:150px; margin-top:10px; border-radius:8px;">
    @endif
    @error('fotografia_asociada')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="estatus">Estatus *</label>
    <select id="estatus" name="estatus" class="form-control @error('estatus') error @enderror" required>
        @foreach(\App\Models\CalendarioFoto::ESTATUS as $estatusOpcion)
            <option value="{{ $estatusOpcion }}" @selected(old('estatus', $publicacion->estatus ?? 'programada') == $estatusOpcion)>
                {{ ucfirst($estatusOpcion) }}
            </option>
        @endforeach
    </select>
    @error('estatus')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="observaciones">Observaciones</label>
    <textarea id="observaciones" name="observaciones" class="form-control @error('observaciones') error @enderror" rows="3">{{ old('observaciones', $publicacion->observaciones ?? '') }}</textarea>
    @error('observaciones')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-actions">
    <a href="{{ route('calendario.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Publicación' : 'Guardar Publicación' }}</button>
</div>
