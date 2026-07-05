@extends('layouts.app')

@section('title', 'Reporte Financiero General')

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
    .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px; }
    .stat-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 20px; text-align: center; }
    .stat-card .label { color: #7d7d87; font-size: 13px; text-transform: uppercase; font-weight: 600; }
    .stat-card .value { font-size: 24px; font-weight: 700; margin-top: 8px; }
    .value.positivo { color: #155724; }
    .value.negativo { color: #721c24; }
    .card-box { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 25px; }
    @media (max-width: 640px) { .stats-row { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="reporte-container">
    <div class="page-header">
        <h1>Reporte Financiero General</h1>
    </div>

    <form method="GET" action="{{ route('reportes.financiero') }}" class="filters">
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

    <div class="stats-row">
        <div class="stat-card">
            <div class="label">Ingresos</div>
            <div class="value positivo">${{ number_format($datos['total_ingresos'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Gastos</div>
            <div class="value negativo">${{ number_format($datos['total_gastos'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Utilidad</div>
            <div class="value {{ $datos['utilidad'] >= 0 ? 'positivo' : 'negativo' }}">${{ number_format($datos['utilidad'], 2) }}</div>
        </div>
    </div>

    <div class="card-box">
        <canvas id="graficaFinanciero" height="90"></canvas>
        <div style="margin-top:20px; text-align:right;">
            <button type="button" class="btn-primary" onclick="descargarGraficaComoPng(window.graficaFinanciero, 'reporte-financiero.png')">Exportar PNG</button>
            <button type="button" class="btn-primary" onclick="exportarGraficaComoPdf(window.graficaFinanciero, 'formExportarPdf')">Exportar PDF</button>
        </div>

        <form id="formExportarPdf" method="POST" action="{{ route('reportes.financiero.pdf') }}" style="display:none;">
            @csrf
            <input type="hidden" name="desde" value="{{ $desde->format('Y-m-d') }}">
            <input type="hidden" name="hasta" value="{{ $hasta->format('Y-m-d') }}">
            <input type="hidden" name="chart_image">
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('graficaFinanciero');
        window.graficaFinanciero = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($datos['labels']),
                datasets: [
                    { label: 'Ingresos', data: @json($datos['ingresos']), backgroundColor: '#14b8a6' },
                    { label: 'Gastos', data: @json($datos['gastos']), backgroundColor: '#dc3545' },
                ],
            },
            options: { responsive: true },
        });
    });
</script>
@endsection
