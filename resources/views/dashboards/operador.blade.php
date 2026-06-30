@extends('layouts.app')

@section('title', 'Panel de Operador')

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
        color: #ffc107;
        font-size: 36px;
        font-weight: bold;
    }
    .operador-section {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .operador-section h2 {
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #ffc107;
    }
    .operador-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
    .operador-btn {
        padding: 15px 20px;
        background: #ffc107;
        color: #333;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        transition: background 0.3s;
        text-align: center;
    }
    .operador-btn:hover {
        background: #e0a800;
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
    .activity-list {
        list-style: none;
    }
    .activity-list li {
        padding: 10px;
        border-bottom: 1px solid #eee;
        color: #555;
    }
    .activity-list li:last-child {
        border-bottom: none;
    }
</style>
@endsection

@section('content')
<div class="dashboard-header">
    <h1>Panel de Operador</h1>
    <p>Bienvenido, {{ $user->name }}. Puedes operar el sistema y gestionar tareas.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Tareas Asignadas</h3>
        <div class="number">12</div>
    </div>
    <div class="stat-card">
        <h3>Clientes Atendidos</h3>
        <div class="number">{{ \App\Models\User::where('role', 'cliente')->count() }}</div>
    </div>
    <div class="stat-card">
        <h3>Servicios Activos</h3>
        <div class="number">5</div>
    </div>
    <div class="stat-card">
        <h3>Incidentes Hoy</h3>
        <div class="number">3</div>
    </div>
</div>

<div class="operador-section">
    <h2>Gestion de Operaciones</h2>
    <div class="operador-grid">
        <button class="operador-btn">Ver Tareas</button>
        <button class="operador-btn">Asignar Servicios</button>
        <button class="operador-btn">Desempeño</button>
        <button class="operador-btn">Horarios</button>
    </div>
</div>

<div class="operador-section">
    <h2>Actividad Reciente</h2>
    <ul class="activity-list">
        <li>Hace 5 minutos - Se completo la tarea #1234</li>
        <li>Hace 15 minutos - Nuevo cliente asignado</li>
        <li>Hace 30 minutos - Revision de reporte mensual</li>
        <li>Hace 1 hora - Actualizacion de equipo completada</li>
    </ul>
</div>

<div class="session-info">
    <p><strong>Rol:</strong> Operador</p>
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>Sesion iniciada:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
</div>
@endsection
