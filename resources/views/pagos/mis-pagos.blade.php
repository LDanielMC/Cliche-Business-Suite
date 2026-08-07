@extends('layouts.app')

@section('title', 'Mis Pagos')

@section('content')
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Mis Pagos</h1>
            <p class="page-subtitle">Historial de pagos de tu cuenta</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($pagos->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Periodo</th>
                                <th>Monto</th>
                                <th>Fecha de Pago</th>
                                <th>Documentos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pagos as $pago)
                                <tr>
                                    <td class="font-medium">{{ $pago->concepto_servicio }}</td>
                                    <td style="font-size:.85rem;">
                                        @if($pago->periodo_inicio && $pago->periodo_fin)
                                            {{ $pago->periodo_inicio->format('d/m/Y') }} – {{ $pago->periodo_fin->format('d/m/Y') }}
                                        @elseif($pago->periodo_facturado)
                                            {{ $pago->periodo_facturado }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>${{ number_format($pago->monto, 2) }}</td>
                                    <td>{{ $pago->fecha_pago?->format('d/m/Y') ?? '—' }}</td>
                                    <td>
                                        <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                                            @if($pago->comprobante_url)
                                                <a href="{{ $pago->comprobante_url }}" target="_blank" class="btn btn-secondary btn-sm">Comprobante</a>
                                            @endif
                                            @if($pago->factura_pdf_url)
                                                <a href="{{ $pago->factura_pdf_url }}" target="_blank" class="btn btn-secondary btn-sm">Factura PDF</a>
                                            @endif
                                            @if($pago->factura_xml_url)
                                                <a href="{{ $pago->factura_xml_url }}" download class="btn btn-secondary btn-sm">XML</a>
                                            @endif
                                            @if(!$pago->comprobante_url && !$pago->factura_pdf_url && !$pago->factura_xml_url)
                                                <span style="color:var(--color-text-secondary);">—</span>
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
                    <h3 class="empty-state-title">Aún no tienes pagos registrados</h3>
                    <p class="empty-state-description">Los pagos de tu cuenta aparecerán aquí una vez que se valide tu comprobante.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
