@extends('layouts.app')

@section('title', 'Editar Categoría de Gasto')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('categorias-gastos.index') }}" class="breadcrumb-link">Categorías de Gastos</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Editar Categoría</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Editar Categoría de Gasto</h1>
            <p class="page-subtitle">{{ $categoria->nombre }}</p>
        </div>
        <div class="page-actions">
            @if($categoria->gastos_count > 0)
                <button type="button" class="btn btn-danger" disabled
                        title="No se puede eliminar: tiene {{ $categoria->gastos_count }} gasto(s) asociado(s).">
                    Eliminar
                </button>
            @else
                <form action="{{ route('categorias-gastos.destroy', $categoria) }}" method="POST"
                      onsubmit="showConfirmModal('Eliminar categoría', '¿Eliminar la categoría \'{{ addslashes($categoria->nombre) }}\'? Esta acción no se puede deshacer.', () => this.submit(), {danger: true}); return false;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('categorias-gastos.update', $categoria) }}">
                @include('categorias-gastos._form', ['edit' => true, 'categoria' => $categoria])
            </form>
        </div>
    </div>
</div>
@endsection
