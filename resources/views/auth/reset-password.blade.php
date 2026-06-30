@extends('layouts.app')

@section('title', 'Nueva Contraseña')

@section('styles')
<style>
    .auth-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
    }
    .auth-card {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        width: 100%;
        max-width: 400px;
    }
    .auth-title {
        text-align: center;
        margin-bottom: 30px;
        color: #333;
    }
    .auth-title h1 {
        font-size: 24px;
        margin-bottom: 10px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        font-weight: 500;
    }
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s;
    }
    .form-control:focus {
        outline: none;
        border-color: #667eea;
    }
    .form-control.error {
        border-color: #dc3545;
    }
    .error-message {
        color: #dc3545;
        font-size: 14px;
        margin-top: 5px;
    }
    .btn-primary {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    .info-text {
        text-align: center;
        margin-bottom: 25px;
        color: #666;
        font-size: 14px;
    }
</style>
@endsection

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-title">
            <h1>Establecer Nueva Contraseña</h1>
        </div>

        <p class="info-text">Ingresa una nueva contraseña segura para tu cuenta.</p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="code" value="{{ $code }}">

            <div class="form-group">
                <label for="password">Nueva Contraseña</label>
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control @error('password') error @enderror"
                       required
                       autofocus
                       placeholder="Mínimo 8 caracteres">
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar Contraseña</label>
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       class="form-control"
                       required
                       placeholder="Repite tu contraseña">
            </div>

            <button type="submit" class="btn-primary">
                Actualizar Contraseña
            </button>
        </form>
    </div>
</div>
@endsection
