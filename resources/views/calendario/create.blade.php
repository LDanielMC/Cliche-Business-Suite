@extends('layouts.app')

@section('title', 'Nueva Publicación')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('calendario.index') }}" class="breadcrumb-link">Calendario</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Nueva Publicación</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Nueva Publicación</h1>
            <p class="page-subtitle">Programa una publicación en el calendario</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('calendario.store') }}" enctype="multipart/form-data">
                @include('calendario._form', ['edit' => false, 'publicacion' => null, 'clientes' => $clientes])
            </form>
        </div>
    </div>
</div>
@endsection
