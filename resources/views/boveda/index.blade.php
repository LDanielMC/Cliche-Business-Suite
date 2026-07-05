@extends('layouts.app')

@section('title', 'Bóveda de Contraseñas')

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
    .btn-secondary-sm { background: #6c757d; color: white; padding: 4px 10px; border-radius: 6px; border: none; cursor: pointer; font-size: 12px; }
    .table-card { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; white-space: nowrap; }
    th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
    tr:hover { background: #f8f9fa; }
    .actions { display: flex; gap: 6px; }
    .password-cell { display: flex; align-items: center; gap: 8px; font-family: monospace; }
    .empty-state { text-align: center; padding: 50px; color: #666; }
    .pagination { margin-top: 20px; }
</style>
@endsection

@section('content')
<div class="clientes-container">
    <div class="page-header">
        <h1>Bóveda de Contraseñas</h1>
        <a href="{{ route('boveda.create') }}" class="btn-primary">+ Nueva Credencial</a>
    </div>

    @if($credenciales->count() > 0)
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Servicio</th>
                        <th>URL</th>
                        <th>Usuario</th>
                        <th>Contraseña</th>
                        <th>Notas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($credenciales as $credencial)
                        <tr>
                            <td>{{ $credencial->nombre_servicio }}</td>
                            <td>{{ $credencial->url ?? 'N/A' }}</td>
                            <td>{{ $credencial->usuario }}</td>
                            <td>
                                <div class="password-cell" x-data="{ visible: false }">
                                    <span x-text="visible ? @js($credencial->password) : '••••••••'"></span>
                                    <button type="button" class="btn-secondary-sm" @click="visible = !visible" x-text="visible ? 'Ocultar' : 'Mostrar'"></button>
                                </div>
                            </td>
                            <td>{{ $credencial->notas ?? 'N/A' }}</td>
                            <td class="actions">
                                <a href="{{ route('boveda.edit', $credencial) }}" class="btn-warning">Editar</a>
                                <form action="{{ route('boveda.destroy', $credencial) }}" method="POST" onsubmit="return confirm('¿Eliminar esta credencial?');">
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
            {{ $credenciales->links() }}
        </div>
    @else
        <div class="empty-state">
            <h3>No hay credenciales guardadas</h3>
            <p>Comienza guardando una nueva credencial.</p>
        </div>
    @endif
</div>
@endsection
