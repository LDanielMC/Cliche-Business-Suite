@extends('layouts.app')

@section('title', 'Verificar Código')

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
        text-align: center;
        letter-spacing: 8px;
        font-size: 24px;
        font-weight: bold;
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
        text-align: center;
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
    .back-link {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #667eea;
        text-decoration: none;
        font-size: 14px;
    }
    .back-link:hover {
        text-decoration: underline;
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
            <h1>Verificar Código</h1>
        </div>

        <p class="info-text">Hemos enviado un código de 6 dígitos a <strong>{{ $email }}</strong>. El código expira en 15 minutos.</p>

        <form method="POST" action="{{ route('password.verify.post') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="form-group">
                <label for="code">Código de Verificación</label>
                <input type="text"
                       id="code"
                       name="code"
                       class="form-control @error('code') error @enderror"
                       maxlength="6"
                       inputmode="numeric"
                       pattern="[0-9]{6}"
                       required
                       autofocus
                       placeholder="000000">
                @error('code')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-primary">
                Verificar Código
            </button>
        </form>

        <a href="{{ route('password.request') }}" class="back-link">Solicitar un nuevo código</a>
    </div>
</div>
@endsection
