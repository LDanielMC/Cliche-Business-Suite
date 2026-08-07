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
                <option value="{{ $cliente->id }}" @selected(old('cliente_id', $credencial->cliente_id ?? '') == $cliente->id)>
                    {{ $cliente->nombre_negocio }}{{ $cliente->user->estatus !== 'activo' ? ' (' . ucfirst(str_replace('_', ' ', $cliente->user->estatus)) . ')' : '' }}
                </option>
            @endforeach
        </select>
        @error('cliente_id')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="nombre_plataforma" class="form-label">Plataforma / Servicio <span class="required">*</span></label>
        <input type="text" id="nombre_plataforma" name="nombre_plataforma" class="form-input @error('nombre_plataforma') error @enderror" value="{{ old('nombre_plataforma', $credencial->nombre_plataforma ?? '') }}" maxlength="100" required>
        @error('nombre_plataforma')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="form-group">
        <label for="url_acceso" class="form-label">URL de Acceso</label>
        <input type="text" id="url_acceso" name="url_acceso" class="form-input @error('url_acceso') error @enderror" value="{{ old('url_acceso', $credencial->url_acceso ?? '') }}" maxlength="255" placeholder="https://...">
        @error('url_acceso')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="correo_asociado" class="form-label">Correo Asociado</label>
        <input type="email" id="correo_asociado" name="correo_asociado" class="form-input @error('correo_asociado') error @enderror" value="{{ old('correo_asociado', $credencial->correo_asociado ?? '') }}" maxlength="150">
        @error('correo_asociado')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="form-group">
        <label for="usuario" class="form-label">Usuario <span class="required">*</span></label>
        <input type="text" id="usuario" name="usuario" class="form-input @error('usuario') error @enderror" value="{{ old('usuario', $credencial->usuario ?? '') }}" maxlength="150" required>
        @error('usuario')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group" x-data="passwordVaultField()">
        <label for="password" class="form-label">{{ $edit ? 'Cambiar contraseña' : 'Contraseña' }} <span class="required">{{ $edit ? '' : '*' }}</span></label>
        <div class="flex gap-2">
            <input :type="visible ? 'text' : 'password'" id="password" name="password" class="form-input @error('password') error @enderror"
                   x-model="password" autocomplete="new-password" @if(!$edit) required @endif>
            <button type="button" class="btn btn-secondary" @click="visible = !visible" x-text="visible ? 'Ocultar' : 'Mostrar'"></button>
        </div>
        @error('password')
            <span class="form-error">{{ $message }}</span>
        @enderror

        <div class="flex gap-2 mt-2">
            <input :type="visible ? 'text' : 'password'" name="password_confirmation" class="form-input @error('password_confirmation') error @enderror"
                   x-model="passwordConfirmation" placeholder="Confirmar contraseña" autocomplete="new-password" x-show="password.length > 0" x-cloak>
        </div>
        <p style="font-size:.75rem; color:var(--color-error); margin-top:.3rem;" x-show="password && passwordConfirmation && password !== passwordConfirmation" x-cloak>
            Las contraseñas no coinciden.
        </p>
        @error('password_confirmation')
            <span class="form-error">{{ $message }}</span>
        @enderror

        <div class="flex items-center gap-2 mt-2">
            <button type="button" class="btn btn-secondary btn-sm" @click="generar()">Generar contraseña segura</button>
            <span style="font-size:.72rem; color:var(--color-success);" x-show="generada" x-cloak>Generada — cópiala antes de guardar si la necesitas.</span>
        </div>

        @if($edit)
            <p style="font-size:.75rem; color:var(--color-text-secondary); margin-top:.5rem;">
                Este campo es para reemplazar la contraseña por una nueva. Para ver la contraseña que ya está guardada, usa "Contraseña actual" arriba.
            </p>
            <p style="font-size:.75rem; color:var(--color-warning, #f59e0b); margin-top:.3rem;">
                Para guardar una contraseña nueva necesitas haberte verificado con el botón "Mostrar" de arriba en los últimos 10 minutos.
            </p>
        @endif
    </div>
</div>

<script>
function passwordVaultField() {
    return {
        visible: false,
        password: '',
        passwordConfirmation: '',
        generada: false,
        generar() {
            const mayus = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            const minus = 'abcdefghijkmnpqrstuvwxyz';
            const numeros = '23456789';
            const simbolos = '!@#$%^&*-_=+';
            const todos = mayus + minus + numeros + simbolos;

            let clave = mayus[Math.floor(Math.random() * mayus.length)]
                + minus[Math.floor(Math.random() * minus.length)]
                + numeros[Math.floor(Math.random() * numeros.length)]
                + simbolos[Math.floor(Math.random() * simbolos.length)];

            for (let i = clave.length; i < 16; i++) {
                clave += todos[Math.floor(Math.random() * todos.length)];
            }

            this.password = clave.split('').sort(() => Math.random() - 0.5).join('');
            this.passwordConfirmation = this.password;
            this.visible = true;
            this.generada = true;
        },
    };
}
</script>

<div class="form-group">
    <label for="observaciones" class="form-label">Observaciones</label>
    <textarea id="observaciones" name="observaciones" class="form-textarea @error('observaciones') error @enderror" rows="3">{{ old('observaciones', $credencial->observaciones ?? '') }}</textarea>
    @error('observaciones')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="flex justify-end gap-3 mt-8">
    <a href="{{ route('boveda.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Credencial' : 'Guardar Credencial' }}</button>
</div>
