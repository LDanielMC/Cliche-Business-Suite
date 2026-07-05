@extends('layouts.app')

@section('title', 'Mis Pagos')

@section('styles')
<style>
    .clientes-container { padding: 25px; }
    .page-header { margin-bottom: 25px; }
    .page-header h1 { color: #333; font-size: 28px; }
    .table-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
    th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
    tr:hover { background: #f8f9fa; }
    .empty-state { text-align: center; padding: 50px; color: #666; }
    .pagination { margin-top: 20px; }
</style>
@endsection

@section('content')
<div class="clientes-container">
    <div class="page-header">
        <h1>Mis Pagos</h1>
    </div>

    @if($pagos->count() > 0)
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Concepto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagos as $pago)
                        <tr>
                            <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                            <td>${{ number_format($pago->monto, 2) }}</td>
                            <td>{{ $pago->metodo_pago ?? 'N/A' }}</td>
                            <td>{{ $pago->concepto ?? 'N/A' }}</td>
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
            <h3>Aún no tienes pagos registrados</h3>
        </div>
    @endif
</div>
@endsection
