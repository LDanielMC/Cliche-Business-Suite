@extends('layouts.app')

@section('title', 'Panel de Cliente')

@section('content')
@php
    use App\Models\ControlRenovacion;
    $cliente    = $user->cliente;
    $diasGracia = config('renovaciones.dias_gracia', 5);
    $renovacion = $cliente?->renovacionActiva;

    // Only hide the stale vencido cycle when the user is activo (manually reactivated
    // from dado_de_baja and waiting for admin to create a new cycle).
    // Suspended clients still need to see their cycle to submit a payment proof.
    if ($renovacion
        && $renovacion->estatus === ControlRenovacion::ESTATUS_VENCIDO
        && $renovacion->fecha_vencimiento->copy()->addDays($diasGracia)->lt(now())
        && $user->estatus === \App\Models\User::ESTATUS_ACTIVO
    ) {
        $renovacion = null;
    }

    $diasRest    = $renovacion ? $renovacion->diasRestantes() : null;
    $alertaRenovacion = $renovacion && in_array($renovacion->estatus, [
        ControlRenovacion::ESTATUS_POR_VENCER,
        ControlRenovacion::ESTATUS_PAGO_RECHAZADO,
        ControlRenovacion::ESTATUS_VENCIDO,
    ]);
@endphp

<div class="max-w-4xl mx-auto">

    {{-- Welcome header --}}
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Bienvenido, {{ $user->nombres ?? $user->name }}</h1>
            <p class="page-subtitle">{{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
        </div>
    </div>

    {{-- ── Renewal alert ─────────────────────────────────────────────────────── --}}
    @if($alertaRenovacion)
        @if($renovacion->estatus === ControlRenovacion::ESTATUS_PAGO_RECHAZADO)
        <div style="background:#fef2f2; border:1px solid #ef4444; border-radius:12px; padding:1.25rem 1.5rem; margin-bottom:1.5rem; display:flex; gap:1rem; align-items:flex-start;">
            <svg width="24" height="24" fill="none" stroke="#ef4444" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:.1rem;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div style="flex:1;">
                <p style="font-weight:700; color:#ef4444;">Comprobante rechazado</p>
                <p style="font-size:.875rem; color:#7f1d1d; margin-top:.25rem;">{{ $renovacion->motivo_rechazo }}</p>
                <a href="{{ route('renovaciones.cliente.renovar', $renovacion) }}" class="btn btn-danger btn-sm" style="margin-top:.75rem; display:inline-flex;">
                    Enviar nuevo comprobante
                </a>
            </div>
        </div>
        @elseif($renovacion->estatus === ControlRenovacion::ESTATUS_VENCIDO)
        <div style="background:#fef2f2; border:1px solid #ef4444; border-radius:12px; padding:1.25rem 1.5rem; margin-bottom:1.5rem; display:flex; gap:1rem; align-items:center; justify-content:space-between; flex-wrap:wrap;">
            <div style="display:flex; gap:.75rem; align-items:center;">
                <svg width="24" height="24" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p style="font-weight:700; color:#ef4444;">Tu servicio ha vencido</p>
                    <p style="font-size:.875rem; color:#7f1d1d;">Renueva ahora para recuperar el acceso completo.</p>
                </div>
            </div>
            <a href="{{ route('renovaciones.cliente.renovar', $renovacion) }}" class="btn btn-danger">
                Renovar ahora
            </a>
        </div>
        @else {{-- por_vencer --}}
        <div style="background:#fffbeb; border:1px solid #f59e0b; border-radius:12px; padding:1.25rem 1.5rem; margin-bottom:1.5rem; display:flex; gap:1rem; align-items:center; justify-content:space-between; flex-wrap:wrap;">
            <div style="display:flex; gap:.75rem; align-items:center;">
                <svg width="24" height="24" fill="none" stroke="#d97706" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p style="font-weight:700; color:#92400e;">Tu servicio vence en {{ $diasRest }} día{{ $diasRest !== 1 ? 's' : '' }}</p>
                    <p style="font-size:.875rem; color:#92400e;">Vence el {{ $renovacion->fecha_vencimiento->format('d/m/Y') }}. Renueva pronto para no perder el servicio.</p>
                </div>
            </div>
            <a href="{{ route('renovaciones.cliente.renovar', $renovacion) }}" class="btn btn-primary">
                Renovar servicio
            </a>
        </div>
        @endif
    @endif

    {{-- ── Fotos pendientes de revisión ──────────────────────────────────────── --}}
    @if($paquetesPendientes->isNotEmpty())
        @php $primerPendiente = $paquetesPendientes->first(); @endphp
        <div style="background:#eef0fe; border:1px solid #667eea; border-radius:12px; padding:1.25rem 1.5rem; margin-bottom:1.5rem; display:flex; gap:1rem; align-items:center; justify-content:space-between; flex-wrap:wrap;">
            <div style="display:flex; gap:.75rem; align-items:center;">
                <svg width="24" height="24" fill="none" stroke="#5b4fd6" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div>
                    <p style="font-weight:700; color:#3730a3;">
                        Tienes {{ $paquetesPendientes->count() }} paquete{{ $paquetesPendientes->count() !== 1 ? 's' : '' }} de fotos esperando tu revisión
                    </p>
                    <p style="font-size:.875rem; color:#3730a3;">
                        {{ ucfirst($primerPendiente->mes_revision_legible) }}
                        @if($primerPendiente->fecha_limite)
                            &middot; Fecha límite: {{ $primerPendiente->fecha_limite->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
            </div>
            <a href="{{ route('cliente.aprobaciones.show', $primerPendiente) }}" class="btn btn-primary">
                Revisar ahora
            </a>
        </div>
    @endif

    {{-- ── Service summary card ──────────────────────────────────────────────── --}}
    @if($cliente && $renovacion)
    <div class="card mb-6" style="background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); color:white;">
        <div class="card-body" style="padding:1.75rem;">
            <div style="font-size:.8rem; opacity:.75; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.35rem;">Mi servicio activo</div>
            <div style="font-size:1.25rem; font-weight:700; margin-bottom:1.25rem;">{{ $cliente->servicio_contratado }}</div>
            <div style="display:flex; gap:2.5rem; flex-wrap:wrap;">
                <div>
                    <div style="font-size:.75rem; opacity:.7; text-transform:uppercase;">Vencimiento</div>
                    <div style="font-weight:600; margin-top:.2rem;">{{ $renovacion->fecha_vencimiento->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem; opacity:.7; text-transform:uppercase;">Días restantes</div>
                    <div style="font-weight:700; font-size:1.4rem; margin-top:.1rem;">{{ max(0, $diasRest) }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem; opacity:.7; text-transform:uppercase;">Costo mensual</div>
                    <div style="font-weight:600; margin-top:.2rem;">${{ number_format($cliente->precio_mensual, 2) }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem; opacity:.7; text-transform:uppercase;">Fotos incluidas</div>
                    <div style="font-weight:600; margin-top:.2rem;">{{ $cliente->cantidad_fotos }} por mes</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Quick access grid ─────────────────────────────────────────────────── --}}
    <div class="grid gap-4 mb-6" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
        <a href="{{ route('cliente.aprobaciones.index') }}" class="card" style="text-decoration:none; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="card-body text-center" style="padding:1.5rem 1rem;">
                <svg width="32" height="32" fill="none" stroke="var(--color-brand)" viewBox="0 0 24 24" style="margin:0 auto .75rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div style="font-weight:600;">Mis Fotos</div>
                <div style="font-size:.8rem; color:var(--color-text-secondary); margin-top:.25rem;">Aprobar contenido</div>
            </div>
        </a>

        <a href="{{ route('pagos.misPagos') }}" class="card" style="text-decoration:none; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="card-body text-center" style="padding:1.5rem 1rem;">
                <svg width="32" height="32" fill="none" stroke="var(--color-brand)" viewBox="0 0 24 24" style="margin:0 auto .75rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <div style="font-weight:600;">Mis Pagos</div>
                <div style="font-size:.8rem; color:var(--color-text-secondary); margin-top:.25rem;">Historial de pagos</div>
            </div>
        </a>

        <a href="{{ route('renovaciones.cliente.index') }}" class="card" style="text-decoration:none; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="card-body text-center" style="padding:1.5rem 1rem;">
                <svg width="32" height="32" fill="none" stroke="var(--color-brand)" viewBox="0 0 24 24" style="margin:0 auto .75rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <div style="font-weight:600;">Mi Renovación</div>
                <div style="font-size:.8rem; color:var(--color-text-secondary); margin-top:.25rem;">Estado del servicio</div>
            </div>
        </a>

        <a href="{{ route('clientes.perfil') }}" class="card" style="text-decoration:none; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="card-body text-center" style="padding:1.5rem 1rem;">
                <svg width="32" height="32" fill="none" stroke="var(--color-brand)" viewBox="0 0 24 24" style="margin:0 auto .75rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <div style="font-weight:600;">Mi Perfil</div>
                <div style="font-size:.8rem; color:var(--color-text-secondary); margin-top:.25rem;">Mis datos</div>
            </div>
        </a>
    </div>

    {{-- Account info --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información de la sesión</h3>
        </div>
        <div class="card-body" style="display:flex; gap:2rem; flex-wrap:wrap; font-size:.9rem;">
            <div><span style="color:var(--color-text-secondary);">Rol:</span> <strong>Cliente</strong></div>
            <div><span style="color:var(--color-text-secondary);">Email:</span> <strong>{{ $user->email }}</strong></div>
            <div><span style="color:var(--color-text-secondary);">Ingreso:</span> <strong>{{ now()->format('d/m/Y H:i') }}</strong></div>
        </div>
    </div>

</div>
@endsection
