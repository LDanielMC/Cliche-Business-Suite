@extends('layouts.app')

@section('title', 'Editar Publicación')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('calendario.index') }}" class="breadcrumb-link">Calendario</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Editar Publicación</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Editar Publicación</h1>
            <p class="page-subtitle">{{ $publicacion->cliente->nombre_negocio ?? '' }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('calendario.update', $publicacion) }}" enctype="multipart/form-data">
                @include('calendario._form', ['edit' => true, 'publicacion' => $publicacion, 'clientes' => $clientes])
            </form>
        </div>
    </div>
</div>
@endsection
