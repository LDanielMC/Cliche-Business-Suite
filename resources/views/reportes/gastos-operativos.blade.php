@extends('layouts.app')

@section('title', 'Reporte de Gastos Operativos')

@section('styles')
<style>
    .reporte-container { padding: 25px; max-width: 1100px; margin: 0 auto; }
    .page-header { margin-bottom: 20px; }
    .page-header h1 { color: #333; font-size: 26px; }
    .filters { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; align-items: end; }
    .filters .form-group { margin-bottom: 0; }
    .filters label { display: block; margin-bottom: 6px; color: #555; font-size: 13px; font-weight: 500; }
    .filters select, .filters input { padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px; }
    .btn-primary { background: #667eea; color: white; padding: 9px 18px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500; }
    .card-box { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px; }
    .grafica-wrapper { max-width: 400px; margin: 0 auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e0e0e0; }
    th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
    .empty-state { text-align: center; padding: 30px; color: #666; }
</style>
@endsection

@section('content')
<div class="reporte-container">
    <div class="page-header">
        <h1>Reporte de Gastos Operativos por Categoría</h1>
    </div>

    <form method="GET" action="{{ route('reportes.gastos') }}" class="filters">
        <div class="form-group">
            <label for="desde">Desde</label>
            <input type="date" id="desde" name="desde" value="{{ $desde->format('Y-m-d') }}">
        </div>
        <div class="form-group">
            <label for="hasta">Hasta</label>
            <input type="date" id="hasta" name="hasta" value="{{ $hasta->format('Y-m-d') }}">
        </div>
        <div class="form-group">
            <label for="categoria_gasto_id">Categoría</label>
            <select id="categoria_gasto_id" name="categoria_gasto_id">
                <option value="">Todas</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected($categoriaId == $categoria->id)>{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-primary">Filtrar</button>
        </div>
    </form>

    @if(count($filas) > 0)
        <div class="card-box">
            <div class="grafica-wrapper">
                <canvas id="graficaGastos"></canvas>
            </div>
            <div style="margin-top:20px; text-align:right;">
                <button type="button" class="btn-primary" onclick="descargarGraficaComoPng(window.graficaGastos, 'reporte-gastos.png')">Exportar PNG</button>
                <button type="button" class="btn-primary" onclick="exportarGraficaComoPdf(window.graficaGastos, 'formExportarPdfGastos')">Exportar PDF</button>
            </div>

            <form id="formExportarPdfGastos" method="POST" action="{{ route('reportes.gastos.pdf') }}" style="display:none;">
                @csrf
                <input type="hidden" name="desde" value="{{ $desde->format('Y-m-d') }}">
                <input type="hidden" name="hasta" value="{{ $hasta->format('Y-m-d') }}">
                <input type="hidden" name="categoria_gasto_id" value="{{ $categoriaId }}">
                <input type="hidden" name="chart_image">
            </form>
        </div>

        <div class="card-box">
            <table>
                <thead>
                    <tr><th>Categoría</th><th>Total</th><th>Porcentaje</th></tr>
                </thead>
                <tbody>
                    @foreach($filas as $fila)
                        <tr>
                            <td>{{ $fila['categoria'] }}</td>
                            <td>${{ number_format($fila['total'], 2) }}</td>
                            <td>{{ $fila['porcentaje'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">No hay gastos registrados en el periodo seleccionado.</div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('graficaGastos');
        if (!ctx) return;
        window.graficaGastos = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: @json(array_column($filas, 'categoria')),
                datasets: [{
                    data: @json(array_column($filas, 'total')),
                    backgroundColor: ['#7c3aed', '#14b8a6', '#f59e0b', '#dc3545', '#6366f1', '#0d9488', '#b45309', '#004085'],
                }],
            },
            options: { responsive: true },
        });
    });
</script>
@endsection
