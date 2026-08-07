@extends('layouts.app')

@section('title', 'Renovar Servicio')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('renovaciones.cliente.index') }}" class="breadcrumb-link">Mi Renovación</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Enviar comprobante</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Renovar servicio</h1>
            <p class="page-subtitle">Sube tu comprobante de pago para continuar con el servicio</p>
        </div>
    </div>

    {{-- Summary card --}}
    <div class="card mb-6" style="background: linear-gradient(135deg, var(--color-brand) 0%, #6366f1 100%); color: white;">
        <div class="card-body">
            <div style="font-size:.85rem; opacity:.8; margin-bottom:.5rem;">Renovando el plan</div>
            <div style="font-size:1.25rem; font-weight:700;">{{ $cliente->servicio_contratado }}</div>
            <div style="margin-top:.75rem; display:flex; gap:2rem; flex-wrap:wrap;">
                <div>
                    <div style="font-size:.75rem; opacity:.75; text-transform:uppercase; letter-spacing:.05em;">Monto a pagar</div>
                    <div style="font-size:1.75rem; font-weight:700;">${{ number_format($cliente->precio_mensual, 2) }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem; opacity:.75; text-transform:uppercase; letter-spacing:.05em;">Vence el</div>
                    <div style="font-size:1.1rem; font-weight:600; margin-top:.2rem;">{{ $renovacion->fecha_vencimiento->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem; opacity:.75; text-transform:uppercase; letter-spacing:.05em;">Días restantes</div>
                    <div style="font-size:1.1rem; font-weight:600; margin-top:.2rem;">{{ $renovacion->diasRestantes() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main form --}}
    <form action="{{ route('renovaciones.cliente.enviar', $renovacion) }}" method="POST" enctype="multipart/form-data" id="form-renovar">
        @csrf

        {{-- Step 1: Payment proof --}}
        <div class="card mb-6">
            <div class="card-header">
                <div style="display:flex; align-items:center; gap:.75rem;">
                    <div style="width:28px; height:28px; border-radius:50%; background:var(--color-brand); color:white; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; flex-shrink:0;">1</div>
                    <h3 class="card-title" style="margin:0;">Comprobante de pago</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label" for="comprobante_pago">
                        Archivo del comprobante <span style="color:var(--color-error)">*</span>
                    </label>
                    <input type="file" id="comprobante_pago" name="comprobante_pago"
                        accept="image/*,.pdf" class="form-input" required>
                    <p style="font-size:.8rem; color:var(--color-text-secondary); margin-top:.35rem;">
                        Formatos aceptados: JPG, PNG, PDF. Tamaño máximo: 5 MB.
                    </p>
                    @error('comprobante_pago')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label class="form-label" for="fecha_pago_cliente">
                            Fecha del pago <span style="color:var(--color-error)">*</span>
                        </label>
                        <input type="date" id="fecha_pago_cliente" name="fecha_pago_cliente"
                            class="form-input" required
                            min="{{ $renovacion->fecha_inicio->format('Y-m-d') }}"
                            max="{{ now()->format('Y-m-d') }}"
                            value="{{ old('fecha_pago_cliente', now()->format('Y-m-d')) }}">
                        @error('fecha_pago_cliente')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="monto">
                            Monto pagado <span style="color:var(--color-error)">*</span>
                        </label>
                        <input type="number" id="monto" name="monto"
                            class="form-input" required step="0.01" min="0.01"
                            value="{{ old('monto', $cliente->precio_mensual) }}"
                            placeholder="0.00">
                        @error('monto')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="forma_pago">
                        Forma de pago <span style="color:var(--color-error)">*</span>
                    </label>
                    <select id="forma_pago" name="forma_pago" class="form-select" required>
                        <option value="">Selecciona...</option>
                        @foreach(\App\Models\PagoCliente::FORMA_PAGO as $forma)
                            <option value="{{ $forma }}" {{ old('forma_pago') === $forma ? 'selected' : '' }}>
                                {{ ucfirst($forma) }}
                            </option>
                        @endforeach
                    </select>
                    @error('forma_pago')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="referencia_bancaria">Referencia bancaria (opcional)</label>
                    <input type="text" id="referencia_bancaria" name="referencia_bancaria"
                        class="form-input" maxlength="100"
                        value="{{ old('referencia_bancaria') }}"
                        placeholder="Número de operación, folio, etc.">
                    @error('referencia_bancaria')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Step 2: Invoice --}}
        <div class="card mb-6">
            <div class="card-header">
                <div style="display:flex; align-items:center; gap:.75rem;">
                    <div style="width:28px; height:28px; border-radius:50%; background:var(--color-brand); color:white; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; flex-shrink:0;">2</div>
                    <h3 class="card-title" style="margin:0;">¿Requieres factura?</h3>
                </div>
            </div>
            <div class="card-body">
                <div style="display:flex; align-items:center; gap:1rem; padding:1rem; background:var(--color-background-alt,#f8fafc); border-radius:var(--radius-lg); margin-bottom:1rem;">
                    <label style="display:flex; align-items:center; gap:.6rem; cursor:pointer; flex:1;">
                        <div style="position:relative; display:inline-flex; align-items:center;">
                            <input type="checkbox" id="solicita_factura" name="solicita_factura" value="1"
                                class="sr-only" onchange="toggleFacturaFields(this.checked)"
                                {{ old('solicita_factura') ? 'checked' : '' }}>
                            <div id="toggle-track" style="
                                width:52px; height:28px; border-radius:14px; transition:background .2s;
                                background: {{ old('solicita_factura') ? 'var(--color-brand)' : 'var(--color-border)' }};
                                position:relative; cursor:pointer;"
                                onclick="document.getElementById('solicita_factura').click();">
                                <div id="toggle-thumb" style="
                                    position:absolute; top:3px;
                                    left: {{ old('solicita_factura') ? '25px' : '3px' }};
                                    width:22px; height:22px; border-radius:50%; background:white;
                                    box-shadow:0 1px 3px rgba(0,0,0,.2); transition:left .2s;">
                                </div>
                            </div>
                        </div>
                        <span style="font-weight:500;">Sí, requiero factura por este pago</span>
                    </label>
                </div>

                {{-- Fiscal data fields --}}
                <div id="campos-fiscales" style="{{ old('solicita_factura') ? '' : 'display:none;' }}">
                    <p style="font-size:.875rem; color:var(--color-text-secondary); margin-bottom:1rem;">
                        Revisa y confirma tus datos fiscales. Puedes actualizarlos si han cambiado.
                    </p>
                    <div class="grid gap-4" style="grid-template-columns: 1fr 1fr;">
                        <div class="form-group">
                            <label class="form-label" for="rfc">RFC <span style="color:var(--color-error)">*</span></label>
                            <input type="text" id="rfc" name="rfc"
                                class="form-input" maxlength="20"
                                value="{{ old('rfc', $cliente->rfc) }}"
                                placeholder="XXXX000000XXX">
                            @error('rfc')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="codigo_postal_fiscal">Código Postal <span style="color:var(--color-error)">*</span></label>
                            <input type="text" id="codigo_postal_fiscal" name="codigo_postal_fiscal"
                                class="form-input" maxlength="10"
                                value="{{ old('codigo_postal_fiscal', $cliente->codigo_postal_fiscal) }}"
                                placeholder="62000">
                            @error('codigo_postal_fiscal')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label" for="razon_social">Razón Social <span style="color:var(--color-error)">*</span></label>
                            <input type="text" id="razon_social" name="razon_social"
                                class="form-input"
                                value="{{ old('razon_social', $cliente->razon_social) }}"
                                placeholder="Nombre o razón social tal como aparece en el SAT">
                            @error('razon_social')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="regimen_fiscal">Régimen Fiscal <span style="color:var(--color-error)">*</span></label>
                            <select id="regimen_fiscal" name="regimen_fiscal" class="form-select">
                                <option value="">Selecciona...</option>
                                @php $regimenes = [
                                    '601' => '601 - General de Ley Personas Morales',
                                    '603' => '603 - Personas Morales con Fines no Lucrativos',
                                    '605' => '605 - Sueldos y Salarios e Ingresos Asimilados',
                                    '606' => '606 - Arrendamiento',
                                    '607' => '607 - Régimen de Enajenación o Adquisición de Bienes',
                                    '608' => '608 - Demás Ingresos',
                                    '610' => '610 - Residentes en el Extranjero',
                                    '611' => '611 - Ingresos por Dividendos',
                                    '612' => '612 - Personas Físicas con Actividades Empresariales y Profesionales',
                                    '614' => '614 - Ingresos por intereses',
                                    '615' => '615 - Régimen de los ingresos por obtención de premios',
                                    '616' => '616 - Sin obligaciones fiscales',
                                    '620' => '620 - Sociedades Cooperativas de Producción',
                                    '621' => '621 - Incorporación Fiscal',
                                    '622' => '622 - Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
                                    '623' => '623 - Opcional para Grupos de Sociedades',
                                    '624' => '624 - Coordinados',
                                    '625' => '625 - Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
                                    '626' => '626 - Régimen Simplificado de Confianza (RESICO)',
                                ] @endphp
                                @foreach($regimenes as $key => $label)
                                <option value="{{ $key }}"
                                    {{ old('regimen_fiscal', $cliente->regimen_fiscal) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                            @error('regimen_fiscal')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="uso_cfdi">Uso CFDI <span style="color:var(--color-error)">*</span></label>
                            <select id="uso_cfdi" name="uso_cfdi" class="form-select">
                                <option value="">Selecciona...</option>
                                @php $usos = [
                                    'G01' => 'G01 - Adquisición de mercancias',
                                    'G02' => 'G02 - Devoluciones, descuentos o bonificaciones',
                                    'G03' => 'G03 - Gastos en general',
                                    'I01' => 'I01 - Construcciones',
                                    'I02' => 'I02 - Mobiliario y equipo de oficina por inversiones',
                                    'I03' => 'I03 - Equipo de transporte',
                                    'I04' => 'I04 - Equipo de computo y accesorios',
                                    'I05' => 'I05 - Dados, troqueles, moldes, matrices y herramental',
                                    'I06' => 'I06 - Comunicaciones telefónicas',
                                    'I07' => 'I07 - Comunicaciones satelitales',
                                    'I08' => 'I08 - Otra maquinaria y equipo',
                                    'D01' => 'D01 - Honorarios médicos y gastos hospitalarios',
                                    'D02' => 'D02 - Gastos médicos por incapacidad o discapacidad',
                                    'D03' => 'D03 - Gastos funerales',
                                    'D04' => 'D04 - Donativos',
                                    'D05' => 'D05 - Intereses reales efectivamente pagados por créditos hipotecarios',
                                    'D08' => 'D08 - Primas por seguros de gastos médicos',
                                    'D10' => 'D10 - Pagos por servicios educativos (colegiaturas)',
                                    'S01' => 'S01 - Sin efectos fiscales',
                                    'CP01' => 'CP01 - Pagos',
                                ] @endphp
                                @foreach($usos as $key => $label)
                                <option value="{{ $key }}"
                                    {{ old('uso_cfdi', $cliente->uso_cfdi) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                            @error('uso_cfdi')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden field: when not checked send 0 --}}
        <input type="hidden" name="solicita_factura" value="0" id="solicita_factura_hidden">

        {{-- Submit --}}
        <div class="flex gap-3 justify-between">
            <a href="{{ route('renovaciones.cliente.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="font-size:1rem; padding:.65rem 2rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Enviar comprobante
            </button>
        </div>
    </form>

</div>

@section('scripts')
<script>
function toggleFacturaFields(checked) {
    const campos  = document.getElementById('campos-fiscales');
    const track   = document.getElementById('toggle-track');
    const thumb   = document.getElementById('toggle-thumb');
    const hidden  = document.getElementById('solicita_factura_hidden');

    campos.style.display = checked ? '' : 'none';
    track.style.background = checked ? 'var(--color-brand)' : 'var(--color-border)';
    thumb.style.left = checked ? '25px' : '3px';
    hidden.disabled = checked; // When checkbox checked, hidden field is disabled

    // Toggle required on fiscal fields
    const required = ['rfc', 'razon_social', 'codigo_postal_fiscal', 'regimen_fiscal', 'uso_cfdi'];
    required.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.required = checked;
    });
}

// Init on page load (for old() repopulation)
document.addEventListener('DOMContentLoaded', function () {
    const cb = document.getElementById('solicita_factura');
    if (cb) toggleFacturaFields(cb.checked);

    // When checkbox changes, also update hidden field logic
    cb.addEventListener('change', function() {
        toggleFacturaFields(this.checked);
    });
});
</script>
@endsection
@endsection
