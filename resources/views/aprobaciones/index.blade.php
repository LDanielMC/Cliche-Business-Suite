@extends('layouts.app')

@section('title', 'Paquetes de Aprobación')

@section('styles')
<style>
    .clientes-container { padding: 25px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .page-header h1 { color: #333; font-size: 28px; }
    .btn-primary { background: #667eea; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; }
    .btn-primary:hover { background: #5a6fd6; }
    .btn-secondary { background: #6c757d; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; white-space: nowrap; }
    .btn-danger { background: #dc3545; color: white; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; white-space: nowrap; }
    .table-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; white-space: nowrap; }
    th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
    tr:hover { background: #f8f9fa; }
    .actions { display: flex; gap: 6px; }
    .badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 500; text-transform: capitalize; }
    .badge-pendiente { background: #cce5ff; color: #004085; }
    .badge-completado { background: #d4edda; color: #155724; }
    .badge-auto_aprobado { background: #fff3cd; color: #856404; }
    .empty-state { text-align: center; padding: 50px; color: #666; }
    .pagination { margin-top: 20px; }
</style>
@endsection

@section('content')
<div class="clientes-container">
    <div class="page-header">
        <h1>Paquetes de Aprobación de Fotografía</h1>
        <a href="{{ route('aprobaciones.create') }}" class="btn-primary">+ Nuevo Paquete</a>
    </div>

    @if($paquetes->count() > 0)
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Periodo</th>
                        <th>Cuota</th>
                        <th>Fotos Cargadas</th>
                        <th>Fecha Límite</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paquetes as $paquete)
                        <tr>
                            <td>{{ $paquete->cliente->nombre_negocio }}</td>
                            <td>{{ $paquete->mes }}/{{ $paquete->anio }}</td>
                            <td>{{ $paquete->cantidad_requerida }}</td>
                            <td>{{ $paquete->fotos_count }}</td>
                            <td>{{ $paquete->fecha_limite->format('d/m/Y') }}</td>
                            <td><span class="badge badge-{{ $paquete->estado }}">{{ str_replace('_', ' ', $paquete->estado) }}</span></td>
                            <td class="actions">
                                <a href="{{ route('aprobaciones.show', $paquete) }}" class="btn-secondary">Ver / Subir Fotos</a>
                                <form action="{{ route('aprobaciones.destroy', $paquete) }}" method="POST" onsubmit="return confirm('¿Eliminar este paquete y sus fotografías?');">
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

        <div class="pagination">
            {{ $paquetes->links() }}
        </div>
    @else
        <div class="empty-state">
            <h3>No hay paquetes de aprobación creados</h3>
            <p>Comienza creando uno nuevo para un cliente.</p>
        </div>
    @endif
</div>
@endsection
