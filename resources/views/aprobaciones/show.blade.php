@extends('layouts.app')

@section('title', 'Paquete de Aprobación')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('aprobaciones.index') }}" class="breadcrumb-link">Aprobaciones</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">{{ $paquete->cliente->nombre_negocio }}</span>
        </div>
    </div>
@endsection

@section('content')
@php
    $esBorrador = $paquete->estatus === 'borrador';
    $fotoBadgeMap = ['pendiente' => 'badge-primary', 'conservada' => 'badge-warning', 'aprobada' => 'badge-success', 'descartada' => 'badge-error'];
    $estadoBadge  = ['borrador' => 'badge-gray', 'pendiente' => 'badge-primary', 'completado' => 'badge-success', 'auto_aprobado' => 'badge-warning'];
@endphp
<div class="max-w-6xl mx-auto">

    {{-- ── Cabecera ──────────────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Paquete — {{ $paquete->cliente->nombre_negocio }}</h1>
            <p class="page-subtitle">
                Periodo {{ ucfirst($paquete->mes_revision_legible) }}
                &middot; Cuota: <strong>{{ $paquete->cantidad_requerida }}</strong> fotos
                @if($paquete->fecha_limite)
                    &middot; Fecha límite: {{ $paquete->fecha_limite->format('d/m/Y') }}
                @endif
                &middot; <span class="badge {{ $estadoBadge[$paquete->estatus] ?? 'badge-gray' }}">{{ str_replace('_', ' ', $paquete->estatus) }}</span>
            </p>
        </div>

    </div>

    {{-- Aviso inline (no flotante) de que ya se cumplió la cuota --}}
    @if($esBorrador && $paquete->fotos->count() >= $paquete->cantidad_requerida)
        <div style="display:flex;align-items:center;gap:.6rem;background:#eef0fe;border:1px solid #c7d2fe;border-radius:10px;padding:.75rem 1rem;margin-bottom:1.5rem;">
            <svg width="18" height="18" fill="none" stroke="#5b4fd6" viewBox="0 0 24 24" style="flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p style="color:#3730a3;font-size:.88rem;margin:0;">
                ¡Cuota completa! ({{ $paquete->fotos->count() }} de {{ $paquete->cantidad_requerida }} fotos). Usa el botón <strong>Enviar al cliente</strong> en la esquina inferior para continuar.
            </p>
        </div>
    @endif

    {{-- Botón flotante Enviar al cliente — siempre visible al hacer scroll, sin importar cuántas fotos haya --}}
    @if($esBorrador && $paquete->fotos->count() >= $paquete->cantidad_requerida)
        <div class="floating-enviar-cta" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:var(--z-dropdown);display:flex;align-items:center;gap:.75rem;background:linear-gradient(135deg,#667eea,#5b4fd6);border-radius:999px;padding:.75rem 1.25rem .75rem .75rem;box-shadow:0 8px 24px rgba(91,79,214,.45);animation:enviar-pulse 2.2s ease-in-out infinite;">
            <div style="width:2.25rem;height:2.25rem;border-radius:50%;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="#fff" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span style="color:#fff;font-weight:600;font-size:.85rem;white-space:nowrap;">Cuota completa</span>
            <form action="{{ route('aprobaciones.enviar-cliente', $paquete) }}" method="POST"
                  onsubmit="showConfirmModal('Enviar paquete', '¿Enviar este paquete al cliente? Recibirá una notificación por email y tendrá un plazo para seleccionar sus fotos.', () => this.submit()); return false;">
                @csrf
                <button type="submit" class="btn" style="background:#fff;color:#5b4fd6;font-weight:700;white-space:nowrap;padding:.5rem 1rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Enviar al cliente
                </button>
            </form>
        </div>
        <style>
            @keyframes enviar-pulse {
                0%, 100% { box-shadow: 0 8px 24px rgba(91,79,214,.45); }
                50% { box-shadow: 0 8px 32px rgba(91,79,214,.7); }
            }
            @media (max-width: 640px) {
                .floating-enviar-cta { right: 1rem; bottom: 1rem; }
            }
        </style>
    @endif

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    {{-- ── Zona de subida (solo en borrador) ───────────────────────────────── --}}
    @if($esBorrador)
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title">Subir fotografías candidatas</h3>
        </div>
        <div class="card-body">
            <div id="drop-zone" style="border:2px dashed #c7d2fe;border-radius:12px;padding:2.5rem 1.5rem;text-align:center;background:#f5f7ff;cursor:pointer;transition:border-color .2s,background .2s;margin-bottom:1.25rem;">
                <svg width="40" height="40" fill="none" stroke="#667eea" viewBox="0 0 24 24" style="margin:0 auto .75rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p style="font-weight:600;color:#374151;margin-bottom:.3rem;">Arrastra tus fotos aquí</p>
                <p style="font-size:.85rem;color:#6b7280;margin-bottom:1rem;">o haz clic para seleccionarlas — sin límite de cantidad</p>
                <input type="file" id="foto-input" multiple accept="image/*" style="display:none">
                <button type="button" onclick="document.getElementById('foto-input').click()"
                    style="display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.4rem;background:#667eea;color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:.9rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Seleccionar archivos
                </button>
            </div>
            <div id="upload-panel" style="display:none;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.6rem;">
                    <span id="upload-label" style="font-size:.9rem;font-weight:600;color:#374151;"></span>
                    <span id="upload-count" style="font-size:.82rem;color:#6b7280;"></span>
                </div>
                <div style="background:#e5e7eb;border-radius:999px;height:10px;overflow:hidden;margin-bottom:.5rem;">
                    <div id="upload-bar" style="height:100%;background:#667eea;width:0%;border-radius:999px;transition:width .3s;"></div>
                </div>
                <div id="upload-errors" style="margin-top:.75rem;"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Grid de fotografías ───────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 class="card-title">
                Fotografías candidatas ({{ $paquete->fotos->count() }})
            </h3>
        </div>
        <div class="card-body">
            @if($paquete->fotos->count() > 0)
                <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));">
                    @foreach($paquete->fotos as $foto)
                    @php $deReserva = $foto->fecha_ingreso_reserva !== null; @endphp
                    <div style="border:2px solid {{ $deReserva ? '#fde68a' : '#e5e7eb' }};border-radius:10px;overflow:hidden;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.07);position:relative;">
                        @if($deReserva)
                            <span class="badge badge-warning" style="position:absolute;top:6px;left:6px;font-size:.62rem;z-index:1;">De reserva</span>
                        @endif
                        <img src="{{ $foto->url }}" alt="Foto candidata" data-lightbox-trigger
                             onclick="abrirLightbox('{{ $foto->url }}')"
                             style="width:100%;height:140px;object-fit:cover;display:block;">
                        <div style="padding:.5rem .6rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                            @unless($esBorrador)
                                <span class="badge {{ $fotoBadgeMap[$foto->estatus] ?? 'badge-gray' }}">{{ $foto->estatus }}</span>
                            @endunless

                            @if($esBorrador && $foto->estatus !== 'aprobada')
                                @php
                                    $tituloConfirmFoto  = $deReserva ? 'Regresar a reserva' : 'Eliminar fotografía';
                                    $mensajeConfirmFoto = $deReserva
                                        ? '¿Quitar esta fotografía del paquete? Regresará al Banco de Reserva, no se elimina.'
                                        : '¿Eliminar esta fotografía?';
                                @endphp
                                <form action="{{ route('aprobaciones.fotos.destroy', [$paquete, $foto]) }}" method="POST"
                                      onsubmit="showConfirmModal('{{ $tituloConfirmFoto }}', '{{ $mensajeConfirmFoto }}', () => this.submit(), {danger: {{ $deReserva ? 'false' : 'true' }}}); return false;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-icon-danger"
                                        title="{{ $deReserva ? 'Regresar al Banco de Reserva' : 'Quitar foto' }}"
                                        aria-label="{{ $deReserva ? 'Regresar al Banco de Reserva' : 'Quitar foto' }}">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9.5 4h5a1 1 0 011 1v2h-7V5a1 1 0 011-1z"></path>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <p class="empty-state-description">Aún no se han subido fotografías candidatas.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<x-image-lightbox />

@section('scripts')
<script>
(function () {
    const input    = document.getElementById('foto-input');
    const dropZone = document.getElementById('drop-zone');
    if (!input) return; // no estamos en modo borrador

    const panel    = document.getElementById('upload-panel');
    const bar      = document.getElementById('upload-bar');
    const label    = document.getElementById('upload-label');
    const countEl  = document.getElementById('upload-count');
    const errorsEl = document.getElementById('upload-errors');
    const url      = '{{ route('aprobaciones.fotos.store', $paquete) }}';
    const csrf     = document.querySelector('meta[name="csrf-token"]').content;

    dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.style.borderColor = '#667eea'; dropZone.style.background = '#eef0fe'; });
    dropZone.addEventListener('dragleave', ()  => { dropZone.style.borderColor = '#c7d2fe'; dropZone.style.background = '#f5f7ff'; });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.style.borderColor = '#c7d2fe';
        dropZone.style.background  = '#f5f7ff';
        subirArchivos([...e.dataTransfer.files]);
    });
    input.addEventListener('change', () => subirArchivos([...input.files]));

    let subiendo = false;
    window.addEventListener('beforeunload', e => {
        if (subiendo) { e.preventDefault(); e.returnValue = 'Las fotos se están subiendo. ¿Seguro que quieres salir?'; }
    });

    async function subirArchivos(archivos) {
        if (!archivos.length) return;
        subiendo = true;
        panel.style.display = 'block';
        errorsEl.innerHTML  = '';
        bar.style.background = '#667eea';
        const total = archivos.length;
        let subidos = 0, errores = 0;

        for (let i = 0; i < archivos.length; i++) {
            const archivo = archivos[i];
            label.textContent   = `Subiendo: ${archivo.name}`;
            countEl.textContent = `${i + 1} de ${total}`;
            const form = new FormData();
            form.append('_token', csrf);
            form.append('fotos[]', archivo);
            try {
                const res = await fetch(url, { method: 'POST', body: form });
                res.ok || res.redirected ? subidos++ : (errores++, mostrarError(archivo.name, 'Error del servidor'));
            } catch (e) { errores++; mostrarError(archivo.name, e.message); }
            bar.style.width = ((i + 1) / total * 100) + '%';
        }

        bar.style.background = subidos === total ? '#10b981' : '#f59e0b';
        label.textContent    = subidos === total
            ? `✓ ${subidos} foto${subidos !== 1 ? 's' : ''} subida${subidos !== 1 ? 's' : ''} correctamente`
            : `${subidos} subidas, ${errores} con error`;
        countEl.textContent = '';
        subiendo = false;
        setTimeout(() => location.reload(), 1500);
    }

    function mostrarError(nombre, msg) {
        const div = document.createElement('div');
        div.style.cssText = 'padding:.4rem .8rem;background:#fee2e2;border-radius:6px;font-size:.8rem;color:#7f1d1d;margin-bottom:.3rem;';
        div.textContent = `✗ ${nombre}: ${msg}`;
        errorsEl.appendChild(div);
    }
})();
</script>
@endsection
@endsection
