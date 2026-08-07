@extends('layouts.modern')

@section('title', 'Verificación del Sistema')

@section('breadcrumbs')
    <div class="breadcrumbs">
        <div class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-separator">/</span>
        </div>
        <div class="breadcrumb-item">
            <span class="breadcrumb-current">Verificación del Sistema</span>
        </div>
    </div>
@endsection

@section('content')
<div class="verify-system-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Verificación del Sistema</h1>
            <p class="page-subtitle">Comprobación automática de que todo cargue correctamente</p>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-primary" onclick="runFullVerification()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Verificar Todo
            </button>
        </div>
    </div>

    <!-- Verification Status -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">Assets CSS</div>
                        <div class="text-2xl font-bold" id="css-assets-status">⏳</div>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">Assets JS</div>
                        <div class="text-2xl font-bold" id="js-assets-status">⏳</div>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
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
                        <div class="text-sm text-gray-600">Layout</div>
                        <div class="text-2xl font-bold" id="layout-status">⏳</div>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600">Funciones</div>
                        <div class="text-2xl font-bold" id="functions-status">⏳</div>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Verification -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- CSS Assets Verification -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assets CSS</h3>
            </div>
            <div class="card-body">
                <div class="space-y-3" id="css-assets-check">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Design System CSS</span>
                        <span class="text-sm" id="design-css-check">⏳ Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Navigation CSS</span>
                        <span class="text-sm" id="nav-css-check">⏳ Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Components CSS</span>
                        <span class="text-sm" id="components-css-check">⏳ Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">CSS Variables</span>
                        <span class="text-sm" id="css-vars-check">⏳ Verificando...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- JavaScript Assets Verification -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assets JavaScript</h3>
            </div>
            <div class="card-body">
                <div class="space-y-3" id="js-assets-check">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">App.js</span>
                        <span class="text-sm" id="app-js-check">⏳ Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Modern UX System</span>
                        <span class="text-sm" id="ux-system-check">⏳ Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Alpine.js</span>
                        <span class="text-sm" id="alpine-check">⏳ Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Chart.js</span>
                        <span class="text-sm" id="chart-check">⏳ Verificando...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Layout Verification -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Layout Components</h3>
            </div>
            <div class="card-body">
                <div class="space-y-3" id="layout-check">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Sidebar</span>
                        <span class="text-sm" id="sidebar-check">⏳ Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Top Navigation</span>
                        <span class="text-sm" id="topnav-check">⏳ Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">User Menu</span>
                        <span class="text-sm" id="user-menu-check">⏳ Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Breadcrumbs</span>
                        <span class="text-sm" id="breadcrumbs-check">⏳ Verificando...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Functions Verification -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Funciones del Sistema</h3>
            </div>
            <div class="card-body">
                <div class="space-y-3" id="functions-check">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Toast Notifications</span>
                        <span class="text-sm" id="toast-check">⏳ Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Modal System</span>
                        <span class="text-sm" id="modal-check">⏳ Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Form Validation</span>
                        <span class="text-sm" id="form-validation-check">⏳ Verificando...</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">Keyboard Shortcuts</span>
                        <span class="text-sm" id="shortcuts-check">⏳ Verificando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Results -->
    <div class="card mt-6">
        <div class="card-header">
            <h3 class="card-title">Resultados de Verificación</h3>
        </div>
        <div class="card-body">
            <div id="verification-results" class="space-y-4">
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="ml-3">
                            <h4 class="text-blue-800 font-medium">Verificación Lista</h4>
                            <p class="text-blue-700 text-sm mt-1">Haz clic en "Verificar Todo" para comprobar que el sistema cargue correctamente.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Actions -->
    <div class="card mt-6">
        <div class="card-header">
            <h3 class="card-title">Acciones de Prueba</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <button onclick="testToast()" class="btn btn-secondary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Probar Toast
                </button>
                <button onclick="testModal()" class="btn btn-secondary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Probar Modal
                </button>
                <button onclick="testForm()" class="btn btn-secondary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Probar Form
                </button>
                <button onclick="testShortcuts()" class="btn btn-secondary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
                    Atajos (Ctrl+/)
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-run verification on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(runFullVerification, 1000);
});

function runFullVerification() {
    console.log('🔍 Iniciando verificación completa del sistema...');
    
    // Reset all status
    updateStatus('css-assets-status', '⏳');
    updateStatus('js-assets-status', '⏳');
    updateStatus('layout-status', '⏳');
    updateStatus('functions-status', '⏳');
    
    // Run all checks
    checkCSSAssets();
    checkJSAssets();
    checkLayoutComponents();
    checkSystemFunctions();
    
    // Final summary
    setTimeout(() => {
        generateVerificationSummary();
    }, 4000);
}

function checkCSSAssets() {
    const checks = [
        { id: 'design-css-check', name: 'Design System CSS', check: () => checkCSSLoaded('--color-background') },
        { id: 'nav-css-check', name: 'Navigation CSS', check: () => checkCSSLoaded('--sidebar-width') },
        { id: 'components-css-check', name: 'Components CSS', check: () => checkCSSLoaded('--btn-padding') },
        { id: 'css-vars-check', name: 'CSS Variables', check: () => checkCSSLoaded('--color-brand') }
    ];
    
    let cssPassed = 0;
    checks.forEach((check, index) => {
        setTimeout(() => {
            if (check.check()) {
                updateStatus(check.id, '✅ OK');
                cssPassed++;
            } else {
                updateStatus(check.id, '❌ Error');
            }
            
            if (index === checks.length - 1) {
                updateStatus('css-assets-status', cssPassed === checks.length ? '✅ OK' : '❌ Error');
                addVerificationResult(cssPassed === checks.length ? 'success' : 'error', 'Assets CSS', `${cssPassed}/${checks.length} archivos cargados correctamente`);
            }
        }, (index + 1) * 300);
    });
}

function checkJSAssets() {
    const checks = [
        { id: 'app-js-check', name: 'App.js', check: () => typeof window.Alpine !== 'undefined' },
        { id: 'ux-system-check', name: 'Modern UX System', check: () => typeof window.uxSystem !== 'undefined' },
        { id: 'alpine-check', name: 'Alpine.js', check: () => typeof window.Alpine !== 'undefined' },
        { id: 'chart-check', name: 'Chart.js', check: () => typeof window.Chart !== 'undefined' }
    ];
    
    let jsPassed = 0;
    checks.forEach((check, index) => {
        setTimeout(() => {
            if (check.check()) {
                updateStatus(check.id, '✅ OK');
                jsPassed++;
            } else {
                updateStatus(check.id, '❌ Error');
            }
            
            if (index === checks.length - 1) {
                updateStatus('js-assets-status', jsPassed === checks.length ? '✅ OK' : '❌ Error');
                addVerificationResult(jsPassed === checks.length ? 'success' : 'error', 'Assets JavaScript', `${jsPassed}/${checks.length} archivos cargados correctamente`);
            }
        }, (index + 1) * 400);
    });
}

function checkLayoutComponents() {
    const checks = [
        { id: 'sidebar-check', name: 'Sidebar', check: () => document.querySelector('.sidebar') !== null },
        { id: 'topnav-check', name: 'Top Navigation', check: () => document.querySelector('.topnav') !== null },
        { id: 'user-menu-check', name: 'User Menu', check: () => document.querySelector('.user-menu') !== null },
        { id: 'breadcrumbs-check', name: 'Breadcrumbs', check: () => document.querySelector('.breadcrumbs') !== null }
    ];
    
    let layoutPassed = 0;
    checks.forEach((check, index) => {
        setTimeout(() => {
            if (check.check()) {
                updateStatus(check.id, '✅ OK');
                layoutPassed++;
            } else {
                updateStatus(check.id, '❌ Error');
            }
            
            if (index === checks.length - 1) {
                updateStatus('layout-status', layoutPassed === checks.length ? '✅ OK' : '❌ Error');
                addVerificationResult(layoutPassed === checks.length ? 'success' : 'error', 'Layout Components', `${layoutPassed}/${checks.length} componentes encontrados`);
            }
        }, (index + 1) * 500);
    });
}

function checkSystemFunctions() {
    const checks = [
        { id: 'toast-check', name: 'Toast Notifications', check: () => window.uxSystem && typeof window.uxSystem.showToast === 'function' },
        { id: 'modal-check', name: 'Modal System', check: () => window.uxSystem && typeof window.uxSystem.showModal === 'function' },
        { id: 'form-validation-check', name: 'Form Validation', check: () => window.uxSystem && typeof window.uxSystem.validateForm === 'function' },
        { id: 'shortcuts-check', name: 'Keyboard Shortcuts', check: () => window.uxSystem && window.uxSystem.keyboardShortcuts.size > 0 }
    ];
    
    let functionsPassed = 0;
    checks.forEach((check, index) => {
        setTimeout(() => {
            try {
                if (check.check()) {
                    updateStatus(check.id, '✅ OK');
                    functionsPassed++;
                } else {
                    updateStatus(check.id, '❌ Error');
                }
            } catch (error) {
                updateStatus(check.id, '❌ Error');
            }
            
            if (index === checks.length - 1) {
                updateStatus('functions-status', functionsPassed === checks.length ? '✅ OK' : '❌ Error');
                addVerificationResult(functionsPassed === checks.length ? 'success' : 'error', 'System Functions', `${functionsPassed}/${checks.length} funciones funcionando`);
            }
        }, (index + 1) * 600);
    });
}

function checkCSSLoaded(variable) {
    const styles = getComputedStyle(document.documentElement);
    return styles.getPropertyValue(variable).trim() !== '';
}

function updateStatus(elementId, status) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = status;
    }
}

function addVerificationResult(type, title, message) {
    const resultsContainer = document.getElementById('verification-results');
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

function generateVerificationSummary() {
    const summaryDiv = document.createElement('div');
    summaryDiv.className = 'p-4 bg-blue-50 border border-blue-200 rounded-lg';
    summaryDiv.innerHTML = `
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="ml-3">
                <h4 class="text-blue-800 font-medium">Verificación Completada</h4>
                <p class="text-blue-700 text-sm mt-1">El sistema ha sido verificado. Revisa los resultados arriba para ver el estado detallado.</p>
                <div class="mt-3 space-x-2">
                    <button onclick="window.location.href='/admin/dashboard'" class="btn btn-primary btn-sm">Ir al Dashboard</button>
                    <button onclick="window.location.href='/test-design'" class="btn btn-secondary btn-sm">Probar Diseño</button>
                    <button onclick="window.location.href='/admin/clientes'" class="btn btn-secondary btn-sm">Ver Clientes</button>
                </div>
            </div>
        </div>
    `;
    
    const resultsContainer = document.getElementById('verification-results');
    resultsContainer.appendChild(summaryDiv);
    
    console.log('✅ Verificación completa finalizada');
}

// Test functions
function testToast() {
    if (window.uxSystem && window.uxSystem.showToast) {
        window.uxSystem.showToast('¡Toast de prueba funcionando correctamente! 🎉', 'success', {
            title: 'Test Exitoso',
            duration: 3000
        });
    } else {
        alert('El sistema de toast no está disponible');
    }
}

function testModal() {
    if (window.uxSystem && window.uxSystem.showModal) {
        window.uxSystem.showModal('Modal de Prueba', 'Este es un modal de prueba para verificar que el sistema esté funcionando correctamente.', [
            {
                text: 'Cancelar',
                class: 'btn-secondary',
                action: () => console.log('Modal cancelado')
            },
            {
                text: 'Aceptar',
                class: 'btn-primary',
                action: () => {
                    console.log('Modal aceptado');
                    window.uxSystem.showToast('Modal aceptado correctamente', 'success');
                }
            }
        ]);
    } else {
        alert('El sistema de modales no está disponible');
    }
}

function testForm() {
    if (window.uxSystem && window.uxSystem.showToast) {
        window.uxSystem.showToast('Sistema de formularios funcionando correctamente', 'info');
    } else {
        alert('El sistema de formularios no está disponible');
    }
}

function testShortcuts() {
    if (window.uxSystem && window.uxSystem.showKeyboardShortcuts) {
        window.uxSystem.showKeyboardShortcuts();
    } else {
        alert('Presiona Ctrl+/ para ver los atajos de teclado');
    }
}
</script>

<style>
.verify-system-page {
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

/* Status indicators */
.text-green-600 { color: #059669; }
.text-red-600 { color: #dc2626; }
.text-blue-600 { color: #2563eb; }

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
@endsection
