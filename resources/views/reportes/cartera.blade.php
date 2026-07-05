@extends('layouts.app')

@section('title', 'Evolución de Cartera de Clientes')

@section('styles')
<style>
    .reporte-container { padding: 25px; max-width: 1100px; margin: 0 auto; }
    .page-header { margin-bottom: 20px; }
    .page-header h1 { color: #333; font-size: 26px; }
    .filters { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; align-items: end; }
    .filters .form-group { margin-bottom: 0; }
    .filters label { display: block; margin-bottom: 6px; color: #555; font-size: 13px; font-weight: 500; }
    .filters input { padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px; width: 120px; }
    .btn-primary { background: #667eea; color: white; padding: 9px 18px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500; }
    .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px; }
    .stat-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 20px; text-align: center; }
    .stat-card .label { color: #7d7d87; font-size: 13px; text-transform: uppercase; font-weight: 600; }
    .stat-card .value { font-size: 24px; font-weight: 700; margin-top: 8px; color: #2d2d35; }
    .card-box { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 25px; }
    @media (max-width: 640px) { .stats-row { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="reporte-container">
    <div class="page-header">
        <h1>Evolución de Cartera de Clientes</h1>
    </div>

    <form method="GET" action="{{ route('reportes.cartera') }}" class="filters">
        <div class="form-group">
            <label for="anio">Año</label>
            <input type="number" id="anio" name="anio" value="{{ $anio }}">
        </div>
        <div class="form-group">
            <button type="submit" class="btn-primary">Filtrar</button>
        </div>
    </form>

    <div class="stats-row">
        <div class="stat-card">
            <div class="label">Clientes al Inicio</div>
            <div class="value">{{ $datos['total_inicio'] }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Clientes al Cierre</div>
            <div class="value">{{ $datos['total_cierre'] }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Crecimiento Anual</div>
            <div class="value">{{ $datos['crecimiento_pct'] }}%</div>
        </div>
    </div>

    <div class="card-box">
        <canvas id="graficaCartera" height="90"></canvas>
        <div style="margin-top:20px; text-align:right;">
            <button type="button" class="btn-primary" onclick="descargarGraficaComoPng(window.graficaCartera, 'reporte-cartera.png')">Exportar PNG</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('graficaCartera');
        window.graficaCartera = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($datos['labels']),
                datasets: [{
                    label: 'Clientes acumulados',
                    data: @json($datos['acumulado']),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    tension: 0.3,
                    fill: true,
                }],
            },
            options: { responsive: true },
        });
    });
</script>
@endsection
