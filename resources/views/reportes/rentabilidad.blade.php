@extends('layouts.app')

@section('title', 'Reporte de Clientes Más Rentables')

@section('styles')
<style>
    .reporte-container { padding: 25px; max-width: 1100px; margin: 0 auto; }
    .page-header { margin-bottom: 20px; }
    .page-header h1 { color: #333; font-size: 26px; }
    .filters { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; align-items: end; }
    .filters .form-group { margin-bottom: 0; }
    .filters label { display: block; margin-bottom: 6px; color: #555; font-size: 13px; font-weight: 500; }
    .filters input { padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px; }
    .btn-primary { background: #667eea; color: white; padding: 9px 18px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500; }
    .card-box { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e0e0e0; }
    th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
    .positivo { color: #155724; font-weight: 600; }
    .negativo { color: #721c24; font-weight: 600; }
    .empty-state { text-align: center; padding: 30px; color: #666; }
</style>
@endsection

@section('content')
<div class="reporte-container">
    <div class="page-header">
        <h1>Reporte de Clientes Más Rentables</h1>
    </div>

    <form method="GET" action="{{ route('reportes.rentabilidad') }}" class="filters">
        <div class="form-group">
            <label for="desde">Desde</label>
            <input type="date" id="desde" name="desde" value="{{ $desde->format('Y-m-d') }}">
        </div>
        <div class="form-group">
            <label for="hasta">Hasta</label>
            <input type="date" id="hasta" name="hasta" value="{{ $hasta->format('Y-m-d') }}">
        </div>
        <div class="form-group">
            <button type="submit" class="btn-primary">Filtrar</button>
        </div>
    </form>

    @if(count($filas) > 0)
        <div class="card-box">
            <canvas id="graficaRentabilidad" height="90"></canvas>
            <div style="margin-top:20px; text-align:right;">
                <button type="button" class="btn-primary" onclick="descargarGraficaComoPng(window.graficaRentabilidad, 'reporte-rentabilidad.png')">Exportar PNG</button>
            </div>
        </div>

        <div class="card-box">
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Ingresos</th>
                        <th>Gastos Directos</th>
                        <th>Gastos Generales (prorrateo)</th>
                        <th>Rentabilidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filas as $fila)
                        <tr>
                            <td>{{ $fila['cliente']->nombre_negocio }}</td>
                            <td>${{ number_format($fila['ingresos'], 2) }}</td>
                            <td>${{ number_format($fila['gastos_directos'], 2) }}</td>
                            <td>${{ number_format($fila['gastos_generales'], 2) }}</td>
                            <td class="{{ $fila['rentabilidad'] >= 0 ? 'positivo' : 'negativo' }}">${{ number_format($fila['rentabilidad'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">No hay clientes activos para calcular rentabilidad.</div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('graficaRentabilidad');
        if (!ctx) return;
        window.graficaRentabilidad = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json(array_map(fn($f) => $f['cliente']->nombre_negocio, $filas)),
                datasets: [{ label: 'Rentabilidad', data: @json(array_map(fn($f) => $f['rentabilidad'], $filas)), backgroundColor: '#7c3aed' }],
            },
            options: { responsive: true },
        });
    });
</script>
@endsection
