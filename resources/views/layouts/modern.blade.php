<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cliche Business Suite') - Cliche Business Suite</title>
    
    <!-- Preconnect to fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Vite Assets -->
    @vite([
        'resources/css/tailwind.css',
        'resources/css/modern-design-system.css',
        'resources/css/modern-navigation.css',
        'resources/css/modern-components.css',
        'resources/js/app.js',
        'resources/js/modern-ux-system.js'
    ])
    
    <!-- Estilos adicionales por vista -->
    @yield('styles')

    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">Saltar al contenido principal</a>
</head>
<body class="h-full bg-background text-text-primary">
    
    @auth
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobile-overlay" onclick="closeMobileSidebar()"></div>
        
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar" role="navigation" aria-label="Menú principal">
            <!-- Sidebar Header -->
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-logo">
                    <div class="sidebar-logo-icon">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                        </svg>
                    </div>
                    <span class="sidebar-logo-text">Cliche Suite</span>
                </a>
                <button type="button" class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Contraer menú">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
            </div>
            
            <!-- Navigation Menu -->
            <nav class="sidebar-nav">
                @if(auth()->user()->isAdmin())
                    <!-- Admin Navigation -->
                    <div class="sidebar-section">
                        <div class="sidebar-section-title">Principal</div>

                        <div class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </div>

                        <div class="nav-item">
                            <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="nav-text">Clientes</span>
                                @if(isset($clientesCount) && $clientesCount > 0)
                                    <span class="nav-badge">{{ $clientesCount }}</span>
                                @endif
                            </a>
                        </div>

                        <div class="nav-item">
                            <a href="{{ route('aprobaciones.index') }}" class="nav-link {{ request()->routeIs('aprobaciones.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="nav-text">Aprobaciones</span>
                                @php
                                    $pendingAprobaciones = \App\Support\PaquetesEnRiesgo::detectar()->count();
                                @endphp
                                @if($pendingAprobaciones > 0)
                                    <span class="nav-badge" title="Clientes cuyo período está por vencer sin paquete enviado">{{ $pendingAprobaciones }}</span>
                                @endif
                            </a>
                        </div>

                        <div class="nav-item">
                            <a href="{{ route('renovaciones.index') }}" class="nav-link {{ request()->routeIs('renovaciones.*') && !request()->routeIs('renovaciones.cliente.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span class="nav-text">Renovaciones</span>
                                @php
                                    $pendingRenovaciones = \App\Models\ControlRenovacion::whereIn('estatus', ['en_revision','factura_pendiente','pago_rechazado'])->count();
                                @endphp
                                @if($pendingRenovaciones > 0)
                                    <span class="nav-badge">{{ $pendingRenovaciones }}</span>
                                @endif
                            </a>
                        </div>

                        <div class="nav-item">
                            <a href="{{ route('calendario.index') }}" class="nav-link {{ request()->routeIs('calendario.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="nav-text">Calendario</span>
                            </a>
                        </div>
                    </div>

                    <div class="sidebar-section">
                        <div class="sidebar-section-title">Gestión</div>

                        <div class="nav-item">
                            <a href="{{ route('pagos.index') }}" class="nav-link {{ request()->routeIs('pagos.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="nav-text">Pagos</span>
                            </a>
                        </div>

                        <div class="nav-item">
                            <a href="{{ route('gastos.index') }}" class="nav-link {{ request()->routeIs('gastos.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="nav-text">Gastos</span>
                            </a>
                        </div>

                        <div class="nav-item">
                            <a href="{{ route('operadores.index') }}" class="nav-link {{ request()->routeIs('operadores.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span class="nav-text">Operadores</span>
                            </a>
                        </div>
                    </div>

                    <div class="sidebar-section">
                        <div class="sidebar-section-title">Reportes</div>

                        <div class="nav-item">
                            <a href="{{ route('reportes.financiero') }}" class="nav-link {{ request()->routeIs('reportes.financiero') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span class="nav-text">Financiero</span>
                            </a>
                        </div>

                        <div class="nav-item">
                            <a href="{{ route('reportes.rentabilidad') }}" class="nav-link {{ request()->routeIs('reportes.rentabilidad') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                <span class="nav-text">Rentabilidad</span>
                            </a>
                        </div>

                        <div class="nav-item">
                            <a href="{{ route('reportes.cartera') }}" class="nav-link {{ request()->routeIs('reportes.cartera') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M7 14l3-3 3 3 5-6"></path>
                                </svg>
                                <span class="nav-text">Evolución de Cartera</span>
                            </a>
                        </div>

                        <div class="nav-item">
                            <a href="{{ route('reportes.gastos') }}" class="nav-link {{ request()->routeIs('reportes.gastos') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 8V3a2 2 0 012-2z"></path>
                                </svg>
                                <span class="nav-text">Gastos por Categoría</span>
                            </a>
                        </div>
                    </div>

                    <div class="sidebar-section">
                        <div class="sidebar-section-title">Sistema</div>
                        
                        <div class="nav-item">
                            <a href="{{ route('backups.index') }}" class="nav-link {{ request()->routeIs('backups.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                <span class="nav-text">Backups</span>
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('boveda.index') }}" class="nav-link {{ request()->routeIs('boveda.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <span class="nav-text">Bóveda</span>
                            </a>
                        </div>
                    </div>
                @elseif(auth()->user()->isOperador())
                    <!-- Operador Navigation -->
                    <div class="sidebar-section">
                        <div class="sidebar-section-title">Operaciones</div>
                        
                        <div class="nav-item">
                            <a href="{{ route('operador.dashboard') }}" class="nav-link {{ request()->routeIs('operador.dashboard') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('calendario.index') }}" class="nav-link {{ request()->routeIs('calendario.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="nav-text">Calendario</span>
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('aprobaciones.index') }}" class="nav-link {{ request()->routeIs('aprobaciones.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="nav-text">Aprobaciones</span>
                            </a>
                        </div>

                        <div class="nav-item">
                            <a href="{{ route('gastos.index') }}" class="nav-link {{ request()->routeIs('gastos.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="nav-text">Gastos</span>
                            </a>
                        </div>
                    </div>
                @elseif(auth()->user()->isCliente())
                    <!-- Cliente Navigation -->
                    <div class="sidebar-section">
                        <div class="sidebar-section-title">Mi Cuenta</div>
                        
                        <div class="nav-item">
                            <a href="{{ route('cliente.dashboard') }}" class="nav-link {{ request()->routeIs('cliente.dashboard') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('clientes.perfil') }}" class="nav-link {{ request()->routeIs('clientes.perfil') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="nav-text">Mi Perfil</span>
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('pagos.misPagos') }}" class="nav-link {{ request()->routeIs('pagos.misPagos') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="nav-text">Mis Pagos</span>
                            </a>
                        </div>
                        
                        <div class="nav-item">
                            <a href="{{ route('cliente.aprobaciones.index') }}" class="nav-link {{ request()->routeIs('cliente.aprobaciones.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="nav-text">Mis Fotos</span>
                                @php
                                    $pendingMisFotos = \App\Models\PaqueteAprobacion::whereHas('cliente', fn ($q) => $q->where('user_id', auth()->id()))
                                        ->where('estatus', \App\Models\PaqueteAprobacion::ESTATUS_PENDIENTE)
                                        ->count();
                                @endphp
                                @if($pendingMisFotos > 0)
                                    <span class="nav-badge" title="Paquetes esperando tu revisión">{{ $pendingMisFotos }}</span>
                                @endif
                            </a>
                        </div>

                        <div class="nav-item">
                            <a href="{{ route('calendario.cliente') }}" class="nav-link {{ request()->routeIs('calendario.cliente') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="nav-text">Mi Calendario</span>
                            </a>
                        </div>

                        <div class="nav-item">
                            <a href="{{ route('renovaciones.cliente.index') }}" class="nav-link {{ request()->routeIs('renovaciones.cliente.*') ? 'active' : '' }}">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span class="nav-text">Mi Renovación</span>
                            </a>
                        </div>
                    </div>
                @endif
            </nav>
        </aside>
        
        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Top Navigation -->
            <header class="topnav" role="banner">
                <div class="topnav-content">
                    <!-- Mobile Menu Toggle -->
                    <button type="button" class="mobile-menu-toggle" onclick="toggleMobileSidebar()" aria-label="Abrir menú">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    
                    <!-- Breadcrumbs -->
                    <div class="breadcrumbs-container">
                        @yield('breadcrumbs')
                    </div>
                    
                    <!-- Right Side Actions -->
                    <div class="flex items-center gap-4">
                        <!-- Global Search -->
                        <div class="global-search">
                            <input 
                                type="search" 
                                id="global-search"
                                class="search-input"
                                placeholder="Buscar (Ctrl+K)"
                                aria-label="Búsqueda global"
                            >
                            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <kbd class="search-shortcut">⌘K</kbd>
                        </div>
                        
                        <!-- Centro de notificaciones -->
                        @php
                            $notifUnreadCount = auth()->user()->unreadNotifications()->count();
                            $notifRecientes   = auth()->user()->notifications()->latest()->limit(8)->get();
                            $notifIconos = [
                                'foto' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
                                'renovacion' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
                                'pago' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
                                'alerta' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
                            ];
                        @endphp
                        <div class="notif-bell" x-data="{ open: false }" @keydown.escape="open = false">
                            <button type="button" class="notif-bell-trigger" @click="open = !open" aria-label="Notificaciones" aria-haspopup="true" :aria-expanded="open.toString()">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                @if($notifUnreadCount > 0)
                                    <span class="notif-bell-badge">{{ $notifUnreadCount > 9 ? '9+' : $notifUnreadCount }}</span>
                                @endif
                            </button>

                            <div class="notif-dropdown" x-show="open" x-cloak @click.outside="open = false" x-transition>
                                <div class="notif-dropdown-header">
                                    <span>Notificaciones</span>
                                    <div class="notif-dropdown-actions">
                                        @if($notifUnreadCount > 0)
                                            <form method="POST" action="{{ route('notificaciones.marcar-todas') }}">
                                                @csrf
                                                <button type="submit" class="notif-mark-all">Marcar todas como leídas</button>
                                            </form>
                                        @endif
                                        @if($notifRecientes->contains(fn ($n) => $n->read_at !== null))
                                            <form method="POST" action="{{ route('notificaciones.eliminar-leidas') }}"
                                                  onsubmit="showConfirmModal('Eliminar leídas', '¿Eliminar todas las notificaciones ya leídas?', () => this.submit(), {danger: true}); return false;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="notif-mark-all">Eliminar leídas</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="notif-dropdown-list">
                                    @forelse($notifRecientes as $notif)
                                        <div class="notif-row">
                                            <form method="POST" action="{{ route('notificaciones.leer', $notif->id) }}" class="notif-item-form">
                                                @csrf
                                                <button type="submit" class="notif-item {{ $notif->read_at ? '' : 'unread' }}">
                                                    <span class="notif-item-icon notif-icon-{{ $notif->data['icono'] ?? 'alerta' }}">
                                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            {!! $notifIconos[$notif->data['icono'] ?? 'alerta'] !!}
                                                        </svg>
                                                    </span>
                                                    <span class="notif-item-body">
                                                        <span class="notif-item-title">{{ $notif->data['titulo'] ?? '' }}</span>
                                                        <span class="notif-item-msg">{{ $notif->data['mensaje'] ?? '' }}</span>
                                                        <span class="notif-item-time">{{ $notif->created_at->diffForHumans() }}</span>
                                                    </span>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('notificaciones.destroy', $notif->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="notif-delete-btn" title="Eliminar" aria-label="Eliminar notificación">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    @empty
                                        <div class="notif-empty">No tienes notificaciones.</div>
                                    @endforelse
                                </div>
                                <a href="{{ route('notificaciones.index') }}" class="notif-dropdown-footer">Ver todas</a>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="user-menu">
                            <button type="button" class="user-menu-trigger" onclick="toggleUserMenu()" aria-expanded="false">
                                <div class="user-avatar">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <div class="user-info">
                                    <div class="user-name">{{ auth()->user()->name }}</div>
                                    <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
                                </div>
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <div class="user-dropdown" id="user-dropdown">
                                <div class="user-dropdown-header">
                                    <div class="user-avatar">
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
                                    
                                    <button type="button" class="dropdown-item" onclick="showKeyboardShortcuts()">
                                        <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                        </svg>
                                        Atajos de Teclado
                                    </button>
                                    
                                    <button type="button" class="dropdown-item" onclick="toggleDarkMode()">
                                        <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                        </svg>
                                        Modo Oscuro
                                    </button>
                                </div>
                                
                                <div class="user-dropdown-section">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item danger">
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
            
            <!-- Page Content -->
            <main id="main-content" class="main-page-content" role="main">
                @yield('content')
            </main>
        </div>
    @else
        <!-- Guest Layout -->
        <div class="min-h-screen flex items-center justify-center bg-background">
            @yield('content')
        </div>
    @endauth

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container" aria-live="polite"></div>

    <!-- Confirmation Modal -->
    <div class="modal-overlay" id="confirm-modal" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="confirm-title">Confirmar Acción</h3>
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeConfirmModal()">✕</button>
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
