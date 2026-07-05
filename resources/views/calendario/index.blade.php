@extends('layouts.app')

@section('title', 'Calendario de Fotos')

@section('styles')
<style>
    .calendario-container { padding: 25px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
    .page-header h1 { color: #333; font-size: 28px; }
    .btn-primary { background: #667eea; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; }
    .btn-primary:hover { background: #5a6fd6; }
    .month-nav { display: flex; align-items: center; gap: 12px; background: white; border-radius: 10px; padding: 10px 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .month-nav a { color: #667eea; text-decoration: none; font-weight: 600; font-size: 18px; }
    .month-nav span { font-weight: 600; color: #333; min-width: 160px; text-align: center; text-transform: capitalize; }

    .calendar-scroll { overflow-x: auto; }
    .calendar-grid { width: 100%; min-width: 900px; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .calendar-grid th { background: #667eea; color: white; padding: 10px; font-size: 13px; text-transform: uppercase; text-align: center; }
    .calendar-grid td { border: 1px solid #eee; vertical-align: top; height: 130px; width: 14.28%; padding: 6px; }
    .calendar-grid td.fuera-de-mes { background: #fafafa; }
    .calendar-grid td.hoy { background: #f0f4ff; }
    .day-number { font-weight: 600; color: #555; font-size: 13px; margin-bottom: 6px; }
    .fuera-de-mes .day-number { color: #ccc; }
    .entry { display: flex; gap: 6px; align-items: center; background: #f8f9fa; border-radius: 6px; padding: 3px 6px; margin-bottom: 4px; text-decoration: none; color: #333; }
    .entry img { width: 24px; height: 24px; object-fit: cover; border-radius: 4px; flex-shrink: 0; }
    .entry-text { font-size: 11px; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .entry.badge-programada { border-left: 3px solid #004085; }
    .entry.badge-publicada { border-left: 3px solid #155724; }
    .entry.badge-cancelada { border-left: 3px solid #721c24; text-decoration: line-through; opacity: 0.6; }
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

    <div class="calendar-scroll">
        <table class="calendar-grid">
            <thead>
                <tr>
                    <th>Lunes</th>
                    <th>Martes</th>
                    <th>Miércoles</th>
                    <th>Jueves</th>
                    <th>Viernes</th>
                    <th>Sábado</th>
                    <th>Domingo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($semanas as $semana)
                    <tr>
                        @foreach($semana as $dia)
                            @php
                                $clave = $dia['fecha']->format('Y-m-d');
                                $entradas = $publicaciones->get($clave, collect());
                            @endphp
                            <td class="{{ $dia['delMes'] ? '' : 'fuera-de-mes' }} {{ $dia['fecha']->isToday() ? 'hoy' : '' }}">
                                <div class="day-number">{{ $dia['fecha']->day }}</div>
                                @foreach($entradas as $entrada)
                                    <a href="{{ route('calendario.edit', $entrada) }}" class="entry badge-{{ $entrada->estatus }}" title="{{ $entrada->cliente->nombre_negocio }} — {{ $entrada->fecha_publicacion_programada->format('H:i') }}">
                                        <img src="{{ $entrada->fotografia_url }}" alt="">
                                        <span class="entry-text">{{ $entrada->fecha_publicacion_programada->format('H:i') }} {{ $entrada->cliente->nombre_negocio }}</span>
                                    </a>
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
