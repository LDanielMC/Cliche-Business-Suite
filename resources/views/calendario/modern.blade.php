@extends('layouts.modern')

@section('title', 'Calendario de Fotos')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Calendario de Fotos</span>
        </div>
    </div>
@endsection

@section('content')
@php
    $mesAnterior = \Carbon\Carbon::create($anio, $mes, 1)->subMonth();
    $mesSiguiente = \Carbon\Carbon::create($anio, $mes, 1)->addMonth();
    $nombreMes = \Carbon\Carbon::create($anio, $mes, 1)->translatedFormat('F Y');

    $hoy = \Carbon\Carbon::today();

    // Tres niveles de severidad por cliente, según sus fotos aprobadas sin agendar:
    //  - Vencida:  el paquete ya pasó fecha_vencimiento_periodo sin agendarse. Huérfana
    //              hasta que se registre una renovación nueva.
    //  - Urgente:  todavía no vence, pero para caber antes de esa fecha se necesita más
    //              de 1 foto/día (mismo cálculo que el tope dinámico del calendario) —
    //              no es "hay tiempo", es que ya aprieta.
    //  - Pendiente: sin agendar pero a ritmo cómodo (1 foto/día o menos alcanza).
    $pendientesPorCliente = $clientes->mapWithKeys(fn ($c) => [
        $c->id => $fotosDisponibles->get($c->id, collect())->count(),
    ]);
    $vencidasPorCliente = $clientes->mapWithKeys(fn ($c) => [
        $c->id => $fotosDisponibles->get($c->id, collect())
            ->filter(fn ($f) => $f->paquete->fecha_vencimiento_periodo?->lt($hoy))
            ->count(),
    ]);
    $urgentesPorCliente = $clientes->mapWithKeys(function ($c) use ($fotosDisponibles, $hoy) {
        $noVencidas = $fotosDisponibles->get($c->id, collect())
            ->filter(fn ($f) => ! $f->paquete->fecha_vencimiento_periodo?->lt($hoy));

        if ($noVencidas->isEmpty()) {
            return [$c->id => false];
        }

        $finMasCercano = $noVencidas->map(fn ($f) => $f->paquete->fecha_vencimiento_periodo)->min();
        $dias          = $hoy->diffInDays($finMasCercano) + 1;

        return [$c->id => ceil($noVencidas->count() / max(1, $dias)) > 1];
    });
    $clientesOrdenados = $clientes->sortByDesc(
        fn ($c) => $vencidasPorCliente[$c->id] * 1000
            + ($urgentesPorCliente[$c->id] ? 500 : 0)
            + $pendientesPorCliente[$c->id]
    )->values();
    $totalConPendientes = $pendientesPorCliente->filter(fn ($n) => $n > 0)->count();
    $totalConVencidas   = $vencidasPorCliente->filter(fn ($n) => $n > 0)->count();
    $totalFotosVencidas = $vencidasPorCliente->sum();
    $totalConUrgentes   = $urgentesPorCliente->filter(fn ($u) => $u)->count();

    // Datos de cada publicación por día, para el modal de detalle (solo lectura).
    $entradasDiaJson = $publicaciones->map(function ($grupo) {
        return $grupo->map(function ($e) {
            return [
                'id'      => $e->id,
                'url'     => $e->fotoAprobacion?->url ?? $e->fotografia_url,
                'estatus' => $e->estatus,
                'cliente' => $e->cliente->nombre_negocio ?? '—',
                'href'    => route('calendario.cliente-view', $e->cliente_id),
            ];
        })->values();
    });
@endphp

<div class="calendario-page">

    {{-- ── Mensajes flash ─────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    @if($totalFotosVencidas > 0 || $totalConUrgentes > 0)
        <div class="cal-notices">
            @if($totalFotosVencidas > 0)
                <div class="cal-notice cal-notice--vencida">
                    <svg class="cal-notice-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="cal-notice-body">
                        <strong>{{ $totalFotosVencidas }}</strong> fotografía{{ $totalFotosVencidas !== 1 ? 's' : '' }} aprobada{{ $totalFotosVencidas !== 1 ? 's' : '' }}
                        en <strong>{{ $totalConVencidas }}</strong> cliente{{ $totalConVencidas !== 1 ? 's' : '' }}
                        se quedaron sin agendar antes de vencer su vigencia. Revisa la pestaña
                        "Vencidas" y agéndalas cuanto antes — se pueden colocar desde hoy, sin límite por día,
                        aunque la renovación del cliente no esté vigente.
                    </div>
                </div>
            @endif

            @if($totalConUrgentes > 0)
                <div class="cal-notice cal-notice--urgente">
                    <svg class="cal-notice-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    <div class="cal-notice-body">
                        <strong>{{ $totalConUrgentes }}</strong> cliente{{ $totalConUrgentes !== 1 ? 's' : '' }} todavía
                        no vence{{ $totalConUrgentes !== 1 ? 'n' : '' }} pero ya no alcanza con 1 foto/día para agendar
                        todo lo pendiente a tiempo — revisa la pestaña "Urgentes" y arma su calendario hoy.
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ── Cabecera ────────────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Calendario de Fotos</h1>
            <p class="page-subtitle">Vista general de solo lectura de todos tus clientes. Para programar, mover o publicar fotos, entra al calendario del cliente desde el panel de la derecha.</p>
        </div>
    </div>

    {{-- ── Leyenda de estatus ──────────────────────────────────────────────── --}}
    <div class="cal-leyenda">
        <span class="cal-leyenda-item"><i class="cal-leyenda-dot cal-dot--programada"></i>Programada</span>
        <span class="cal-leyenda-item"><i class="cal-leyenda-dot cal-dot--publicada"></i>Publicada</span>
        <span class="cal-leyenda-item"><i class="cal-leyenda-dot cal-dot--cancelada"></i>Cancelada</span>
        <span class="cal-leyenda-item"><i class="cal-leyenda-dot cal-dot--pausada"></i>Pausada</span>
    </div>

    {{-- ── Layout principal: calendario + panel derecho ───────────────────── --}}
    <div class="cal-layout">

        {{-- ── Grilla del calendario (solo lectura) ────────────────────────── --}}
        <div class="card cal-main">
            <div class="card-header cal-toolbar">
                <div class="cal-nav">
                    <a href="{{ route('calendario.index', ['mes' => $mesAnterior->month, 'anio' => $mesAnterior->year]) }}"
                       class="cal-nav-btn" title="Mes anterior">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <h3 class="cal-nav-title">{{ ucfirst($nombreMes) }}</h3>
                    <a href="{{ route('calendario.index', ['mes' => $mesSiguiente->month, 'anio' => $mesSiguiente->year]) }}"
                       class="cal-nav-btn" title="Mes siguiente">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                <a href="{{ route('calendario.index', ['mes' => now()->month, 'anio' => now()->year]) }}"
                   class="btn btn-secondary btn-sm">Hoy</a>
            </div>

            <div class="card-body p-0">
                <div class="cal-grid">
                    @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dia)
                        <div class="cal-dow">{{ $dia }}</div>
                    @endforeach

                    @foreach($semanas as $semana)
                        @foreach($semana as $celda)
                        @php
                            $fechaStr = $celda['fecha']->format('Y-m-d');
                            $esHoy    = $celda['fecha']->isToday();
                            $delMes   = $celda['delMes'];
                            $entradasDia = $publicaciones[$fechaStr] ?? collect();

                            // Misma lógica de color que el calendario por cliente: ámbar si
                            // queda algo programado, verde si todo está publicado, gris si
                            // solo hay canceladas.
                            $activasDia   = $entradasDia->whereIn('estatus', ['programada', 'publicada']);
                            $claseResumen = $entradasDia->contains('estatus', 'programada')
                                ? 'cal-dia-resumen--programada'
                                : ($activasDia->isNotEmpty() ? 'cal-dia-resumen--publicada' : 'cal-dia-resumen--cancelada');
                            $totalDia = $activasDia->count() ?: $entradasDia->count();
                        @endphp
                        <div class="cal-day {{ !$delMes ? 'cal-day--fuera' : '' }} {{ $esHoy ? 'cal-day--hoy' : '' }}">
                            <div class="cal-day-head">
                                <span class="cal-day-num">{{ $celda['fecha']->day }}</span>
                            </div>

                            @if($entradasDia->isNotEmpty())
                                <button type="button"
                                    class="cal-dia-resumen {{ $claseResumen }}"
                                    onclick="calAbrirDetalleDia('{{ $fechaStr }}')"
                                    title="Ver las publicaciones de este día">
                                    {{ $totalDia }} foto{{ $totalDia !== 1 ? 's' : '' }}
                                </button>
                            @endif
                        </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Panel derecho: selector de calendarios por cliente ─────────────── --}}
        <div class="cal-panel">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Clientes</h3>
                </div>
                <div class="card-body" style="padding:.75rem;">
                    <div class="cal-cliente-buscar">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" id="cal-cliente-input" placeholder="Buscar cliente…" oninput="filtrarClientes(this.value)">
                    </div>

                    <div class="cal-filtro-tabs">
                        <button type="button" class="cal-filtro-tab active" data-filtro="todos" onclick="cambiarFiltroClientes(this, 'todos')" title="Todos los clientes">
                            Todos <span>{{ $clientes->count() }}</span>
                        </button>
                        <button type="button" class="cal-filtro-tab cal-filtro-tab--urgente-critico" data-filtro="urgentes" onclick="cambiarFiltroClientes(this, 'urgentes')" title="Ya no alcanza 1 foto/día antes de que venza su vigencia">
                            Urgentes <span>{{ $totalConUrgentes }}</span>
                        </button>
                        <button type="button" class="cal-filtro-tab cal-filtro-tab--vencida" data-filtro="vencidas" onclick="cambiarFiltroClientes(this, 'vencidas')" title="Su vigencia ya venció con fotos sin agendar">
                            Vencidas <span>{{ $totalConVencidas }}</span>
                        </button>
                        <button type="button" class="cal-filtro-tab cal-filtro-tab--pendiente" data-filtro="pendientes" onclick="cambiarFiltroClientes(this, 'pendientes')" title="Necesitan calendario, todavía a ritmo cómodo">
                            Pendientes <span>{{ $totalConPendientes }}</span>
                        </button>
                    </div>

                    <div class="cal-clientes-lista" id="cal-clientes-lista">
                        @forelse($clientesOrdenados as $cliente)
                            @php
                                $pendientes = $pendientesPorCliente[$cliente->id];
                                $vencidas   = $vencidasPorCliente[$cliente->id];
                                $urgente    = $urgentesPorCliente[$cliente->id];
                                $itemClass  = $vencidas > 0
                                    ? 'cal-cliente-item--vencida'
                                    : ($urgente
                                        ? 'cal-cliente-item--urgente-critico'
                                        : ($pendientes > 0 ? 'cal-cliente-item--pendiente' : ''));
                            @endphp
                            <a href="{{ route('calendario.cliente-view', $cliente) }}"
                               class="cal-cliente-item {{ $itemClass }}"
                               data-nombre="{{ \Illuminate\Support\Str::lower($cliente->nombre_negocio) }}"
                               data-pendiente="{{ $pendientes > 0 ? 1 : 0 }}"
                               data-vencida="{{ $vencidas > 0 ? 1 : 0 }}"
                               data-urgente="{{ $urgente ? 1 : 0 }}">
                                <span class="cal-cliente-nombre">{{ $cliente->nombre_negocio }}</span>
                                @if($vencidas > 0)
                                    <span class="cal-cliente-badge cal-cliente-badge--vencida" title="Fotos aprobadas cuya vigencia ya venció sin agendarse">
                                        {{ $vencidas }} vencida{{ $vencidas !== 1 ? 's' : '' }}
                                    </span>
                                @elseif($urgente)
                                    <span class="cal-cliente-badge cal-cliente-badge--urgente-critico" title="Ya no alcanza con 1 foto/día para agendar todo antes de que venza la vigencia">
                                        {{ $pendientes }} urgente
                                    </span>
                                @elseif($pendientes > 0)
                                    <span class="cal-cliente-badge" title="Fotos aprobadas sin programar — necesita calendario">{{ $pendientes }}</span>
                                @endif
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @empty
                            <div class="empty-state py-6">
                                <p class="empty-state-description">No hay clientes activos.</p>
                            </div>
                        @endforelse
                        <p id="cal-clientes-sin-resultados" class="text-sm" style="display:none;color:var(--color-text-tertiary);padding:.5rem;">
                            Ningún cliente coincide con la búsqueda.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Detalle del día — lista de publicaciones de todos los clientes (solo lectura) --}}
{{-- ════════════════════════════════════════════════════════════════════════════ --}}
<div id="cal-modal-detalle" class="cal-modal-overlay" style="display:none"
     onclick="calCerrarModalSiOverlay(event,'cal-modal-detalle')">
    <div class="cal-modal">
        <div class="cal-modal-header">
            <h4 class="cal-modal-title" id="cal-detalle-dia-titulo">Publicaciones del día</h4>
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

// ── Modal: Detalle del día (todas las publicaciones, de cualquier cliente) ───
const calEntradasPorDia = @json($entradasDiaJson);

function calAbrirDetalleDia(fecha) {
    const entradas = calEntradasPorDia[fecha] || [];
    const lista    = document.getElementById('cal-detalle-dia-lista');

    document.getElementById('cal-detalle-dia-titulo').textContent =
        'Publicaciones del ' + new Date(fecha + 'T12:00:00').toLocaleDateString('es-MX', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });

    lista.innerHTML = '';

    // mes/año de la fecha que se está viendo, para que el enlace abra
    // directamente el calendario del cliente en ese mes (no el mes actual).
    const [anio, mes] = fecha.split('-');

    entradas.forEach(e => {
        const etiqueta = e.estatus.charAt(0).toUpperCase() + e.estatus.slice(1);
        const fila = document.createElement('a');
        fila.href = e.href + '?mes=' + parseInt(mes, 10) + '&anio=' + anio;
        fila.className = 'cal-dia-item cal-dia-item--' + e.estatus;
        fila.title = 'Ver calendario de ' + e.cliente;
        fila.innerHTML = `
            <img src="${e.url}" alt="Foto" class="cal-dia-item-img" loading="lazy" onerror="calImgFallback(this)" onclick="calAbrirLightbox(event, '${e.url}')">
            <div class="cal-dia-item-info">
                <span class="cal-dia-item-cliente">${e.cliente}</span>
                <span class="cal-dia-item-badge cal-dia-item-badge--${e.estatus}">${etiqueta}</span>
            </div>
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0;color:var(--color-text-quaternary);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>`;
        lista.appendChild(fila);
    });

    if (entradas.length === 0) {
        lista.innerHTML = '<p style="text-align:center; color:var(--color-text-tertiary); font-size:0.85rem; padding:1rem 0;">No hay publicaciones en este día.</p>';
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
// Cada fila del detalle es un enlace al calendario del cliente — el clic en la
// miniatura debe abrir el lightbox en vez de navegar.
function calAbrirLightbox(event, url) {
    event.preventDefault();
    event.stopPropagation();
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

// ── Filtrar la lista de clientes del panel derecho ────────────────────────────
// Combina el texto buscado con la pestaña activa (todos / solo los que
// necesitan calendario), para encontrar clientes más rápido.
let filtroClientesActivo = 'todos';

function cambiarFiltroClientes(btn, filtro) {
    filtroClientesActivo = filtro;
    document.querySelectorAll('.cal-filtro-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filtrarClientes(document.getElementById('cal-cliente-input').value);
}

function filtrarClientes(texto) {
    const q = texto.trim().toLowerCase();
    const items = document.querySelectorAll('#cal-clientes-lista .cal-cliente-item');
    let visibles = 0;

    items.forEach(el => {
        const coincideTexto  = el.dataset.nombre.includes(q);
        const coincideFiltro = filtroClientesActivo === 'todos'
            || (filtroClientesActivo === 'pendientes' && el.dataset.pendiente === '1')
            || (filtroClientesActivo === 'urgentes' && el.dataset.urgente === '1')
            || (filtroClientesActivo === 'vencidas' && el.dataset.vencida === '1');
        const mostrar = coincideTexto && coincideFiltro;
        el.style.display = mostrar ? '' : 'none';
        if (mostrar) visibles++;
    });

    document.getElementById('cal-clientes-sin-resultados').style.display =
        (visibles === 0 && items.length > 0) ? 'block' : 'none';
}
</script>

<style>
/* ── Layout ─────────────────────────────────────────────────────────────────── */
.cal-layout {
    display: grid;
    grid-template-columns: 1fr 270px;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 1024px) {
    .cal-layout { grid-template-columns: 1fr; }
}

/* ── Leyenda de estatus ─────────────────────────────────────────────────────── */
.cal-leyenda {
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem;
    margin-bottom: 1.25rem;
    padding: .75rem 1rem;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
}
.cal-leyenda-item {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    color: var(--color-text-secondary);
}
.cal-leyenda-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.cal-dot--programada { background: #f59e0b; }
.cal-dot--publicada  { background: #10b981; }
.cal-dot--cancelada  { background: #ef4444; }
.cal-dot--pausada    { background: #9ca3af; }

/* ── Barra superior: navegación de mes ─────────────────────────────────────── */
.cal-toolbar { display: flex; align-items: center; justify-content: space-between; }
.cal-nav { display: flex; align-items: center; gap: .5rem; }
.cal-nav-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--color-border);
    color: var(--color-text-secondary);
    background: var(--color-surface);
    transition: all var(--transition-fast);
}
.cal-nav-btn:hover { background: var(--color-surface-hover); color: var(--color-text-primary); border-color: var(--color-border-hover); }
.cal-nav-title {
    font-size: 1.05rem;
    font-weight: var(--font-weight-semibold);
    color: var(--color-text-primary);
    min-width: 170px;
    text-align: center;
    text-transform: capitalize;
}

/* ── Cuadrícula del calendario (CSS Grid, no tabla) ──────────────────────────── */
.cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: var(--color-border);
    border-top: 1px solid var(--color-border);
}
.cal-dow {
    padding: .6rem 0;
    text-align: center;
    font-size: .72rem;
    font-weight: var(--font-weight-semibold);
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--color-text-tertiary);
    background: var(--color-surface);
}

.cal-day {
    background: var(--color-surface);
    height: 96px; /* fija, no min-height: todas las celdas miden lo mismo tengan
                     publicaciones o no — antes crecían con el contenido */
    min-width: 0; /* deja que la celda se encoja al ancho de su columna en vez de
                     estirar la grilla para caber el texto largo de un evento */
    padding: .4rem;
    display: flex;
    flex-direction: column;
    gap: .3rem;
}
.cal-day--fuera { background: var(--color-background-secondary); }
.cal-day--fuera .cal-day-num { color: var(--color-text-quaternary); }

.cal-day-head { display: flex; align-items: center; justify-content: space-between; min-height: 1.5rem; }
.cal-day-num {
    font-size: .8rem;
    font-weight: var(--font-weight-medium);
    color: var(--color-text-secondary);
}
.cal-day--hoy .cal-day-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    background: var(--color-brand);
    color: #fff;
    font-weight: var(--font-weight-bold);
}

/* ── Resumen del día: una etiqueta con el conteo, clic abre el detalle ────────── */
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
.cal-dia-resumen--cancelada  { background: var(--color-background-tertiary); color: var(--color-text-tertiary); border-left: 3px solid #9ca3af; opacity: .8; }

/* ── Avisos de vigencia: acento de color en vez de bloque sólido ─────────────── */
.cal-notices { display: flex; flex-direction: column; gap: .6rem; margin-bottom: 1.25rem; }
.cal-notice {
    display: flex;
    align-items: flex-start;
    gap: .65rem;
    padding: .7rem .9rem;
    border-radius: 10px;
    border: 1px solid transparent;
    font-size: .82rem;
    line-height: 1.5;
}
.cal-notice-icon { flex-shrink: 0; margin-top: 1px; }
.cal-notice-body { flex: 1; min-width: 0; }
.cal-notice--vencida { background: #fff7ed; border-color: #fed7aa; color: #7c2d12; }
.cal-notice--vencida .cal-notice-icon { color: #ea580c; }
.cal-notice--urgente { background: #fee2e2; border-color: #fecaca; color: #7f1d1d; }
.cal-notice--urgente .cal-notice-icon { color: #ef4444; }

/* ── Modal de detalle del día ──────────────────────────────────────────────────
   Mismo patrón autocontenido usado en el calendario por cliente. */
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
    max-width: 440px;
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

/* Filas de la lista dentro del modal */
.cal-dia-lista { display: flex; flex-direction: column; gap: .5rem; }
.cal-dia-item {
    display: flex;
    align-items: center;
    gap: .7rem;
    padding: .5rem;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    text-decoration: none;
    color: var(--color-text-primary);
    transition: background .12s;
}
.cal-dia-item:hover { background: var(--color-surface-hover); }
.cal-dia-item--cancelada { opacity: .65; }
.cal-dia-item-img { width: 52px; height: 52px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: var(--color-background-secondary); cursor: pointer; }
.cal-dia-item-img.cal-img-fallback { object-fit: contain; padding: 10%; opacity: .55; cursor: default; }

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
.cal-dia-item-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: .3rem; }
.cal-dia-item-cliente { font-size: .82rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cal-dia-item-badge { align-self: flex-start; font-size: .62rem; font-weight: 700; border-radius: 999px; padding: 1px 8px; text-transform: capitalize; }
.cal-dia-item-badge--programada { background: #fef3c7; color: #92400e; }
.cal-dia-item-badge--publicada  { background: #d1fae5; color: #065f46; }
.cal-dia-item-badge--cancelada  { background: var(--color-background-tertiary); color: var(--color-text-tertiary); }

/* ── Responsivo: celular ──────────────────────────────────────────────────────
   La cuadrícula de 7 columnas se aprieta demasiado en pantallas angostas —
   en vez de encoger el texto hasta ser ilegible, se deja un ancho mínimo por
   día y la tarjeta completa se vuelve desplazable horizontalmente. ── */
@media (max-width: 768px) {
    .cal-main .card-body { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .cal-grid { min-width: 640px; }
    .cal-toolbar { flex-wrap: wrap; gap: .6rem; }
    .cal-nav-title { min-width: 0; font-size: .95rem; }
    .cal-clientes-lista { max-height: 50vh; }
}
@media (max-width: 480px) {
    .cal-day { height: 72px; padding: .3rem; }
    .cal-dow { font-size: .62rem; }
    .cal-dia-resumen { font-size: .6rem; padding: .22rem .3rem; }
}

/* ── Panel derecho: selector de clientes ─────────────────────────────────────── */
.cal-cliente-buscar {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .5rem .7rem;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    color: var(--color-text-tertiary);
    margin-bottom: .75rem;
}
.cal-cliente-buscar input {
    border: none;
    outline: none;
    background: none;
    font-size: var(--font-size-sm);
    color: var(--color-text-primary);
    width: 100%;
}

/* ── Pestañas de filtro rápido ────────────────────────────────────────────────── */
/* 4 pestañas no caben en una sola fila dentro del panel angosto — se acomodan
   en una grilla 2x2 para que ninguna quede cortada. */
.cal-filtro-tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .4rem;
    margin-bottom: .75rem;
}
.cal-filtro-tab {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .3rem;
    padding: .4rem .3rem;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    background: var(--color-surface);
    color: var(--color-text-secondary);
    font-size: .72rem;
    font-weight: var(--font-weight-medium);
    cursor: pointer;
    transition: all .12s;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cal-filtro-tab span {
    font-size: .66rem;
    font-weight: var(--font-weight-bold);
    background: var(--color-background-tertiary);
    color: var(--color-text-secondary);
    border-radius: 999px;
    padding: 0 5px;
}
.cal-filtro-tab:hover { background: var(--color-surface-hover); }
.cal-filtro-tab.active { border-color: var(--color-brand); color: var(--color-brand); background: var(--color-brand-lighter); }
.cal-filtro-tab.active span { background: var(--color-brand); color: #fff; }
.cal-filtro-tab--pendiente.active { border-color: #f59e0b; color: #92400e; background: #fef3c7; }
.cal-filtro-tab--pendiente.active span { background: #f59e0b; color: #fff; }
.cal-filtro-tab--urgente-critico.active { border-color: #f97316; color: #c2410c; background: #ffedd5; }
.cal-filtro-tab--urgente-critico.active span { background: #f97316; color: #fff; }
.cal-filtro-tab--vencida.active { border-color: #ef4444; color: #b91c1c; background: #fee2e2; }
.cal-filtro-tab--vencida.active span { background: #ef4444; color: #fff; }

.cal-clientes-lista {
    display: flex;
    flex-direction: column;
    gap: .15rem;
    max-height: 68vh;
    overflow-y: auto;
}
.cal-cliente-item {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .55rem .6rem;
    border-radius: var(--radius-md);
    border-left: 3px solid transparent;
    text-decoration: none;
    color: var(--color-text-primary);
    transition: background-color .12s, border-color .12s;
}
.cal-cliente-item:hover { background: var(--color-surface-hover); }
.cal-cliente-item svg { color: var(--color-text-quaternary); flex-shrink: 0; }

/* Tres niveles de severidad, del más al menos grave (quedan ordenados en ese
   orden en la lista):
     vencida           (rojo)         ya pasó su vigencia sin agendarse
     urgente-critico   (naranja-rojo) no ha vencido pero ya no alcanza 1 foto/día
     pendiente         (ámbar)        sin agendar, a ritmo cómodo todavía */
.cal-cliente-item--pendiente { border-left-color: #f59e0b; background: #fffbeb; }
.cal-cliente-item--pendiente:hover { background: #fef3c7; }
.cal-cliente-item--pendiente .cal-cliente-nombre { color: #92400e; font-weight: var(--font-weight-semibold); }

.cal-cliente-item--urgente-critico { border-left-color: #f97316; background: #fff7ed; }
.cal-cliente-item--urgente-critico:hover { background: #ffedd5; }
.cal-cliente-item--urgente-critico .cal-cliente-nombre { color: #c2410c; font-weight: var(--font-weight-semibold); }

.cal-cliente-item--vencida { border-left-color: #ef4444; background: #fef2f2; }
.cal-cliente-item--vencida:hover { background: #fee2e2; }
.cal-cliente-item--vencida .cal-cliente-nombre { color: #b91c1c; font-weight: var(--font-weight-semibold); }
.cal-cliente-nombre {
    flex: 1;
    min-width: 0;
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.cal-cliente-badge {
    flex-shrink: 0;
    background: #f59e0b;
    color: #fff;
    font-size: .68rem;
    font-weight: var(--font-weight-bold);
    border-radius: 999px;
    padding: 1px 7px;
}
.cal-cliente-badge--urgente-critico { background: #f97316; }
.cal-cliente-badge--vencida { background: #ef4444; }
</style>
@endsection
