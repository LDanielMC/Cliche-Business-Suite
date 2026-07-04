@extends('layouts.app')

@section('title', 'Clientes dados de baja')

@section('styles')
<style>
    .clientes-container { padding: 25px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .page-header h1 { color: #333; font-size: 28px; }
    .btn-secondary { background: #6c757d; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; }
    .btn-secondary:hover { background: #5a6268; }
    .btn-success { background: #28a745; color: white; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; white-space: nowrap; }
    .btn-success:hover { background: #218838; }
    .btn-danger { background: #dc3545; color: white; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; white-space: nowrap; }
    .btn-danger:hover { background: #c82333; }
    .table-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow-x: auto; }
    .table-wrapper { min-width: 850px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; white-space: nowrap; }
    th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
    .actions { display: flex; gap: 6px; }
    .badge-eliminado { background: #f8d7da; color: #721c24; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 500; }
    .empty-state { text-align: center; padding: 50px; color: #666; }
    .pagination { margin-top: 20px; }
</style>
@endsection

@section('content')
<div class="clientes-container">
    <div class="page-header">
        <h1>Clientes dados de baja</h1>
        <a href="{{ route('clientes.index') }}" class="btn-secondary">Volver a Clientes</a>
    </div>

    @if($clientes->count() > 0)
        <div class="table-card">
            <div class="table-wrapper">
                <table>
                <thead>
                    <tr>
                        <th>Negocio</th>
                        <th>Contacto</th>
                        <th>Email</th>
                        <th>Fecha de baja</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->nombre_negocio }}</td>
                            <td>{{ $cliente->user->name }}</td>
                            <td>{{ $cliente->user->email }}</td>
                            <td>{{ $cliente->user->fecha_baja ? $cliente->user->fecha_baja->format('d/m/Y') : 'N/A' }}</td>
                            <td><span class="badge-eliminado">Dado de baja</span></td>
                            <td class="actions">
                                <form action="{{ route('clientes.restaurar', $cliente->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-success">Restaurar</button>
                                </form>
                                <form action="{{ route('clientes.forceDelete', $cliente->id) }}" method="POST" onsubmit="return confirm('¿Eliminar permanentemente? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Eliminar Permanentemente</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <div class="pagination">
            {{ $clientes->links() }}
        </div>
    @else
        <div class="empty-state">
            <h3>No hay clientes dados de baja</h3>
        </div>
    @endif
</div>
@endsection
