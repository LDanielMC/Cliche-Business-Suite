@extends('layouts.app')

@section('title', 'Nuevo Operador')

@section('styles')
<style>
    .form-container { max-width: 700px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .form-header { margin-bottom: 25px; }
    .form-header h1 { color: #333; font-size: 28px; }
    .form-group { margin-bottom: 20px; }
    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
    label { display: block; margin-bottom: 6px; color: #555; font-weight: 500; }
    input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
    input:focus, select:focus { outline: none; border-color: #667eea; }
    .error { color: #dc3545; font-size: 12px; margin-top: 4px; display: block; }
    .actions { display: flex; gap: 10px; margin-top: 25px; }
    .btn-primary { background: #667eea; color: white; padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500; }
    .btn-primary:hover { background: #5a6fd6; }
    .btn-secondary { background: #6c757d; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; }
    .btn-secondary:hover { background: #5a6268; }
</style>
@endsection

@section('content')
<div class="form-container">
    <div class="form-header">
        <h1>Nuevo Operador</h1>
    </div>

    <form action="{{ route('operadores.store') }}" method="POST">
        @csrf
        @include('operadores._form')

        <div class="actions">
            <button type="submit" class="btn-primary">Guardar</button>
            <a href="{{ route('operadores.index') }}" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
