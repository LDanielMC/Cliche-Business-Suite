@extends('layouts.app')

@section('title', 'Mi Renovación')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('cliente.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Mi Renovación</span>
        </div>
    </div>
@endsection

@php
    use App\Models\ControlRenovacion;
    $badgeMap = [
        'vigente'           => ['class' => 'badge-success', 'label' => 'Activo'],
        'por_vencer'        => ['class' => 'badge-warning', 'label' => 'Próximo a vencer'],
        'en_revision'       => ['class' => 'badge-primary', 'label' => 'Comprobante en revisión'],
        'pago_rechazado'    => ['class' => 'badge-error',   'label' => 'Pago rechazado'],
        'pago_validado'     => ['class' => 'badge-success', 'label' => 'Pago validado'],
        'factura_pendiente' => ['class' => 'badge-warning', 'label' => 'Factura en proceso'],
        'vencido'           => ['class' => 'badge-error',   'label' => 'Servicio vencido'],
    ];
@endphp

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Mi Renovación</h1>
            <p class="page-subtitle">Estado de tu servicio de posicionamiento local</p>
        </div>
    </div>

    @if(!$renovacion)
        {{-- No renewal record yet --}}
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">Sin ciclo de servicio activo</h3>
                    <p class="empty-state-description">El equipo de Cliché aún no ha registrado tu ciclo de servicio. Contáctanos si crees que esto es un error.</p>
                </div>
            </div>
        </div>
    @else
        @php
            $badge          = $badgeMap[$renovacion->estatus] ?? ['class' => 'badge-gray', 'label' => $renovacion->estatus];
            $dias           = $renovacion->diasRestantes();
            $diasGracia     = config('renovaciones.dias_gracia', 5);
            $diasSuspension = (int) now()->startOfDay()->diffInDays(
                $renovacion->fecha_vencimiento->copy()->addDays($diasGracia), false
            );
            // A suspended client's account only lifts once a payment is validated —
            // never automatically when a cycle is merely registered. But if the admin
            // already registered a fresh cycle since the suspension, fecha_vencimiento
            // was reset and $diasSuspension is still >= 0 (the automatic-suspension
            // cron only ever fires once that goes negative). That's the signal that
            // the client can now submit a payment proof instead of having to request one.
            $puedeEnviarComprobanteSuspendido = Auth::user()->estatus === \App\Models\User::ESTATUS_SUSPENDIDO
                && $renovacion->dentroDePeriodoGracia();
        @endphp

        {{-- ── Alert banner for urgent statuses ───────────────────────────── --}}
        @if($renovacion->estatus === ControlRenovacion::ESTATUS_PAGO_RECHAZADO)
        <div style="background:var(--color-error-light,#fef2f2); border:1px solid var(--color-error); border-radius:var(--radius-lg); padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; gap:.75rem; align-items:flex-start;">
            <svg width="20" height="20" fill="none" stroke="var(--color-error)" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:.1rem;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p style="font-weight:600; color:var(--color-error);">Comprobante rechazado</p>
                <p style="font-size:.875rem; color:var(--color-error); margin-top:.25rem;">
                    {{ $renovacion->motivo_rechazo }}
                </p>
                <p style="font-size:.875rem; margin-top:.5rem;">Por favor envía un nuevo comprobante válido.</p>
            </div>
        </div>
        @endif

        @if($renovacion->estatus === ControlRenovacion::ESTATUS_VENCIDO)
        <div style="background:var(--color-error-light,#fef2f2); border:1px solid var(--color-error); border-radius:var(--radius-lg); padding:1rem 1.25rem; margin-bottom:1.5rem;">
            @if(Auth::user()->estatus === \App\Models\User::ESTATUS_SUSPENDIDO)
                <p style="font-weight:600; color:var(--color-error);">Tu cuenta está suspendida</p>
                @if($puedeEnviarComprobanteSuspendido)
                    {{-- Admin already registered a new cycle: client can pay now --}}
                    <p style="font-size:.875rem; margin-top:.25rem;">
                        El equipo ya registró tu nuevo ciclo. Envía tu comprobante de pago
                        @if($diasSuspension > 0)
                            en los próximos <strong>{{ $diasSuspension }} {{ $diasSuspension === 1 ? 'día' : 'días' }}</strong>
                        @else
                            <strong>hoy</strong>
                        @endif
                        para reactivar tu cuenta.
                    </p>
                    <a href="{{ route('renovaciones.cliente.renovar', $renovacion) }}" class="btn btn-danger btn-sm" style="margin-top:.75rem; display:inline-block;">Enviar comprobante</a>
                @elseif($renovacion->tieneSolicitudPendiente())
                    <p style="font-size:.875rem; margin-top:.25rem;">
                        Tu solicitud de renovación fue enviada el
                        <strong>{{ $renovacion->fecha_solicitud_renovacion->format('d/m/Y H:i') }}</strong>.
                        El equipo la revisará y te habilitará el pago pronto.
                    </p>
                @else
                    <p style="font-size:.875rem; margin-top:.25rem;">Para reactivar tu cuenta, solicita una nueva renovación. El equipo la revisará y te habilitará el pago.</p>
                    <form method="POST" action="{{ route('renovaciones.cliente.solicitar', $renovacion) }}" style="margin-top:.75rem;">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">Solicitar renovación</button>
                    </form>
                @endif
            @else
                <p style="font-weight:600; color:var(--color-error);">Tu servicio ha vencido</p>
                <p style="font-size:.875rem; margin-top:.25rem;">Renueva ahora para recuperar el acceso completo al servicio.</p>
                @if($diasSuspension > 0)
                <p style="font-size:.875rem; margin-top:.5rem; color:var(--color-error);">
                    <strong>Tu cuenta será suspendida en {{ $diasSuspension }} {{ $diasSuspension === 1 ? 'día' : 'días' }}</strong>
                    ({{ $renovacion->fecha_vencimiento->copy()->addDays($diasGracia)->format('d/m/Y') }}).
                </p>
                @elseif($diasSuspension === 0)
                <p style="font-size:.875rem; margin-top:.5rem; font-weight:600; color:var(--color-error);">
                    Tu cuenta será suspendida hoy. Contacta al equipo si ya realizaste tu pago.
                </p>
                @endif
            @endif
        </div>
        @endif

        {{-- ── Main service card ────────────────────────────────────────────── --}}
        <div class="card mb-6">
            <div class="card-body" style="padding: 1.75rem;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">
                    <div>
                        <div style="font-size:.75rem; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:.05em; margin-bottom:.35rem;">Estado del servicio</div>
                        <span class="badge {{ $badge['class'] }}" style="font-size:.9rem; padding:.35rem .75rem;">{{ $badge['label'] }}</span>
                    </div>
                    {{-- Suspended clients without a fresh cycle use the "Solicitar renovación" button in the alert above --}}
                    @if($renovacion->puedeRenovar() && (Auth::user()->estatus !== \App\Models\User::ESTATUS_SUSPENDIDO || $puedeEnviarComprobanteSuspendido))
                    <a href="{{ route('renovaciones.cliente.renovar', $renovacion) }}" class="btn btn-primary" style="font-size:1rem; padding:.65rem 1.5rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Renovar servicio
                    </a>
                    @endif
                </div>

                <div class="grid gap-6 mt-6" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                    <div>
                        <div style="font-size:.75rem; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:.05em;">Plan contratado</div>
                        <div style="font-weight:600; margin-top:.25rem;">{{ $cliente->servicio_contratado }}</div>
                    </div>
                    <div>
                        <div style="font-size:.75rem; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:.05em;">Costo mensual</div>
                        <div style="font-weight:600; margin-top:.25rem;">${{ number_format($cliente->precio_mensual, 2) }}</div>
                    </div>
                    <div>
                        <div style="font-size:.75rem; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:.05em;">Fecha de vencimiento</div>
                        <div style="font-weight:600; margin-top:.25rem;">{{ $renovacion->fecha_vencimiento->format('d/m/Y') }}</div>
                    </div>
                    <div>
                        <div style="font-size:.75rem; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:.05em;">Días restantes</div>
                        <div style="font-weight:700; font-size:1.4rem; margin-top:.1rem;
                            color: {{ $dias < 0 ? 'var(--color-error)' : ($dias <= 5 ? 'var(--color-warning,#f59e0b)' : 'var(--color-success)') }}">
                            {{ $dias >= 0 ? $dias : '0' }}
                        </div>
                    </div>
                    {{-- Once already suspended, the alert banner above covers the relevant countdown/messaging --}}
                    @if($renovacion->estatus === ControlRenovacion::ESTATUS_VENCIDO && Auth::user()->estatus !== \App\Models\User::ESTATUS_SUSPENDIDO)
                    <div>
                        <div style="font-size:.75rem; color:var(--color-error); text-transform:uppercase; letter-spacing:.05em;">Días para suspensión</div>
                        @if($diasSuspension > 0)
                        <div style="font-weight:700; font-size:1.4rem; margin-top:.1rem; color:var(--color-warning,#f59e0b);">
                            {{ $diasSuspension }}
                        </div>
                        @elseif($diasSuspension === 0)
                        <div style="font-weight:700; font-size:1rem; margin-top:.25rem; color:var(--color-error);">Hoy</div>
                        @else
                        <div style="font-weight:700; font-size:1rem; margin-top:.25rem; color:var(--color-error);">Suspendida</div>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Progress bar for days remaining --}}
                @if($dias >= 0)
                <div style="margin-top:1.25rem;">
                    <div style="font-size:.75rem; color:var(--color-text-secondary); margin-bottom:.4rem;">Tiempo transcurrido del ciclo</div>
                    @php
                        $totalDias = 30;
                        $transcurridos = $totalDias - $dias;
                        $pct = min(100, max(0, round($transcurridos / $totalDias * 100)));
                        $barColor = $dias <= 5 ? 'var(--color-error)' : ($dias <= 10 ? 'var(--color-warning,#f59e0b)' : 'var(--color-success)');
                    @endphp
                    <div style="height:8px; background:var(--color-border); border-radius:4px; overflow:hidden;">
                        <div style="height:100%; width:{{ $pct }}%; background:{{ $barColor }}; border-radius:4px; transition:width .4s;"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ── Status tracker (workflow steps) ────────────────────────────── --}}
        @if($renovacion->enProceso() || $renovacion->estatus === ControlRenovacion::ESTATUS_PAGO_RECHAZADO)
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">Seguimiento de tu renovación</h3>
            </div>
            <div class="card-body">
                @php
                    $steps = [
                        ['key' => 'enviado',   'label' => 'Comprobante enviado',   'icon' => '📄'],
                        ['key' => 'revision',  'label' => 'En revisión',            'icon' => '🔍'],
                        ['key' => 'validado',  'label' => 'Pago validado',          'icon' => '✅'],
                        ['key' => 'completo',  'label' => 'Servicio renovado',      'icon' => '🎉'],
                    ];
                    $currentStep = match($renovacion->estatus) {
                        'en_revision'       => 1,
                        'pago_validado'     => 2,
                        'factura_pendiente' => 2,
                        default             => 0,
                    };
                    if ($renovacion->estatus === 'pago_rechazado') $currentStep = -1; // error state
                @endphp
                <div style="display:flex; align-items:center; gap:0; flex-wrap:wrap;">
                    @foreach($steps as $i => $step)
                        @php
                            $done   = $currentStep > $i;
                            $active = $currentStep === $i;
                            $error  = $currentStep === -1 && $i === 1;
                        @endphp
                        <div style="display:flex; align-items:center; flex:1; min-width:100px;">
                            <div style="display:flex; flex-direction:column; align-items:center; gap:.35rem; flex:1;">
                                <div style="
                                    width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.1rem;
                                    {{ $error ? 'background:var(--color-error-light,#fef2f2); border:2px solid var(--color-error);' : ($done ? 'background:var(--color-success-light,#ecfdf5); border:2px solid var(--color-success);' : ($active ? 'background:var(--color-brand-light,#eff6ff); border:2px solid var(--color-brand);' : 'background:var(--color-border); border:2px solid var(--color-border);')) }}
                                ">{{ $step['icon'] }}</div>
                                <div style="font-size:.75rem; text-align:center; font-weight:{{ $active || $done ? '600' : '400' }}; color:{{ $error && $i===1 ? 'var(--color-error)' : ($active || $done ? 'var(--color-text-primary)' : 'var(--color-text-secondary)') }}">{{ $step['label'] }}</div>
                            </div>
                            @if(!$loop->last)
                                <div style="height:2px; flex:1; background:{{ $done ? 'var(--color-success)' : 'var(--color-border)' }}; margin-top:-1rem;"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if($renovacion->estatus === ControlRenovacion::ESTATUS_FACTURA_PENDIENTE)
                <p style="margin-top:1rem; font-size:.875rem; color:var(--color-text-secondary); text-align:center;">
                    Tu pago fue validado. Estamos generando tu factura y se la cargaremos próximamente.
                </p>
                @endif
            </div>
        </div>
        @endif

        {{-- ── Renewal history ─────────────────────────────────────────────── --}}
        @if($cliente->historialRenovaciones->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Historial de renovaciones</h3>
            </div>
            <div class="card-body" style="padding:0;">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Periodo</th>
                                <th>Pago</th>
                                <th>Monto</th>
                                <th>Factura</th>
                                <th>Archivos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cliente->historialRenovaciones as $h)
                            <tr>
                                <td style="font-size:.85rem;">
                                    {{ $h->periodo_inicio->format('d/m/Y') }}
                                    <span style="color:var(--color-text-secondary)">—</span>
                                    {{ $h->periodo_fin->format('d/m/Y') }}
                                </td>
                                <td>{{ $h->fecha_pago->format('d/m/Y') }}</td>
                                <td class="font-medium">${{ number_format($h->monto, 2) }}</td>
                                <td>
                                    @if($h->solicita_factura)
                                        <span class="badge badge-primary">Con factura</span>
                                    @else
                                        <span class="badge badge-gray">Sin factura</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ $h->comprobante_url }}" target="_blank" class="btn btn-ghost btn-xs" title="Comprobante">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                        @if($h->factura_pdf_url)
                                        <a href="{{ $h->factura_pdf_url }}" target="_blank" class="btn btn-ghost btn-xs">PDF</a>
                                        @endif
                                        @if($h->factura_xml_url)
                                        <a href="{{ $h->factura_xml_url }}" target="_blank" class="btn btn-ghost btn-xs">XML</a>
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
    @endif

</div>
@endsection
