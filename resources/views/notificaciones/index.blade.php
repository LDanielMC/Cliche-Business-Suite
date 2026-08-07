@extends('layouts.app')

@section('title', 'Notificaciones')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Notificaciones</span>
        </div>
    </div>
@endsection

@section('content')
@php
    $iconos = [
        'foto' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'renovacion' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
        'pago' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
        'alerta' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
    ];
@endphp
<div class="max-w-3xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Notificaciones</h1>
            <p class="page-subtitle">Historial completo de avisos de tu cuenta</p>
        </div>
        <div class="page-actions">
            @if($notificaciones->contains(fn ($n) => is_null($n->read_at)))
                <form method="POST" action="{{ route('notificaciones.marcar-todas') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Marcar todas como leídas</button>
                </form>
            @endif
            @if($notificaciones->contains(fn ($n) => !is_null($n->read_at)))
                <form method="POST" action="{{ route('notificaciones.eliminar-leidas') }}"
                      onsubmit="showConfirmModal('Eliminar leídas', '¿Eliminar todas las notificaciones ya leídas?', () => this.submit(), {danger: true}); return false;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-secondary">Eliminar leídas</button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body" style="padding:0;">
            @if($notificaciones->isEmpty())
                <div class="empty-state">
                    <p class="empty-state-description">No tienes notificaciones todavía.</p>
                </div>
            @else
                <div>
                    @foreach($notificaciones as $notif)
                        <div class="notif-row">
                            <form method="POST" action="{{ route('notificaciones.leer', $notif->id) }}" class="notif-item-form">
                                @csrf
                                <button type="submit" class="notif-item {{ $notif->read_at ? '' : 'unread' }}" style="width:100%;">
                                    <span class="notif-item-icon notif-icon-{{ $notif->data['icono'] ?? 'alerta' }}">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {!! $iconos[$notif->data['icono'] ?? 'alerta'] !!}
                                        </svg>
                                    </span>
                                    <span class="notif-item-body">
                                        <span class="notif-item-title">{{ $notif->data['titulo'] ?? '' }}</span>
                                        <span class="notif-item-msg" style="-webkit-line-clamp:unset;">{{ $notif->data['mensaje'] ?? '' }}</span>
                                        <span class="notif-item-time">{{ $notif->created_at->format('d/m/Y H:i') }} &middot; {{ $notif->created_at->diffForHumans() }}</span>
                                    </span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('notificaciones.destroy', $notif->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="notif-delete-btn" title="Eliminar" aria-label="Eliminar notificación">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <div class="pagination-container" style="padding:1rem;">
                    {{ $notificaciones->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
