@extends('layouts.app')

@section('title', 'Gestión de Clientes')

@section('styles')
<style>
    .clientes-container { padding: 25px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .page-header h1 { color: #333; font-size: 28px; }
    .btn-primary { background: #667eea; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; }
    .btn-primary:hover { background: #5a6fd6; }
    .btn-secondary { background: #6c757d; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; white-space: nowrap; }
    .btn-secondary:hover { background: #5a6268; }
    .btn-danger { background: #dc3545; color: white; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; white-space: nowrap; }
    .btn-danger:hover { background: #c82333; }
    .btn-warning { background: #ffc107; color: #333; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; white-space: nowrap; }
    .btn-warning:hover { background: #e0a800; }
    .table-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow-x: auto; }
    .table-wrapper { min-width: 950px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; white-space: nowrap; }
    th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
    tr:hover { background: #f8f9fa; }
    .actions { display: flex; gap: 6px; }
    .badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 500; text-transform: capitalize; }
    .badge-activo { background: #d4edda; color: #155724; }
    .badge-inactivo { background: #fff3cd; color: #856404; }
    .badge-suspendido { background: #f8d7da; color: #721c24; }
    .badge-baja { background: #f8d7da; color: #721c24; }
    .empty-state { text-align: center; padding: 50px; color: #666; }
    .pagination { margin-top: 20px; }
</style>
@endsection

@section('content')
<div class="clientes-container">
    <div class="page-header">
        <h1>Gestión de Clientes</h1>
        <div>
            <a href="{{ route('clientes.eliminados') }}" class="btn-secondary" style="margin-right: 10px;">Clientes dados de baja</a>
            <a href="{{ route('clientes.create') }}" class="btn-primary">+ Nuevo Cliente</a>
        </div>
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
                        <th>Servicio</th>
                        <th>Fotos</th>
                        <th>Precio Mensual</th>
                        <th>Registro</th>
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
                            <td>{{ $cliente->servicio_contratado ?? 'N/A' }}</td>
                            <td>{{ $cliente->cantidad_fotos }}</td>
                            <td>${{ number_format($cliente->precio_mensual, 2) }}</td>
                            <td>{{ $cliente->fecha_registro->format('d/m/Y') }}</td>
                            <td>
                                @if($cliente->user->estatus === 'activo')
                                    <span class="badge badge-activo">Activo</span>
                                @elseif($cliente->user->estatus === 'inactivo')
                                    <span class="badge badge-inactivo">Inactivo</span>
                                @elseif($cliente->user->estatus === 'suspendido')
                                    <span class="badge badge-suspendido">Suspendido</span>
                                @elseif($cliente->user->estatus === 'dado_de_baja')
                                    <span class="badge badge-baja">Dado de baja</span>
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('clientes.show', $cliente) }}" class="btn-secondary">Ver</a>
                                <a href="{{ route('clientes.edit', $cliente) }}" class="btn-warning">Editar</a>
                                <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" onsubmit="return confirm('¿Estás seguro de dar de baja este cliente?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Dar de baja</button>
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
            <h3>No hay clientes registrados</h3>
            <p>Comienza creando un nuevo cliente.</p>
        </div>
    @endif
</div>
@endsection
