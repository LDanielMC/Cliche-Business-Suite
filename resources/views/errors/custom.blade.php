@extends('layouts.app-new')

@section('title', 'Error - {{ $exception->getMessage() ?? 'Ha ocurrido un error' }}')

@section('content')
<div class="error-page">
    <div class="error-container">
        <div class="error-illustration">
            @php
                $errorCode = $exception->getStatusCode() ?? 500;
                $errorType = match($errorCode) {
                    404 => 'not-found',
                    403 => 'forbidden',
                    500 => 'server-error',
                    419 => 'csrf-expired',
                    default => 'generic'
                };
            @endphp
            
            @switch($errorType)
                @case('not-found')
                    <svg width="200" height="200" viewBox="0 0 200 200" fill="none">
                        <circle cx="100" cy="100" r="80" stroke="#e5e7eb" stroke-width="8"/>
                        <path d="M70 70 L130 130 M130 70 L70 130" stroke="#ef4444" stroke-width="8" stroke-linecap="round"/>
                    </svg>
                @break
                
                @case('forbidden')
                    <svg width="200" height="200" viewBox="0 0 200 200" fill="none">
                        <path d="M40 160 L100 40 L160 160 Z" stroke="#f59e0b" stroke-width="8" stroke-linejoin="round"/>
                        <circle cx="100" cy="130" r="8" fill="#f59e0b"/>
                        <path d="M100 70 L100 110" stroke="#f59e0b" stroke-width="8" stroke-linecap="round"/>
                    </svg>
                @break
                
                @case('server-error')
                    <svg width="200" height="200" viewBox="0 0 200 200" fill="none">
                        <rect x="40" y="60" width="120" height="80" rx="8" stroke="#ef4444" stroke-width="8"/>
                        <path d="M60 90 L140 90 M60 110 L140 110" stroke="#ef4444" stroke-width="6" stroke-linecap="round"/>
                        <circle cx="100" cy="40" r="20" stroke="#ef4444" stroke-width="6"/>
                        <path d="M100 20 L100 30 M100 50 L100 60" stroke="#ef4444" stroke-width="6" stroke-linecap="round"/>
                    </svg>
                @break
                
                @case('csrf-expired')
                    <svg width="200" height="200" viewBox="0 0 200 200" fill="none">
                        <circle cx="100" cy="100" r="80" stroke="#f59e0b" stroke-width="8"/>
                        <path d="M100 60 L100 100 M100 120 L100 140" stroke="#f59e0b" stroke-width="8" stroke-linecap="round"/>
                    </svg>
                @break
                
                @default
                    <svg width="200" height="200" viewBox="0 0 200 200" fill="none">
                        <circle cx="100" cy="100" r="80" stroke="#6b7280" stroke-width="8"/>
                        <text x="100" y="110" text-anchor="middle" font-size="48" font-weight="bold" fill="#6b7280">!</text>
                    </svg>
                @endswitch
        </div>
        
        <div class="error-content">
            <h1 class="error-title">
                @switch($errorType)
                    @case('not-found')
                        Página no encontrada
                    @break
                    @case('forbidden')
                        Acceso denegado
                    @break
                    @case('server-error')
                        Error del servidor
                    @break
                    @case('csrf-expired')
                        Sesión expirada
                    @break
                    @default
                        Ha ocurrido un error
                @endswitch
            </h1>
            
            <p class="error-description">
                @switch($errorType)
                    @case('not-found')
                        La página que estás buscando no existe o ha sido movida.
                    @break
                    @case('forbidden')
                        No tienes permisos para acceder a esta página.
                    @break
                    @case('server-error')
                        Ha ocurrido un error inesperado en nuestros servidores. Estamos trabajando para solucionarlo.
                    @break
                    @case('csrf-expired')
                        Tu sesión ha expirado por seguridad. Por favor, inicia sesión nuevamente.
                    @break
                    @default
                        Ha ocurrido un error inesperado. Por favor, intenta nuevamente.
                @endswitch
            </p>
            
            @if(config('app.debug'))
                <details class="error-details">
                    <summary>Detalles técnicos (solo visible en desarrollo)</summary>
                    <div class="error-debug">
                        <div class="debug-item">
                            <strong>Error:</strong> {{ get_class($exception) }}
                        </div>
                        <div class="debug-item">
                            <strong>Mensaje:</strong> {{ $exception->getMessage() }}
                        </div>
                        <div class="debug-item">
                            <strong>Código:</strong> {{ $exception->getCode() }}
                        </div>
                        <div class="debug-item">
                            <strong>Archivo:</strong> {{ $exception->getFile() }}:{{ $exception->getLine() }}
                        </div>
                        @if($exception->getTrace())
                            <div class="debug-item">
                                <strong>Stack Trace:</strong>
                                <pre class="stack-trace">{{ $exception->getTraceAsString() }}</pre>
                            </div>
                        @endif
                    </div>
                </details>
            @endif
            
            <div class="error-actions">
                @switch($errorType)
                    @case('csrf-expired')
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Iniciar Sesión
                        </a>
                    @break
                    
                    @case('forbidden')
                        @if(auth()->check())
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Ir al Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Iniciar Sesión
                            </a>
                        @endif
                    @break
                    
                    @default
                        <button onclick="history.back()" class="btn btn-secondary">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver Atrás
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Ir al Inicio
                        </a>
                @endswitch
            </div>
            
            <div class="help-section">
                <h3>¿Necesitas ayuda?</h3>
                <p>Si el problema persiste, puedes:</p>
                <ul>
                    <li>Intentar recargar la página</li>
                    <li>Verificar tu conexión a internet</li>
                    <li>Contactar al equipo de soporte</li>
                </ul>
                
                <div class="contact-options">
                    <button onclick="reportError()" class="btn btn-ghost">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Reportar Error
                    </button>
                    <button onclick="showHelp()" class="btn btn-ghost">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Obtener Ayuda
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function reportError() {
    const errorData = {
        url: window.location.href,
        userAgent: navigator.userAgent,
        timestamp: new Date().toISOString(),
        error: '{{ $exception->getMessage() }}',
        code: '{{ $exception->getCode() }}'
    };
    
    // Send error report (implement actual endpoint)
    console.log('Error report:', errorData);
    
    if (window.uxSystem) {
        window.uxSystem.showToast('Error reportado. Gracias por tu ayuda.', 'success');
    }
}

function showHelp() {
    if (window.uxSystem) {
        window.uxSystem.showHelpMenu();
    }
}

// Auto-refresh for server errors
@if($errorType === 'server-error')
    setTimeout(() => {
        if (confirm('¿Deseas intentar recargar la página?')) {
            window.location.reload();
        }
    }, 10000);
@endif
</script>
@endsection

<style>
.error-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-4);
    background: linear-gradient(135deg, var(--color-gray-50) 0%, var(--color-primary-50) 100%);
}

.error-container {
    background: white;
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-2xl);
    padding: var(--space-8);
    max-width: 600px;
    width: 100%;
    text-align: center;
    animation: error-slide-in 0.5s ease-out;
}

@keyframes error-slide-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.error-illustration {
    margin-bottom: var(--space-6);
}

.error-illustration svg {
    max-width: 200px;
    height: auto;
    margin: 0 auto;
}

.error-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--color-gray-900);
    margin: 0 0 var(--space-4) 0;
}

.error-description {
    font-size: 1.125rem;
    color: var(--color-gray-600);
    line-height: 1.6;
    margin: 0 0 var(--space-6) 0;
}

.error-details {
    text-align: left;
    margin: var(--space-6) 0;
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.error-details summary {
    padding: var(--space-4);
    background: var(--color-gray-50);
    cursor: pointer;
    font-weight: 500;
    color: var(--color-gray-700);
    border: none;
    width: 100%;
    text-align: left;
}

.error-details summary:hover {
    background: var(--color-gray-100);
}

.error-debug {
    padding: var(--space-4);
    background: #1f2937;
    color: #f3f4f6;
    font-family: var(--font-mono);
    font-size: 0.875rem;
}

.debug-item {
    margin-bottom: var(--space-3);
}

.debug-item:last-child {
    margin-bottom: 0;
}

.stack-trace {
    background: #111827;
    padding: var(--space-3);
    border-radius: var(--radius-md);
    overflow-x: auto;
    white-space: pre-wrap;
    word-break: break-all;
}

.error-actions {
    display: flex;
    gap: var(--space-3);
    justify-content: center;
    margin-bottom: var(--space-8);
    flex-wrap: wrap;
}

.help-section {
    border-top: 1px solid var(--color-gray-200);
    padding-top: var(--space-6);
}

.help-section h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--color-gray-900);
    margin: 0 0 var(--space-3) 0;
}

.help-section p {
    color: var(--color-gray-600);
    margin: 0 0 var(--space-3) 0;
}

.help-section ul {
    text-align: left;
    color: var(--color-gray-600);
    margin: 0 0 var(--space-4) 0;
    padding-left: var(--space-6);
}

.help-section li {
    margin-bottom: var(--space-1);
}

.contact-options {
    display: flex;
    gap: var(--space-3);
    justify-content: center;
    flex-wrap: wrap;
}

/* Responsive Design */
@media (max-width: 768px) {
    .error-container {
        padding: var(--space-6);
    }
    
    .error-title {
        font-size: 1.5rem;
    }
    
    .error-description {
        font-size: 1rem;
    }
    
    .error-actions {
        flex-direction: column;
    }
    
    .error-actions .btn {
        width: 100%;
    }
    
    .contact-options {
        flex-direction: column;
    }
    
    .contact-options .btn {
        width: 100%;
    }
}

/* Error-specific colors */
.error-page[data-error="404"] .error-illustration svg {
    color: #ef4444;
}

.error-page[data-error="403"] .error-illustration svg {
    color: #f59e0b;
}

.error-page[data-error="500"] .error-illustration svg {
    color: #ef4444;
}

.error-page[data-error="419"] .error-illustration svg {
    color: #f59e0b;
}
</style>
