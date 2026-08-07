<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cliche Business Suite')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">Saltar al contenido principal</a>
</head>
<body class="bg-gray-50">
    <!-- Sidebar Navigation -->
    @auth
        <aside id="sidebar" class="sidebar" role="navigation" aria-label="Menú principal">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-logo">
                    Cliche Business Suite
                </a>
                <button 
                    type="button" 
                    class="btn btn-ghost btn-sm mobile-only"
                    onclick="toggleSidebar()"
                    aria-label="Cerrar menú"
                >
                    ✕
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <!-- Admin Navigation -->
                @if(auth()->user()->isAdmin())
                    <div class="nav-section">
                        <div class="nav-section-title">Principal</div>
                        
                        <div class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Dashboard
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Clientes
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('calendario.index') }}" class="nav-link {{ request()->routeIs('calendario.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Calendario
                            </a>
                        </div>
                    </div>
                    
                    <div class="nav-section">
                        <div class="nav-section-title">Gestión</div>
                        
                        <div class="nav-item">
                            <a href="{{ route('gastos.index') }}" class="nav-link {{ request()->routeIs('gastos.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Gastos
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('pagos.index') }}" class="nav-link {{ request()->routeIs('pagos.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Pagos
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('aprobaciones.index') }}" class="nav-link {{ request()->routeIs('aprobaciones.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Aprobaciones
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('operadores.index') }}" class="nav-link {{ request()->routeIs('operadores.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                Operadores
                            </a>
                        </div>
                    </div>
                    
                    <div class="nav-section">
                        <div class="nav-section-title">Reportes</div>
                        
                        <div class="nav-item">
                            <a href="{{ route('reportes.financiero') }}" class="nav-link {{ request()->routeIs('reportes.financiero') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Financiero
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('reportes.rentabilidad') }}" class="nav-link {{ request()->routeIs('reportes.rentabilidad') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                Rentabilidad
                            </a>
                        </div>
                    </div>
                    
                    <div class="nav-section">
                        <div class="nav-section-title">Sistema</div>
                        
                        <div class="nav-item">
                            <a href="{{ route('backups.index') }}" class="nav-link {{ request()->routeIs('backups.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Backups
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('boveda.index') }}" class="nav-link {{ request()->routeIs('boveda.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Bóveda
                            </a>
                        </div>
                    </div>
                @endif
                
                <!-- Operador Navigation -->
                @if(auth()->user()->isOperador())
                    <div class="nav-section">
                        <div class="nav-section-title">Operaciones</div>
                        
                        <div class="nav-item">
                            <a href="{{ route('operador.dashboard') }}" class="nav-link {{ request()->routeIs('operador.dashboard') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Dashboard
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('calendario.index') }}" class="nav-link {{ request()->routeIs('calendario.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Calendario
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('aprobaciones.index') }}" class="nav-link {{ request()->routeIs('aprobaciones.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Aprobaciones
                            </a>
                        </div>
                    </div>
                @endif
                
                <!-- Cliente Navigation -->
                @if(auth()->user()->isCliente())
                    <div class="nav-section">
                        <div class="nav-section-title">Mi Cuenta</div>
                        
                        <div class="nav-item">
                            <a href="{{ route('cliente.dashboard') }}" class="nav-link {{ request()->routeIs('cliente.dashboard') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Dashboard
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('clientes.perfil') }}" class="nav-link {{ request()->routeIs('clientes.perfil') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Mi Perfil
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('pagos.misPagos') }}" class="nav-link {{ request()->routeIs('pagos.misPagos') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Mis Pagos
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('cliente.aprobaciones.index') }}" class="nav-link {{ request()->routeIs('cliente.aprobaciones.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Mis Fotos
                            </a>
                        </div>
                    </div>
                @endif
            </nav>
        </aside>
    @endauth

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Top Navigation -->
        @auth
            <header class="topnav" role="banner">
                <div class="topnav-content">
                    <div class="topnav-left">
                        <button 
                            type="button" 
                            class="btn btn-ghost mobile-only"
                            onclick="toggleSidebar()"
                            aria-label="Abrir menú"
                        >
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        
                        <!-- Breadcrumbs -->
                        @yield('breadcrumbs')
                    </div>
                    
                    <div class="topnav-right">
                        <!-- Global Search -->
                        <div class="search-container desktop-only">
                            <input 
                                type="search" 
                                placeholder="Buscar..." 
                                class="search-input"
                                id="global-search"
                                aria-label="Búsqueda global"
                            >
                        </div>
                        
                        <!-- User Menu -->
                        <div class="user-menu">
                            <button 
                                type="button" 
                                class="user-menu-button"
                                onclick="toggleUserMenu()"
                                aria-label="Menú de usuario"
                                aria-expanded="false"
                            >
                                <div class="user-avatar">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <div class="user-info desktop-only">
                                    <div class="user-name">{{ auth()->user()->name }}</div>
                                    <div class="user-role">{{ auth()->user()->role }}</div>
                                </div>
                                <svg class="dropdown-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <div class="user-dropdown" id="user-dropdown">
                                <div class="user-dropdown-header">
                                    <div class="user-avatar large">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="user-name">{{ auth()->user()->name }}</div>
                                        <div class="user-email">{{ auth()->user()->email }}</div>
                                    </div>
                                </div>
                                
                                <div class="user-dropdown-section">
                                    @if(auth()->user()->isCliente())
                                        <a href="{{ route('clientes.perfil') }}" class="dropdown-item">
                                            <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            Mi Perfil
                                        </a>
                                    @endif
                                    
                                    <a href="#" class="dropdown-item" onclick="showKeyboardShortcuts()">
                                        <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                        </svg>
                                        Atajos de Teclado
                                    </a>
                                    
                                    <a href="#" class="dropdown-item" onclick="toggleDarkMode()">
                                        <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                        </svg>
                                    </a>
                                </div>
                                
                                <div class="user-dropdown-section">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item dropdown-item-danger">
                                            <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            Cerrar Sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
        @endauth

        <!-- Page Content -->
        <main id="main-content" class="main-page-content" role="main">
            @yield('content')
        </main>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container" aria-live="polite"></div>

    <!-- Confirmation Modal -->
    <div class="modal-overlay" id="confirm-modal" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="confirm-title">Confirmar Acción</h3>
                <button type="button" class="btn btn-ghost" onclick="closeConfirmModal()">✕</button>
            </div>
            <div class="modal-body" id="confirm-message">
                ¿Estás seguro de que deseas realizar esta acción?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="confirm-button">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner spinner-lg"></div>
            <p class="loading-text">Cargando...</p>
        </div>
    </div>

    @yield('scripts')
</body>
</html>
