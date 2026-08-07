@extends('layouts.app')

@section('title', 'Editar Credencial')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('boveda.index') }}" class="breadcrumb-link">Bóveda</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Editar Credencial</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Editar Credencial</h1>
            <p class="page-subtitle">{{ $credencial->nombre_plataforma }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    <div class="card mb-6" x-data="revelarPassword({{ $credencial->id }})">
        <div class="card-header">
            <h3 class="card-title" style="margin:0;">Contraseña actual</h3>
        </div>
        <div class="card-body">
            <div class="flex gap-2" style="max-width:28rem;" x-show="!pidiendoVerificacion">
                <input type="text" class="form-input" readonly
                       :value="visible ? password : '••••••••••••'"
                       style="font-family: monospace;">
                <button type="button" class="btn btn-secondary" @click="revelar()" x-show="!visible" :disabled="cargando">
                    <span x-show="!cargando">Mostrar</span>
                    <span x-show="cargando">...</span>
                </button>
                <button type="button" class="btn btn-secondary" @click="ocultar()" x-show="visible">Ocultar</button>
                <button type="button" class="btn btn-secondary" @click="copiar()" x-show="visible" x-text="copiado ? 'Copiado' : 'Copiar'"></button>
            </div>

            <div x-show="pidiendoVerificacion" x-cloak style="max-width:28rem;">
                <template x-if="enviandoCodigo">
                    <p style="font-size:.85rem; color:var(--color-text-secondary);">Enviando código a tu correo...</p>
                </template>
                <template x-if="!enviandoCodigo">
                    <div>
                        <label class="form-label" for="verificar_codigo">Te enviamos un código de 6 dígitos a tu correo</label>
                        <div class="flex gap-2">
                            <input type="text" id="verificar_codigo" class="form-input" x-model="codigoVerificacion"
                                   @keydown.enter="confirmarCodigo()" placeholder="000000" maxlength="6" inputmode="numeric"
                                   style="font-family: monospace; letter-spacing: 2px;" autocomplete="one-time-code">
                            <button type="button" class="btn btn-primary" @click="confirmarCodigo()" :disabled="cargando">
                                <span x-show="!cargando">Confirmar</span>
                                <span x-show="cargando">...</span>
                            </button>
                            <button type="button" class="btn btn-secondary" @click="cancelarVerificacion()">Cancelar</button>
                        </div>
                        <p style="font-size:.75rem; color:var(--color-text-secondary); margin-top:.4rem;">
                            Expira en 10 minutos. ¿No te llegó? <a href="#" @click.prevent="enviarCodigo()">Reenviar código</a>.
                        </p>
                        <p style="font-size:.75rem; color:var(--color-error); margin-top:.3rem;" x-show="errorVerificacion" x-text="errorVerificacion"></p>
                    </div>
                </template>
            </div>

            <p style="font-size:.75rem; color:var(--color-text-secondary); margin-top:.5rem;" x-show="visible" x-cloak>
                Se oculta sola en <span x-text="segundosRestantes"></span>s.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('boveda.update', $credencial) }}">
                @include('boveda._form', ['edit' => true, 'credencial' => $credencial, 'clientes' => $clientes])
            </form>
        </div>
    </div>
</div>

<script>
function revelarPassword(credencialId) {
    return {
        visible: false,
        cargando: false,
        copiado: false,
        password: '',
        segundosRestantes: 20,
        pidiendoVerificacion: false,
        enviandoCodigo: false,
        codigoVerificacion: '',
        errorVerificacion: '',
        _timer: null,
        _cuenta: null,
        _csrf() {
            return document.querySelector('meta[name="csrf-token"]').content;
        },
        revelar() {
            this.cargando = true;

            fetch(`/admin/boveda/${credencialId}/revelar`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this._csrf(), 'Accept': 'application/json' },
            })
                .then(async (r) => {
                    if (r.status === 428) {
                        this.cargando = false;
                        this.enviarCodigo();
                        return null;
                    }
                    if (!r.ok) throw new Error('No se pudo revelar la contraseña.');
                    return r.json();
                })
                .then(data => {
                    if (!data) return;
                    this.password = data.password;
                    this.visible = true;
                    this.segundosRestantes = 20;
                    clearTimeout(this._timer);
                    clearInterval(this._cuenta);
                    this._cuenta = setInterval(() => { this.segundosRestantes--; }, 1000);
                    this._timer = setTimeout(() => this.ocultar(), 20000);
                    this.cargando = false;
                })
                .catch(() => {
                    window.uxSystem?.showToast('No se pudo revelar la contraseña.', 'error');
                    this.cargando = false;
                });
        },
        enviarCodigo() {
            this.pidiendoVerificacion = true;
            this.enviandoCodigo = true;
            this.errorVerificacion = '';
            this.codigoVerificacion = '';

            fetch('{{ route('boveda.verificar.enviar-codigo') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this._csrf(), 'Accept': 'application/json' },
            })
                .then(r => { if (!r.ok) throw new Error('No se pudo enviar el código.'); })
                .then(() => { window.uxSystem?.showToast('Código enviado a tu correo.', 'success'); })
                .catch(() => window.uxSystem?.showToast('No se pudo enviar el código.', 'error'))
                .finally(() => { this.enviandoCodigo = false; });
        },
        confirmarCodigo() {
            if (!this.codigoVerificacion) return;

            this.cargando = true;
            this.errorVerificacion = '';

            fetch('{{ route('boveda.verificar.confirmar-codigo') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this._csrf(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ codigo: this.codigoVerificacion }),
            })
                .then(async (r) => {
                    if (r.status === 422) {
                        const data = await r.json();
                        this.errorVerificacion = data.error || 'Código inválido.';
                        this.cargando = false;
                        return;
                    }
                    if (!r.ok) throw new Error('No se pudo confirmar el código.');

                    this.pidiendoVerificacion = false;
                    this.codigoVerificacion = '';
                    this.revelar();
                })
                .catch(() => {
                    window.uxSystem?.showToast('No se pudo confirmar el código.', 'error');
                    this.cargando = false;
                });
        },
        cancelarVerificacion() {
            this.pidiendoVerificacion = false;
            this.codigoVerificacion = '';
            this.errorVerificacion = '';
        },
        ocultar() {
            this.visible = false;
            this.copiado = false;
            this.password = '';
            clearTimeout(this._timer);
            clearInterval(this._cuenta);
        },
        copiar() {
            navigator.clipboard.writeText(this.password).then(() => {
                this.copiado = true;
                setTimeout(() => { this.copiado = false; }, 2000);
            });
        },
    };
}
</script>
@endsection
