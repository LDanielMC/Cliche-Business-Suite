<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión · Cliche Business Suite</title>
    
    <!-- Preconnect to fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Vite Assets -->
    @vite([
        'resources/css/modern-design-system.css',
        'resources/css/modern-navigation.css',
        'resources/css/modern-components.css',
        'resources/js/app.js',
        'resources/js/modern-ux-system.js'
    ])
    
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">Saltar al contenido principal</a>
</head>
<body class="h-full bg-background text-text-primary">

<style>
    .login-page {
        position: fixed;
        inset: 0;
        display: grid;
        place-items: center;
        overflow: hidden;
        padding: 24px;
        background: linear-gradient(135deg, #f8f7ff 0%, #f0fdfa 50%, #f5f3ff 100%);
        font-family: 'Raleway', sans-serif;
    }

    .login-page::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at 20% 30%, rgba(139, 92, 246, 0.08) 0%, transparent 50%),
                    radial-gradient(ellipse at 80% 70%, rgba(20, 184, 166, 0.07) 0%, transparent 50%);
        pointer-events: none;
    }

    /* Mouse spotlight effect */
    .mouse-spotlight {
        position: absolute;
        inset: 0;
        z-index: 2;
        pointer-events: none;
        background: radial-gradient(600px circle at var(--mouse-x, 50%) var(--mouse-y, 50%),
            rgba(139, 92, 246, 0.08),
            rgba(20, 184, 166, 0.04),
            transparent 60%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .login-page:hover .mouse-spotlight {
        opacity: 1;
    }

    /* Animated mesh gradient background */
    .login-mesh {
        position: absolute;
        inset: -50px;
        z-index: 0;
        overflow: hidden;
        opacity: 0.9;
    }

    .mesh-blob {
        position: absolute;
        border-radius: 50%;
        filter: blur(100px);
        animation: meshMove 20s ease-in-out infinite;
        transition: transform 0.3s ease-out;
    }

    .mesh-blob:nth-child(1) {
        width: 600px;
        height: 600px;
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.16) 0%, rgba(139, 92, 246, 0.1) 100%);
        top: -180px;
        right: -140px;
        animation-delay: 0s;
    }

    .mesh-blob:nth-child(2) {
        width: 500px;
        height: 500px;
        background: linear-gradient(135deg, rgba(20, 184, 166, 0.14) 0%, rgba(45, 212, 191, 0.08) 100%);
        bottom: -180px;
        left: -140px;
        animation-delay: -7s;
    }

    .mesh-blob:nth-child(3) {
        width: 400px;
        height: 400px;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.12) 0%, rgba(139, 92, 246, 0.07) 100%);
        top: 40%;
        left: 50%;
        animation-delay: -14s;
    }

    @keyframes meshMove {
        0%, 100% { transform: translate(0, 0) scale(1) rotate(0deg); }
        25% { transform: translate(40px, -50px) scale(1.1) rotate(5deg); }
        50% { transform: translate(-30px, 30px) scale(0.95) rotate(-3deg); }
        75% { transform: translate(50px, 40px) scale(1.05) rotate(2deg); }
    }

    /* Floating particles */
    .particles {
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
    }

    .particle {
        position: absolute;
        width: 6px;
        height: 6px;
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.4), rgba(20, 184, 166, 0.3));
        border-radius: 50%;
        opacity: 0;
        animation: particleFloat 18s linear infinite;
        box-shadow: 0 0 12px rgba(139, 92, 246, 0.25);
        transition: transform 0.2s ease-out;
    }

    .particle:nth-child(1) { top: 8%; left: 6%; animation-delay: 0s; animation-duration: 16s; }
    .particle:nth-child(2) { top: 18%; left: 92%; animation-delay: -3s; animation-duration: 20s; }
    .particle:nth-child(3) { top: 72%; left: 12%; animation-delay: -6s; animation-duration: 17s; }
    .particle:nth-child(4) { top: 82%; left: 82%; animation-delay: -9s; animation-duration: 22s; }
    .particle:nth-child(5) { top: 38%; left: 46%; animation-delay: -5s; animation-duration: 19s; }
    .particle:nth-child(6) { top: 88%; left: 38%; animation-delay: -11s; animation-duration: 15s; }
    .particle:nth-child(7) { top: 55%; left: 75%; animation-delay: -7s; animation-duration: 21s; }
    .particle:nth-child(8) { top: 28%; left: 22%; animation-delay: -13s; animation-duration: 18s; }
    .particle:nth-child(9) { top: 65%; left: 58%; animation-delay: -2s; animation-duration: 23s; }
    .particle:nth-child(10) { top: 45%; left: 8%; animation-delay: -15s; animation-duration: 20s; }
    .particle:nth-child(11) { top: 12%; left: 62%; animation-delay: -8s; animation-duration: 17s; }
    .particle:nth-child(12) { top: 92%; left: 68%; animation-delay: -4s; animation-duration: 19s; }

    @keyframes particleFloat {
        0% { transform: translateY(0) translateX(0); opacity: 0; }
        10% { opacity: 0.45; }
        25% { transform: translateY(-50px) translateX(30px); opacity: 0.65; }
        50% { transform: translateY(-100px) translateX(-20px); opacity: 0.5; }
        75% { transform: translateY(-50px) translateX(-40px); opacity: 0.6; }
        90% { opacity: 0.45; }
        100% { transform: translateY(0) translateX(0); opacity: 0; }
    }

    /* Floating abstract shapes */
    .floating-shapes {
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
        overflow: hidden;
    }

    .shape {
        position: absolute;
        opacity: 0.12;
        color: #7c3aed;
        animation: shapeFloat 30s ease-in-out infinite;
        transition: transform 0.3s ease-out;
    }

    .shape:nth-child(1) {
        top: 15%;
        left: 10%;
        width: 80px;
        height: 80px;
        animation-delay: 0s;
        animation-duration: 28s;
    }

    .shape:nth-child(2) {
        top: 75%;
        right: 8%;
        width: 60px;
        height: 60px;
        animation-delay: -7s;
        animation-duration: 32s;
        color: #14b8a6;
    }

    .shape:nth-child(3) {
        top: 45%;
        left: 5%;
        width: 40px;
        height: 40px;
        animation-delay: -14s;
        animation-duration: 26s;
    }

    .shape:nth-child(4) {
        top: 25%;
        right: 15%;
        width: 50px;
        height: 50px;
        animation-delay: -21s;
        animation-duration: 24s;
        color: #14b8a6;
    }

    .shape:nth-child(5) {
        bottom: 20%;
        left: 18%;
        width: 35px;
        height: 35px;
        animation-delay: -10s;
        animation-duration: 29s;
    }

    @keyframes shapeFloat {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        25% { transform: translate(20px, -30px) rotate(90deg); }
        50% { transform: translate(-10px, 20px) rotate(180deg); }
        75% { transform: translate(30px, 10px) rotate(270deg); }
    }

    /* Connection lines */
    .network-lines {
        position: absolute;
        inset: 0;
        z-index: 1;
        opacity: 0.04;
        background-image:
            linear-gradient(to right, rgba(124, 58, 237, 0.5) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(124, 58, 237, 0.5) 1px, transparent 1px);
        background-size: 80px 80px;
        animation: gridMove 50s linear infinite;
        transition: transform 0.3s ease-out;
    }

    @keyframes gridMove {
        0% { transform: perspective(500px) rotateX(60deg) translateY(0); }
        100% { transform: perspective(500px) rotateX(60deg) translateY(80px); }
    }

    .login-card {
        position: relative;
        z-index: 10;
        width: 100%;
        max-width: 440px;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(255, 255, 255, 0.85);
        border-radius: 32px;
        padding: 56px 48px;
        backdrop-filter: blur(28px);
        -webkit-backdrop-filter: blur(28px);
        box-shadow:
            0 4px 20px -6px rgba(139, 92, 246, 0.08),
            0 24px 60px -20px rgba(124, 58, 237, 0.1),
            0 0 0 1px rgba(255, 255, 255, 0.8) inset;
        transform-style: preserve-3d;
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s ease;
        animation: cardAppear 0.9s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        opacity: 0;
        transform: translateY(35px) rotateX(5deg);
    }

    @keyframes cardAppear {
        to {
            opacity: 1;
            transform: translateY(0) rotateX(0deg);
        }
    }

    .login-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 32px;
        padding: 1.5px;
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.35), rgba(20, 184, 166, 0.15), rgba(139, 92, 246, 0.25));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask-composite: exclude;
        pointer-events: none;
    }

    .login-card:hover {
        box-shadow:
            0 6px 24px -6px rgba(139, 92, 246, 0.12),
            0 28px 70px -24px rgba(124, 58, 237, 0.14),
            0 0 0 1px rgba(255, 255, 255, 0.8) inset;
    }

    .login-brand {
        text-align: center;
        margin-bottom: 40px;
    }

    .login-logo {
        width: 76px;
        height: 76px;
        margin: 0 auto 22px;
        background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 50%, #14b8a6 100%);
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 14px 36px rgba(124, 58, 237, 0.22);
        animation: logoPulse 3.5s ease-in-out infinite;
        position: relative;
        overflow: hidden;
    }

    .login-logo::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent 40%, rgba(255, 255, 255, 0.25) 50%, transparent 60%);
        animation: logoShine 4s ease-in-out infinite;
    }

    @keyframes logoShine {
        0%, 100% { transform: translateX(-100%) rotate(25deg); }
        50% { transform: translateX(100%) rotate(25deg); }
    }

    @keyframes logoPulse {
        0%, 100% { box-shadow: 0 14px 36px rgba(124, 58, 237, 0.22); transform: scale(1); }
        50% { box-shadow: 0 18px 48px rgba(124, 58, 237, 0.32); transform: scale(1.02); }
    }

    .login-logo svg {
        width: 40px;
        height: 40px;
        color: white;
        position: relative;
        z-index: 1;
    }

    .login-brand h1 {
        font-size: 2.1rem;
        line-height: 1.2;
        margin-bottom: 0.85rem;
        color: #2d2d2d;
        font-family: 'Cinzel Decorative', 'Cinzel', serif;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .login-brand p {
        font-size: 0.92rem;
        color: #7d7b78;
        font-weight: 500;
        line-height: 1.6;
        max-width: 280px;
        margin: 0 auto;
    }

    .login-form {
        margin-top: 28px;
    }

    .form-group {
        margin-bottom: 22px;
        position: relative;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.6rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #6b6966;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        transition: color 0.3s ease;
    }

    .form-group:focus-within label {
        color: #7c3aed;
    }

    .input-wrapper {
        position: relative;
    }

    .input-wrapper::before {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: 16px;
        background: linear-gradient(135deg, #7c3aed, #14b8a6);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
        filter: blur(5px);
    }

    .form-group:focus-within .input-wrapper::before {
        opacity: 0.2;
    }

    .form-control {
        width: 100%;
        padding: 1.05rem 1rem 1.05rem 3rem;
        background: rgba(255, 255, 255, 0.95);
        border: 1.5px solid #e5e2f0;
        border-radius: 14px;
        color: #4a4845;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-control::placeholder {
        color: #b8b5c8;
        font-weight: 400;
    }

    .form-control:focus {
        outline: none;
        border-color: #14b8a6;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.08);
    }

    .form-control.error {
        border-color: #e08e8e;
        background: rgba(239, 68, 68, 0.04);
    }

    .form-control.error:focus {
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
    }

    .input-icon {
        position: absolute;
        left: 1.05rem;
        top: 50%;
        transform: translateY(-50%);
        color: #b8b5c8;
        transition: color 0.3s ease;
        pointer-events: none;
    }

    .form-group:focus-within .input-icon {
        color: #14b8a6;
    }

    .form-control.error + .input-icon {
        color: #e08e8e;
    }

    .password-wrapper .form-control {
        padding-right: 48px;
    }

    .toggle-password {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        cursor: pointer;
        color: #b8b5c8;
        padding: 0;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        border-radius: 4px;
    }

    .toggle-password:hover {
        color: #7c3aed;
        transform: translateY(-50%) scale(1.1);
    }

    .error-message {
        color: #c75b5b;
        font-size: 0.8rem;
        margin-top: 0.55rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        animation: shake 0.4s ease-in-out;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20% { transform: translateX(-4px); }
        40% { transform: translateX(4px); }
        60% { transform: translateX(-2px); }
        80% { transform: translateX(2px); }
    }

    .btn-login {
        width: 100%;
        margin-top: 14px;
        position: relative;
        overflow: hidden;
        padding: 1.05rem 1.5rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 50%, #14b8a6 100%);
        color: white;
        border: none;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 28px rgba(124, 58, 237, 0.22);
    }

    .btn-login::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.35), transparent);
        transition: left 0.7s ease;
    }

    .btn-login:hover::before {
        left: 100%;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 36px rgba(124, 58, 237, 0.3);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .btn-login.loading {
        color: transparent;
        pointer-events: none;
    }

    .btn-login.loading::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .login-options {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 20px 0;
        font-size: 0.85rem;
    }

    .forgot-link {
        color: #7c3aed;
        text-decoration: none;
        font-weight: 600;
        position: relative;
        transition: color 0.2s ease;
    }

    .forgot-link::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, #7c3aed, #14b8a6);
        transition: width 0.3s ease;
    }

    .forgot-link:hover {
        color: #14b8a6;
    }

    .forgot-link:hover::after {
        width: 100%;
    }

    .login-footer {
        margin-top: 36px;
        text-align: center;
    }

    .login-divider {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 22px;
        color: #b8b5c8;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.12em;
    }

    .login-divider::before,
    .login-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, transparent, #e5e2f0, transparent);
    }

    .login-security {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: #7d7b78;
        font-weight: 500;
        padding: 10px 16px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.7);
        border: 1px solid #e5e2f0;
    }

    .login-security svg {
        width: 14px;
        height: 14px;
        color: #14b8a6;
    }

    @media (max-width: 640px) {
        .login-card {
            padding: 44px 28px;
            border-radius: 28px;
        }

        .login-brand h1 {
            font-size: 1.8rem;
        }

        .login-logo {
            width: 68px;
            height: 68px;
        }

        .network-lines {
            display: none;
        }
    }
</style>
<div class="login-page" id="loginPage">
    <div class="login-mesh" id="loginMesh">
        <div class="mesh-blob" data-speed="0.02"></div>
        <div class="mesh-blob" data-speed="-0.015"></div>
        <div class="mesh-blob" data-speed="0.01"></div>
    </div>

    <div class="mouse-spotlight" id="mouseSpotlight"></div>

    <div class="network-lines" id="networkLines"></div>

    <div class="floating-shapes" id="floatingShapes">
        <svg class="shape" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>
        <svg class="shape" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
        <svg class="shape" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect><line x1="12" x2="12" y1="8" y2="16"></line><line x1="8" x2="16" y1="12" y2="12"></line></svg>
        <svg class="shape" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
        <svg class="shape" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="m7 12 3 3 7-7"></path></svg>
    </div>

    <div class="particles" id="particles">
        <div class="particle" data-speed="0.8"></div>
        <div class="particle" data-speed="1.2"></div>
        <div class="particle" data-speed="0.6"></div>
        <div class="particle" data-speed="1.0"></div>
        <div class="particle" data-speed="0.9"></div>
        <div class="particle" data-speed="1.1"></div>
        <div class="particle" data-speed="0.7"></div>
        <div class="particle" data-speed="1.3"></div>
        <div class="particle" data-speed="0.5"></div>
        <div class="particle" data-speed="1.4"></div>
        <div class="particle" data-speed="0.8"></div>
        <div class="particle" data-speed="1.0"></div>
    </div>

    <div class="login-card" id="loginCard">
        <div class="login-brand">
            <div class="login-logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
            </div>
            <h1>Cliche Business Suite</h1>
            <p>Plataforma integral para gestionar el posicionamiento local de tu negocio</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <div class="input-wrapper">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control @error('email') error @enderror"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           placeholder="nombre@empresa.com"
                           autocomplete="email">
                </div>
                @error('email')
                    <span class="error-message">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" x2="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg>
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-wrapper password-wrapper">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control @error('password') error @enderror"
                           required
                           placeholder="••••••••"
                           autocomplete="current-password">
                    <button type="button" class="toggle-password" id="togglePassword" title="Mostrar contraseña">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
                @error('password')
                    <span class="error-message">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" x2="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg>
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="login-options">
                <a href="{{ route('password.request') }}" class="forgot-link">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                Iniciar Sesión
            </button>
        </form>

        <div class="login-footer">
            <div class="login-divider">seguro</div>
            <div class="login-security">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                Sesión con expiración por inactividad
            </div>
        </div>
    </div>
</div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    const loginCard = document.getElementById('loginCard');
    const loginPage = document.getElementById('loginPage');
    const mouseSpotlight = document.getElementById('mouseSpotlight');
    const meshBlobs = document.querySelectorAll('.mesh-blob');
    const particles = document.querySelectorAll('.particle');
    const shapes = document.querySelectorAll('.shape');
    const networkLines = document.getElementById('networkLines');

    const eyeOpen = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle>';
    const eyeClosed = '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" x2="22" y1="2" y2="22"></line>';

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        eyeIcon.innerHTML = type === 'password' ? eyeOpen : eyeClosed;
        togglePassword.setAttribute('title', type === 'password' ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });

    loginForm.addEventListener('submit', function () {
        submitBtn.classList.add('loading');
    });

    // Mouse tracking for interactive background
    let mouseX = 0.5;
    let mouseY = 0.5;
    let targetMouseX = 0.5;
    let targetMouseY = 0.5;
    let isMouseActive = false;
    let mouseTimeout;
    let rafId;

    function updateMousePosition(x, y) {
        targetMouseX = x / window.innerWidth;
        targetMouseY = y / window.innerHeight;
        isMouseActive = true;
        loginPage.classList.add('mouse-active');

        clearTimeout(mouseTimeout);
        mouseTimeout = setTimeout(() => {
            isMouseActive = false;
            loginPage.classList.remove('mouse-active');
        }, 100);
    }

    loginPage.addEventListener('mousemove', function (e) {
        updateMousePosition(e.clientX, e.clientY);

        // Update spotlight position
        mouseSpotlight.style.setProperty('--mouse-x', `${e.clientX}px`);
        mouseSpotlight.style.setProperty('--mouse-y', `${e.clientY}px`);
    });

    loginPage.addEventListener('touchmove', function (e) {
        if (e.touches.length > 0) {
            updateMousePosition(e.touches[0].clientX, e.touches[0].clientY);
            mouseSpotlight.style.setProperty('--mouse-x', `${e.touches[0].clientX}px`);
            mouseSpotlight.style.setProperty('--mouse-y', `${e.touches[0].clientY}px`);
        }
    }, { passive: true });

    function lerp(start, end, factor) {
        return start + (end - start) * factor;
    }

    function animate() {
        mouseX = lerp(mouseX, targetMouseX, 0.08);
        mouseY = lerp(mouseY, targetMouseY, 0.08);

        const centerX = mouseX - 0.5;
        const centerY = mouseY - 0.5;

        // Parallax for mesh blobs (move opposite to mouse)
        meshBlobs.forEach((blob, index) => {
            const speed = parseFloat(blob.dataset.speed) || 0.02;
            const offsetX = centerX * speed * window.innerWidth * -1;
            const offsetY = centerY * speed * window.innerHeight * -1;
            blob.style.transform = `translate(${offsetX}px, ${offsetY}px)`;
        });

        // Parallax for particles (move with mouse, different speeds)
        particles.forEach((particle, index) => {
            const speed = parseFloat(particle.dataset.speed) || 1;
            const offsetX = centerX * speed * 25;
            const offsetY = centerY * speed * 25;
            particle.style.transform = `translate(${offsetX}px, ${offsetY}px)`;
        });

        // Parallax and rotation for shapes
        shapes.forEach((shape, index) => {
            const speed = (index + 1) * 0.3;
            const offsetX = centerX * speed * 15;
            const offsetY = centerY * speed * 15;
            const rotation = centerX * 10 * (index % 2 === 0 ? 1 : -1);
            shape.style.transform = `translate(${offsetX}px, ${offsetY}px) rotate(${rotation}deg)`;
        });

        // Subtle grid movement
        if (networkLines) {
            const gridOffsetX = centerX * 10;
            const gridOffsetY = centerY * 10;
            networkLines.style.transform = `perspective(500px) rotateX(60deg) translateY(${gridOffsetY}px) translateX(${gridOffsetX}px)`;
        }

        // 3D tilt effect on card
        if (window.innerWidth > 640) {
            const rect = loginCard.getBoundingClientRect();
            const cardX = mouseX * window.innerWidth - rect.left;
            const cardY = mouseY * window.innerHeight - rect.top;
            const centerCardX = rect.width / 2;
            const centerCardY = rect.height / 2;
            const rotateX = ((cardY - centerCardY) / centerCardY) * -2.5;
            const rotateY = ((cardX - centerCardX) / centerCardX) * 2.5;
            loginCard.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        }

        rafId = requestAnimationFrame(animate);
    }

    animate();

    loginPage.addEventListener('mouseleave', function () {
        targetMouseX = 0.5;
        targetMouseY = 0.5;
        loginCard.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)';
    });

    // Pause animations when tab is hidden
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            cancelAnimationFrame(rafId);
        } else {
            animate();
        }
    });
</script>
</body>
</html>
