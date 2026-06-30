@csrf

@if($edit)
    @method('PUT')
@endif

<div class="form-row">
    <div class="form-group col-2">
        <label for="nombre_negocio">Nombre del Negocio *</label>
        <input type="text" id="nombre_negocio" name="nombre_negocio" class="form-control @error('nombre_negocio') error @enderror" value="{{ old('nombre_negocio', $cliente->nombre_negocio ?? '') }}" required>
        @error('nombre_negocio')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="giro">Giro</label>
        <input type="text" id="giro" name="giro" class="form-control @error('giro') error @enderror" value="{{ old('giro', $cliente->giro ?? '') }}">
        @error('giro')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-2">
        <label for="name">Nombre de Contacto *</label>
        <input type="text" id="name" name="name" class="form-control @error('name') error @enderror" value="{{ old('name', $cliente->user->name ?? '') }}" required>
        @error('name')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="email">Correo Electrónico *</label>
        <input type="email" id="email" name="email" class="form-control @error('email') error @enderror" value="{{ old('email', $cliente->user->email ?? '') }}" required>
        @error('email')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="direccion">Dirección</label>
    <textarea id="direccion" name="direccion" class="form-control @error('direccion') error @enderror" rows="3">{{ old('direccion', $cliente->direccion ?? '') }}</textarea>
    @error('direccion')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="form-row">
    <div class="form-group col-2">
        <label for="servicio_contratado">Servicio Contratado</label>
        <input type="text" id="servicio_contratado" name="servicio_contratado" class="form-control @error('servicio_contratado') error @enderror" value="{{ old('servicio_contratado', $cliente->servicio_contratado ?? '') }}">
        @error('servicio_contratado')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="cantidad_fotos">Cantidad de Fotos</label>
        <input type="number" id="cantidad_fotos" name="cantidad_fotos" class="form-control @error('cantidad_fotos') error @enderror" value="{{ old('cantidad_fotos', $cliente->cantidad_fotos ?? 0) }}" min="0">
        @error('cantidad_fotos')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-2">
        <label for="precio_mensual">Precio Mensual</label>
        <input type="number" id="precio_mensual" name="precio_mensual" step="0.01" min="0" class="form-control @error('precio_mensual') error @enderror" value="{{ old('precio_mensual', $cliente->precio_mensual ?? 0) }}">
        @error('precio_mensual')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-actions">
    <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Cliente' : 'Guardar Cliente' }}</button>
</div>
