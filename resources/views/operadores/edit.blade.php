@extends('layouts.app')

@section('title', 'Editar Operador')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('operadores.index') }}" class="breadcrumb-link">Operadores</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Editar Operador</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Editar Operador</h1>
            <p class="page-subtitle">{{ $operador->name }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('operadores.update', $operador) }}" method="POST">
                @csrf
                @method('PUT')
                @include('operadores._form')

                <div class="flex justify-end gap-3 mt-8">
                    <a href="{{ route('operadores.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
