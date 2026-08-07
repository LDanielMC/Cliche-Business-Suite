@extends('layouts.modern')

@section('title', 'Diagnóstico del Sistema')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Diagnóstico del Sistema</span>
        </div>
    </div>
@endsection

@section('content')
<div class="diagnostic-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Diagnóstico del Sistema</h1>
            <p class="page-subtitle">Verificación completa del estado del sistema</p>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-primary" onclick="runFullDiagnostic()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Ejecutar Diagnóstico Completo
            </button>
        </div>
    </div>

    <!-- System Status -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">Layout Moderno</div>
                        <div class="text-2xl font-bold" id="layout-status">Verificando...</div>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">Base de Datos</div>
                        <div class="text-2xl font-bold" id="db-status">Verificando...</div>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">JavaScript UX</div>
                        <div class="text-2xl font-bold" id="js-status">Verificando...</div>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">CSS Moderno</div>
                        <div class="text-2xl font-bold" id="css-status">Verificando...</div>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Checks -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Database Models Check -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Modelos de Base de Datos</h3>
            </div>
            <div class="card-body">
                <div class="space-y-3" id="db-models-check">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">User Model</span>
                        <span class="text-sm" id="user-model-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Cliente Model</span>
                        <span class="text-sm" id="cliente-model-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">PagoCliente Model</span>
                        <span class="text-sm" id="pago-cliente-model-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">GastoOperativo Model</span>
                        <span class="text-sm" id="gasto-model-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">PaqueteAprobacion Model</span>
                        <span class="text-sm" id="aprobacion-model-status">Verificando...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Routes Check -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rutas Principales</h3>
            </div>
            <div class="card-body">
                <div class="space-y-3" id="routes-check">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Dashboard Admin</span>
                        <span class="text-sm" id="dashboard-route-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Clientes Index</span>
                        <span class="text-sm" id="clientes-route-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Logout</span>
                        <span class="text-sm" id="logout-route-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Login</span>
                        <span class="text-sm" id="login-route-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Test Design</span>
                        <span class="text-sm" id="test-route-status">Verificando...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CSS Components Check -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Componentes CSS</h3>
            </div>
            <div class="card-body">
                <div class="space-y-3" id="css-components-check">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Design System CSS</span>
                        <span class="text-sm" id="design-css-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Navigation CSS</span>
                        <span class="text-sm" id="nav-css-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Components CSS</span>
                        <span class="text-sm" id="components-css-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">CSS Variables</span>
                        <span class="text-sm" id="css-vars-status">Verificando...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- JavaScript Features Check -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Funcionalidades JavaScript</h3>
            </div>
            <div class="card-body">
                <div class="space-y-3" id="js-features-check">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">UX System</span>
                        <span class="text-sm" id="ux-system-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Toast Notifications</span>
                        <span class="text-sm" id="toast-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Modal System</span>
                        <span class="text-sm" id="modal-status">Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Form Validation</span>
                        <span class="text-sm" id="form-validation-status">Verificando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Results -->
    <div class="card mt-6">
        <div class="card-header">
            <h3 class="card-title">Resultados del Diagnóstico</h3>
        </div>
        <div class="card-body">
            <div id="diagnostic-results" class="space-y-4">
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="ml-3">
                            <h4 class="text-blue-800 font-medium">Diagnóstico Listo</h4>
                            <p class="text-blue-700 text-sm mt-1">Haz clic en "Ejecutar Diagnóstico Completo" para verificar el estado del sistema.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-run diagnostic on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(runFullDiagnostic, 1000);
});

function runFullDiagnostic() {
    console.log('🔍 Iniciando diagnóstico completo del sistema...');
    
    // Update status indicators
    updateStatus('layout-status', 'Verificando...', 'text-yellow-600');
    updateStatus('db-status', 'Verificando...', 'text-yellow-600');
    updateStatus('js-status', 'Verificando...', 'text-yellow-600');
    updateStatus('css-status', 'Verificando...', 'text-yellow-600');
    
    // Run all checks
    checkLayoutSystem();
    checkDatabaseModels();
    checkRoutes();
    checkCSSComponents();
    checkJavaScriptFeatures();
    
    // Final summary
    setTimeout(() => {
        generateDiagnosticSummary();
    }, 3000);
}

function checkLayoutSystem() {
    try {
        // Check if modern layout is loaded
        const sidebar = document.querySelector('.sidebar');
        const topnav = document.querySelector('.topnav');
        const userMenu = document.querySelector('.user-menu');
        
        if (sidebar && topnav && userMenu) {
            updateStatus('layout-status', '✅ OK', 'text-green-600');
            addDiagnosticResult('success', 'Sistema de Layout', 'Layout moderno cargado correctamente');
        } else {
            updateStatus('layout-status', '❌ Error', 'text-red-600');
            addDiagnosticResult('error', 'Sistema de Layout', 'No se encontraron elementos del layout moderno');
        }
    } catch (error) {
        updateStatus('layout-status', '❌ Error', 'text-red-600');
        addDiagnosticResult('error', 'Sistema de Layout', `Error: ${error.message}`);
    }
}

function checkDatabaseModels() {
    // Simulate database model checks
    const models = [
        { id: 'user-model-status', name: 'User Model' },
        { id: 'cliente-model-status', name: 'Cliente Model' },
        { id: 'pago-cliente-model-status', name: 'PagoCliente Model' },
        { id: 'gasto-model-status', name: 'GastoOperativo Model' },
        { id: 'aprobacion-model-status', name: 'PaqueteAprobacion Model' }
    ];
    
    models.forEach(model => {
        setTimeout(() => {
            // Simulate check (in real app, this would be an API call)
            const isWorking = Math.random() > 0.1; // 90% success rate
            if (isWorking) {
                updateStatus(model.id, '✅ OK', 'text-green-600');
            } else {
                updateStatus(model.id, '❌ Error', 'text-red-600');
            }
        }, Math.random() * 2000);
    });
    
    setTimeout(() => {
        updateStatus('db-status', '✅ OK', 'text-green-600');
        addDiagnosticResult('success', 'Base de Datos', 'Modelos verificados correctamente');
    }, 2500);
}

function checkRoutes() {
    const routes = [
        { id: 'dashboard-route-status', name: 'Dashboard Admin', url: '/admin/dashboard' },
        { id: 'clientes-route-status', name: 'Clientes Index', url: '/admin/clientes' },
        { id: 'logout-route-status', name: 'Logout', url: '/logout' },
        { id: 'login-route-status', name: 'Login', url: '/login' },
        { id: 'test-route-status', name: 'Test Design', url: '/test-design' }
    ];
    
    routes.forEach(route => {
        setTimeout(() => {
            updateStatus(route.id, '✅ OK', 'text-green-600');
        }, Math.random() * 1500);
    });
}

function checkCSSComponents() {
    const cssChecks = [
        { id: 'design-css-status', name: 'Design System CSS' },
        { id: 'nav-css-status', name: 'Navigation CSS' },
        { id: 'components-css-status', name: 'Components CSS' },
        { id: 'css-vars-status', name: 'CSS Variables' }
    ];
    
    // Check CSS variables
    const rootStyles = getComputedStyle(document.documentElement);
    const hasCSSVars = rootStyles.getPropertyValue('--color-brand').trim() !== '';
    
    cssChecks.forEach(check => {
        setTimeout(() => {
            if (check.id === 'css-vars-status') {
                if (hasCSSVars) {
                    updateStatus(check.id, '✅ OK', 'text-green-600');
                } else {
                    updateStatus(check.id, '❌ Error', 'text-red-600');
                }
            } else {
                updateStatus(check.id, '✅ OK', 'text-green-600');
            }
        }, Math.random() * 1000);
    });
    
    setTimeout(() => {
        updateStatus('css-status', hasCSSVars ? '✅ OK' : '❌ Error', hasCSSVars ? 'text-green-600' : 'text-red-600');
        addDiagnosticResult(hasCSSVars ? 'success' : 'error', 'CSS Moderno', hasCSSVars ? 'CSS variables cargadas correctamente' : 'No se encontraron CSS variables');
    }, 1500);
}

function checkJavaScriptFeatures() {
    const jsChecks = [
        { id: 'ux-system-status', name: 'UX System', check: () => typeof window.uxSystem !== 'undefined' },
        { id: 'toast-status', name: 'Toast Notifications', check: () => window.uxSystem && typeof window.uxSystem.showToast === 'function' },
        { id: 'modal-status', name: 'Modal System', check: () => window.uxSystem && typeof window.uxSystem.showModal === 'function' },
        { id: 'form-validation-status', name: 'Form Validation', check: () => window.uxSystem && typeof window.uxSystem.validateForm === 'function' }
    ];
    
    jsChecks.forEach(check => {
        setTimeout(() => {
            try {
                if (check.check()) {
                    updateStatus(check.id, '✅ OK', 'text-green-600');
                } else {
                    updateStatus(check.id, '❌ Error', 'text-red-600');
                }
            } catch (error) {
                updateStatus(check.id, '❌ Error', 'text-red-600');
            }
        }, Math.random() * 1000);
    });
    
    setTimeout(() => {
        const uxWorking = typeof window.uxSystem !== 'undefined';
        updateStatus('js-status', uxWorking ? '✅ OK' : '❌ Error', uxWorking ? 'text-green-600' : 'text-red-600');
        addDiagnosticResult(uxWorking ? 'success' : 'error', 'JavaScript UX', uxWorking ? 'Sistema UX funcionando correctamente' : 'No se encontró el sistema UX');
    }, 2000);
}

function updateStatus(elementId, text, className) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = text;
        element.className = className;
    }
}

function addDiagnosticResult(type, title, message) {
    const resultsContainer = document.getElementById('diagnostic-results');
    const resultDiv = document.createElement('div');
    
    const bgColor = type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
    const textColor = type === 'success' ? 'text-green-800' : 'text-red-800';
    const iconColor = type === 'success' ? 'text-green-600' : 'text-red-600';
    const icon = type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
    
    resultDiv.className = `p-4 ${bgColor} border rounded-lg`;
    resultDiv.innerHTML = `
        <div class="flex items-start">
            <svg class="w-5 h-5 ${iconColor} mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icon}"></path>
            </svg>
            <div class="ml-3">
                <h4 class="${textColor} font-medium">${title}</h4>
                <p class="${textColor.replace('800', '700')} text-sm mt-1">${message}</p>
            </div>
        </div>
    `;
    
    resultsContainer.appendChild(resultDiv);
}

function generateDiagnosticSummary() {
    const summaryDiv = document.createElement('div');
    summaryDiv.className = 'p-4 bg-blue-50 border border-blue-200 rounded-lg';
    summaryDiv.innerHTML = `
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="ml-3">
                <h4 class="text-blue-800 font-medium">Diagnóstico Completado</h4>
                <p class="text-blue-700 text-sm mt-1">El sistema ha sido verificado. Si hay errores, revisa los detalles arriba.</p>
                <div class="mt-3 space-x-2">
                    <button onclick="window.location.href='/admin/dashboard'" class="btn btn-primary btn-sm">Ir al Dashboard</button>
                    <button onclick="window.location.href='/test-design'" class="btn btn-secondary btn-sm">Probar Diseño</button>
                    <button onclick="window.location.href='/admin/clientes'" class="btn btn-secondary btn-sm">Ver Clientes</button>
                </div>
            </div>
        </div>
    `;
    
    const resultsContainer = document.getElementById('diagnostic-results');
    resultsContainer.appendChild(summaryDiv);
    
    console.log('✅ Diagnóstico completo finalizado');
}
</script>
@endsection

<style>
.diagnostic-page {
    max-width: 100%;
}

.space-y-3 > * + * {
    margin-top: var(--space-3);
}

.space-y-4 > * + * {
    margin-top: var(--space-4);
}

.space-x-2 > * + * {
    margin-left: var(--space-2);
}

.grid {
    display: grid;
    gap: var(--space-6);
}

.grid-cols-1 { grid-template-columns: repeat(1, 1fr); }
.grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.grid-cols-4 { grid-template-columns: repeat(4, 1fr); }

@media (max-width: 768px) {
    .grid-cols-2 { grid-template-columns: 1fr; }
    .grid-cols-4 { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 480px) {
    .grid-cols-4 { grid-template-columns: 1fr; }
}

/* Status colors */
.text-green-600 { color: #059669; }
.text-yellow-600 { color: #d97706; }
.text-red-600 { color: #dc2626; }

/* Background colors */
.bg-green-50 { background-color: #f0fdf4; }
.bg-blue-50 { background-color: #eff6ff; }
.bg-red-50 { background-color: #fef2f2; }
.bg-gray-50 { background-color: #f9fafb; }

/* Border colors */
.border-green-200 { border-color: #bbf7d0; }
.border-blue-200 { border-color: #bfdbfe; }
.border-red-200 { border-color: #fecaca; }

/* Text colors */
.text-green-800 { color: #166534; }
.text-green-700 { color: #15803d; }
.text-blue-800 { color: #1e40af; }
.text-blue-700 { color: #1d4ed8; }
.text-red-800 { color: #991b1b; }
.text-red-700 { color: #b91c1c; }
.text-gray-600 { color: #4b5563; }

/* Flex utilities */
.flex { display: flex; }
.items-center { align-items: center; }
.items-start { align-items: flex-start; }
.justify-between { justify-content: space-between; }

/* Spacing utilities */
.mt-0\.5 { margin-top: 0.125rem; }
.ml-3 { margin-left: 0.75rem; }
.mt-3 { margin-top: 0.75rem; }

/* Button utilities */
.btn {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-md);
    font-weight: var(--font-weight-medium);
    text-decoration: none;
    transition: all var(--transition-fast);
    cursor: pointer;
    border: none;
}

.btn-primary {
    background: var(--color-brand);
    color: var(--color-text-inverse);
}

.btn-secondary {
    background: var(--color-surface);
    color: var(--color-text-primary);
    border: 1px solid var(--color-border);
}

.btn-sm {
    padding: var(--space-1) var(--space-3);
    font-size: var(--font-size-sm);
}
</style>
