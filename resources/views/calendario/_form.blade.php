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
        <label for="fecha_publicacion">Fecha de Publicación *</label>
        <input type="date" id="fecha_publicacion" name="fecha_publicacion" class="form-control @error('fecha_publicacion') error @enderror" value="{{ old('fecha_publicacion', isset($publicacion) ? $publicacion->fecha_publicacion->format('Y-m-d') : '') }}" required>
        @error('fecha_publicacion')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="descripcion">Descripción</label>
    <input type="text" id="descripcion" name="descripcion" class="form-control @error('descripcion') error @enderror" value="{{ old('descripcion', $publicacion->descripcion ?? '') }}" maxlength="255">
    @error('descripcion')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="estado">Estado *</label>
    <select id="estado" name="estado" class="form-control @error('estado') error @enderror" required>
        @foreach(\App\Models\CalendarioFoto::ESTADOS as $estadoOpcion)
            <option value="{{ $estadoOpcion }}" @selected(old('estado', $publicacion->estado ?? 'programada') == $estadoOpcion)>
                {{ ucfirst($estadoOpcion) }}
            </option>
        @endforeach
    </select>
    @error('estado')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-actions">
    <a href="{{ route('calendario.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Publicación' : 'Guardar Publicación' }}</button>
</div>
