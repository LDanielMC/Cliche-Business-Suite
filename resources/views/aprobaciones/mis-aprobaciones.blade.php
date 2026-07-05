@extends('layouts.app')

@section('title', 'Mis Aprobaciones de Fotos')

@section('styles')
<style>
    .clientes-container { padding: 25px; }
    .page-header { margin-bottom: 25px; }
    .page-header h1 { color: #333; font-size: 28px; }
    .btn-secondary { background: #6c757d; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; }
    .table-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
    th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
    tr:hover { background: #f8f9fa; }
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
        <h1>Mis Aprobaciones de Fotos</h1>
    </div>

    @if($paquetes->count() > 0)
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Periodo</th>
                        <th>Cuota</th>
                        <th>Fotos Disponibles</th>
                        <th>Fecha Límite</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paquetes as $paquete)
                        <tr>
                            <td>{{ $paquete->mes }}/{{ $paquete->anio }}</td>
                            <td>{{ $paquete->cantidad_requerida }}</td>
                            <td>{{ $paquete->fotos_count }}</td>
                            <td>{{ $paquete->fecha_limite->format('d/m/Y') }}</td>
                            <td><span class="badge badge-{{ $paquete->estado }}">{{ str_replace('_', ' ', $paquete->estado) }}</span></td>
                            <td>
                                <a href="{{ route('cliente.aprobaciones.show', $paquete) }}" class="btn-secondary">
                                    {{ $paquete->estado === 'pendiente' ? 'Seleccionar Fotos' : 'Ver Detalle' }}
                                </a>
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
            <h3>No tienes paquetes de aprobación pendientes</h3>
        </div>
    @endif
</div>
@endsection
