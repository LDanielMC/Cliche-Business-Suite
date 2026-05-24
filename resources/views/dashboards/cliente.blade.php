@extends('layouts.app')

@section('title', 'Panel de Cliente')

@section('styles')
<style>
    .dashboard-header {
        margin-bottom: 30px;
    }
    .dashboard-header h1 {
        color: #333;
        font-size: 32px;
    }
    .dashboard-header p {
        color: #666;
        margin-top: 5px;
    }
    .welcome-banner {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    .welcome-banner h2 {
        font-size: 24px;
        margin-bottom: 10px;
    }
    .cliente-section {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .cliente-section h2 {
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #28a745;
    }
    .cliente-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
    .cliente-btn {
        padding: 15px 20px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        transition: background 0.3s;
        text-align: center;
    }
    .cliente-btn:hover {
        background: #218838;
    }
    .info-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    .info-card h4 {
        color: #333;
        margin-bottom: 10px;
    }
    .info-card p {
        color: #666;
        font-size: 14px;
    }
    .session-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
    }
    .session-info p {
        color: #666;
        font-size: 14px;
    }
</style>
@endsection

@section('content')
<div class="dashboard-header">
    <h1>Panel de Cliente</h1>
    <p>Bienvenido, {{ $user->name }}. Aqui puedes gestionar tu cuenta.</p>
</div>

<div class="welcome-banner">
    <h2>Hola, {{ $user->name }}!</h2>
    <p>Bienvenido a tu portal de cliente. Desde aqui puedes acceder a todos los servicios disponibles.</p>
</div>

<div class="cliente-section">
    <h2>Mis Servicios</h2>
    <div class="cliente-grid">
        <button class="cliente-btn">Mis Pedidos</button>
        <button class="cliente-btn">Facturas</button>
        <button class="cliente-btn">Soporte</button>
        <button class="cliente-btn">Mi Perfil</button>
    </div>
</div>

<div class="cliente-section">
    <h2>Informacion de la Cuenta</h2>
    <div class="info-card">
        <h4>Estado de la Cuenta</h4>
        <p>Tu cuenta esta activa y en buen estado. Tienes acceso a todos los servicios contratados.</p>
    </div>
    <div class="info-card">
        <h4>Proximos Vencimientos</h4>
        <p>No tienes pagos pendientes. Tu proxima factura se generara el 1ro del mes siguiente.</p>
    </div>
</div>

<div class="session-info">
    <p><strong>Rol:</strong> Cliente</p>
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>Sesion iniciada:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
</div>
@endsection
