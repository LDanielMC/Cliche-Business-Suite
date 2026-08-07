@extends('layouts.app')

@section('title', 'Respaldos de Base de Datos')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Respaldos</span>
        </div>
    </div>
@endsection

@section('content')
<div>
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Respaldos de Base de Datos</h1>
            <p class="page-subtitle">Genera, descarga y restaura copias de seguridad</p>
        </div>
        <div class="page-actions">
            <form action="{{ route('backups.generar') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Generar Respaldo
                </button>
            </form>
        </div>
    </div>

    <div class="flex items-start gap-3 p-4 mb-6 rounded-xl" style="background: var(--color-warning-light); color: var(--color-warning);">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="flex-shrink-0 mt-0.5">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <div>Restaurar un respaldo <strong>reemplaza por completo</strong> la información actual de la base de datos. Úsalo con precaución.</div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($archivos->count() > 0)
                <div class="table-container">
                    <table class="table">
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
                                    <td class="font-medium">{{ $archivo['nombre'] }}</td>
                                    <td>{{ number_format($archivo['tamano'] / 1024, 1) }} KB</td>
                                    <td>{{ \Carbon\Carbon::createFromTimestamp($archivo['fecha'])->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="{{ route('backups.descargar', $archivo['nombre']) }}" class="btn btn-secondary btn-sm">Descargar</a>
                                            <form action="{{ route('backups.restaurar', $archivo['nombre']) }}" method="POST" onsubmit="showConfirmModal('Restaurar respaldo', '¿Restaurar este respaldo? Se sobrescribirá toda la información actual de la base de datos.', () => this.submit(), {danger: true}); return false;">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-sm">Restaurar</button>
                                            </form>
                                            <form action="{{ route('backups.eliminar', $archivo['nombre']) }}" method="POST" onsubmit="showConfirmModal('Eliminar respaldo', '¿Eliminar este respaldo?', () => this.submit(), {danger: true}); return false;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No hay respaldos generados</h3>
                    <p class="empty-state-description">Genera tu primer respaldo de la base de datos.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
