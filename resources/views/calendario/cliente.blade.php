@extends('layouts.app')

@section('title', 'Mi Calendario de Fotografías')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('cliente.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Mi Calendario</span>
        </div>
    </div>
@endsection

@section('content')
@php
    $mesAnterior  = \Carbon\Carbon::create($anio, $mes, 1)->subMonth();
    $mesSiguiente = \Carbon\Carbon::create($anio, $mes, 1)->addMonth();
    $nombreMes    = \Carbon\Carbon::create($anio, $mes, 1)->translatedFormat('F Y');

    // Datos de cada publicación por día, para el modal de detalle.
    $entradasDiaJson = $publicaciones->map(function ($grupo) {
        return $grupo->map(function ($e) {
            return [
                'id'      => $e->id,
                'url'     => $e->fotoAprobacion?->url ?? $e->fotografia_url,
                'estatus' => $e->estatus,
            ];
        })->values();
    });
@endphp

<div class="max-w-4xl mx-auto">

    {{-- ── Cabecera ─────────────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Mi Calendario de Fotografías</h1>
            <p class="page-subtitle">{{ $cliente->nombre_negocio }} — fotografías programadas y publicadas</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            {{-- Navegación mes --}}
            <div style="display:flex; align-items:center; gap:.75rem;">
                <a href="{{ route('calendario.cliente', ['mes' => $mesAnterior->month, 'anio' => $mesAnterior->year]) }}"
                   class="btn btn-ghost btn-sm">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span style="font-weight:600; min-width:180px; text-align:center;">{{ ucfirst($nombreMes) }}</span>
                <a href="{{ route('calendario.cliente', ['mes' => $mesSiguiente->month, 'anio' => $mesSiguiente->year]) }}"
                   class="btn btn-ghost btn-sm">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <a href="{{ route('calendario.cliente', ['mes' => now()->month, 'anio' => now()->year]) }}"
               class="btn btn-secondary btn-sm">Hoy</a>
        </div>
        <div class="card-body p-0" style="overflow-x:auto;">
            <table class="cal-table">
                <thead>
                    <tr>
                        @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dia)
                            <th class="cal-th">{{ $dia }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($semanas as $semana)
                    <tr>
                        @foreach($semana as $celda)
                        @php
                            $fechaStr    = $celda['fecha']->format('Y-m-d');
                            $esHoy       = $celda['fecha']->isToday();
                            $delMes      = $celda['delMes'];
                            $entradasDia = $publicaciones[$fechaStr] ?? collect();

                            // Ámbar si queda algo programado, verde si todo está publicado.
                            $claseResumen = $entradasDia->contains('estatus', 'programada')
                                ? 'cal-dia-resumen--programada'
                                : 'cal-dia-resumen--publicada';
                        @endphp
                        <td class="cal-cell {{ !$delMes ? 'cal-cell--fuera' : '' }} {{ $esHoy ? 'cal-cell--hoy' : '' }}">
                            <div class="cal-day-num {{ $esHoy ? 'cal-day-num--hoy' : '' }}">
                                {{ $celda['fecha']->day }}
                            </div>
                            @if($entradasDia->isNotEmpty())
                                <button type="button"
                                    class="cal-dia-resumen {{ $claseResumen }}"
                                    onclick="calAbrirDetalleDia('{{ $fechaStr }}')">
                                    {{ $entradasDia->count() }} foto{{ $entradasDia->count() !== 1 ? 's' : '' }}
                                </button>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Leyenda ──────────────────────────────────────────────────────────── --}}
    <div class="card mt-4">
        <div class="card-body" style="display:flex; gap:1.5rem; flex-wrap:wrap; align-items:center;">
            <div style="display:flex; align-items:center; gap:.4rem;">
                <span style="width:14px; height:14px; background:#d1fae5; border-left:3px solid #10b981; display:inline-block; border-radius:2px;"></span>
                <span class="text-sm">Publicada</span>
            </div>
            <div style="display:flex; align-items:center; gap:.4rem;">
                <span style="width:14px; height:14px; background:#fef3c7; border-left:3px solid #f59e0b; display:inline-block; border-radius:2px;"></span>
                <span class="text-sm">Programada (pendiente de publicar)</span>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Detalle del día — miniaturas y estatus de cada foto (solo lectura)    --}}
{{-- ════════════════════════════════════════════════════════════════════════════ --}}
<div id="cal-modal-detalle" class="cal-modal-overlay" style="display:none"
     onclick="calCerrarModalSiOverlay(event,'cal-modal-detalle')">
    <div class="cal-modal">
        <div class="cal-modal-header">
            <h4 class="cal-modal-title" id="cal-detalle-dia-titulo">Fotos del día</h4>
            <button type="button" onclick="calCerrarModal('cal-modal-detalle')" class="cal-modal-close">&times;</button>
        </div>
        <div class="cal-modal-body">
            <div id="cal-detalle-dia-lista" class="cal-dia-lista"></div>
        </div>
        <div class="cal-modal-footer">
            <button type="button" onclick="calCerrarModal('cal-modal-detalle')" class="btn btn-secondary">Cerrar</button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════ --}}
{{-- LIGHTBOX: foto en grande al hacer clic en una miniatura                     --}}
{{-- ════════════════════════════════════════════════════════════════════════════ --}}
<div id="cal-lightbox" class="cal-lightbox-overlay" style="display:none" onclick="calCerrarLightbox()">
    <button type="button" class="cal-lightbox-close" onclick="calCerrarLightbox()">&times;</button>
    <img id="cal-lightbox-img" src="" alt="Foto en grande">
</div>
@endsection

@section('scripts')
<script>
// ── Respaldo visual cuando una foto no carga ──────────────────────────────────
function calImgFallback(img) {
    img.onerror = null;
    img.src = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">' +
        '<rect width="48" height="48" fill="#f3f4f6"/>' +
        '<path d="M12 32l7-7a2 2 0 012.8 0l4.2 4.2 5-5a2 2 0 012.8 0L36 26" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
        '<circle cx="19" cy="18" r="2.5" fill="#9ca3af"/>' +
        '<rect x="9" y="9" width="30" height="30" rx="3" fill="none" stroke="#9ca3af" stroke-width="2"/>' +
        '</svg>'
    );
    img.classList.add('cal-img-fallback');
}

// ── Modal: Detalle del día ─────────────────────────────────────────────────────
const calEntradasPorDia = @json($entradasDiaJson);

function calAbrirDetalleDia(fecha) {
    const entradas = calEntradasPorDia[fecha] || [];
    const lista    = document.getElementById('cal-detalle-dia-lista');

    document.getElementById('cal-detalle-dia-titulo').textContent =
        'Fotos del ' + new Date(fecha + 'T12:00:00').toLocaleDateString('es-MX', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });

    lista.innerHTML = '';

    entradas.forEach(e => {
        const etiqueta = e.estatus === 'publicada' ? 'Publicada' : 'Programada';
        const fila = document.createElement('div');
        fila.className = 'cal-dia-item cal-dia-item--' + e.estatus;
        fila.innerHTML = `
            <img src="${e.url}" alt="Foto" class="cal-dia-item-img" loading="lazy" onerror="calImgFallback(this)" onclick="calAbrirLightbox('${e.url}')">
            <span class="cal-dia-item-badge cal-dia-item-badge--${e.estatus}">${etiqueta}</span>`;
        lista.appendChild(fila);
    });

    if (entradas.length === 0) {
        lista.innerHTML = '<p style="text-align:center; color:var(--color-text-tertiary); font-size:0.85rem; padding:1rem 0;">No hay fotos en este día.</p>';
    }

    document.getElementById('cal-modal-detalle').style.display = 'flex';
}

function calCerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}
function calCerrarModalSiOverlay(event, id) {
    if (event.target === document.getElementById(id)) calCerrarModal(id);
}

// ── Lightbox: ver la foto en grande ───────────────────────────────────────────
function calAbrirLightbox(url) {
    document.getElementById('cal-lightbox-img').src = url;
    document.getElementById('cal-lightbox').style.display = 'flex';
}
function calCerrarLightbox() {
    document.getElementById('cal-lightbox').style.display = 'none';
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        calCerrarLightbox();
        calCerrarModal('cal-modal-detalle');
    }
});
</script>

<style>
.cal-table {
    width: 100%;
    min-width: 560px;
    border-collapse: collapse;
    table-layout: fixed;
}
.cal-th {
    padding: 0.5rem;
    text-align: center;
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-secondary);
    background: var(--color-surface);
    border-bottom: 1px solid var(--color-border);
}
.cal-cell {
    vertical-align: top;
    padding: 0.35rem;
    height: 74px; /* fija, no min-height: las celdas de tabla la ignoran y antes
                     crecían de forma dispareja según cuántas fotos tuviera el día */
    border: 1px solid var(--color-border);
    background: var(--color-surface);
}
.cal-cell--fuera { background: var(--color-background); }
.cal-cell--hoy   { background: var(--color-brand-light, #eff6ff); }
.cal-day-num {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--color-text-secondary);
    margin-bottom: .25rem;
}
.cal-day-num--hoy { color: var(--color-brand); font-weight: var(--font-weight-bold); }

/* Resumen del día: una etiqueta con el conteo, clic abre el detalle */
.cal-dia-resumen {
    display: block;
    width: 100%;
    border: none;
    font-size: .7rem;
    font-weight: 700;
    border-radius: 6px;
    padding: .3rem .4rem;
    cursor: pointer;
    text-align: left;
    transition: filter .12s;
}
.cal-dia-resumen:hover { filter: brightness(.96); }
.cal-dia-resumen--programada { background: #fef3c7; color: #92400e; border-left: 3px solid #f59e0b; }
.cal-dia-resumen--publicada  { background: #d1fae5; color: #065f46; border-left: 3px solid #10b981; }

/* ── Modal de detalle del día ──────────────────────────────────────────────── */
.cal-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.cal-modal {
    background: var(--color-surface);
    border-radius: var(--radius-lg, 12px);
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    width: 100%;
    max-width: 420px;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
}
.cal-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--color-border);
    flex-shrink: 0;
}
.cal-modal-title { font-size: 1rem; font-weight: 700; margin: 0; color: var(--color-text-primary); text-transform: capitalize; }
.cal-modal-close { background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--color-text-tertiary); line-height: 1; padding: 0 4px; }
.cal-modal-close:hover { color: var(--color-text-primary); }
.cal-modal-body { padding: 1rem 1.25rem; overflow-y: auto; }
.cal-modal-footer { display: flex; justify-content: flex-end; padding: 1rem 1.25rem; border-top: 1px solid var(--color-border); flex-shrink: 0; }

/* Filas dentro del modal */
.cal-dia-lista { display: flex; flex-direction: column; gap: .6rem; }
.cal-dia-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .5rem;
    border: 1px solid var(--color-border);
    border-radius: 8px;
}
.cal-dia-item-img { width: 64px; height: 64px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: var(--color-background-secondary); cursor: pointer; }
.cal-dia-item-img.cal-img-fallback { object-fit: contain; padding: 10%; opacity: .55; cursor: default; }
.cal-dia-item-badge { font-size: .68rem; font-weight: 700; border-radius: 999px; padding: 3px 10px; }
.cal-dia-item-badge--programada { background: #fef3c7; color: #92400e; }
.cal-dia-item-badge--publicada  { background: #d1fae5; color: #065f46; }

/* ── Lightbox: foto en grande ─────────────────────────────────────────────── */
.cal-lightbox-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.85);
    z-index: 10050;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    cursor: zoom-out;
}
.cal-lightbox-overlay img {
    max-width: 100%;
    max-height: 100%;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0,0,0,.5);
    cursor: default;
}
.cal-lightbox-close {
    position: fixed;
    top: 1.25rem;
    right: 1.5rem;
    background: rgba(255,255,255,.15);
    border: none;
    color: #fff;
    font-size: 1.75rem;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    cursor: pointer;
    line-height: 1;
}
.cal-lightbox-close:hover { background: rgba(255,255,255,.3); }

@media (max-width: 480px) {
    .cal-cell { height: 62px; padding: .25rem; }
    .cal-dia-resumen { font-size: .62rem; padding: .22rem .3rem; }
}
</style>
@endsection
