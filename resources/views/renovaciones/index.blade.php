@extends('layouts.app')

@section('title', 'Control de Renovaciones')

@section('styles')
<style>
    .clientes-container { padding: 25px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .page-header h1 { color: #333; font-size: 28px; }
    .btn-primary { background: #667eea; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; border: none; cursor: pointer; }
    .btn-primary:hover { background: #5a6fd6; }
    .table-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; white-space: nowrap; }
    th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
    tr:hover { background: #f8f9fa; }
    .badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 500; text-transform: capitalize; }
    .badge-vigente { background: #d4edda; color: #155724; }
    .badge-por_vencer { background: #fff3cd; color: #856404; }
    .badge-vencido { background: #f8d7da; color: #721c24; }
    .renovar-form { display: flex; gap: 6px; align-items: center; }
    .renovar-form input { width: 55px; padding: 6px; border: 2px solid #e0e0e0; border-radius: 6px; }
    .empty-state { text-align: center; padding: 50px; color: #666; }
    .pagination { margin-top: 20px; }
</style>
@endsection

@section('content')
<div class="clientes-container">
    <div class="page-header">
        <h1>Control de Renovaciones</h1>
        <a href="{{ route('renovaciones.create') }}" class="btn-primary">+ Nuevo Ciclo</a>
    </div>

    @if($renovaciones->count() > 0)
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Inicio</th>
                        <th>Vencimiento</th>
                        <th>Recordatorio</th>
                        <th>Estatus</th>
                        <th>Renovar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($renovaciones as $renovacion)
                        <tr>
                            <td>{{ $renovacion->cliente->nombre_negocio }}</td>
                            <td>{{ $renovacion->fecha_inicio->format('d/m/Y') }}</td>
                            <td>{{ $renovacion->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td>{{ $renovacion->fecha_recordatorio?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td><span class="badge badge-{{ $renovacion->estatus }}">{{ str_replace('_', ' ', $renovacion->estatus) }}</span></td>
                            <td>
                                <form action="{{ route('renovaciones.renovar', $renovacion) }}" method="POST" class="renovar-form" onsubmit="return confirm('¿Registrar la renovación de este cliente?');">
                                    @csrf
                                    <input type="number" name="meses" min="1" max="12" value="1" title="Meses a renovar">
                                    <button type="submit" class="btn-primary" style="padding:6px 14px; font-size:13px;">Renovar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $renovaciones->links() }}
        </div>
    @else
        <div class="empty-state">
            <h3>No hay ciclos de renovación registrados</h3>
            <p>Comienza registrando un nuevo ciclo para un cliente.</p>
        </div>
    @endif
</div>
@endsection
