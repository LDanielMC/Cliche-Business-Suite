@extends('layouts.app')

@section('title', 'Editar Gasto Operativo')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('gastos.index') }}" class="breadcrumb-link">Gastos</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Editar Gasto</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Editar Gasto Operativo</h1>
            <p class="page-subtitle">{{ $gasto->concepto_gasto }}</p>
        </div>
        <div class="page-actions">
            <form action="{{ route('gastos.destroy', $gasto) }}" method="POST"
                  onsubmit="showConfirmModal('Eliminar gasto', '¿Eliminar el gasto \'{{ addslashes($gasto->concepto_gasto) }}\'? Esta acción no se puede deshacer.', () => this.submit(), {danger: true}); return false;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('gastos.update', $gasto) }}" enctype="multipart/form-data">
                @include('gastos._form', ['edit' => true, 'gasto' => $gasto, 'categorias' => $categorias, 'clientes' => $clientes])
            </form>
        </div>
    </div>
</div>
@endsection
