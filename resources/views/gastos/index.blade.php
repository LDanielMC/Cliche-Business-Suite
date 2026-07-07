@extends('layouts.app')

@section('title', 'Gastos Operativos')

@section('styles')
<style>
    .clientes-container { padding: 25px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
    .page-header h1 { color: #333; font-size: 28px; }
    .btn-primary { background: #667eea; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; }
    .btn-primary:hover { background: #5a6fd6; }
    .btn-warning { background: #ffc107; color: #333; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; white-space: nowrap; }
    .btn-warning:hover { background: #e0a800; }
    .btn-danger { background: #dc3545; color: white; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; white-space: nowrap; }
    .btn-danger:hover { background: #c82333; }
    .filters { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; align-items: end; }
    .filters .form-group { margin-bottom: 0; }
    .filters label { display: block; margin-bottom: 6px; color: #555; font-size: 13px; font-weight: 500; }
    .filters select, .filters input { padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px; }
    .filters .btn-primary { padding: 9px 18px; }
    .table-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; white-space: nowrap; }
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
        <h1>Gastos Operativos</h1>
        <div>
            <a href="{{ route('categorias-gastos.index') }}" class="btn-primary" style="background:#6c757d; margin-right:10px;">Categorías</a>
            <a href="{{ route('gastos.create') }}" class="btn-primary">+ Nuevo Gasto</a>
        </div>
    </div>

    <form method="GET" action="{{ route('gastos.index') }}" class="filters">
        <div class="form-group">
            <label for="categoria_gasto_id">Categoría</label>
            <select id="categoria_gasto_id" name="categoria_gasto_id">
                <option value="">Todas</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected(request('categoria_gasto_id') == $categoria->id)>{{ $categoria->nombre_categoria }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="desde">Desde</label>
            <input type="date" id="desde" name="desde" value="{{ request('desde') }}">
        </div>
        <div class="form-group">
            <label for="hasta">Hasta</label>
            <input type="date" id="hasta" name="hasta" value="{{ request('hasta') }}">
        </div>
        <div class="form-group">
            <button type="submit" class="btn-primary">Filtrar</button>
        </div>
    </form>

    @if($gastos->count() > 0)
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Categoría</th>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Forma de Pago</th>
                        <th>Comprobante</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gastos as $gasto)
                        <tr>
                            <td>{{ $gasto->concepto_gasto }}</td>
                            <td>{{ $gasto->categoria->nombre_categoria }}</td>
                            <td>{{ $gasto->cliente->nombre_negocio ?? 'General' }}</td>
                            <td>${{ number_format($gasto->monto, 2) }}</td>
                            <td>{{ $gasto->fecha_gasto->format('d/m/Y') }}</td>
                            <td>{{ ucfirst($gasto->forma_pago) }}</td>
                            <td>
                                @if($gasto->comprobante)
                                    <a href="{{ $gasto->comprobante_url }}" target="_blank">Ver</a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('gastos.edit', $gasto) }}" class="btn-warning">Editar</a>
                                <form action="{{ route('gastos.destroy', $gasto) }}" method="POST" onsubmit="return confirm('¿Eliminar este gasto?');">
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
            {{ $gastos->links() }}
        </div>
    @else
        <div class="empty-state">
            <h3>No hay gastos registrados</h3>
            <p>Comienza registrando un nuevo gasto.</p>
        </div>
    @endif
</div>
@endsection
