@extends('layouts.app')

@section('title', 'Iniciar Sesion')

@section('styles')
<style>
    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
    }
    .login-card {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        width: 100%;
        max-width: 400px;
    }
    .login-title {
        text-align: center;
        margin-bottom: 30px;
        color: #333;
    }
    .login-title h1 {
        font-size: 28px;
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
    .btn-login {
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
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    .password-wrapper {
        position: relative;
    }
    .password-wrapper .form-control {
        padding-right: 45px;
    }
    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #667eea;
        font-size: 18px;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .toggle-password:hover {
        color: #764ba2;
    }
    .info-text {
        text-align: center;
        margin-top: 20px;
        color: #666;
        font-size: 14px;
    }
</style>
@endsection

@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="login-title">
            <h1>Bienvenido</h1>
            <p>Inicia sesion para continuar</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Correo Electronico</label>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control @error('email') error @enderror"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       placeholder="tu@email.com">
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="password-wrapper">
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control"
                           required
                           placeholder="Tu contraseña">
                    <button type="button" class="toggle-password" id="togglePassword" title="Mostrar contraseña">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Iniciar Sesion
            </button>
        </form>

        <div class="info-text">
            <p><a href="{{ route('password.request') }}" style="color: #667eea; text-decoration: none;">¿Olvidaste tu contraseña?</a></p>
            <p>Sesion expira despues de 10 minutos de inactividad</p>
        </div>
    </div>
</div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    const eyeOpen = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle>';
    const eyeClosed = '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" x2="22" y1="2" y2="22"></line>';

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        eyeIcon.innerHTML = type === 'password' ? eyeOpen : eyeClosed;
        togglePassword.setAttribute('title', type === 'password' ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });
</script>
@endsection
