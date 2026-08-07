@extends('layouts.app')

@section('title', 'Seleccionar Fotografías')

@section('styles')
<style>
/* ── Reset local ─────────────────────────────────────────────────────────────── */
.sel-wrap * { box-sizing: border-box; }

/* ── Barra de pasos ──────────────────────────────────────────────────────────── */
.steps-bar {
    display: flex;
    align-items: center;
    margin-bottom: 2rem;
}
.step-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.step-connector {
    flex: 1;
    height: 2px;
    background: #e5e7eb;
    margin: 0 0.75rem;
    transition: background .3s;
}
.step-connector.done { background: #10b981; }
.step-dot {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: 2px solid #e5e7eb;
    background: #fff;
    color: #9ca3af;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .8rem;
    font-weight: 700;
    flex-shrink: 0;
    transition: background .25s, border-color .25s, color .25s;
}
.step-dot.active  { border-color: #667eea; background: #667eea; color: #fff; }
.step-dot.done    { border-color: #10b981; background: #10b981; color: #fff; }
.step-label {
    font-size: .85rem;
    font-weight: 600;
    color: #9ca3af;
    transition: color .25s;
    white-space: nowrap;
}
.step-label.active { color: #667eea; }
.step-label.done   { color: #10b981; }

/* ── Notice informativo ──────────────────────────────────────────────────────── */
.sel-notice {
    display: flex;
    align-items: flex-start;
    gap: .6rem;
    background: #eff0fd;
    border: 1px solid rgba(102,126,234,.25);
    border-radius: 8px;
    padding: .85rem 1.1rem;
    font-size: .875rem;
    color: #3b4da8;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}
.sel-notice svg { flex-shrink: 0; margin-top: 1px; }
.sel-notice.success {
    background: #d1fae5;
    border-color: #a7f3d0;
    color: #065f46;
}

/* ── Contador ────────────────────────────────────────────────────────────────── */
.sel-counter {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 999px;
    padding: .35rem 1rem .35rem .7rem;
    font-size: .9rem;
    font-weight: 600;
    color: #374151;
    background: #fff;
    margin-bottom: 1.25rem;
    transition: border-color .2s, background .2s;
}
.sel-counter.completo {
    border-color: #10b981;
    background: #d1fae5;
    color: #065f46;
}
.sel-counter-pill {
    background: #667eea;
    color: #fff;
    border-radius: 999px;
    padding: 2px 10px;
    font-size: .78rem;
    font-weight: 700;
    transition: background .2s;
}
.sel-counter.completo .sel-counter-pill { background: #10b981; }

/* ── Grid paso 1 ─────────────────────────────────────────────────────────────── */
.fotos-grid-s1 {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
}

/* ── Tarjeta foto paso 1 ─────────────────────────────────────────────────────── */
.foto-s1 {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    border: 3px solid transparent;
    background: #f3f4f6;
    transition: border-color .18s, transform .15s, box-shadow .15s;
    box-shadow: 0 4px 14px rgba(15,23,42,.10), 0 1px 3px rgba(15,23,42,.08);
    user-select: none;
    /* Altura fija para que no dependa del aspect-ratio */
    height: 200px;
}
.foto-s1:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 30px rgba(15,23,42,.18), 0 3px 8px rgba(15,23,42,.1);
}
.foto-s1.selected {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16,185,129,.3), 0 10px 24px rgba(15,23,42,.15);
}

.foto-s1 img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: opacity .18s;
}
.foto-s1.selected img { opacity: .78; }

/* Overlay oscuro que aparece al seleccionar */
.foto-s1-overlay {
    position: absolute;
    inset: 0;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .18s;
    pointer-events: none;
}
.foto-s1.selected .foto-s1-overlay {
    background: rgba(16,185,129,.22);
}
.foto-s1:not(.selected):hover .foto-s1-overlay {
    background: rgba(0,0,0,.1);
}

/* Checkmark circular */
.foto-s1-check {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: #10b981;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: scale(0);
    transition: transform .22s cubic-bezier(.175,.885,.32,1.275);
    box-shadow: 0 4px 14px rgba(16,185,129,.55);
}
.foto-s1.selected .foto-s1-check { transform: scale(1); }
.foto-s1-check svg { color: #fff; }

/* Casilla de selección — esquina superior izquierda */
.foto-s1-checkbox {
    position: absolute;
    top: 7px;
    left: 7px;
    z-index: 2;
    cursor: pointer;
    display: block;
    line-height: 0;
}
.foto-s1-checkbox input {
    position: absolute;
    inset: 0;
    width: 26px;
    height: 26px;
    margin: 0;
    opacity: 0;
    cursor: pointer;
}
.foto-s1-checkbox input:disabled { cursor: not-allowed; }
.foto-s1-checkbox-mark {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 7px;
    background: rgba(255,255,255,.88);
    border: 2px solid #fff;
    box-shadow: 0 1px 5px rgba(0,0,0,.3);
    color: transparent;
    transition: background .15s, border-color .15s, color .15s, transform .12s;
}
.foto-s1-checkbox input:checked + .foto-s1-checkbox-mark {
    background: #10b981;
    border-color: #10b981;
    color: #fff;
}
.foto-s1-checkbox input:disabled + .foto-s1-checkbox-mark {
    opacity: .5;
}
.foto-s1-checkbox input:focus-visible + .foto-s1-checkbox-mark {
    outline: 2px solid #667eea;
    outline-offset: 2px;
}
.foto-s1-checkbox:hover .foto-s1-checkbox-mark { transform: scale(1.08); }

/* Número de orden cuando está seleccionada — esquina superior derecha */
.foto-s1-num {
    position: absolute;
    top: 6px;
    right: 7px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #10b981;
    color: #fff;
    font-size: .7rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0);
    transition: opacity .2s, transform .22s cubic-bezier(.175,.885,.32,1.275);
    pointer-events: none;
}
.foto-s1.selected .foto-s1-num { opacity: 1; transform: scale(1); }

/* ── Botón siguiente ─────────────────────────────────────────────────────────── */
.btn-siguiente {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .7rem 1.75rem;
    background: #667eea;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .95rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .18s, transform .12s, box-shadow .18s;
    box-shadow: 0 2px 10px rgba(102,126,234,.4);
}
.btn-siguiente:hover:not(:disabled) {
    background: #5a6fd6;
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(102,126,234,.5);
}
.btn-siguiente:disabled {
    background: #d1d5db;
    color: #9ca3af;
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
}

/* ── Strip de seleccionadas – inicio del paso 2 ──────────────────────────────── */
.strip-box {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.75rem;
}
.strip-header {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: .75rem;
    font-size: .8rem;
    font-weight: 700;
    color: #065f46;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.strip-fotos {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}
.strip-thumb {
    position: relative;
    width: 68px;
    height: 68px;
    border-radius: 7px;
    overflow: hidden;
    border: 2px solid #10b981;
    flex-shrink: 0;
}
.strip-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.strip-thumb-badge {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: #10b981;
    color: #fff;
    font-size: .55rem;
    font-weight: 700;
    text-align: center;
    padding: 2px 0;
    text-transform: uppercase;
    letter-spacing: .04em;
}

/* ── Grid paso 2 ─────────────────────────────────────────────────────────────── */
.fotos-grid-s2 {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
}
.foto-s2 {
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
    transition: border-color .18s, box-shadow .18s;
}
.foto-s2.conservar { border-color: #f59e0b; box-shadow: 0 0 0 2px rgba(245,158,11,.2); }
.foto-s2.descartar { border-color: #ef4444; box-shadow: 0 0 0 2px rgba(239,68,68,.12); opacity: .7; }

.foto-s2 img { width: 100%; height: 140px; object-fit: cover; display: block; }

.foto-s2-btns {
    display: flex;
    border-top: 1px solid #e5e7eb;
}
.btn-dec {
    flex: 1;
    padding: .55rem .4rem;
    font-size: .75rem;
    font-weight: 600;
    border: none;
    background: #fff;
    cursor: pointer;
    transition: background .14s, color .14s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .25rem;
    line-height: 1.2;
}
.btn-dec:first-child { border-right: 1px solid #e5e7eb; }
.btn-dec:hover { background: #f9fafb; }
.btn-dec.act-conservar { background: #fef3c7; color: #92400e; }
.btn-dec.act-descartar { background: #fee2e2; color: #7f1d1d; }

/* ── Contador de pendientes ──────────────────────────────────────────────────── */
.pend-counter {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .85rem;
    font-weight: 600;
    padding: .3rem .9rem;
    border-radius: 999px;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    color: #6b7280;
    margin-bottom: 1.25rem;
    transition: border-color .2s, background .2s, color .2s;
}
.pend-counter.pendiente { border-color: #f59e0b; background: #fef3c7; color: #92400e; }
.pend-counter.listo     { border-color: #10b981; background: #d1fae5; color: #065f46; }

/* ── Acciones finales paso 2 ─────────────────────────────────────────────────── */
.s2-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    padding-top: 1.25rem;
    border-top: 1px solid #e5e7eb;
    margin-top: .5rem;
}
.btn-volver {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .65rem 1.25rem;
    background: #fff;
    color: #374151;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: .9rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s, border-color .15s;
}
.btn-volver:hover { background: #f9fafb; border-color: #9ca3af; }
.btn-confirmar {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .7rem 1.75rem;
    background: #667eea;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .95rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .18s, transform .12s, box-shadow .18s;
    box-shadow: 0 2px 10px rgba(102,126,234,.4);
}
.btn-confirmar:hover:not(:disabled) {
    background: #5a6fd6;
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(102,126,234,.5);
}
.btn-confirmar:disabled {
    background: #d1d5db;
    color: #9ca3af;
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
}

/* ── Vista ya-procesado ──────────────────────────────────────────────────────── */
.fotos-seccion { margin-bottom: 2rem; }
.fotos-seccion:last-child { margin-bottom: 0; }
.fotos-seccion-title {
    display: flex;
    align-items: center;
    gap: .5rem;
    font-size: .95rem;
    font-weight: 700;
    margin-bottom: 1rem;
    padding-bottom: .6rem;
    border-bottom: 2px solid #e5e7eb;
}
.fotos-seccion-title svg { flex-shrink: 0; }
.fotos-seccion-title.aprobada   { color: #065f46; }
.fotos-seccion-title.conservada { color: #92400e; }
.fotos-seccion-title.descartada { color: #7f1d1d; }
.fotos-seccion-count {
    margin-left: auto;
    font-size: .75rem;
    font-weight: 700;
    padding: 2px 10px;
    border-radius: 999px;
}
.fotos-seccion-title.aprobada   .fotos-seccion-count { background:#d1fae5; color:#065f46; }
.fotos-seccion-title.conservada .fotos-seccion-count { background:#fef3c7; color:#92400e; }
.fotos-seccion-title.descartada .fotos-seccion-count { background:#fee2e2; color:#7f1d1d; }

.fotos-static {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 1rem;
}
.foto-static { border: 2px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
.foto-static.aprobada   { border-color: #a7f3d0; }
.foto-static.conservada { border-color: #fde68a; }
.foto-static.descartada { border-color: #fecaca; opacity: .75; }
.foto-static img { width: 100%; height: 140px; object-fit: cover; display: block; }

/* ── x-cloak ─────────────────────────────────────────────────────────────────── */
[x-cloak] { display: none !important; }
</style>
@endsection

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('cliente.aprobaciones.index') }}" class="breadcrumb-link">Mis Aprobaciones</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Seleccionar Fotografías</span>
        </div>
    </div>
@endsection

@section('content')
<div class="sel-wrap" style="max-width:1100px; margin:0 auto;">

    {{-- ── Cabecera ─────────────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Selecciona tus Fotografías</h1>
            <p class="page-subtitle">
                Período {{ ucfirst($paquete->mes_revision_legible) }}
                &middot; Cuota: <strong>{{ $paquete->cantidad_requerida }}</strong> fotos
                &middot; Fecha límite: <strong>{{ $paquete->fecha_limite?->format('d/m/Y') ?? '—' }}</strong>
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

        {{-- ── Sin fotos ──────────────────────────────────────────────────── --}}
        @if($paquete->fotos->isEmpty())
            <div class="empty-state">
                <p class="empty-state-description">Aún no hay fotografías candidatas cargadas para este período.</p>
            </div>

        {{-- ── Ya procesado ────────────────────────────────────────────────── --}}
        @elseif($paquete->estatus !== 'pendiente')
            @php
                $aprobadas   = $paquete->fotos->where('estatus', 'aprobada');
                $conservadas = $paquete->fotos->where('estatus', 'conservada');
                $descartadas = $paquete->fotos->where('estatus', 'descartada');
            @endphp

            <div class="alert alert-info mb-4" style="margin-bottom:1.25rem; padding:.85rem 1.1rem; background:#eff0fd; border-radius:8px; color:#3b4da8; font-size:.875rem;">
                Este paquete ya fue procesado: <strong>{{ str_replace('_', ' ', $paquete->estatus) }}</strong>.
            </div>

            @if($aprobadas->isNotEmpty())
                <div class="fotos-seccion">
                    <h3 class="fotos-seccion-title aprobada">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Aprobadas — se publicarán este ciclo
                        <span class="fotos-seccion-count">{{ $aprobadas->count() }}</span>
                    </h3>
                    <div class="fotos-static">
                        @foreach($aprobadas as $foto)
                            <div class="foto-static aprobada">
                                <img src="{{ $foto->url }}" alt="Fotografía aprobada" data-lightbox-trigger
                                     onclick="abrirLightbox('{{ $foto->url }}')">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($conservadas->isNotEmpty())
                <div class="fotos-seccion">
                    <h3 class="fotos-seccion-title conservada">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        Conservadas — en reserva para el próximo ciclo
                        <span class="fotos-seccion-count">{{ $conservadas->count() }}</span>
                    </h3>
                    <div class="fotos-static">
                        @foreach($conservadas as $foto)
                            <div class="foto-static conservada">
                                <img src="{{ $foto->url }}" alt="Fotografía conservada" data-lightbox-trigger
                                     onclick="abrirLightbox('{{ $foto->url }}')">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($descartadas->isNotEmpty())
                <div class="fotos-seccion">
                    <h3 class="fotos-seccion-title descartada">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Descartadas
                        <span class="fotos-seccion-count">{{ $descartadas->count() }}</span>
                    </h3>
                    <div class="fotos-static">
                        @foreach($descartadas as $foto)
                            <div class="foto-static descartada">
                                <img src="{{ $foto->url }}" alt="Fotografía descartada" data-lightbox-trigger
                                     onclick="abrirLightbox('{{ $foto->url }}')">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        {{-- ── Flujo interactivo de dos pasos ──────────────────────────────── --}}
        @else
        @php
            $cuota     = $paquete->cantidad_requerida;
            $fotosJson = $paquete->fotos->map(fn($f) => ['id' => $f->id, 'url' => $f->url])->toJson();
        @endphp

        <div
            x-data="{
                paso: 1,
                cuota: {{ $cuota }},
                fotos: {{ $fotosJson }},
                seleccionadas: [],
                decisiones: {},

                ids() { return this.fotos.map(f => f.id); },
                sobrantes() { return this.ids().filter(id => !this.seleccionadas.includes(id)); },
                get soloConservar() {
                    return this.sobrantes().filter(id => this.decisiones[id] === 'conservar');
                },
                sinDecidir() {
                    return this.sobrantes().filter(id => !this.decisiones[id]);
                },
                puedeConfirmar() {
                    const s = this.sobrantes();
                    return s.length === 0 || s.every(id => !!this.decisiones[id]);
                },
                ordenSeleccion(id) {
                    const i = this.seleccionadas.indexOf(id);
                    return i >= 0 ? i + 1 : null;
                },
                toggle(id) {
                    if (this.seleccionadas.includes(id)) {
                        this.seleccionadas = this.seleccionadas.filter(x => x !== id);
                    } else if (this.seleccionadas.length < this.cuota) {
                        this.seleccionadas.push(id);
                    }
                },
                setDec(id, val) {
                    if (this.decisiones[id] === val) {
                        const d = { ...this.decisiones };
                        delete d[id];
                        this.decisiones = d;
                    } else {
                        this.decisiones = { ...this.decisiones, [id]: val };
                    }
                },
                fotoUrl(id) {
                    return (this.fotos.find(f => f.id === id) || {}).url || '';
                },
                paso2() {
                    if (this.seleccionadas.length === this.cuota) {
                        this.paso = 2;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                paso1() {
                    this.paso = 1;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }"
        >

            {{-- ── Barra de pasos ──────────────────────────────────────────── --}}
            <div class="steps-bar">
                <div class="step-item">
                    <div class="step-dot" :class="{ active: paso===1, done: paso>1 }">
                        <template x-if="paso > 1">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <template x-if="paso <= 1"><span>1</span></template>
                    </div>
                    <span class="step-label" :class="{ active: paso===1, done: paso>1 }">Elige tus fotos</span>
                </div>
                <div class="step-connector" :class="{ done: paso>1 }"></div>
                <div class="step-item">
                    <div class="step-dot" :class="{ active: paso===2 }">2</div>
                    <span class="step-label" :class="{ active: paso===2 }">¿Qué hacemos con las demás?</span>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- PASO 1 — Seleccionar fotos para aprobar                       --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <div x-show="paso === 1" x-cloak>

                <div class="sel-notice">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>
                        Marca la casilla de <strong>{{ $cuota }} {{ $cuota === 1 ? 'fotografía' : 'fotografías' }}</strong>
                        para publicar este ciclo — haz clic en la imagen para verla en detalle.
                        Una vez alcanzada la cuota podrás continuar al siguiente paso.
                    </span>
                </div>

                <div class="sel-counter" :class="{ completo: seleccionadas.length === cuota }">
                    <span>Seleccionadas</span>
                    <span class="sel-counter-pill" x-text="seleccionadas.length + ' de ' + cuota"></span>
                </div>

                <div class="fotos-grid-s1">
                    @foreach($paquete->fotos as $foto)
                    <div
                        class="foto-s1"
                        :class="{
                            selected:  seleccionadas.includes({{ $foto->id }}),
                            bloqueada: seleccionadas.length >= cuota && !seleccionadas.includes({{ $foto->id }})
                        }"
                        @click="window.abrirLightbox('{{ $foto->url }}')"
                        title="Ver en detalle"
                    >
                        <img src="{{ $foto->url }}" alt="Fotografía candidata" loading="lazy">

                        <label class="foto-s1-checkbox" @click.stop title="Seleccionar para este ciclo">
                            <input
                                type="checkbox"
                                :checked="seleccionadas.includes({{ $foto->id }})"
                                :disabled="seleccionadas.length >= cuota && !seleccionadas.includes({{ $foto->id }})"
                                @change="toggle({{ $foto->id }})"
                                aria-label="Seleccionar fotografía"
                            >
                            <span class="foto-s1-checkbox-mark">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                        </label>

                        <div class="foto-s1-overlay">
                            <div class="foto-s1-check">
                                <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Número de orden de selección --}}
                        <div class="foto-s1-num" x-text="ordenSeleccion({{ $foto->id }})"></div>
                    </div>
                    @endforeach
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top:.5rem;">
                    <button
                        type="button"
                        class="btn-siguiente"
                        :disabled="seleccionadas.length !== cuota"
                        @click="paso2()"
                    >
                        Siguiente
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

            </div>{{-- /paso 1 --}}

            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- PASO 2 — Decidir sobre las fotos sobrantes                    --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <div x-show="paso === 2" x-cloak>

                {{-- Strip de fotos aprobadas --}}
                <div class="strip-box">
                    <div class="strip-header">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Fotos para publicar este ciclo
                        <span style="margin-left:auto; font-size:.75rem; font-weight:700; color:#065f46;"
                              x-text="seleccionadas.length + ' / ' + cuota + ' seleccionadas'"></span>
                    </div>
                    <div class="strip-fotos">
                        <template x-for="id in seleccionadas" :key="'strip-'+id">
                            <div class="strip-thumb">
                                <img :src="fotoUrl(id)" alt="Foto seleccionada">
                                <div class="strip-thumb-badge">Publicar</div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Si no hay sobrantes --}}
                <template x-if="sobrantes().length === 0">
                    <div class="sel-notice success" style="margin-bottom:1.5rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Has seleccionado exactamente todas las fotografías disponibles. ¡Puedes confirmar tu selección!</span>
                    </div>
                </template>

                {{-- Instrucción y grid de sobrantes --}}
                <template x-if="sobrantes().length > 0">
                    <div>
                        <div class="sel-notice" style="margin-bottom:1.25rem;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>
                                Para las <strong x-text="sobrantes().length"></strong> fotografías restantes,
                                decide si quieres <strong>conservarlas</strong> para el siguiente ciclo (quedan en reserva 6 meses)
                                o <strong>descartarlas</strong> definitivamente.
                            </span>
                        </div>

                        <div class="pend-counter"
                             :class="{ pendiente: sinDecidir().length > 0, listo: sinDecidir().length === 0 }">
                            <template x-if="sinDecidir().length > 0">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </template>
                            <template x-if="sinDecidir().length === 0">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </template>
                            <span x-text="sinDecidir().length > 0
                                ? sinDecidir().length + ' fotografía' + (sinDecidir().length !== 1 ? 's' : '') + ' sin decidir'
                                : 'Todas las fotografías tienen decisión ✓'"></span>
                        </div>

                        <div class="fotos-grid-s2">
                            @foreach($paquete->fotos as $foto)
                            <div
                                x-show="sobrantes().includes({{ $foto->id }})"
                                class="foto-s2"
                                :class="{
                                    conservar: decisiones[{{ $foto->id }}] === 'conservar',
                                    descartar: decisiones[{{ $foto->id }}] === 'descartar'
                                }"
                            >
                                <img src="{{ $foto->url }}" alt="Fotografía" loading="lazy" data-lightbox-trigger
                                     onclick="abrirLightbox('{{ $foto->url }}')">
                                <div class="foto-s2-btns">
                                    <button
                                        type="button"
                                        class="btn-dec"
                                        :class="{ 'act-conservar': decisiones[{{ $foto->id }}] === 'conservar' }"
                                        @click="setDec({{ $foto->id }}, 'conservar')"
                                        title="Guardar en reserva para el próximo ciclo"
                                    >
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                        Conservar
                                    </button>
                                    <button
                                        type="button"
                                        class="btn-dec"
                                        :class="{ 'act-descartar': decisiones[{{ $foto->id }}] === 'descartar' }"
                                        @click="setDec({{ $foto->id }}, 'descartar')"
                                        title="Descartar definitivamente"
                                    >
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Descartar
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </template>

                {{-- Formulario con hidden inputs --}}
                <form method="POST" action="{{ route('cliente.aprobaciones.confirmar', $paquete) }}">
                    @csrf

                    <template x-for="id in seleccionadas" :key="'ap-'+id">
                        <input type="hidden" name="aprobadas[]" :value="id">
                    </template>
                    <template x-for="id in soloConservar" :key="'cv-'+id">
                        <input type="hidden" name="conservar[]" :value="id">
                    </template>

                    <div class="s2-actions">
                        <button type="button" class="btn-volver" @click="paso1()">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Volver al paso anterior
                        </button>

                        <div style="flex:1"></div>

                        <button type="submit" class="btn-confirmar" :disabled="!puedeConfirmar()">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Confirmar selección
                        </button>
                    </div>
                </form>

            </div>{{-- /paso 2 --}}

        </div>{{-- /x-data --}}
        @endif

        </div>{{-- /card-body --}}
    </div>{{-- /card --}}
</div>

<x-image-lightbox />
@endsection
