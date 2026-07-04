@extends('layouts.app')

@section('title', 'Panel de Administrador')

@section('styles')
<style>
    .admin-dashboard {
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Page header */
    .page-header {
        margin-bottom: 28px;
    }

    .page-header h1 {
        font-size: 1.8rem;
        color: #2d2d35;
        margin-bottom: 6px;
    }

    .page-header .subtitle {
        color: #7d7d87;
        font-size: 0.95rem;
    }

    /* Section containers */
    .section-block {
        margin-bottom: 28px;
    }

    .section-label {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #7c3aed;
        margin-bottom: 14px;
        padding-left: 4px;
    }

    /* Stats grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    .stat-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 20px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 12px rgba(124, 58, 237, 0.04);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(124, 58, 237, 0.08);
        border-color: rgba(139, 92, 246, 0.2);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }

    .stat-title {
        color: #7d7d87;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(124, 58, 237, 0.08);
        color: #7c3aed;
    }

    .stat-icon.accent {
        background: rgba(20, 184, 166, 0.08);
        color: #14b8a6;
    }

    .stat-icon svg {
        width: 18px;
        height: 18px;
    }

    .stat-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2d2d35;
        line-height: 1;
        margin-bottom: 6px;
    }

    .stat-caption {
        font-size: 0.8rem;
        color: #9ca3af;
        font-weight: 500;
    }

    /* Main layout */
    .main-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 28px;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .main-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 540px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    .panel {
        background: white;
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 2px 16px rgba(124, 58, 237, 0.04);
    }

    .panel-title {
        font-size: 1rem;
        font-weight: 700;
        color: #2d2d35;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .panel-title svg {
        width: 20px;
        height: 20px;
        color: #7c3aed;
    }

    /* Action groups */
    .action-group {
        margin-bottom: 24px;
    }

    .action-group:last-child {
        margin-bottom: 0;
    }

    .action-group-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: #7d7d87;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 12px;
        padding-left: 4px;
    }

    .actions-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    @media (max-width: 640px) {
        .actions-row {
            grid-template-columns: 1fr;
        }
    }

    .action-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
        text-decoration: none;
        color: #2d2d35;
        transition: all 0.25s ease;
        cursor: pointer;
    }

    .action-card:hover {
        border-color: rgba(139, 92, 246, 0.25);
        box-shadow: 0 6px 18px rgba(124, 58, 237, 0.08);
        transform: translateY(-2px);
    }

    .action-card.disabled {
        opacity: 0.55;
        cursor: not-allowed;
        background: #fafafa;
    }

    .action-card.disabled:hover {
        transform: none;
        box-shadow: none;
        border-color: var(--border);
    }

    .action-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
        color: white;
        transition: all 0.25s ease;
    }

    .action-card:hover .action-icon {
        background: linear-gradient(135deg, #14b8a6 0%, #2dd4bf 100%);
    }

    .action-card.disabled .action-icon,
    .action-card.disabled:hover .action-icon {
        background: #c4c4cc;
    }

    .action-icon svg {
        width: 20px;
        height: 20px;
    }

    .action-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .action-title {
        font-weight: 700;
        font-size: 0.92rem;
    }

    .action-desc {
        font-size: 0.75rem;
        color: #7d7d87;
    }

    /* Status list */
    .status-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .status-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        background: #fafafa;
        border-radius: 12px;
        border: 1px solid var(--border);
    }

    .status-label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.88rem;
        font-weight: 600;
        color: #4a4a52;
    }

    .status-label svg {
        width: 18px;
        height: 18px;
        color: #7c3aed;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .status-badge.active {
        background: rgba(20, 184, 166, 0.12);
        color: #0d9488;
    }

    .status-badge.warning {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    /* User mini profile */
    .user-mini {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.06) 0%, rgba(20, 184, 166, 0.04) 100%);
        border: 1px solid rgba(139, 92, 246, 0.12);
        border-radius: 16px;
        margin-bottom: 20px;
    }

    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #7c3aed 0%, #14b8a6 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
        min-width: 0;
    }

    .user-name {
        font-weight: 700;
        color: #2d2d35;
        font-size: 1rem;
        margin-bottom: 2px;
    }

    .user-email {
        font-size: 0.8rem;
        color: #7d7d87;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Session info */
    .session-info {
        background: white;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 18px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 2px 16px rgba(124, 58, 237, 0.04);
    }

    .session-info p {
        color: #6b6b75;
        font-size: 0.85rem;
        margin: 0;
    }

    .session-info p strong {
        color: #2d2d35;
    }

    .session-time {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: #7c3aed;
        font-weight: 600;
    }

    .session-time svg {
        width: 16px;
        height: 16px;
    }

    @media (max-width: 640px) {
        .page-header h1 {
            font-size: 1.4rem;
        }
        .session-info {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endsection

@section('content')
<div class="admin-dashboard">
    <div class="page-header">
        <h1>Panel de Administración</h1>
        <div class="subtitle">Bienvenido de nuevo, {{ $user->name }}. Aquí tienes el control total del sistema.</div>
    </div>

    <div class="section-block">
        <div class="section-label">Resumen General</div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Usuarios</div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                </div>
                <div class="stat-number">{{ \App\Models\User::count() }}</div>
                <div class="stat-caption">Total de usuarios</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Administradores</div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                    </div>
                </div>
                <div class="stat-number">{{ \App\Models\User::where('role', 'admin')->count() }}</div>
                <div class="stat-caption">Acceso total</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Operadores</div>
                    <div class="stat-icon accent">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="m16 3 2 2 4-4"></path></svg>
                    </div>
                </div>
                <div class="stat-number">{{ \App\Models\User::where('role', 'operador')->count() }}</div>
                <div class="stat-caption">Gestión operativa</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Clientes</div>
                    <div class="stat-icon accent">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9"></path><path d="M14 17H5"></path><circle cx="17" cy="17" r="3"></circle><circle cx="7" cy="7" r="3"></circle></svg>
                    </div>
                </div>
                <div class="stat-number">{{ \App\Models\Cliente::count() }}</div>
                <div class="stat-caption">Clientes registrados</div>
            </div>
        </div>
    </div>

    <div class="main-layout">
        <div class="section-block">
            <div class="section-label">Gestión</div>
            <div class="panel">
                <div class="action-group">
                    <div class="action-group-title">Clientes</div>
                    <div class="actions-row">
                        <a href="{{ route('clientes.index') }}" class="action-card">
                            <div class="action-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            </div>
                            <div class="action-content">
                                <div class="action-title">Gestionar Clientes</div>
                                <div class="action-desc">Ver, editar y administrar clientes</div>
                            </div>
                        </a>
                        <a href="{{ route('clientes.create') }}" class="action-card">
                            <div class="action-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                            </div>
                            <div class="action-content">
                                <div class="action-title">Nuevo Cliente</div>
                                <div class="action-desc">Registrar un cliente nuevo</div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="action-group">
                    <div class="action-group-title">Administración del Sistema</div>
                    <div class="actions-row">
                        <div class="action-card disabled">
                            <div class="action-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                            </div>
                            <div class="action-content">
                                <div class="action-title">Crear Usuario</div>
                                <div class="action-desc">Nuevo usuario del sistema</div>
                            </div>
                        </div>
                        <div class="action-card disabled">
                            <div class="action-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            </div>
                            <div class="action-content">
                                <div class="action-title">Permisos</div>
                                <div class="action-desc">Roles y accesos</div>
                            </div>
                        </div>
                        <div class="action-card disabled">
                            <div class="action-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.1a2 2 0 0 1-1-1.72v-.51a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </div>
                            <div class="action-content">
                                <div class="action-title">Configuración</div>
                                <div class="action-desc">Ajustes del sistema</div>
                            </div>
                        </div>
                        <div class="action-card disabled">
                            <div class="action-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" x2="8" y1="13" y2="13"></line><line x1="16" x2="8" y1="17" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            </div>
                            <div class="action-content">
                                <div class="action-title">Logs</div>
                                <div class="action-desc">Registro de actividad</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="action-group">
                    <div class="action-group-title">Respaldos y Reportes</div>
                    <div class="actions-row">
                        <div class="action-card disabled">
                            <div class="action-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg>
                            </div>
                            <div class="action-content">
                                <div class="action-title">Respaldos</div>
                                <div class="action-desc">Copias de seguridad</div>
                            </div>
                        </div>
                        <div class="action-card disabled">
                            <div class="action-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>
                            </div>
                            <div class="action-content">
                                <div class="action-title">Reportes</div>
                                <div class="action-desc">Estadísticas y métricas</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-block">
            <div class="section-label">Tu Cuenta</div>
            <div class="user-mini">
                <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-email">{{ $user->email }}</div>
                </div>
            </div>

            <div class="section-label">Estado del Sistema</div>
            <div class="panel">
                <div class="status-list">
                    <div class="status-item">
                        <div class="status-label">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            Autenticación
                        </div>
                        <div class="status-badge active">Activa</div>
                    </div>
                    <div class="status-item">
                        <div class="status-label">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                            Base de Datos
                        </div>
                        <div class="status-badge active">Conectada</div>
                    </div>
                    <div class="status-item">
                        <div class="status-label">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>
                            Sesión
                        </div>
                        <div class="status-badge warning">Por expirar</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="session-info">
        <div>
            <p><strong>Sesión iniciada:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
            <p style="margin-top: 4px;"><strong>IP:</strong> {{ request()->ip() }} · <strong>Navegador:</strong> {{ request()->userAgent() ? substr(request()->userAgent(), 0, 40) : 'Desconocido' }}</p>
        </div>
        <div class="session-time">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</div>
@endsection
