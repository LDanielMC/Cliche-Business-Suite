@extends('layouts.modern')

@section('title', 'Calendario – ' . $cliente->nombre_negocio)

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('calendario.index') }}" class="breadcrumb-link">Calendario</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">{{ $cliente->nombre_negocio }}</span>
        </div>
    </div>
@endsection

@section('styles')
<style>
/* ── Variables ──────────────────────────────────────────────────────────────── */
:root {
    --ca-brand:        var(--color-brand, #667eea);
    --ca-brand-light:  var(--color-brand-light, #eff0fd);
    --ca-surface:      var(--color-surface, #ffffff);
    --ca-border:       var(--color-border, #e5e7eb);
    --ca-text:         var(--color-text-primary, #111827);
    --ca-muted:        var(--color-text-secondary, #6b7280);
    --ca-bg:           var(--color-background, #f9fafb);
    --ca-success:      #10b981;
    --ca-success-bg:   #d1fae5;
    --ca-warning:      #f59e0b;
    --ca-warning-bg:   #fef3c7;
    --ca-danger:       #ef4444;
    --ca-danger-bg:    #fee2e2;
    --ca-teal:         #0d9488;
    --ca-teal-bg:      #f0fdfa;
    --ca-sidebar-w:    260px;
    --ca-panel-w:      280px;
}

/* ── Layout de tres columnas ────────────────────────────────────────────────── */
.ca-layout {
    display: grid;
    grid-template-columns: var(--ca-sidebar-w) 1fr var(--ca-panel-w);
    gap: 1.25rem;
    align-items: start;
}
/* En pantallas medianas el panel de fotos baja a ocupar todo el ancho; en
   móvil todo se apila con el calendario primero. Nada se oculta. */
@media (max-width: 1280px) {
    .ca-layout { grid-template-columns: var(--ca-sidebar-w) 1fr; }
    .ca-panel-col { grid-column: 1 / -1; position: static; }
    .ca-panel-body { max-height: none; }
}
@media (max-width: 900px) {
    .ca-layout { grid-template-columns: 1fr; }
    .ca-main-col { order: -1; }
    .ca-sidebar-col, .ca-panel-col { position: static; }
}

/* ── Sidebar izquierdo ──────────────────────────────────────────────────────── */
.ca-sidebar-col {
    position: sticky;
    top: 1.25rem;
}

/* Tarjeta de cliente */
.ca-client-card {
    background: var(--ca-surface);
    border: 1px solid var(--ca-border);
    border-radius: 12px;
    padding: 1.1rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.ca-client-name {
    font-size: 1rem;
    font-weight: 700;
    color: var(--ca-text);
    margin-bottom: 0.35rem;
    line-height: 1.3;
}
.ca-client-meta {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    margin-top: 0.6rem;
}
.ca-client-meta-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.78rem;
    color: var(--ca-muted);
}
.ca-client-meta-val {
    font-weight: 600;
    color: var(--ca-text);
    font-size: 0.8rem;
}

/* Badges de estatus de usuario */
.badge-estatus {
    display: inline-block;
    padding: 2px 9px;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: capitalize;
}
.badge-estatus-activo        { background: var(--ca-success-bg); color: #065f46; }
.badge-estatus-suspendido    { background: #fed7aa; color: #7c2d12; }
.badge-estatus-inactivo      { background: #f3f4f6; color: #374151; }
.badge-estatus-dado_de_baja  { background: var(--ca-danger-bg); color: #7f1d1d; }

/* ── Sección de períodos en el sidebar ──────────────────────────────────────── */
.ca-periodos-section {
    background: var(--ca-surface);
    border: 1px solid var(--ca-border);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.ca-periodos-header {
    padding: 0.7rem 1rem;
    background: var(--ca-bg);
    border-bottom: 1px solid var(--ca-border);
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--ca-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.ca-periodo-item {
    display: block;
    padding: 0.7rem 1rem;
    border-bottom: 1px solid var(--ca-border);
    text-decoration: none;
    transition: background 0.13s;
    border-left: 3px solid transparent;
}
.ca-periodo-item:last-child { border-bottom: none; }
.ca-periodo-item:hover { background: var(--ca-bg); }
.ca-periodo-item.activo {
    border-left-color: var(--ca-brand);
    background: var(--ca-brand-light);
}
.ca-periodo-rango {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ca-text);
    margin-bottom: 0.3rem;
}
.ca-periodo-badges {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
}

/* Badges de estatus de renovación */
.badge-renovacion {
    display: inline-block;
    padding: 1px 7px;
    border-radius: 999px;
    font-size: 0.67rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.badge-vigente           { background: var(--ca-success-bg); color: #065f46; }
.badge-por_vencer        { background: var(--ca-warning-bg); color: #92400e; }
.badge-vencido           { background: var(--ca-danger-bg); color: #7f1d1d; }
.badge-en_revision       { background: #dbeafe; color: #1e40af; }
.badge-pago_validado     { background: #d1fae5; color: #065f46; }
.badge-factura_pendiente { background: #fce7f3; color: #831843; }
.badge-pago_rechazado    { background: var(--ca-danger-bg); color: #7f1d1d; }

.badge-fotos {
    background: var(--ca-bg);
    border: 1px solid var(--ca-border);
    color: var(--ca-muted);
    padding: 1px 6px;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 600;
}

/* ── Columna principal ──────────────────────────────────────────────────────── */
.ca-main-col {}

/* Avisos de vigencia (reemplazan los alert-error genéricos): acento de color
   en vez de bloque sólido, para que no compitan visualmente con errores reales. */
.ca-notices { display: flex; flex-direction: column; gap: 0.6rem; margin-bottom: 1rem; }
.ca-notice {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    padding: 0.7rem 0.9rem;
    border-radius: 10px;
    border: 1px solid transparent;
    font-size: 0.82rem;
    line-height: 1.5;
}
.ca-notice-icon { flex-shrink: 0; margin-top: 1px; }
.ca-notice-body { flex: 1; min-width: 0; }
.ca-notice--vencida  { background: #fff7ed; border-color: #fed7aa; color: #7c2d12; }
.ca-notice--vencida  .ca-notice-icon { color: #ea580c; }
.ca-notice--urgente  { background: var(--ca-danger-bg); border-color: #fecaca; color: #7f1d1d; }
.ca-notice--urgente  .ca-notice-icon { color: var(--ca-danger); }

/* Bloque info de período */
.ca-periodo-info-bar {
    background: var(--ca-surface);
    border: 1px solid var(--ca-border);
    border-radius: 10px;
    padding: 0.85rem 1.1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.ca-periodo-info-icon {
    flex-shrink: 0;
    color: var(--ca-brand);
    background: var(--ca-brand-light);
    padding: 7px;
    border-radius: 8px;
    box-sizing: content-box;
}
.ca-periodo-info-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--ca-text);
    min-width: 0;
}
.ca-periodo-info-sub {
    font-size: 0.78rem;
    color: var(--ca-muted);
    margin-top: 2px;
}

/* Navegación de mes */
.ca-cal-nav {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.75rem;
}
.ca-cal-nav-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--ca-text);
    min-width: 170px;
    text-align: center;
}

/* ── Tabla del calendario ───────────────────────────────────────────────────── */
/* La tabla vive en un contenedor con scroll horizontal: en pantallas chicas se
   desliza de lado en vez de comprimir las columnas hasta romperse. */
.ca-main-col .card-body.p-0 { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.ca-table {
    width: 100%;
    min-width: 540px;
    border-collapse: collapse;
    table-layout: fixed;
}
.ca-th {
    padding: 0.45rem 0.3rem;
    text-align: center;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--ca-muted);
    background: var(--ca-bg);
    border-bottom: 1px solid var(--ca-border);
    letter-spacing: 0.02em;
    text-transform: uppercase;
}
.ca-cell {
    vertical-align: top;
    padding: 0.3rem 0.25rem;
    /* height (no min-height, que las celdas de tabla ignoran) fija la misma
       altura para todas las filas, tengan fotos o no */
    height: 88px;
    border: 1px solid var(--ca-border);
    position: relative;
    background: var(--ca-surface);
}
.ca-cell--fuera   { background: var(--ca-bg); }
.ca-cell--hoy     { background: #eff6ff; }
.ca-cell--periodo { background: var(--ca-teal-bg); }
.ca-cell--hoy.ca-cell--periodo { background: #ccfbf1; }
/* Día ya pasado: no se puede agendar aquí (el mínimo agendable es siempre hoy) */
.ca-cell--pasado { background: repeating-linear-gradient(135deg, var(--ca-bg) 0 8px, var(--ca-surface) 8px 16px); opacity: 0.6; }
.ca-cell--pasado .ca-day-num { color: var(--ca-muted); text-decoration: line-through; }

.ca-day-num {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--ca-muted);
    margin-bottom: 0.2rem;
}
.ca-day-num--hoy {
    color: var(--ca-brand);
    font-weight: 800;
}

/* Indicador sutil de que el día está dentro del período */
.ca-cell--periodo .ca-day-num {
    color: var(--ca-teal);
}

/* Resumen del día: una sola etiqueta con el conteo de fotos. El clic abre la
   ventana con la lista completa (miniaturas, estatus y acciones). */
.ca-dia-resumen {
    display: block;
    width: 100%;
    border: none;
    font-size: 0.66rem;
    font-weight: 700;
    border-radius: 5px;
    padding: 0.22rem 0.3rem;
    margin-bottom: 0.18rem;
    cursor: pointer;
    text-align: left;
    transition: filter .12s;
}
.ca-dia-resumen:hover { filter: brightness(.95); }
.ca-dia-resumen--programada { background: var(--ca-warning-bg); color: #92400e; border-left: 3px solid var(--ca-warning); }
.ca-dia-resumen--publicada  { background: var(--ca-success-bg); color: #065f46; border-left: 3px solid var(--ca-success); }
.ca-dia-resumen--cancelada  { background: #f3f4f6; color: #6b7280; border-left: 3px solid #9ca3af; opacity: 0.75; }

/* Filas dentro de la ventana "Fotos del día" */
.ca-dia-lista { display: flex; flex-direction: column; gap: 0.6rem; }
.ca-dia-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem;
    border: 1px solid var(--ca-border);
    border-radius: 8px;
    background: var(--ca-surface);
}
.ca-dia-item--cancelada { opacity: 0.6; }
.ca-dia-item-img {
    width: 72px;
    height: 72px;
    border-radius: 6px;
    object-fit: cover;
    flex-shrink: 0;
    background: var(--ca-bg);
    cursor: pointer;
}
.ca-dia-item-img.ca-img-fallback { object-fit: contain; padding: 10%; opacity: 0.55; cursor: default; }

/* ── Lightbox: foto en grande ─────────────────────────────────────────────── */
.ca-lightbox-overlay {
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
.ca-lightbox-overlay img {
    max-width: 100%;
    max-height: 100%;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0,0,0,.5);
    cursor: default;
}
.ca-lightbox-close {
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
.ca-lightbox-close:hover { background: rgba(255,255,255,.3); }
.ca-dia-item-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    align-items: flex-start;
}
.ca-dia-item-badge {
    font-size: 0.65rem;
    font-weight: 700;
    border-radius: 999px;
    padding: 2px 9px;
    text-transform: capitalize;
}
.ca-dia-item-badge--programada { background: var(--ca-warning-bg); color: #92400e; }
.ca-dia-item-badge--publicada  { background: var(--ca-success-bg); color: #065f46; }
.ca-dia-item-badge--cancelada  { background: #f3f4f6; color: #6b7280; }
.ca-dia-item-acciones { display: flex; gap: 0.4rem; flex-wrap: wrap; }
.ca-dia-item-nota { font-size: 0.75rem; color: var(--ca-muted); }

/* Botón "+" agregar */
.ca-btn-agregar {
    position: absolute;
    bottom: 3px;
    right: 3px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: var(--ca-brand);
    color: #fff;
    font-size: 14px;
    line-height: 1;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity .15s;
}
.ca-cell:hover .ca-btn-agregar { opacity: 1; }
/* En pantallas táctiles no hay hover: el "+" se muestra siempre */
@media (hover: none) {
    .ca-btn-agregar { opacity: 1; }
}

/* ── Ajustes para celular ───────────────────────────────────────────────────── */
@media (max-width: 600px) {
    .ca-cell { height: 68px; padding: 0.2rem; }
    .ca-day-num { font-size: 0.68rem; }
    .ca-th { font-size: 0.62rem; padding: 0.35rem 0.15rem; }
    .ca-dia-resumen { font-size: 0.58rem; padding: 0.18rem 0.22rem; }
    .ca-cal-nav-title { min-width: 0; font-size: 0.9rem; }
    .ca-btn-agregar { width: 22px; height: 22px; font-size: 16px; }
    .ca-panel-body { grid-template-columns: repeat(auto-fill, minmax(68px, 1fr)); }
    .cal-modal { margin: 0.5rem; }
}

/* ── Barra de estadísticas del período ──────────────────────────────────────── */
.ca-stats-bar {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 0.85rem 1.1rem;
    background: var(--ca-surface);
    border: 1px solid var(--ca-border);
    border-radius: 10px;
    margin-top: 0.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.ca-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    min-width: 84px;
}
.ca-stat-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    margin-bottom: 2px;
}
.ca-stat-num {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--ca-text);
    line-height: 1;
}
.ca-stat-label {
    font-size: 0.68rem;
    color: var(--ca-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}
.ca-stat-sep {
    width: 1px;
    height: 36px;
    background: var(--ca-border);
}
.ca-progress-wrap {
    flex: 1;
    min-width: 160px;
}
.ca-progress-label {
    display: flex;
    justify-content: space-between;
    font-size: 0.72rem;
    color: var(--ca-muted);
    margin-bottom: 4px;
    font-weight: 600;
}
.ca-progress-bar-bg {
    height: 8px;
    background: var(--ca-bg);
    border: 1px solid var(--ca-border);
    border-radius: 999px;
    overflow: hidden;
}
.ca-progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--ca-teal) 0%, var(--ca-success) 100%);
    border-radius: 999px;
    transition: width 0.4s ease;
    min-width: 2px;
}

/* ── Panel derecho: fotos disponibles ───────────────────────────────────────── */
.ca-panel-col {
    position: sticky;
    top: 1.25rem;
}
.ca-panel-body {
    max-height: 78vh;
    overflow-y: auto;
    padding: 0.75rem;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(84px, 1fr));
    gap: 0.6rem;
    align-content: start;
}
.ca-panel-foto {
    cursor: pointer;
    transition: transform .12s;
}
.ca-panel-foto:hover { transform: translateY(-2px); }
.ca-panel-foto-media {
    position: relative;
    border: 2px solid var(--ca-border);
    border-radius: 8px;
    overflow: hidden;
    aspect-ratio: 1;
    background: var(--ca-bg);
    transition: border-color .15s;
}
.ca-panel-foto:hover .ca-panel-foto-media { border-color: var(--ca-brand); }
.ca-panel-foto--seleccionada .ca-panel-foto-media {
    border-color: var(--ca-brand);
    box-shadow: 0 0 0 3px rgba(102,126,234,.25);
}
.ca-panel-foto-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.ca-panel-foto-img.ca-img-fallback { object-fit: contain; padding: 14%; opacity: 0.55; }
.ca-panel-foto-tag {
    position: absolute;
    top: 4px;
    left: 4px;
    font-size: 0.56rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 999px;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    box-shadow: 0 1px 2px rgba(0,0,0,.25);
}
.ca-panel-foto-tag--aprobada { background: var(--ca-success); }
.ca-panel-foto-tag--vencida  { background: var(--ca-danger); }

/* ── Modales ────────────────────────────────────────────────────────────────── */
.cal-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cal-modal {
    background: var(--ca-surface);
    border-radius: var(--radius-lg, 12px);
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    width: 100%;
    max-width: 480px;
    margin: 1rem;
}
.cal-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--ca-border);
}
.cal-modal-title {
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
    color: var(--ca-text);
}
.cal-modal-close {
    background: none;
    border: none;
    font-size: 1.4rem;
    cursor: pointer;
    color: var(--ca-muted);
    line-height: 1;
    padding: 0 4px;
}
.cal-modal-close:hover { color: var(--ca-text); }
.cal-modal-body   { padding: 1.25rem; }
.cal-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--ca-border);
}

/* ── Cuadrícula de fotos dentro del modal "Programar fotografía" ─────────────── */
.ca-modal-foto-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
    gap: 0.5rem;
    max-height: 260px;
    overflow-y: auto;
    padding: 0.5rem;
    border: 1px solid var(--ca-border);
    border-radius: 8px;
    background: var(--ca-bg);
}
.ca-modal-foto {
    position: relative;
    cursor: pointer;
    border: 2px solid transparent;
    border-radius: 6px;
    overflow: hidden;
    aspect-ratio: 1;
    background: var(--ca-surface);
    transition: border-color .15s, transform .1s;
}
.ca-modal-foto:hover { transform: scale(1.04); }
.ca-modal-foto img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ca-modal-foto img.ca-img-fallback { object-fit: contain; padding: 14%; opacity: 0.55; }
.ca-modal-foto--seleccionada {
    border-color: var(--ca-brand);
    box-shadow: 0 0 0 2px rgba(102,126,234,.35);
}
.ca-modal-foto--seleccionada::after {
    content: '✓';
    position: absolute;
    top: 3px;
    right: 3px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--ca-brand);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.ca-modal-foto-badge {
    position: absolute;
    bottom: 2px;
    left: 2px;
    right: 2px;
    text-align: center;
    font-size: 0.55rem;
    font-weight: 700;
    border-radius: 3px;
    padding: 1px 2px;
    color: #fff;
}
.ca-modal-foto-badge--vencida { background: #ef4444; }
.ca-modal-foto-vacio {
    grid-column: 1 / -1;
    text-align: center;
    padding: 1rem 0.5rem;
    color: var(--ca-muted);
    font-size: 0.8rem;
}
</style>
@endsection

@section('content')
@php
    use Carbon\Carbon;

    $mesAnterior  = Carbon::create($anio, $mes, 1)->subMonth();
    $mesSiguiente = Carbon::create($anio, $mes, 1)->addMonth();
    $nombreMes    = Carbon::create($anio, $mes, 1)->translatedFormat('F Y');

    $periodoId    = $periodoActual?->id;

    // Build prev/next URLs preserving periodo_id
    $urlPrev = route('calendario.cliente-view', array_filter([
        'cliente'    => $cliente->id,
        'periodo_id' => $periodoId,
        'mes'        => $mesAnterior->month,
        'anio'       => $mesAnterior->year,
    ]));
    $urlNext = route('calendario.cliente-view', array_filter([
        'cliente'    => $cliente->id,
        'periodo_id' => $periodoId,
        'mes'        => $mesSiguiente->month,
        'anio'       => $mesSiguiente->year,
    ]));
    $urlHoy = route('calendario.cliente-view', array_filter([
        'cliente'    => $cliente->id,
        'periodo_id' => $periodoId,
        'mes'        => now()->month,
        'anio'       => now()->year,
    ]));

    // Totales del período
    $totalPublicadas   = $totalesPeriodo['publicada']  ?? 0;
    $totalProgramadas  = $totalesPeriodo['programada'] ?? 0;
    $usadas            = $totalPublicadas + $totalProgramadas;
    $disponibles       = max(0, $cliente->cantidad_fotos - $usadas);
    $porcentaje        = $cliente->cantidad_fotos > 0
                            ? min(100, round(($usadas / $cliente->cantidad_fotos) * 100))
                            : 0;

    // Días dentro del período actual
    $periodoInicio     = $periodoActual?->fecha_inicio;
    $periodoFin        = $periodoActual?->fecha_vencimiento;
    $diasTotales       = $periodoInicio && $periodoFin
                            ? $periodoInicio->diffInDays($periodoFin) + 1
                            : null;

    // Badge estatus renovación
    $badgeRenovacion = [
        'vigente'           => 'badge-vigente',
        'por_vencer'        => 'badge-por_vencer',
        'vencido'           => 'badge-vencido',
        'en_revision'       => 'badge-en_revision',
        'pago_validado'     => 'badge-pago_validado',
        'factura_pendiente' => 'badge-factura_pendiente',
        'pago_rechazado'    => 'badge-pago_rechazado',
    ];

    $formatoRango = fn($inicio, $fin) =>
        $inicio?->translatedFormat('d M') . ' – ' . $fin?->translatedFormat('d M Y');

    // Fotos aprobadas cuyo paquete ya venció (fecha_vencimiento_periodo pasada)
    // y que nunca se agendaron a tiempo — se muestran separadas del resto.
    $hoy = \Carbon\Carbon::today();

    // Días que faltan para que venza el período actual (negativo si ya venció)
    $diasRestantesPeriodo = $periodoFin ? $hoy->diffInDays($periodoFin, false) + 1 : null;

    $fotosVencidas = $fotosDisponibles->filter(
        fn ($f) => $f->paquete->fecha_vencimiento_periodo?->lt($hoy)
    );

    // De las que aún no vencen: ¿ya no alcanza 1 foto/día para caber antes de
    // su fecha límite más próxima? Mismo cálculo que el tope dinámico del
    // calendario — si da más de 1, es urgente aunque técnicamente "haya tiempo".
    $fotosNoVencidas = $fotosDisponibles->diff($fotosVencidas);
    $esUrgente = false;
    if ($fotosNoVencidas->isNotEmpty()) {
        $finMasCercano = $fotosNoVencidas->map(fn ($f) => $f->paquete->fecha_vencimiento_periodo)->min();
        $diasRestantes = $hoy->diffInDays($finMasCercano) + 1;
        $esUrgente     = ceil($fotosNoVencidas->count() / max(1, $diasRestantes)) > 1;
    }
@endphp

<div class="ca-layout">

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- SIDEBAR IZQUIERDO                                                        --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    <div class="ca-sidebar-col">

        {{-- Tarjeta de cliente --}}
        <div class="ca-client-card">
            <div class="ca-client-name">{{ $cliente->nombre_negocio }}</div>

            @if($cliente->user)
                <span class="badge-estatus badge-estatus-{{ $cliente->user->estatus }}">
                    {{ str_replace('_', ' ', $cliente->user->estatus) }}
                </span>
            @endif

            <div class="ca-client-meta">
                <div class="ca-client-meta-row">
                    <span>Fotos / mes</span>
                    <span class="ca-client-meta-val">{{ $cliente->cantidad_fotos }}</span>
                </div>
                <div class="ca-client-meta-row">
                    <span>Precio mensual</span>
                    <span class="ca-client-meta-val">
                        ${{ number_format($cliente->precio_mensual, 2) }}
                    </span>
                </div>
                @if($cliente->giro)
                    <div class="ca-client-meta-row">
                        <span>Giro</span>
                        <span class="ca-client-meta-val">{{ $cliente->giro }}</span>
                    </div>
                @endif
            </div>

            <div style="margin-top:0.75rem; padding-top:0.65rem; border-top:1px solid var(--ca-border);">
                <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;">
                    Ver perfil del cliente
                </a>
            </div>
        </div>

        {{-- Lista de períodos --}}
        <div class="ca-periodos-section">
            <div class="ca-periodos-header">Períodos de Renovación</div>

            @forelse($periodos as $periodo)
                @php
                    $esPeriodoActivo = $periodoActual && $periodo->id === $periodoActual->id;
                    $conteo = $conteosPorPeriodo[$periodo->id] ?? 0;
                    $urlPeriodo = route('calendario.cliente-view', [
                        'cliente'    => $cliente->id,
                        'periodo_id' => $periodo->id,
                        'mes'        => $periodo->fecha_inicio->month,
                        'anio'       => $periodo->fecha_inicio->year,
                    ]);
                @endphp
                <a href="{{ $urlPeriodo }}" class="ca-periodo-item {{ $esPeriodoActivo ? 'activo' : '' }}">
                    <div class="ca-periodo-rango">
                        {{ $periodo->fecha_inicio->translatedFormat('d M') }}
                        –
                        {{ $periodo->fecha_vencimiento->translatedFormat('d M Y') }}
                    </div>
                    <div class="ca-periodo-badges">
                        <span class="badge-renovacion {{ $badgeRenovacion[$periodo->estatus] ?? '' }}">
                            {{ str_replace('_', ' ', $periodo->estatus) }}
                        </span>
                        @if($conteo > 0)
                            <span class="badge-fotos">{{ $conteo }} foto{{ $conteo !== 1 ? 's' : '' }}</span>
                        @endif
                    </div>
                </a>
            @empty
                <div style="padding:1rem; font-size:0.8rem; color:var(--ca-muted); text-align:center;">
                    Sin períodos registrados.
                </div>
            @endforelse
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- COLUMNA PRINCIPAL: CALENDARIO                                            --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    <div class="ca-main-col">

        {{-- Mensajes flash --}}
        @if(session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error mb-4">{{ session('error') }}</div>
        @endif

        @if($fotosVencidas->isNotEmpty() || $esUrgente)
            <div class="ca-notices">
                @if($fotosVencidas->isNotEmpty())
                    <div class="ca-notice ca-notice--vencida">
                        <svg class="ca-notice-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="ca-notice-body">
                            <strong>{{ $fotosVencidas->count() }}</strong> fotografía{{ $fotosVencidas->count() !== 1 ? 's' : '' }}
                            aprobada{{ $fotosVencidas->count() !== 1 ? 's' : '' }} quedaron sin agendar antes de vencer su vigencia
                            (marcadas <span class="badge badge-danger" style="font-size:0.62rem;">Vencida</span> en el panel de la derecha).
                            @if($cliente->user && $cliente->user->estatus === \App\Models\User::ESTATUS_ACTIVO)
                                Puedes agendarlas desde hoy, sin límite por día — no hace falta esperar a que renueve.
                            @else
                                Su cuenta está suspendida: se desbloquean en cuanto se reactive al validar su pago.
                            @endif
                        </div>
                    </div>
                @endif

                @if($esUrgente)
                    <div class="ca-notice ca-notice--urgente">
                        <svg class="ca-notice-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                        <div class="ca-notice-body">
                            Todavía no vence, pero con <strong>{{ $fotosNoVencidas->count() }}</strong> foto{{ $fotosNoVencidas->count() !== 1 ? 's' : '' }}
                            pendiente{{ $fotosNoVencidas->count() !== 1 ? 's' : '' }} y solo <strong>{{ $diasRestantes }}</strong> día{{ $diasRestantes !== 1 ? 's' : '' }}
                            de vigencia, ya no alcanza con 1 foto/día. Arma el calendario hoy — se puede colocar más de una el mismo día.
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Barra de info del período --}}
        @if($periodoActual)
            <div class="ca-periodo-info-bar">
                <svg class="ca-periodo-info-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div style="flex:1; min-width:0;">
                    <div class="ca-periodo-info-title">
                        {{ $periodoActual->fecha_inicio->translatedFormat('d M') }}
                        – {{ $periodoActual->fecha_vencimiento->translatedFormat('d M Y') }}
                    </div>
                    <div class="ca-periodo-info-sub">
                        Período de {{ $diasTotales }} días
                        @if($diasRestantesPeriodo !== null)
                            &middot;
                            @if($diasRestantesPeriodo > 0)
                                {{ $diasRestantesPeriodo }} día{{ $diasRestantesPeriodo !== 1 ? 's' : '' }} restante{{ $diasRestantesPeriodo !== 1 ? 's' : '' }}
                            @else
                                venció hace {{ abs($diasRestantesPeriodo) }} día{{ abs($diasRestantesPeriodo) !== 1 ? 's' : '' }}
                            @endif
                        @endif
                    </div>
                </div>
                <span class="badge-renovacion {{ $badgeRenovacion[$periodoActual->estatus] ?? '' }}" style="font-size:0.75rem; padding:4px 11px;">
                    {{ str_replace('_', ' ', $periodoActual->estatus) }}
                </span>
            </div>
        @endif

        {{-- Tarjeta del calendario --}}
        <div class="card">
            <div class="card-header">
                {{-- Navegación de mes --}}
                <div class="ca-cal-nav">
                    <a href="{{ $urlPrev }}" class="btn btn-ghost btn-sm" title="Mes anterior">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <span class="ca-cal-nav-title">{{ ucfirst($nombreMes) }}</span>
                    <a href="{{ $urlNext }}" class="btn btn-ghost btn-sm" title="Mes siguiente">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                <a href="{{ $urlHoy }}" class="btn btn-secondary btn-sm">Hoy</a>
            </div>

            <div class="card-body p-0">
                <table class="ca-table">
                    <thead>
                        <tr>
                            @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $diaLabel)
                                <th class="ca-th">{{ $diaLabel }}</th>
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
                                $esPasado    = $celda['fecha']->lt($hoy);
                                $delMes      = $celda['delMes'];
                                $enPeriodo   = $periodoInicio && $periodoFin
                                                && $celda['fecha']->between($periodoInicio, $periodoFin);
                                $entradasDia = $publicaciones[$fechaStr] ?? collect();

                                $cellClass = '';
                                if (!$delMes)   $cellClass .= ' ca-cell--fuera';
                                if ($esHoy)     $cellClass .= ' ca-cell--hoy';
                                if ($enPeriodo && $delMes) $cellClass .= ' ca-cell--periodo';
                                if ($esPasado && $delMes)  $cellClass .= ' ca-cell--pasado';
                            @endphp
                            <td class="ca-cell {{ $cellClass }}">
                                <div class="ca-day-num {{ $esHoy ? 'ca-day-num--hoy' : '' }}">
                                    {{ $celda['fecha']->day }}
                                </div>

                                @if($entradasDia->isNotEmpty())
                                    @php
                                        // La etiqueta toma el color del estatus "más pendiente":
                                        // ámbar si queda algo programado, verde si todo está
                                        // publicado, gris si solo hay canceladas.
                                        $activasDia = $entradasDia->whereIn('estatus', ['programada', 'publicada']);
                                        $claseResumen = $entradasDia->contains('estatus', 'programada')
                                            ? 'ca-dia-resumen--programada'
                                            : ($activasDia->isNotEmpty() ? 'ca-dia-resumen--publicada' : 'ca-dia-resumen--cancelada');
                                        $totalDia = $activasDia->count() ?: $entradasDia->count();
                                    @endphp
                                    <button type="button"
                                        class="ca-dia-resumen {{ $claseResumen }}"
                                        onclick="caAbrirDetalleDia('{{ $fechaStr }}')"
                                        title="Ver las fotos de este día">
                                        {{ $totalDia }} foto{{ $totalDia !== 1 ? 's' : '' }}
                                    </button>
                                @endif

                                {{-- Botón "+" para colocar foto (solo días del mes, no pasados, con fotos disponibles) --}}
                                @if($delMes && !$esPasado && $fotosDisponibles->isNotEmpty())
                                    <button type="button"
                                        class="ca-btn-agregar"
                                        title="Programar foto en este día"
                                        onclick="caAbrirModalColocar('{{ $fechaStr }}')">+</button>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Barra de estadísticas --}}
        <div class="ca-stats-bar">
            <div class="ca-stat">
                <span class="ca-stat-icon" style="background:var(--ca-success-bg); color:var(--ca-success);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </span>
                <span class="ca-stat-num">{{ $totalPublicadas }}</span>
                <span class="ca-stat-label">Publicadas</span>
            </div>
            <div class="ca-stat-sep"></div>
            <div class="ca-stat">
                <span class="ca-stat-icon" style="background:var(--ca-warning-bg); color:var(--ca-warning);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <span class="ca-stat-num">{{ $totalProgramadas }}</span>
                <span class="ca-stat-label">Programadas</span>
            </div>
            <div class="ca-stat-sep"></div>
            <div class="ca-stat">
                <span class="ca-stat-icon" style="background:var(--ca-teal-bg); color:var(--ca-teal);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
                <span class="ca-stat-num">{{ $disponibles }}</span>
                <span class="ca-stat-label">Disponibles</span>
            </div>
            <div class="ca-stat-sep"></div>
            <div class="ca-progress-wrap">
                <div class="ca-progress-label">
                    <span>Uso del período</span>
                    <span>{{ $usadas }} / {{ $cliente->cantidad_fotos }} fotos</span>
                </div>
                <div class="ca-progress-bar-bg">
                    <div class="ca-progress-bar-fill" style="width: {{ $porcentaje }}%"></div>
                </div>
            </div>
        </div>

    </div>{{-- /ca-main-col --}}

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- PANEL DERECHO: FOTOS DISPONIBLES                                         --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    <div class="ca-panel-col">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" style="font-size:0.85rem;">Fotos disponibles</h3>
                @if($fotosDisponibles->isNotEmpty())
                    <span class="badge badge-primary" style="font-size:0.7rem;">{{ $fotosDisponibles->count() }}</span>
                @endif
            </div>
            <div class="ca-panel-body">
                @forelse($fotosDisponibles as $foto)
                    @php $vencida = $foto->paquete->fecha_vencimiento_periodo?->lt($hoy); @endphp
                    <div class="ca-panel-foto"
                         id="foto-card-{{ $foto->id }}"
                         data-foto-id="{{ $foto->id }}"
                         data-cliente-id="{{ $cliente->id }}"
                         data-inicio="{{ $foto->paquete->fecha_inicio_periodo?->format('Y-m-d') }}"
                         data-fin="{{ $foto->paquete->fecha_vencimiento_periodo?->format('Y-m-d') }}"
                         data-vencida="{{ $vencida ? 1 : 0 }}"
                         onclick="caSeleccionarFoto(this)"
                         title="Período: {{ $foto->paquete->fecha_inicio_periodo?->format('d/m/Y') }} – {{ $foto->paquete->fecha_vencimiento_periodo?->format('d/m/Y') }}{{ $vencida ? ' (vencido sin agendarse)' : '' }}">
                        <div class="ca-panel-foto-media">
                            <img src="{{ $foto->url }}" alt="Foto aprobada" class="ca-panel-foto-img" loading="lazy" onerror="caImgFallback(this)">
                            <span class="ca-panel-foto-tag {{ $vencida ? 'ca-panel-foto-tag--vencida' : 'ca-panel-foto-tag--aprobada' }}">{{ $vencida ? 'Vencida' : 'Aprobada' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="empty-state py-4" style="padding:1.5rem 0.5rem; text-align:center;">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--ca-muted); margin:0 auto 0.5rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="empty-state-description" style="font-size:0.8rem;">No hay fotografías aprobadas pendientes de programar para este cliente.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>{{-- /ca-layout --}}

{{-- ════════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Colocar foto en un día                                               --}}
{{-- ════════════════════════════════════════════════════════════════════════════ --}}
<div id="ca-modal-colocar" class="cal-modal-overlay" style="display:none"
     onclick="caCerrarModalSiOverlay(event,'ca-modal-colocar')">
    <div class="cal-modal">
        <div class="cal-modal-header">
            <h4 class="cal-modal-title">Programar fotografía</h4>
            <button type="button" onclick="caCerrarModal('ca-modal-colocar')" class="cal-modal-close">&times;</button>
        </div>
        <form id="ca-form-colocar" method="POST" action="{{ route('calendario.colocar') }}">
            @csrf
            <input type="hidden" name="fecha"      id="ca-colocar-fecha">
            <div id="ca-colocar-foto-ids"></div>
            <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
            <div class="cal-modal-body">
                <div class="form-group">
                    <label class="form-label">Fecha seleccionada</label>
                    <p id="ca-colocar-fecha-display" class="text-sm font-medium"></p>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        Elige una o varias fotografías
                        <span id="ca-colocar-contador" class="badge badge-primary" style="display:none;"></span>
                    </label>
                    <div class="ca-modal-foto-grid" id="ca-modal-foto-grid">
                        @forelse($fotosDisponibles as $foto)
                            @php $fotoVencida = $foto->paquete->fecha_vencimiento_periodo?->lt($hoy); @endphp
                            <div class="ca-modal-foto"
                                 data-foto-id="{{ $foto->id }}"
                                 data-inicio="{{ $foto->paquete->fecha_inicio_periodo?->format('Y-m-d') }}"
                                 data-fin="{{ $foto->paquete->fecha_vencimiento_periodo?->format('Y-m-d') }}"
                                 data-vencida="{{ $fotoVencida ? 1 : 0 }}"
                                 onclick="caSeleccionarFotoModal(this)"
                                 title="Foto #{{ $foto->id }}{{ $fotoVencida ? ' — de un mes anterior' : ' — período ' . $foto->paquete->fecha_inicio_periodo?->format('d/m/Y') . ' a ' . $foto->paquete->fecha_vencimiento_periodo?->format('d/m/Y') }}">
                                <img src="{{ $foto->url }}" alt="Foto aprobada" loading="lazy" onerror="caImgFallback(this)">
                                @if($fotoVencida)
                                    <span class="ca-modal-foto-badge ca-modal-foto-badge--vencida">Vencida</span>
                                @endif
                            </div>
                        @empty
                            <p class="ca-modal-foto-vacio">No hay fotografías aprobadas disponibles para este cliente.</p>
                        @endforelse
                    </div>
                </div>
                <p id="ca-colocar-periodo-aviso" class="text-sm mt-1" style="display:none;"></p>
            </div>
            <div class="cal-modal-footer">
                <button type="button" onclick="caCerrarModal('ca-modal-colocar')" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="ca-colocar-btn-submit">Programar</button>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Detalle del día — lista de fotos agendadas con miniatura y acciones  --}}
{{-- ════════════════════════════════════════════════════════════════════════════ --}}
<div id="ca-modal-detalle" class="cal-modal-overlay" style="display:none"
     onclick="caCerrarModalSiOverlay(event,'ca-modal-detalle')">
    <div class="cal-modal" style="max-width: 460px;">
        <div class="cal-modal-header">
            <h4 class="cal-modal-title" id="ca-detalle-dia-titulo">Fotos del día</h4>
            <button type="button" onclick="caCerrarModal('ca-modal-detalle')" class="cal-modal-close">&times;</button>
        </div>
        <div class="cal-modal-body" style="max-height: 65vh; overflow-y: auto;">
            <div id="ca-detalle-dia-lista" class="ca-dia-lista"></div>
        </div>
        <div class="cal-modal-footer">
            <button type="button" onclick="caCerrarModal('ca-modal-detalle')" class="btn btn-secondary">Cerrar</button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════ --}}
{{-- LIGHTBOX: foto en grande al hacer clic en una miniatura                     --}}
{{-- ════════════════════════════════════════════════════════════════════════════ --}}
<div id="ca-lightbox" class="ca-lightbox-overlay" style="display:none" onclick="caCerrarLightbox()">
    <button type="button" class="ca-lightbox-close" onclick="caCerrarLightbox()">&times;</button>
    <img id="ca-lightbox-img" src="" alt="Foto en grande">
</div>
@endsection

@section('scripts')
<script>
// ── Respaldo visual cuando una foto no carga (URL rota, bucket inaccesible, etc.) ──
function caImgFallback(img) {
    img.onerror = null;
    img.src = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">' +
        '<rect width="48" height="48" fill="#f3f4f6"/>' +
        '<path d="M12 32l7-7a2 2 0 012.8 0l4.2 4.2 5-5a2 2 0 012.8 0L36 26" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
        '<circle cx="19" cy="18" r="2.5" fill="#9ca3af"/>' +
        '<rect x="9" y="9" width="30" height="30" rx="3" fill="none" stroke="#9ca3af" stroke-width="2"/>' +
        '</svg>'
    );
    img.classList.add('ca-img-fallback');
}

// ── Selección de foto desde el panel ─────────────────────────────────────────
let caFotoSeleccionada = null;

function caSeleccionarFoto(el) {
    if (caFotoSeleccionada === el) {
        el.classList.remove('ca-panel-foto--seleccionada');
        caFotoSeleccionada = null;
        return;
    }
    document.querySelectorAll('.ca-panel-foto--seleccionada')
        .forEach(e => e.classList.remove('ca-panel-foto--seleccionada'));
    el.classList.add('ca-panel-foto--seleccionada');
    caFotoSeleccionada = el;
}

// ── Modal: Colocar foto en un día ─────────────────────────────────────────────

// Marca una foto como elegida dentro de la cuadrícula del modal y actualiza
// el campo oculto que realmente se envía en el formulario.
function caSeleccionarFotoModal(el) {
    // Selección múltiple: cada clic prende/apaga esa foto, no reemplaza a las demás.
    el.classList.toggle('ca-modal-foto--seleccionada');
    caActualizarSeleccionColocar();
}

// Reconstruye los inputs ocultos foto_ids[] a partir de lo marcado en la
// cuadrícula, actualiza el contador y el aviso de fecha/rezago.
function caActualizarSeleccionColocar() {
    const seleccionadas = document.querySelectorAll('#ca-modal-foto-grid .ca-modal-foto--seleccionada');

    const contenedor = document.getElementById('ca-colocar-foto-ids');
    contenedor.innerHTML = '';
    seleccionadas.forEach(el => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'foto_ids[]';
        input.value = el.dataset.fotoId;
        contenedor.appendChild(input);
    });

    const contador = document.getElementById('ca-colocar-contador');
    if (seleccionadas.length > 0) {
        contador.textContent = seleccionadas.length + ' seleccionada' + (seleccionadas.length !== 1 ? 's' : '');
        contador.style.display = 'inline-block';
    } else {
        contador.style.display = 'none';
    }

    document.getElementById('ca-colocar-btn-submit').textContent =
        seleccionadas.length > 1 ? `Programar (${seleccionadas.length})` : 'Programar';

    const fecha = document.getElementById('ca-colocar-fecha').value;
    caVerificarPeriodo(fecha, seleccionadas);
}

function caAbrirModalColocar(fecha) {
    document.querySelectorAll('#ca-modal-foto-grid .ca-modal-foto--seleccionada')
        .forEach(e => e.classList.remove('ca-modal-foto--seleccionada'));

    // Si ya se había elegido una foto desde el panel derecho, se preselecciona
    // automáticamente en la cuadrícula del modal — se pueden agregar más ahí mismo.
    if (caFotoSeleccionada) {
        const item = document.querySelector(`#ca-modal-foto-grid .ca-modal-foto[data-foto-id="${caFotoSeleccionada.dataset.fotoId}"]`);
        if (item) item.classList.add('ca-modal-foto--seleccionada');
    }

    document.getElementById('ca-colocar-fecha').value = fecha;
    document.getElementById('ca-colocar-fecha-display').textContent =
        new Date(fecha + 'T12:00:00').toLocaleDateString('es-MX', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });

    caActualizarSeleccionColocar();

    document.getElementById('ca-modal-colocar').style.display = 'flex';
}

function caVerificarPeriodo(fecha, seleccionadas) {
    const aviso = document.getElementById('ca-colocar-periodo-aviso');
    if (!seleccionadas || seleccionadas.length === 0) { aviso.style.display = 'none'; return; }

    // Foto(s) de un mes anterior sin agendar a tiempo: sin restricción de fecha
    // ni de cuántas por día. Las demás sí respetan el período de su paquete.
    const hayVencidas = Array.from(seleccionadas).some(el => el.dataset.vencida === '1');
    const fueraDePeriodo = Array.from(seleccionadas).some(el =>
        el.dataset.vencida !== '1' && el.dataset.inicio && (fecha < el.dataset.inicio || fecha > el.dataset.fin)
    );

    const mensajes = [];
    if (hayVencidas) {
        mensajes.push('La(s) foto(s) de un mes anterior se pueden colocar cualquier día a partir de hoy, sin límite.');
    }
    if (fueraDePeriodo) {
        mensajes.push('Alguna foto seleccionada queda fuera de los días disponibles de su período para esta fecha.');
    }

    if (mensajes.length === 0) { aviso.style.display = 'none'; return; }

    aviso.style.color = fueraDePeriodo ? '#d97706' : '#065f46';
    aviso.textContent = mensajes.join(' ');
    aviso.style.display = 'block';
}

document.getElementById('ca-form-colocar').addEventListener('submit', function (e) {
    if (document.querySelectorAll('#ca-colocar-foto-ids input').length === 0) {
        e.preventDefault();
        const aviso = document.getElementById('ca-colocar-periodo-aviso');
        aviso.style.color = '#dc2626';
        aviso.textContent = 'Elige al menos una fotografía de la cuadrícula antes de programar.';
        aviso.style.display = 'block';
    }
});

// ── Modal: Detalle del día (lista de fotos con miniatura y acciones) ─────────
@php
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
const caEntradasPorDia = @json($entradasDiaJson);
const caBaseUrl       = '{{ url("/calendario") }}';
const caCsrf          = '{{ csrf_token() }}';

// Envía una acción (publicar / eliminar) construyendo el formulario al vuelo.
function caSubmitAccion(url, metodo, confirmacion) {
    showConfirmModal('Confirmar', confirmacion, () => {
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = url;
        f.innerHTML = `<input type="hidden" name="_token" value="${caCsrf}">`
            + (metodo ? `<input type="hidden" name="_method" value="${metodo}">` : '');
        document.body.appendChild(f);
        f.submit();
    }, {danger: metodo === 'DELETE'});
}

function caAbrirDetalleDia(fecha) {
    const entradas = caEntradasPorDia[fecha] || [];
    const lista    = document.getElementById('ca-detalle-dia-lista');

    document.getElementById('ca-detalle-dia-titulo').textContent =
        'Fotos del ' + new Date(fecha + 'T12:00:00').toLocaleDateString('es-MX', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });

    lista.innerHTML = '';

    entradas.forEach(e => {
        const fila = document.createElement('div');
        fila.className = 'ca-dia-item ca-dia-item--' + e.estatus;

        let acciones = '';
        if (e.estatus === 'programada') {
            acciones = `
                <button type="button" class="btn btn-primary btn-sm" onclick="caSubmitAccion('${caBaseUrl}/${e.id}/publicar', null, '¿Marcar esta fotografía como publicada?')">Publicar</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="caSubmitAccion('${caBaseUrl}/${e.id}', 'DELETE', '¿Eliminar esta entrada del calendario?')">Eliminar</button>`;
        } else if (e.estatus === 'publicada') {
            acciones = '<span class="ca-dia-item-nota">Publicada — ya no se puede modificar.</span>';
        } else {
            acciones = '<span class="ca-dia-item-nota">Cancelada.</span>';
        }

        const etiqueta = e.estatus.charAt(0).toUpperCase() + e.estatus.slice(1);

        fila.innerHTML = `
            <img src="${e.url}" alt="Foto" class="ca-dia-item-img" loading="lazy" onerror="caImgFallback(this)" onclick="caAbrirLightbox('${e.url}')">
            <div class="ca-dia-item-info">
                <span class="ca-dia-item-badge ca-dia-item-badge--${e.estatus}">${etiqueta}</span>
                <div class="ca-dia-item-acciones">${acciones}</div>
            </div>`;

        lista.appendChild(fila);
    });

    if (entradas.length === 0) {
        lista.innerHTML = '<p style="text-align:center; color:var(--ca-muted); font-size:0.85rem; padding:1rem 0;">No hay fotos en este día.</p>';
    }

    document.getElementById('ca-modal-detalle').style.display = 'flex';
}

// ── Cerrar modales ────────────────────────────────────────────────────────────
function caCerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}
function caCerrarModalSiOverlay(event, id) {
    if (event.target === document.getElementById(id)) caCerrarModal(id);
}

// ── Lightbox: ver la foto en grande ───────────────────────────────────────────
function caAbrirLightbox(url) {
    document.getElementById('ca-lightbox-img').src = url;
    document.getElementById('ca-lightbox').style.display = 'flex';
}
function caCerrarLightbox() {
    document.getElementById('ca-lightbox').style.display = 'none';
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        caCerrarLightbox();
        caCerrarModal('ca-modal-colocar');
        caCerrarModal('ca-modal-detalle');
    }
});
</script>
@endsection
