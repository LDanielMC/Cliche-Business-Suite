@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('clientes.index') }}" class="breadcrumb-link">Clientes</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Editar Cliente</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Editar Cliente</h1>
            <p class="page-subtitle">{{ $cliente->nombre_negocio }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('clientes.update', $cliente) }}">
                @include('clientes._form', ['edit' => true, 'cliente' => $cliente])
            </form>
        </div>
    </div>
</div>
@endsection
