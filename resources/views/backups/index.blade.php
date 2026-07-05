@extends('layouts.app')

@section('title', 'Respaldos de Base de Datos')

@section('styles')
<style>
    .clientes-container { padding: 25px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .page-header h1 { color: #333; font-size: 28px; }
    .btn-primary { background: #667eea; color: white; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: 500; }
    .btn-primary:hover { background: #5a6fd6; }
    .btn-secondary { background: #6c757d; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; white-space: nowrap; }
    .btn-warning { background: #ffc107; color: #333; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; white-space: nowrap; }
    .btn-danger { background: #dc3545; color: white; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; white-space: nowrap; }
    .table-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; white-space: nowrap; }
    th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
    tr:hover { background: #f8f9fa; }
    .actions { display: flex; gap: 6px; }
    .empty-state { text-align: center; padding: 50px; color: #666; }
    .warning-box { background: #fff3cd; color: #856404; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; }
</style>
@endsection

@section('content')
<div class="clientes-container">
    <div class="page-header">
        <h1>Respaldos de Base de Datos</h1>
        <form action="{{ route('backups.generar') }}" method="POST">
            @csrf
            <button type="submit" class="btn-primary">Generar Respaldo</button>
        </form>
    </div>

    <div class="warning-box">
        Restaurar un respaldo <strong>reemplaza por completo</strong> la información actual de la base de datos. Úsalo con precaución.
    </div>

    @if($archivos->count() > 0)
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Tamaño</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($archivos as $archivo)
                        <tr>
                            <td>{{ $archivo['nombre'] }}</td>
                            <td>{{ number_format($archivo['tamano'] / 1024, 1) }} KB</td>
                            <td>{{ \Carbon\Carbon::createFromTimestamp($archivo['fecha'])->format('d/m/Y H:i') }}</td>
                            <td class="actions">
                                <a href="{{ route('backups.descargar', $archivo['nombre']) }}" class="btn-secondary">Descargar</a>
                                <form action="{{ route('backups.restaurar', $archivo['nombre']) }}" method="POST" onsubmit="return confirm('¿Restaurar este respaldo? Se sobrescribirá toda la información actual de la base de datos.');">
                                    @csrf
                                    <button type="submit" class="btn-warning">Restaurar</button>
                                </form>
                                <form action="{{ route('backups.eliminar', $archivo['nombre']) }}" method="POST" onsubmit="return confirm('¿Eliminar este respaldo?');">
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
    @else
        <div class="empty-state">
            <h3>No hay respaldos generados</h3>
            <p>Genera tu primer respaldo de la base de datos.</p>
        </div>
    @endif
</div>
@endsection
