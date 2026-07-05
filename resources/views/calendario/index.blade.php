@extends('layouts.app')

@section('title', 'Calendario de Fotos')

@section('styles')
<style>
    .calendario-container { padding: 25px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
    .page-header h1 { color: #333; font-size: 28px; }
    .btn-primary { background: #667eea; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; }
    .btn-primary:hover { background: #5a6fd6; }
    .btn-secondary { background: #6c757d; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; white-space: nowrap; }
    .btn-secondary:hover { background: #5a6268; }
    .btn-danger { background: #dc3545; color: white; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; white-space: nowrap; }
    .btn-danger:hover { background: #c82333; }
    .btn-warning { background: #ffc107; color: #333; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; white-space: nowrap; }
    .btn-warning:hover { background: #e0a800; }
    .month-nav { display: flex; align-items: center; gap: 12px; background: white; border-radius: 10px; padding: 10px 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .month-nav a { color: #667eea; text-decoration: none; font-weight: 600; }
    .month-nav span { font-weight: 600; color: #333; min-width: 160px; text-align: center; }
    .day-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 16px; overflow: hidden; }
    .day-card-header { background: #f8f9fa; padding: 12px 20px; font-weight: 600; color: #555; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 20px; text-align: left; border-bottom: 1px solid #e0e0e0; }
    th { background: #fafafa; color: #666; font-size: 13px; text-transform: uppercase; }
    tr:last-child td { border-bottom: none; }
    .actions { display: flex; gap: 6px; }
    .badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 500; text-transform: capitalize; }
    .badge-programada { background: #cce5ff; color: #004085; }
    .badge-publicada { background: #d4edda; color: #155724; }
    .badge-cancelada { background: #f8d7da; color: #721c24; }
    .empty-state { text-align: center; padding: 50px; color: #666; background: white; border-radius: 10px; }
</style>
@endsection

@section('content')
<div class="calendario-container">
    <div class="page-header">
        <h1>Calendario de Fotos</h1>
        <div class="month-nav">
            @php
                $prev = \Carbon\Carbon::create($anio, $mes, 1)->subMonth();
                $next = \Carbon\Carbon::create($anio, $mes, 1)->addMonth();
            @endphp
            <a href="{{ route('calendario.index', ['mes' => $prev->month, 'anio' => $prev->year]) }}">&larr;</a>
            <span>{{ \Carbon\Carbon::create($anio, $mes, 1)->translatedFormat('F Y') }}</span>
            <a href="{{ route('calendario.index', ['mes' => $next->month, 'anio' => $next->year]) }}">&rarr;</a>
        </div>
        <a href="{{ route('calendario.create') }}" class="btn-primary">+ Nueva Publicación</a>
    </div>

    @if($publicaciones->count() > 0)
        @foreach($publicaciones as $fecha => $items)
            <div class="day-card">
                <div class="day-card-header">{{ \Carbon\Carbon::parse($fecha)->translatedFormat('l d \d\e F') }}</div>
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $publicacion)
                            <tr>
                                <td>{{ $publicacion->cliente->nombre_negocio }}</td>
                                <td>{{ $publicacion->descripcion ?? 'N/A' }}</td>
                                <td><span class="badge badge-{{ $publicacion->estado }}">{{ $publicacion->estado }}</span></td>
                                <td class="actions">
                                    <a href="{{ route('calendario.edit', $publicacion) }}" class="btn-warning">Editar</a>
                                    <form action="{{ route('calendario.destroy', $publicacion) }}" method="POST" onsubmit="return confirm('¿Eliminar esta publicación del calendario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        <div class="empty-state">
            <h3>No hay publicaciones programadas para este mes</h3>
            <p>Agrega una nueva publicación para comenzar.</p>
        </div>
    @endif
</div>
@endsection
