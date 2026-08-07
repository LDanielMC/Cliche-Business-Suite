@csrf

@if($edit)
    @method('PUT')
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="form-group">
        <label for="cliente_id" class="form-label">Cliente <span class="required">*</span></label>
        <select id="cliente_id" name="cliente_id" class="form-select @error('cliente_id') error @enderror" required>
            <option value="">Selecciona un cliente</option>
            @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}" @selected(old('cliente_id', $publicacion->cliente_id ?? '') == $cliente->id)>
                    {{ $cliente->nombre_negocio }} ({{ $cliente->user->name }})
                </option>
            @endforeach
        </select>
        @error('cliente_id')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="fecha_publicacion_programada" class="form-label">Fecha y Hora de Publicación <span class="required">*</span></label>
        <input type="datetime-local" id="fecha_publicacion_programada" name="fecha_publicacion_programada" class="form-input @error('fecha_publicacion_programada') error @enderror" value="{{ old('fecha_publicacion_programada', isset($publicacion) ? $publicacion->fecha_publicacion_programada->format('Y-m-d\TH:i') : '') }}" required>
        @error('fecha_publicacion_programada')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="fotografia_asociada" class="form-label">Fotografía {{ $edit ? '(deja en blanco para conservar la actual)' : '*' }}</label>
    <input type="file" id="fotografia_asociada" name="fotografia_asociada" class="form-input @error('fotografia_asociada') error @enderror" accept="image/*" @if(!$edit) required @endif>
    @if($edit && $publicacion->fotografia_asociada)
        <img src="{{ $publicacion->fotografia_url }}" alt="Foto actual" class="mt-3 rounded-lg" style="max-width:150px;">
    @endif
    @error('fotografia_asociada')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="estatus" class="form-label">Estatus <span class="required">*</span></label>
    <select id="estatus" name="estatus" class="form-select @error('estatus') error @enderror" required>
        @foreach(\App\Models\CalendarioFoto::ESTATUS as $estatusOpcion)
            <option value="{{ $estatusOpcion }}" @selected(old('estatus', $publicacion->estatus ?? 'programada') == $estatusOpcion)>
                {{ ucfirst($estatusOpcion) }}
            </option>
        @endforeach
    </select>
    @error('estatus')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="observaciones" class="form-label">Observaciones</label>
    <textarea id="observaciones" name="observaciones" class="form-textarea @error('observaciones') error @enderror" rows="3">{{ old('observaciones', $publicacion->observaciones ?? '') }}</textarea>
    @error('observaciones')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="flex justify-end gap-3 mt-8">
    <a href="{{ route('calendario.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Publicación' : 'Guardar Publicación' }}</button>
</div>
