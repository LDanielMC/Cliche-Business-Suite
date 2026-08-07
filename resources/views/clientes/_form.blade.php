@csrf

@if($edit)
    @method('PUT')
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="form-group">
        <label for="nombre_negocio" class="form-label">Nombre del Negocio <span class="required">*</span></label>
        <input type="text" id="nombre_negocio" name="nombre_negocio" class="form-input @error('nombre_negocio') error @enderror" value="{{ old('nombre_negocio', $cliente->nombre_negocio ?? '') }}" required>
        @error('nombre_negocio')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="giro" class="form-label">Giro</label>
        <input type="text" id="giro" name="giro" class="form-input @error('giro') error @enderror" value="{{ old('giro', $cliente->giro ?? '') }}">
        @error('giro')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="form-group">
        <label for="nombres" class="form-label">Nombres del Contacto <span class="required">*</span></label>
        <input type="text" id="nombres" name="nombres" class="form-input @error('nombres') error @enderror" value="{{ old('nombres', $cliente->user->nombres ?? '') }}" required>
        @error('nombres')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="apellido_paterno" class="form-label">Apellido Paterno <span class="required">*</span></label>
        <input type="text" id="apellido_paterno" name="apellido_paterno" class="form-input @error('apellido_paterno') error @enderror" value="{{ old('apellido_paterno', $cliente->user->apellido_paterno ?? '') }}" required>
        @error('apellido_paterno')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="apellido_materno" class="form-label">Apellido Materno</label>
        <input type="text" id="apellido_materno" name="apellido_materno" class="form-input @error('apellido_materno') error @enderror" value="{{ old('apellido_materno', $cliente->user->apellido_materno ?? '') }}">
        @error('apellido_materno')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="email" class="form-label">Correo Electrónico <span class="required">*</span></label>
        <input type="email" id="email" name="email" class="form-input @error('email') error @enderror" value="{{ old('email', $cliente->user->email ?? '') }}" required>
        @error('email')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="direccion" class="form-label">Dirección</label>
    <textarea id="direccion" name="direccion" class="form-textarea @error('direccion') error @enderror" rows="3">{{ old('direccion', $cliente->direccion ?? '') }}</textarea>
    @error('direccion')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="form-group">
        <label for="servicio_contratado" class="form-label">Servicio Contratado</label>
        <input type="text" id="servicio_contratado" name="servicio_contratado" class="form-input @error('servicio_contratado') error @enderror" value="{{ old('servicio_contratado', $cliente->servicio_contratado ?? '') }}">
        @error('servicio_contratado')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="cantidad_fotos" class="form-label">Cantidad de Fotos</label>
        <input type="number" id="cantidad_fotos" name="cantidad_fotos" class="form-input @error('cantidad_fotos') error @enderror" value="{{ old('cantidad_fotos', $cliente->cantidad_fotos ?? 0) }}" min="0">
        @error('cantidad_fotos')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="precio_mensual" class="form-label">Precio Mensual</label>
        <input type="number" id="precio_mensual" name="precio_mensual" step="0.01" min="0" class="form-input @error('precio_mensual') error @enderror" value="{{ old('precio_mensual', $cliente->precio_mensual ?? 0) }}">
        @error('precio_mensual')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="flex justify-end gap-3 mt-8">
    <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Cliente' : 'Guardar Cliente' }}</button>
</div>
