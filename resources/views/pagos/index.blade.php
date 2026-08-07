@extends('layouts.app')

@section('title', 'Historial de Pagos')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Pagos</span>
        </div>
    </div>
@endsection

@section('content')
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Historial de Pagos</h1>
            <p class="page-subtitle">Los pagos se generan automáticamente al validar una renovación</p>
        </div>
    </div>

    {{-- Filtros compactos --}}
    <div class="card mb-5">
        <div class="card-body" style="padding: .75rem 1.25rem;">
            <form method="GET" action="{{ route('pagos.index') }}"
                  style="display:flex; flex-wrap:wrap; gap:.625rem; align-items:flex-end;">

                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="dir" value="{{ request('dir') }}">
                @endif

                <div style="flex:2; min-width:160px;">
                    <label for="q" class="form-label" style="font-size:.7rem; margin-bottom:.2rem;">Buscar</label>
                    <input type="text" id="q" name="q" data-live-search class="form-input" style="height:2.1rem; padding:.25rem .5rem; font-size:.82rem;"
                           placeholder="Cliente o concepto..." value="{{ request('q') }}">
                </div>

                <div style="flex:2; min-width:160px;">
                    <label for="cliente_id" class="form-label" style="font-size:.7rem; margin-bottom:.2rem;">Cliente</label>
                    <select id="cliente_id" name="cliente_id" class="form-select" style="height:2.1rem; padding:.25rem .5rem; font-size:.82rem;">
                        <option value="">Todos</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" @selected(request('cliente_id') == $cliente->id)>
                                {{ $cliente->nombre_negocio }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="flex:1; min-width:120px;">
                    <label for="forma_pago" class="form-label" style="font-size:.7rem; margin-bottom:.2rem;">Forma de pago</label>
                    <select id="forma_pago" name="forma_pago" class="form-select" style="height:2.1rem; padding:.25rem .5rem; font-size:.82rem;">
                        <option value="">Todas</option>
                        @foreach(\App\Models\PagoCliente::FORMA_PAGO as $forma)
                            <option value="{{ $forma }}" @selected(request('forma_pago') === $forma)>{{ ucfirst($forma) }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="flex:1; min-width:130px;">
                    <label for="desde" class="form-label" style="font-size:.7rem; margin-bottom:.2rem;">Desde</label>
                    <input type="date" id="desde" name="desde" class="form-input"
                           style="height:2.1rem; padding:.25rem .5rem; font-size:.82rem;"
                           value="{{ request('desde') }}">
                </div>

                <div style="flex:1; min-width:130px;">
                    <label for="hasta" class="form-label" style="font-size:.7rem; margin-bottom:.2rem;">Hasta</label>
                    <input type="date" id="hasta" name="hasta" class="form-input"
                           style="height:2.1rem; padding:.25rem .5rem; font-size:.82rem;"
                           value="{{ request('hasta') }}">
                </div>

                <div style="display:flex; gap:.4rem; align-items:flex-end; padding-bottom:0;">
                    <button type="submit" class="btn btn-primary" style="height:2.1rem; padding:.25rem 1rem; font-size:.82rem; white-space:nowrap;">
                        Filtrar
                    </button>
                    @if(request()->hasAny(['q','cliente_id','forma_pago','desde','hasta']))
                        <a href="{{ route('pagos.index') }}" class="btn btn-secondary" style="height:2.1rem; padding:.25rem .75rem; font-size:.82rem; white-space:nowrap;">
                            Limpiar filtros
                        </a>
                    @endif
                </div>

            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($pagos->count() > 0)
                <div class="table-container">
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <x-th-sort field="cliente" label="Cliente · Concepto" />
                                <th>Periodo</th>
                                <x-th-sort field="monto" label="Monto" />
                                <x-th-sort field="fecha" label="Fecha pago" class="hide-mobile" />
                                <th class="hide-mobile">Forma</th>
                                <th>Documentos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pagos as $pago)
                                <tr>
                                    <td data-label="Cliente">
                                        <div class="font-medium">{{ $pago->cliente->nombre_negocio }}</div>
                                        <div style="font-size:.75rem; color:var(--color-text-secondary); margin-top:.1rem;">{{ $pago->concepto_servicio }}</div>
                                    </td>
                                    <td data-label="Periodo" style="font-size:.85rem;">
                                        @if($pago->periodo_inicio && $pago->periodo_fin)
                                            {{ $pago->periodo_inicio->format('d/m/Y') }} – {{ $pago->periodo_fin->format('d/m/Y') }}
                                        @elseif($pago->periodo_facturado)
                                            {{ $pago->periodo_facturado }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td data-label="Monto" style="font-weight:600;">${{ number_format($pago->monto, 2) }}</td>
                                    <td data-label="Fecha pago" class="hide-mobile">
                                        {{ $pago->fecha_pago?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td data-label="Forma" class="hide-mobile">
                                        {{ $pago->forma_pago ? ucfirst($pago->forma_pago) : '—' }}
                                    </td>
                                    <td data-label="Documentos">
                                        <div style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:center;">
                                            @if($pago->comprobante_url)
                                                <a href="{{ $pago->comprobante_url }}" target="_blank" class="btn btn-secondary btn-sm">Comprobante</a>
                                            @endif
                                            @if($pago->factura_pdf_url)
                                                <a href="{{ $pago->factura_pdf_url }}" target="_blank" class="btn btn-secondary btn-sm">PDF</a>
                                            @endif
                                            @if($pago->factura_xml_url)
                                                <a href="{{ $pago->factura_xml_url }}" download class="btn btn-secondary btn-sm">XML</a>
                                            @endif
                                            @if(!$pago->comprobante_url && !$pago->factura_pdf_url && !$pago->factura_xml_url)
                                                <span style="font-size:.75rem; color:var(--color-text-secondary);">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    {{ $pagos->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No hay pagos registrados</h3>
                    <p class="empty-state-description">Los pagos aparecen aquí automáticamente cuando se valida una renovación.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
