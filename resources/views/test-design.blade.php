@extends('layouts.modern')

@section('title', 'Test de Diseño Moderno')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Test de Diseño</span>
        </div>
    </div>
@endsection

@section('content')
<div class="test-design-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Test de Diseño Moderno</h1>
            <p class="page-subtitle">Verificando que todos los componentes carguen correctamente</p>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-primary" onclick="testToast()">
                Probar Notificación
            </button>
        </div>
    </div>

    <!-- Design System Test -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Buttons Test -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Botones</h3>
            </div>
            <div class="card-body">
                <div class="space-y-3">
                    <button class="btn btn-primary">Primary Button</button>
                    <button class="btn btn-secondary">Secondary Button</button>
                    <button class="btn btn-success">Success Button</button>
                    <button class="btn btn-warning">Warning Button</button>
                    <button class="btn btn-danger">Danger Button</button>
                    <button class="btn btn-ghost">Ghost Button</button>
                </div>
            </div>
        </div>

        <!-- Forms Test -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Formularios</h3>
            </div>
            <div class="card-body">
                <form data-validate>
                    <div class="form-group">
                        <label class="form-label">Nombre <span class="required">*</span></label>
                        <input type="text" class="form-input" placeholder="Ingresa tu nombre" data-rules="required">
                        <div class="form-help">Este campo es obligatorio</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" class="form-input" placeholder="correo@ejemplo.com" data-rules="required|email">
                        <div class="form-help">Ingresa un email válido</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Servicio</label>
                        <select class="form-select">
                            <option>Básico</option>
                            <option>Premium</option>
                            <option>Enterprise</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Mensaje</label>
                        <textarea class="form-textarea" rows="3" placeholder="Escribe tu mensaje aquí..."></textarea>
                    </div>
                </form>
            </div>
        </div>

        <!-- Badges Test -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Badges</h3>
            </div>
            <div class="card-body">
                <div class="space-y-3">
                    <div class="badge badge-primary">Primary</div>
                    <div class="badge badge-success">Success</div>
                    <div class="badge badge-warning">Warning</div>
                    <div class="badge badge-error">Error</div>
                    <div class="badge badge-gray">Gray</div>
                    <div class="badge badge-outline">Outline</div>
                </div>
            </div>
        </div>

        <!-- Table Test -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tabla</h3>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Empresa A</td>
                                <td><span class="badge badge-success">Activo</span></td>
                                <td>$1,000</td>
                            </tr>
                            <tr>
                                <td>Empresa B</td>
                                <td><span class="badge badge-warning">Pendiente</span></td>
                                <td>$2,500</td>
                            </tr>
                            <tr>
                                <td>Empresa C</td>
                                <td><span class="badge badge-error">Inactivo</span></td>
                                <td>$3,200</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Stats Test -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Estadísticas</h3>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon stat-icon-blue">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">156</div>
                            <div class="stat-label">Clientes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading States Test -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Estados de Carga</h3>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div>
                        <p class="mb-2">Spinner:</p>
                        <div class="spinner spinner-md"></div>
                    </div>
                    
                    <div>
                        <p class="mb-2">Skeleton Text:</p>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text" style="width: 60%;"></div>
                    </div>
                    
                    <div>
                        <p class="mb-2">Loading Button:</p>
                        <button class="btn btn-primary loading">Loading...</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State Test -->
    <div class="card mt-6">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                </div>
                <h3 class="empty-state-title">Empty State Funciona</h3>
                <p class="empty-state-description">Este es un ejemplo de estado vacío diseñado correctamente.</p>
                <div class="empty-state-actions">
                    <button class="btn btn-primary">Acción Principal</button>
                    <button class="btn btn-secondary">Acción Secundaria</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function testToast() {
    window.uxSystem.showToast('¡El diseño moderno está funcionando correctamente! 🎉', 'success', {
        title: 'Test Exitoso',
        duration: 5000
    });
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('🎨 Modern Design System Loaded Successfully');
    console.log('✅ CSS Variables:', getComputedStyle(document.documentElement).getPropertyValue('--color-brand'));
    console.log('✅ UX System:', typeof window.uxSystem !== 'undefined');
    console.log('✅ Layout:', document.querySelector('.sidebar') ? 'Sidebar found' : 'Sidebar not found');
    
    // Test form validation
    const form = document.querySelector('form[data-validate]');
    if (form && window.uxSystem) {
        console.log('✅ Form validation ready');
    }
    
    // Test responsive design
    function checkResponsive() {
        const width = window.innerWidth;
        console.log(`📱 Screen width: ${width}px`);
        
        if (width < 768) {
            console.log('📱 Mobile view active');
        } else if (width < 1024) {
            console.log('📱 Tablet view active');
        } else {
            console.log('🖥️ Desktop view active');
        }
    }
    
    checkResponsive();
    window.addEventListener('resize', checkResponsive);
});
</script>
@endsection

<style>
.test-design-page {
    max-width: 100%;
}

.space-y-3 > * + * {
    margin-top: var(--space-3);
}

.space-y-4 > * + * {
    margin-top: var(--space-4);
}

.mt-6 {
    margin-top: var(--space-6);
}

.mb-2 {
    margin-bottom: var(--space-2);
}

/* Test specific styles */
.test-design-page .card {
    min-height: 300px;
}

.test-design-page .stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-4);
}

.test-design-page .stat-card {
    padding: var(--space-4);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    background: var(--color-surface);
}
</style>
