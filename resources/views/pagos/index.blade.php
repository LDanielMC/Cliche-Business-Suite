@extends('layouts.app')

@section('title', 'Pagos de Clientes')

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
    .table-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; white-space: nowrap; }
    th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
    tr:hover { background: #f8f9fa; }
    .actions { display: flex; gap: 6px; }
    .badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 500; text-transform: capitalize; }
    .badge-pagado { background: #d4edda; color: #155724; }
    .badge-pendiente { background: #fff3cd; color: #856404; }
    .badge-vencido { background: #f8d7da; color: #721c24; }
    .empty-state { text-align: center; padding: 50px; color: #666; }
    .pagination { margin-top: 20px; }
</style>
@endsection

@section('content')
<div class="clientes-container">
    <div class="page-header">
        <h1>Pagos de Clientes</h1>
        <a href="{{ route('pagos.create') }}" class="btn-primary">+ Nuevo Pago</a>
    </div>

    <form method="GET" action="{{ route('pagos.index') }}" class="filters">
        <div class="form-group">
            <label for="cliente_id">Cliente</label>
            <select id="cliente_id" name="cliente_id">
                <option value="">Todos</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" @selected(request('cliente_id') == $cliente->id)>{{ $cliente->nombre_negocio }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="estatus">Estatus</label>
            <select id="estatus" name="estatus">
                <option value="">Todos</option>
                @foreach(\App\Models\PagoCliente::ESTATUS as $estatusOpcion)
                    <option value="{{ $estatusOpcion }}" @selected(request('estatus') == $estatusOpcion)>{{ ucfirst($estatusOpcion) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="desde">Vencimiento desde</label>
            <input type="date" id="desde" name="desde" value="{{ request('desde') }}">
        </div>
        <div class="form-group">
            <label for="hasta">Vencimiento hasta</label>
            <input type="date" id="hasta" name="hasta" value="{{ request('hasta') }}">
        </div>
        <div class="form-group">
            <button type="submit" class="btn-primary">Filtrar</button>
        </div>
    </form>

    @if($pagos->count() > 0)
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Periodo</th>
                        <th>Vencimiento</th>
                        <th>Fecha de Pago</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagos as $pago)
                        <tr>
                            <td>{{ $pago->cliente->nombre_negocio }}</td>
                            <td>{{ $pago->concepto_servicio }}</td>
                            <td>${{ number_format($pago->monto, 2) }}</td>
                            <td>{{ $pago->periodo_facturado }}</td>
                            <td>{{ $pago->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td>{{ $pago->fecha_pago?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td><span class="badge badge-{{ $pago->estatus }}">{{ $pago->estatus }}</span></td>
                            <td class="actions">
                                <a href="{{ route('pagos.edit', $pago) }}" class="btn-warning">Editar</a>
                                <form action="{{ route('pagos.destroy', $pago) }}" method="POST" onsubmit="return confirm('¿Eliminar este pago?');">
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
            {{ $pagos->links() }}
        </div>
    @else
        <div class="empty-state">
            <h3>No hay pagos registrados</h3>
            <p>Comienza registrando un nuevo pago.</p>
        </div>
    @endif
</div>
@endsection
