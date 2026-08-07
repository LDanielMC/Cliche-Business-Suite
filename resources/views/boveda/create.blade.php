@extends('layouts.app')

@section('title', 'Nueva Credencial')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('boveda.index') }}" class="breadcrumb-link">Bóveda</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Nueva Credencial</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Nueva Credencial</h1>
            <p class="page-subtitle">Guarda de forma segura los accesos de un cliente</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('boveda.store') }}">
                @include('boveda._form', ['edit' => false, 'credencial' => null, 'clientes' => $clientes])
            </form>
        </div>
    </div>
</div>
@endsection
