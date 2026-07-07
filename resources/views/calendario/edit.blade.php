@extends('layouts.app')

@section('title', 'Editar Publicación')

@section('styles')
<style>
    .form-container { max-width: 800px; margin: 0 auto; padding: 25px; }
    .form-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .form-card h1 { color: #333; margin-bottom: 25px; font-size: 24px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; color: #555; font-weight: 500; }
    .form-control { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; }
    .form-control:focus { outline: none; border-color: #667eea; }
    .form-control.error { border-color: #dc3545; }
    .error-message { color: #dc3545; font-size: 13px; margin-top: 5px; }
    .form-actions { display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; }
    .btn { padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: 500; border: none; cursor: pointer; font-size: 15px; }
    .btn-primary { background: #667eea; color: white; }
    .btn-primary:hover { background: #5a6fd6; }
    .btn-secondary { background: #6c757d; color: white; }
    .btn-secondary:hover { background: #5a6268; }
    @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="form-container">
    <div class="form-card">
        <h1>Editar Publicación</h1>

        <form method="POST" action="{{ route('calendario.update', $publicacion) }}" enctype="multipart/form-data">
            @include('calendario._form', ['edit' => true, 'publicacion' => $publicacion, 'clientes' => $clientes])
        </form>
    </div>
</div>
@endsection
