@extends('layouts.app')

@section('title', 'Gestión de Operadores')

@section('styles')
<style>
    .operadores-container { padding: 25px; }
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
    .table-wrapper { min-width: 850px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; white-space: nowrap; }
    th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
    tr:hover { background: #f8f9fa; }
    .actions { display: flex; gap: 6px; }
    .badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 500; text-transform: capitalize; }
    .badge-activo { background: #d4edda; color: #155724; }
    .badge-inactivo { background: #fff3cd; color: #856404; }
    .badge-suspendido { background: #f8d7da; color: #721c24; }
    .empty-state { text-align: center; padding: 50px; color: #666; }
    .pagination { margin-top: 20px; }
</style>
@endsection

@section('content')
<div class="operadores-container">
    <div class="page-header">
        <h1>Gestión de Operadores</h1>
        <a href="{{ route('operadores.create') }}" class="btn-primary">+ Nuevo Operador</a>
    </div>

    @if($operadores->count() > 0)
        <div class="table-card">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Estatus</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($operadores as $operador)
                            <tr>
                                <td>{{ $operador->name }}</td>
                                <td>{{ $operador->email }}</td>
                                <td>{{ $operador->telefono ?? 'N/A' }}</td>
                                <td>
                                    @if($operador->estatus === 'activo')
                                        <span class="badge badge-activo">Activo</span>
                                    @elseif($operador->estatus === 'inactivo')
                                        <span class="badge badge-inactivo">Inactivo</span>
                                    @elseif($operador->estatus === 'suspendido')
                                        <span class="badge badge-suspendido">Suspendido</span>
                                    @endif
                                </td>
                                <td>{{ $operador->created_at->format('d/m/Y') }}</td>
                                <td class="actions">
                                    <a href="{{ route('operadores.show', $operador) }}" class="btn-secondary">Ver</a>
                                    <a href="{{ route('operadores.edit', $operador) }}" class="btn-warning">Editar</a>
                                    <form action="{{ route('operadores.destroy', $operador) }}" method="POST" onsubmit="return confirm('¿Estás seguro de dar de baja este operador?');">
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
            {{ $operadores->links() }}
        </div>
    @else
        <div class="empty-state">
            <h3>No hay operadores registrados</h3>
            <p>Comienza creando un nuevo operador.</p>
        </div>
    @endif
</div>
@endsection
