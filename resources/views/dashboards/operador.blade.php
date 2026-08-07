@extends('layouts.app')

@section('title', 'Panel de Operador')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Dashboard</span>
        </div>
    </div>
@endsection

@section('content')
<div class="dashboard-page">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Panel de Operador</h1>
            <p class="page-subtitle">Bienvenido, {{ $user->name }}</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('calendario.index') }}" class="btn btn-secondary">Calendario</a>
            <a href="{{ route('aprobaciones.index') }}" class="btn btn-secondary">Aprobaciones</a>
            <a href="{{ route('gastos.create') }}" class="btn btn-primary">Registrar Gasto</a>
        </div>
    </div>

    <div class="op-stats-grid">
        <a href="{{ route('aprobaciones.index') }}" class="op-stat-card">
            <div class="op-stat-icon op-stat-icon-red">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <div>
                <div class="op-stat-value">{{ $enRiesgo->count() }}</div>
                <div class="op-stat-label">En riesgo</div>
            </div>
        </a>

        <a href="{{ route('aprobaciones.index') }}" class="op-stat-card">
            <div class="op-stat-icon op-stat-icon-blue">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div>
                <div class="op-stat-value">{{ $pendientesRevisionCliente }}</div>
                <div class="op-stat-label">Esperando cliente</div>
            </div>
        </a>

        <a href="{{ route('calendario.index') }}" class="op-stat-card">
            <div class="op-stat-icon op-stat-icon-purple">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <div class="op-stat-value">{{ $proximasFotos->count() }}</div>
                <div class="op-stat-label">Por publicar</div>
            </div>
        </a>

        <a href="{{ route('gastos.index') }}" class="op-stat-card">
            <div class="op-stat-icon op-stat-icon-green">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <div class="op-stat-value">${{ number_format($gastosMes->total, 0) }}</div>
                <div class="op-stat-label">Mis gastos</div>
            </div>
        </a>
    </div>

    <div class="card" x-data="{ filtro: 'todos' }">
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem;">
            <h3 class="card-title" style="margin:0;">Qué hacer hoy</h3>
            <div class="op-chips">
                <button type="button" class="op-chip" :class="{ 'op-chip-active': filtro === 'todos' }" @click="filtro = 'todos'">
                    Todos <span class="op-chip-count">{{ $feed->count() }}</span>
                </button>
                <button type="button" class="op-chip" :class="{ 'op-chip-active': filtro === 'urgente' }" @click="filtro = 'urgente'">
                    Urgente <span class="op-chip-count">{{ $feed->where('grupo', 'urgente')->count() }}</span>
                </button>
                <button type="button" class="op-chip" :class="{ 'op-chip-active': filtro === 'proximo' }" @click="filtro = 'proximo'">
                    Próximo <span class="op-chip-count">{{ $feed->where('grupo', 'proximo')->count() }}</span>
                </button>
            </div>
        </div>
        <div class="card-body" style="padding:0;">
            @if($feed->isNotEmpty())
                <div class="op-feed">
                    @foreach($feed as $item)
                        <a href="{{ $item['accion_url'] }}" class="op-feed-item"
                           x-show="filtro === 'todos' || filtro === '{{ $item['grupo'] }}'"
                           x-transition:enter="op-feed-enter" x-transition:leave="op-feed-leave">
                            <div class="op-feed-icon {{ $item['urgente'] ? 'op-feed-icon-red' : ($item['icono'] === 'riesgo' ? 'op-feed-icon-amber' : 'op-feed-icon-purple') }}">
                                @if($item['icono'] === 'riesgo')
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                @else
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="op-feed-text">
                                <span class="op-feed-cliente">{{ $item['cliente'] }}</span>
                                <span class="op-feed-desc">{{ $item['descripcion'] }}</span>
                            </div>
                            @if($item['urgente'])
                                <span class="badge badge-error op-feed-badge">Urgente</span>
                            @endif
                            <span class="op-feed-action">{{ $item['accion_texto'] }} →</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="empty-state" style="padding:2rem;">
                    <h3 class="empty-state-title">Todo al día</h3>
                    <p class="empty-state-description">No hay paquetes en riesgo ni fotos por publicar esta semana.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.op-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: var(--space-3);
    margin-bottom: var(--space-5);
}
.op-stat-card {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: var(--space-3) var(--space-4);
    text-decoration: none;
    transition: box-shadow var(--transition-fast), transform var(--transition-fast);
}
.op-stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
.op-stat-icon {
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
.op-stat-icon-red { background: linear-gradient(135deg, #ef4444, #dc2626); }
.op-stat-icon-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.op-stat-icon-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.op-stat-icon-green { background: linear-gradient(135deg, #10b981, #059669); }
.op-stat-value { font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-text-primary); line-height: 1.1; }
.op-stat-label { font-size: var(--font-size-xs); color: var(--color-text-secondary); }

.op-chips { display: flex; gap: var(--space-2); }
.op-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border: 1px solid var(--color-border);
    background: var(--color-surface);
    color: var(--color-text-secondary);
    border-radius: var(--radius-full);
    padding: .25rem .7rem;
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    cursor: pointer;
    transition: all var(--transition-fast);
}
.op-chip:hover { border-color: var(--color-brand); color: var(--color-brand); }
.op-chip-active { background: var(--color-brand); border-color: var(--color-brand); color: white; }
.op-chip-count {
    background: rgba(0,0,0,.08);
    border-radius: var(--radius-full);
    padding: .05rem .4rem;
    font-size: .68rem;
}
.op-chip-active .op-chip-count { background: rgba(255,255,255,.25); }

.op-feed { display: flex; flex-direction: column; }
.op-feed-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-2) var(--space-5);
    text-decoration: none;
    border-bottom: 1px solid var(--color-border);
    transition: background-color var(--transition-fast);
}
.op-feed-item:last-child { border-bottom: none; }
.op-feed-item:hover { background-color: var(--color-surface-hover); }
.op-feed-icon {
    width: 26px;
    height: 26px;
    flex-shrink: 0;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
.op-feed-icon-red { background: var(--color-error); }
.op-feed-icon-amber { background: var(--color-warning, #f59e0b); }
.op-feed-icon-purple { background: #7c3aed; }
.op-feed-text { flex: 1; min-width: 0; display: flex; align-items: baseline; gap: .5rem; flex-wrap: wrap; }
.op-feed-cliente { font-weight: var(--font-weight-medium); color: var(--color-text-primary); font-size: var(--font-size-sm); }
.op-feed-desc { font-size: var(--font-size-xs); color: var(--color-text-secondary); }
.op-feed-badge { font-size: .65rem; padding: .1rem .5rem; }
.op-feed-action {
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    color: var(--color-brand);
    opacity: 0;
    transform: translateX(-4px);
    transition: all var(--transition-fast);
    white-space: nowrap;
}
.op-feed-item:hover .op-feed-action { opacity: 1; transform: translateX(0); }

.op-feed-enter { transition: opacity .2s ease, transform .2s ease; }
.op-feed-enter-start { opacity: 0; transform: translateY(-4px); }
.op-feed-enter-end { opacity: 1; transform: translateY(0); }
.op-feed-leave { transition: opacity .15s ease; }
.op-feed-leave-end { opacity: 0; }
</style>
@endsection
