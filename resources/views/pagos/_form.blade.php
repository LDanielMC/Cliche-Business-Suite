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
                <option value="{{ $cliente->id }}" @selected(old('cliente_id', $pago->cliente_id ?? '') == $cliente->id)>
                    {{ $cliente->nombre_negocio }}
                </option>
            @endforeach
        </select>
        @error('cliente_id')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="concepto_servicio">Concepto del Servicio *</label>
        <input type="text" id="concepto_servicio" name="concepto_servicio" class="form-control @error('concepto_servicio') error @enderror" value="{{ old('concepto_servicio', $pago->concepto_servicio ?? 'Posicionamiento Local') }}" maxlength="200" required>
        @error('concepto_servicio')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-2">
        <label for="monto">Monto *</label>
        <input type="number" id="monto" name="monto" step="0.01" min="0" class="form-control @error('monto') error @enderror" value="{{ old('monto', $pago->monto ?? '') }}" required>
        @error('monto')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="periodo_facturado">Periodo Facturado *</label>
        <input type="text" id="periodo_facturado" name="periodo_facturado" class="form-control @error('periodo_facturado') error @enderror" value="{{ old('periodo_facturado', $pago->periodo_facturado ?? '') }}" maxlength="50" placeholder="Julio 2026" required>
        @error('periodo_facturado')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-2">
        <label for="fecha_vencimiento">Fecha de Vencimiento *</label>
        <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" class="form-control @error('fecha_vencimiento') error @enderror" value="{{ old('fecha_vencimiento', isset($pago) ? $pago->fecha_vencimiento->format('Y-m-d') : '') }}" required>
        @error('fecha_vencimiento')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="estatus">Estatus *</label>
        <select id="estatus" name="estatus" class="form-control @error('estatus') error @enderror" required>
            @foreach(\App\Models\PagoCliente::ESTATUS as $estatusOpcion)
                <option value="{{ $estatusOpcion }}" @selected(old('estatus', $pago->estatus ?? 'pendiente') == $estatusOpcion)>{{ ucfirst($estatusOpcion) }}</option>
            @endforeach
        </select>
        @error('estatus')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-2">
        <label for="forma_pago">Forma de Pago</label>
        <select id="forma_pago" name="forma_pago" class="form-control @error('forma_pago') error @enderror">
            <option value="">Sin especificar</option>
            @foreach(\App\Models\PagoCliente::FORMA_PAGO as $forma)
                <option value="{{ $forma }}" @selected(old('forma_pago', $pago->forma_pago ?? '') == $forma)>{{ ucfirst($forma) }}</option>
            @endforeach
        </select>
        @error('forma_pago')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-2">
        <label for="fecha_pago">Fecha de Pago (si ya fue pagado)</label>
        <input type="date" id="fecha_pago" name="fecha_pago" class="form-control @error('fecha_pago') error @enderror" value="{{ old('fecha_pago', isset($pago) && $pago->fecha_pago ? $pago->fecha_pago->format('Y-m-d') : '') }}">
        @error('fecha_pago')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-actions">
    <a href="{{ route('pagos.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $edit ? 'Actualizar Pago' : 'Guardar Pago' }}</button>
</div>
