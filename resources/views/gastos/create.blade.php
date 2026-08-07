@extends('layouts.app')

@section('title', 'Nuevo Gasto Operativo')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('gastos.index') }}" class="breadcrumb-link">Gastos</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Nuevo Gasto</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Nuevo Gasto Operativo</h1>
            <p class="page-subtitle">Registra un gasto del negocio</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('gastos.store') }}" enctype="multipart/form-data">
                @include('gastos._form', ['edit' => false, 'gasto' => null, 'categorias' => $categorias, 'clientes' => $clientes])
            </form>
        </div>
    </div>
</div>
@endsection
