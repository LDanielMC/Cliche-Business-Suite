@extends('layouts.app')

@section('title', 'Categorías de Gastos')

@section('styles')
<style>
    .clientes-container { padding: 25px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .page-header h1 { color: #333; font-size: 28px; }
    .btn-primary { background: #667eea; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; }
    .btn-primary:hover { background: #5a6fd6; }
    .btn-warning { background: #ffc107; color: #333; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; white-space: nowrap; }
    .btn-warning:hover { background: #e0a800; }
    .btn-danger { background: #dc3545; color: white; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; white-space: nowrap; }
    .btn-danger:hover { background: #c82333; }
    .table-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
    th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
    tr:hover { background: #f8f9fa; }
    .actions { display: flex; gap: 6px; }
    .empty-state { text-align: center; padding: 50px; color: #666; }
    .pagination { margin-top: 20px; }
</style>
@endsection

@section('content')
<div class="clientes-container">
    <div class="page-header">
        <h1>Categorías de Gastos</h1>
        <a href="{{ route('categorias-gastos.create') }}" class="btn-primary">+ Nueva Categoría</a>
    </div>

    @if($categorias->count() > 0)
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Gastos Registrados</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categorias as $categoria)
                        <tr>
                            <td>{{ $categoria->nombre_categoria }}</td>
                            <td>{{ $categoria->gastos_count }}</td>
                            <td class="actions">
                                <a href="{{ route('categorias-gastos.edit', $categoria) }}" class="btn-warning">Editar</a>
                                <form action="{{ route('categorias-gastos.destroy', $categoria) }}" method="POST" onsubmit="return confirm('¿Eliminar esta categoría?');">
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
            {{ $categorias->links() }}
        </div>
    @else
        <div class="empty-state">
            <h3>No hay categorías de gastos registradas</h3>
            <p>Comienza creando una nueva categoría.</p>
        </div>
    @endif
</div>
@endsection
