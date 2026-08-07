@extends('layouts.app')

@section('title', 'Detalle de Renovación — ' . $renovacion->cliente->nombre_negocio)

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('renovaciones.index') }}" class="breadcrumb-link">Renovaciones</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">{{ $renovacion->cliente->nombre_negocio }}</span>
        </div>
    </div>
@endsection

@php
    $badgeMap = [
        'vigente'           => ['class' => 'badge-success',  'label' => 'Vigente'],
        'por_vencer'        => ['class' => 'badge-warning',  'label' => 'Próximo a vencer'],
        'en_revision'       => ['class' => 'badge-primary',  'label' => 'En revisión'],
        'pago_rechazado'    => ['class' => 'badge-error',    'label' => 'Pago rechazado'],
        'pago_validado'     => ['class' => 'badge-success',  'label' => 'Pago validado'],
        'factura_pendiente' => ['class' => 'badge-warning',  'label' => 'Factura pendiente'],
        'vencido'           => ['class' => 'badge-error',    'label' => 'Vencido'],
    ];
    $badge           = $badgeMap[$renovacion->estatus] ?? ['class' => 'badge-gray', 'label' => $renovacion->estatus];
    $diasGracia      = config('renovaciones.dias_gracia', 5);
    $diasSuspension  = (int) now()->startOfDay()->diffInDays(
        $renovacion->fecha_vencimiento->copy()->addDays($diasGracia), false
    );
@endphp

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">{{ $renovacion->cliente->nombre_negocio }}</h1>
            <p class="page-subtitle">
                {{ $renovacion->cliente->servicio_contratado }} ·
                ${{ number_format($renovacion->cliente->precio_mensual, 2) }}/mes ·
                <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
            </p>
        </div>
        <div class="page-actions" style="display:flex; gap:.75rem; align-items:center;">
            @if($renovacion->tieneSolicitudPendiente())
            <a href="{{ route('renovaciones.create.solicitud', $renovacion) }}" class="btn btn-primary">
                Aprobar y crear ciclo
            </a>
            @endif
            <a href="{{ route('renovaciones.index') }}" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    {{-- Solicitud pendiente alert --}}
    @if($renovacion->tieneSolicitudPendiente())
    <div style="background:#ede9fe; border:1px solid #7c3aed; border-radius:var(--radius-lg); padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; gap:.75rem; align-items:flex-start;">
        <svg width="20" height="20" fill="none" stroke="#7c3aed" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:.1rem;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <div>
            <p style="font-weight:600; color:#6d28d9;">Solicitud de renovación pendiente</p>
            <p style="font-size:.875rem; color:#5b21b6; margin-top:.25rem;">
                El cliente solicitó renovación el <strong>{{ $renovacion->fecha_solicitud_renovacion->format('d/m/Y \a \l\a\s H:i') }}</strong>.
                Aprueba la solicitud creando el nuevo ciclo para que pueda realizar su pago.
            </p>
        </div>
    </div>
    @endif

    {{-- Current period info --}}
    <div class="grid gap-4 mb-6" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="card">
            <div class="card-body text-center" style="padding:1rem;">
                <div style="font-size:.75rem; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:.05em;">Periodo actual</div>
                <div style="font-size:.95rem; font-weight:600; margin-top:.25rem;">
                    {{ $renovacion->fecha_inicio->format('d/m/Y') }} — {{ $renovacion->fecha_vencimiento->format('d/m/Y') }}
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body text-center" style="padding:1rem;">
                @php $dias = $renovacion->diasRestantes(); @endphp
                <div style="font-size:.75rem; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:.05em;">Días restantes</div>
                <div style="font-size:1.5rem; font-weight:700; margin-top:.25rem;
                    color: {{ $dias < 0 ? 'var(--color-error)' : ($dias <= 5 ? 'var(--color-warning,#f59e0b)' : 'var(--color-success)') }}">
                    {{ $dias >= 0 ? $dias : 'Vencido' }}
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body text-center" style="padding:1rem;">
                <div style="font-size:.75rem; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:.05em;">Historial ciclos</div>
                <div style="font-size:1.5rem; font-weight:700; margin-top:.25rem;">{{ $renovacion->historial->count() }}</div>
            </div>
        </div>
        @if($renovacion->estatus === 'vencido')
        <div class="card" style="border:1px solid var(--color-error);">
            <div class="card-body text-center" style="padding:1rem;">
                <div style="font-size:.75rem; color:var(--color-error); text-transform:uppercase; letter-spacing:.05em;">Días para suspensión</div>
                @if($diasSuspension > 0)
                    <div style="font-size:1.5rem; font-weight:700; margin-top:.25rem; color:var(--color-warning,#f59e0b);">{{ $diasSuspension }}</div>
                    <div style="font-size:.75rem; color:var(--color-text-secondary); margin-top:.15rem;">{{ $renovacion->fecha_vencimiento->copy()->addDays($diasGracia)->format('d/m/Y') }}</div>
                @elseif($diasSuspension === 0)
                    <div style="font-size:1.1rem; font-weight:700; margin-top:.25rem; color:var(--color-error);">Hoy</div>
                @else
                    <div style="font-size:1.1rem; font-weight:700; margin-top:.25rem; color:var(--color-error);">Ya suspendida</div>
                    <div style="font-size:.75rem; color:var(--color-text-secondary); margin-top:.15rem;">desde {{ $renovacion->fecha_vencimiento->copy()->addDays($diasGracia)->format('d/m/Y') }}</div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ── Comprobante section (only when there's one) ──────────────────────── --}}
    @if($renovacion->comprobante_pago)
    <div class="card mb-6">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3 class="card-title">Comprobante de pago enviado</h3>
            <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
        </div>
        <div class="card-body">
            <div class="grid gap-4" style="grid-template-columns: 1fr 1fr;">
                <div>
                    <div class="form-group">
                        <span class="form-label">Fecha de pago reportada</span>
                        <p class="font-medium">{{ $renovacion->fecha_pago_cliente?->format('d/m/Y') ?? 'N/A' }}</p>
                    </div>
                    <div class="form-group">
                        <span class="form-label">Monto</span>
                        <p class="font-medium">${{ number_format($renovacion->monto, 2) }}</p>
                    </div>
                    @if($renovacion->referencia_bancaria)
                    <div class="form-group">
                        <span class="form-label">Referencia bancaria</span>
                        <p class="font-medium">{{ $renovacion->referencia_bancaria }}</p>
                    </div>
                    @endif
                    <div class="form-group">
                        <span class="form-label">Solicita factura</span>
                        <p class="font-medium">{{ $renovacion->solicita_factura ? 'Sí' : 'No' }}</p>
                    </div>
                </div>
                <div>
                    <span class="form-label">Archivo comprobante</span>
                    @php $ext = strtolower(pathinfo($renovacion->comprobante_pago, PATHINFO_EXTENSION)); @endphp
                    @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                        <a href="{{ $renovacion->comprobante_url }}" target="_blank">
                            <img src="{{ $renovacion->comprobante_url }}" alt="Comprobante de pago"
                                 style="max-width:100%; max-height:340px; border-radius:.5rem; border:1px solid var(--color-border); margin-top:.5rem; display:block; cursor:zoom-in;">
                        </a>
                    @elseif($ext === 'pdf')
                        <iframe src="{{ $renovacion->comprobante_url }}"
                                style="width:100%; height:340px; border-radius:.5rem; border:1px solid var(--color-border); margin-top:.5rem; display:block;">
                        </iframe>
                    @endif
                    <a href="{{ $renovacion->comprobante_url }}" target="_blank" class="btn btn-secondary btn-sm mt-2">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Abrir en nueva pestaña
                    </a>
                </div>
            </div>

            {{-- Fiscal data --}}
            @if($renovacion->solicita_factura && $renovacion->rfc)
            <div style="margin-top:1.5rem; padding-top:1.5rem; border-top: 1px solid var(--color-border);">
                <h4 style="font-weight:600; margin-bottom:1rem;">Datos fiscales para factura</h4>
                <div class="grid gap-4" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group"><span class="form-label">RFC</span><p class="font-medium">{{ $renovacion->rfc }}</p></div>
                    <div class="form-group"><span class="form-label">Razón Social</span><p class="font-medium">{{ $renovacion->razon_social }}</p></div>
                    <div class="form-group"><span class="form-label">Código Postal</span><p class="font-medium">{{ $renovacion->codigo_postal_fiscal }}</p></div>
                    <div class="form-group"><span class="form-label">Régimen Fiscal</span><p class="font-medium">{{ $renovacion->regimen_fiscal }}</p></div>
                    <div class="form-group"><span class="form-label">Uso CFDI</span><p class="font-medium">{{ $renovacion->uso_cfdi }}</p></div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Admin actions ─────────────────────────────────────────────────────── --}}
    @if($renovacion->estatus === \App\Models\ControlRenovacion::ESTATUS_EN_REVISION)
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title">Validar comprobante</h3>
        </div>
        <div class="card-body">
            <p style="color:var(--color-text-secondary); margin-bottom:1.5rem;">
                Revisa el comprobante de pago del cliente y realiza una de las siguientes acciones:
            </p>
            <div class="flex gap-3 flex-wrap">
                {{-- Validate --}}
                <form action="{{ route('renovaciones.validar', $renovacion) }}" method="POST"
                      onsubmit="showConfirmModal('Validar pago', '¿Confirmas que el pago es válido?', () => this.submit()); return false;">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Validar pago
                    </button>
                </form>

                {{-- Reject --}}
                <button type="button" class="btn btn-danger" onclick="document.getElementById('modal-rechazo').style.display='flex'">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Rechazar pago
                </button>
            </div>
        </div>
    </div>

    {{-- Reject modal --}}
    <div id="modal-rechazo" style="display:none; position:fixed; inset:0; z-index:50; align-items:center; justify-content:center; background:rgba(0,0,0,.5);">
        <div class="card" style="width:100%; max-width:480px; margin:1rem;">
            <div class="card-header">
                <h3 class="card-title">Motivo del rechazo</h3>
            </div>
            <form action="{{ route('renovaciones.rechazar', $renovacion) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label" for="motivo_rechazo">Motivo <span style="color:var(--color-error)">*</span></label>
                        <textarea id="motivo_rechazo" name="motivo_rechazo" rows="4"
                            class="form-input" required
                            placeholder="Ej: El comprobante es ilegible, el monto no coincide, pago no localizado..."></textarea>
                        @error('motivo_rechazo')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="card-footer flex gap-3 justify-end">
                    <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('modal-rechazo').style.display='none'">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rechazar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ── Upload invoice (when factura_pendiente) ─────────────────────────── --}}
    @if($renovacion->estatus === \App\Models\ControlRenovacion::ESTATUS_FACTURA_PENDIENTE)
    <div class="card mb-6" style="border-left: 4px solid var(--color-warning,#f59e0b);">
        <div class="card-header">
            <h3 class="card-title">Cargar factura</h3>
        </div>
        <div class="card-body">
            <p style="color:var(--color-text-secondary); margin-bottom:1.5rem;">
                El cliente solicitó factura. Sube ambos archivos para completar la renovación.
            </p>
            <form action="{{ route('renovaciones.factura', $renovacion) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid gap-4" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label class="form-label" for="factura_pdf">Factura PDF <span style="color:var(--color-error)">*</span></label>
                        <input type="file" id="factura_pdf" name="factura_pdf" accept=".pdf" class="form-input" required>
                        @error('factura_pdf')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="factura_xml">Factura XML <span style="color:var(--color-error)">*</span></label>
                        <input type="file" id="factura_xml" name="factura_xml" accept=".xml" class="form-input" required>
                        @error('factura_xml')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Cargar y completar renovación
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ── Historial de renovaciones ─────────────────────────────────────────── --}}
    @if($renovacion->historial->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historial de renovaciones ({{ $renovacion->historial->count() }})</h3>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Periodo</th>
                            <th>Fecha pago</th>
                            <th>Monto</th>
                            <th>Factura</th>
                            <th>Validado por</th>
                            <th>Archivos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($renovacion->historial->sortByDesc('created_at') as $h)
                        <tr>
                            <td style="font-size:.85rem;">{{ $h->periodo_inicio->format('d/m/Y') }} — {{ $h->periodo_fin->format('d/m/Y') }}</td>
                            <td>{{ $h->fecha_pago->format('d/m/Y') }}</td>
                            <td class="font-medium">${{ number_format($h->monto, 2) }}</td>
                            <td>
                                @if($h->solicita_factura)
                                    <span class="badge badge-primary">Con factura</span>
                                @else
                                    <span class="badge badge-gray">Sin factura</span>
                                @endif
                            </td>
                            <td style="font-size:.85rem;">{{ $h->validador->name ?? '—' }}</td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="{{ $h->comprobante_url }}" target="_blank" class="btn btn-ghost btn-xs" title="Ver comprobante">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                    @if($h->factura_pdf_url)
                                    <a href="{{ $h->factura_pdf_url }}" target="_blank" class="btn btn-ghost btn-xs" title="PDF">PDF</a>
                                    @endif
                                    @if($h->factura_xml_url)
                                    <a href="{{ $h->factura_xml_url }}" target="_blank" class="btn btn-ghost btn-xs" title="XML">XML</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
