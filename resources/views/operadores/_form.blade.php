@php
    $isEdit = isset($operador);
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="form-group">
        <label for="nombres" class="form-label">Nombres <span class="required">*</span></label>
        <input type="text" name="nombres" id="nombres" class="form-input @error('nombres') error @enderror" value="{{ old('nombres', $operador->nombres ?? '') }}" required>
        @error('nombres')<span class="form-error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="apellido_paterno" class="form-label">Apellido paterno <span class="required">*</span></label>
        <input type="text" name="apellido_paterno" id="apellido_paterno" class="form-input @error('apellido_paterno') error @enderror" value="{{ old('apellido_paterno', $operador->apellido_paterno ?? '') }}" required>
        @error('apellido_paterno')<span class="form-error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="apellido_materno" class="form-label">Apellido materno</label>
        <input type="text" name="apellido_materno" id="apellido_materno" class="form-input @error('apellido_materno') error @enderror" value="{{ old('apellido_materno', $operador->apellido_materno ?? '') }}">
        @error('apellido_materno')<span class="form-error">{{ $message }}</span>@enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="form-group">
        <label for="email" class="form-label">Correo electrónico <span class="required">*</span></label>
        <input type="email" name="email" id="email" class="form-input @error('email') error @enderror" value="{{ old('email', $operador->email ?? '') }}" required>
        @error('email')<span class="form-error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" name="telefono" id="telefono" class="form-input @error('telefono') error @enderror"
               value="{{ old('telefono', $operador->telefono ?? '') }}"
               inputmode="numeric" pattern="\d{10}" maxlength="10" placeholder="10 dígitos"
               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
        @error('telefono')<span class="form-error">{{ $message }}</span>@enderror
    </div>
</div>

@if($isEdit)
    <div class="form-group">
        <label for="estatus" class="form-label">Estatus <span class="required">*</span></label>
        <select name="estatus" id="estatus" class="form-select @error('estatus') error @enderror" required>
            @foreach(\App\Models\User::ESTATUS as $estatus)
                <option value="{{ $estatus }}" {{ old('estatus', $operador->estatus ?? 'activo') === $estatus ? 'selected' : '' }}>
                    {{ ucfirst($estatus) }}
                </option>
            @endforeach
        </select>
        @error('estatus')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="form-group">
            <label for="password" class="form-label">Contraseña (dejar en blanco para no cambiar)</label>
            <input type="password" name="password" id="password" class="form-input @error('password') error @enderror">
            @error('password')<span class="form-error">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-input">
        </div>
    </div>
@else
    <p style="font-size:.85rem; color:var(--color-text-secondary);">
        El operador recibirá un correo para establecer su propia contraseña. La cuenta se crea activa.
    </p>
@endif
