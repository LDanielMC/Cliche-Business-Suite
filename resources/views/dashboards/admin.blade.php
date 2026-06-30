@extends('layouts.app')

@section('title', 'Panel de Administrador')

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
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .stat-card h3 {
        color: #666;
        font-size: 14px;
        text-transform: uppercase;
        margin-bottom: 10px;
    }
    .stat-card .number {
        color: #667eea;
        font-size: 36px;
        font-weight: bold;
    }
    .admin-section {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .admin-section h2 {
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #667eea;
    }
    .admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
    .admin-btn {
        padding: 15px 20px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.3s;
        text-align: center;
    }
    .admin-btn:hover {
        background: #5a6fd6;
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
    <h1>Panel de Administrador</h1>
    <p>Bienvenido, {{ $user->name }}. Tienes acceso total al sistema.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Usuarios Totales</h3>
        <div class="number">{{ \App\Models\User::count() }}</div>
    </div>
    <div class="stat-card">
        <h3>Administradores</h3>
        <div class="number">{{ \App\Models\User::where('role', 'admin')->count() }}</div>
    </div>
    <div class="stat-card">
        <h3>Operadores</h3>
        <div class="number">{{ \App\Models\User::where('role', 'operador')->count() }}</div>
    </div>
    <div class="stat-card">
        <h3>Clientes</h3>
        <div class="number">{{ \App\Models\Cliente::count() }}</div>
    </div>
</div>

<div class="admin-section">
    <h2>Gestion del Sistema</h2>
    <div class="admin-grid">
        <a href="{{ route('clientes.index') }}" class="admin-btn">Gestionar Clientes</a>
        <button class="admin-btn">Configuracion</button>
        <button class="admin-btn">Reportes</button>
        <button class="admin-btn">Auditoria</button>
    </div>
</div>

<div class="admin-section">
    <h2>Herramientas de Admin</h2>
    <div class="admin-grid">
        <button class="admin-btn">Crear Usuario</button>
        <button class="admin-btn">Permisos</button>
        <button class="admin-btn">Respaldos</button>
        <button class="admin-btn">Logs del Sistema</button>
    </div>
</div>

<div class="session-info">
    <p><strong>Rol:</strong> Administrador</p>
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>Sesion iniciada:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
</div>
@endsection
