@php
    $isEdit = isset($operador);
@endphp

<div class="form-row">
    <div class="form-group">
        <label for="nombres">Nombres</label>
        <input type="text" name="nombres" id="nombres" value="{{ old('nombres', $operador->nombres ?? '') }}" required>
        @error('nombres')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="apellido_paterno">Apellido paterno</label>
        <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno', $operador->apellido_paterno ?? '') }}" required>
        @error('apellido_paterno')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="apellido_materno">Apellido materno</label>
        <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno', $operador->apellido_materno ?? '') }}">
        @error('apellido_materno')<span class="error">{{ $message }}</span>@enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="email">Correo electrónico</label>
        <input type="email" name="email" id="email" value="{{ old('email', $operador->email ?? '') }}" required>
        @error('email')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="telefono">Teléfono</label>
        <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $operador->telefono ?? '') }}">
        @error('telefono')<span class="error">{{ $message }}</span>@enderror
    </div>
</div>

<div class="form-group">
    <label for="estatus">Estatus</label>
    <select name="estatus" id="estatus" required>
        @foreach(\App\Models\User::ESTATUS as $estatus)
            <option value="{{ $estatus }}" {{ old('estatus', $operador->estatus ?? 'activo') === $estatus ? 'selected' : '' }}>
                {{ ucfirst($estatus) }}
            </option>
        @endforeach
    </select>
    @error('estatus')<span class="error">{{ $message }}</span>@enderror
</div>

<div class="form-row">
    <div class="form-group">
        <label for="password">Contraseña{{ $isEdit ? ' (dejar en blanco para no cambiar)' : '' }}</label>
        <input type="password" name="password" id="password" {{ $isEdit ? '' : 'required' }}>
        @error('password')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="password_confirmation">Confirmar contraseña</label>
        <input type="password" name="password_confirmation" id="password_confirmation" {{ $isEdit ? '' : 'required' }}>
    </div>
</div>
