@extends('layouts.app')

@section('title', 'Nueva Categoría de Gasto')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('categorias-gastos.index') }}" class="breadcrumb-link">Categorías de Gastos</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Nueva Categoría</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Nueva Categoría de Gasto</h1>
            <p class="page-subtitle">Crea una categoría para clasificar gastos operativos</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('categorias-gastos.store') }}">
                @include('categorias-gastos._form', ['edit' => false, 'categoria' => null])
            </form>
        </div>
    </div>
</div>
@endsection
