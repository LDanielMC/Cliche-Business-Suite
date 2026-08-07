@extends('layouts.app')

@section('title', 'Nuevo Operador')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('operadores.index') }}" class="breadcrumb-link">Operadores</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Nuevo Operador</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Nuevo Operador</h1>
            <p class="page-subtitle">Registra un nuevo operador del sistema</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('operadores.store') }}" method="POST">
                @csrf
                @include('operadores._form')

                <div class="flex justify-end gap-3 mt-8">
                    <a href="{{ route('operadores.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
