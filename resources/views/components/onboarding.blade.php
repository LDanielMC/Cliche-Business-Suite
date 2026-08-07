@props([
    'steps' => [],
    'showOnLoad' => false
])

<div id="onboarding-overlay" class="onboarding-overlay" style="display: {{ $showOnLoad ? 'flex' : 'none' }};">
    <div class="onboarding-backdrop" onclick="closeOnboarding()"></div>
    
    @if(count($steps) > 0)
        <div class="onboarding-container">
            <!-- Progress Bar -->
            <div class="onboarding-progress">
                <div class="progress-bar">
                    <div class="progress-fill" id="onboarding-progress" style="width: 0%"></div>
                </div>
                <div class="progress-text">
                    Paso <span id="current-step">1</span> de {{ count($steps) }}
                </div>
            </div>
            
            <!-- Step Content -->
            <div class="onboarding-content" id="onboarding-content">
                @foreach($steps as $index => $step)
                    <div class="onboarding-step" data-step="{{ $index + 1 }}" style="{{ $index === 0 ? 'display: block' : 'display: none' }}">
                        @if($step['image'])
                            <div class="step-image">
                                <img src="{{ $step['image'] }}" alt="{{ $step['title'] }}">
                            </div>
                        @endif
                        
                        <div class="step-header">
                            <h2 class="step-title">{{ $step['title'] }}</h2>
                            @if($step['subtitle'])
                                <p class="step-subtitle">{{ $step['subtitle'] }}</p>
                            @endif
                        </div>
                        
                        <div class="step-body">
                            <p class="step-description">{{ $step['description'] }}</p>
                            
                            @if(isset($step['features']) && count($step['features']) > 0)
                                <ul class="step-features">
                                    @foreach($step['features'] as $feature)
                                        <li class="feature-item">
                                            <svg class="feature-icon" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                            </svg>
                                            {{ $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            
                            @if(isset($step['tip']))
                                <div class="step-tip">
                                    <svg class="tip-icon" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                                    </svg>
                                    <span class="tip-text">{{ $step['tip'] }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="step-actions">
                            @if($index > 0)
                                <button type="button" class="btn btn-secondary" onclick="previousStep()">
                                    Anterior
                                </button>
                            @endif
                            
                            @if($index === count($steps) - 1)
                                <button type="button" class="btn btn-primary" onclick="completeOnboarding()">
                                    Comenzar a usar la aplicación
                                </button>
                            @else
                                <button type="button" class="btn btn-primary" onclick="nextStep()">
                                    Siguiente
                                </button>
                            @endif
                            
                            @if($index === 0)
                                <button type="button" class="btn btn-ghost" onclick="skipOnboarding()">
                                    Omitir tutorial
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Navigation Dots -->
            <div class="onboarding-dots">
                @foreach($steps as $index => $step)
                    <button 
                        type="button" 
                        class="dot {{ $index === 0 ? 'active' : '' }}" 
                        onclick="goToStep({{ $index + 1 }})"
                        aria-label="Ir al paso {{ $index + 1 }}"
                    ></button>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Floating Help Button -->
<button 
    type="button" 
    class="help-button" 
    onclick="showHelpMenu()"
    aria-label="Ayuda"
    data-tooltip="Presiona ? para atajos de teclado"
>
    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
</button>

<!-- Help Menu -->
<div class="help-menu" id="help-menu" style="display: none;">
    <div class="help-menu-header">
        <h3>Ayuda Rápida</h3>
        <button type="button" class="btn btn-ghost btn-sm" onclick="hideHelpMenu()">×</button>
    </div>
    
    <div class="help-menu-content">
        <div class="help-section">
            <h4>Atajos de Teclado</h4>
            <div class="shortcut-list">
                <div class="shortcut-item">
                    <kbd>Ctrl</kbd> + <kbd>K</kbd>
                    <span>Búsqueda global</span>
                </div>
                <div class="shortcut-item">
                    <kbd>Ctrl</kbd> + <kbd>/</kbd>
                    <span>Mostrar atajos</span>
                </div>
                <div class="shortcut-item">
                    <kbd>Ctrl</kbd> + <kbd>B</kbd>
                    <span>Alternar menú</span>
                </div>
                <div class="shortcut-item">
                    <kbd>Esc</kbd>
                    <span>Cerrar modales</span>
                </div>
            </div>
        </div>
        
        <div class="help-section">
            <h4>Enlaces Rápidos</h4>
            <div class="quick-links">
                <a href="{{ route('admin.dashboard') }}" class="help-link">Dashboard</a>
                <a href="{{ route('clientes.index') }}" class="help-link">Clientes</a>
                <a href="{{ route('gastos.index') }}" class="help-link">Gastos</a>
                <a href="{{ route('reportes.financiero') }}" class="help-link">Reportes</a>
            </div>
        </div>
        
        <div class="help-section">
            <h4>Soporte</h4>
            <div class="support-links">
                <button type="button" class="help-link" onclick="startTour()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Iniciar Tour
                </button>
                <button type="button" class="help-link" onclick="showDocumentation()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Documentación
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Onboarding Overlay */
.onboarding-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: var(--z-modal);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-4);
}

.onboarding-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(4px);
}

.onboarding-container {
    position: relative;
    background: white;
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-2xl);
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    animation: onboarding-slide-in 0.4s ease-out;
}

@keyframes onboarding-slide-in {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Progress Bar */
.onboarding-progress {
    padding: var(--space-6) var(--space-6) 0 var(--space-6);
}

.progress-bar {
    height: 4px;
    background: var(--color-gray-200);
    border-radius: var(--radius-full);
    overflow: hidden;
    margin-bottom: var(--space-2);
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--color-primary-500), var(--color-accent-500));
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.875rem;
    color: var(--color-gray-600);
    text-align: center;
}

/* Step Content */
.onboarding-content {
    padding: var(--space-6);
}

.onboarding-step {
    animation: step-fade-in 0.3s ease-out;
}

@keyframes step-fade-in {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.step-image {
    text-align: center;
    margin-bottom: var(--space-6);
}

.step-image img {
    max-width: 100%;
    height: auto;
    border-radius: var(--radius-lg);
}

.step-header {
    text-align: center;
    margin-bottom: var(--space-6);
}

.step-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-gray-900);
    margin: 0 0 var(--space-2) 0;
}

.step-subtitle {
    font-size: 1rem;
    color: var(--color-gray-600);
    margin: 0;
}

.step-body {
    margin-bottom: var(--space-6);
}

.step-description {
    font-size: 1rem;
    color: var(--color-gray-700);
    line-height: 1.6;
    margin: 0 0 var(--space-4) 0;
}

.step-features {
    list-style: none;
    padding: 0;
    margin: 0 0 var(--space-4) 0;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-2) 0;
    color: var(--color-gray-700);
}

.feature-icon {
    color: var(--color-success);
    flex-shrink: 0;
}

.step-tip {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-3);
    background: var(--color-primary-50);
    border-radius: var(--radius-md);
    border-left: 4px solid var(--color-primary-500);
}

.tip-icon {
    color: var(--color-primary-600);
    flex-shrink: 0;
}

.tip-text {
    font-size: 0.875rem;
    color: var(--color-primary-700);
}

/* Step Actions */
.step-actions {
    display: flex;
    gap: var(--space-3);
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.step-actions .btn {
    min-width: 120px;
}

/* Navigation Dots */
.onboarding-dots {
    display: flex;
    justify-content: center;
    gap: var(--space-2);
    padding: var(--space-4) var(--space-6) var(--space-6);
}

.dot {
    width: 8px;
    height: 8px;
    border-radius: var(--radius-full);
    border: 2px solid var(--color-gray-300);
    background: transparent;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.dot:hover {
    border-color: var(--color-gray-400);
}

.dot.active {
    background: var(--color-primary-500);
    border-color: var(--color-primary-500);
}

/* Help Button */
.help-button {
    position: fixed;
    bottom: var(--space-6);
    right: var(--space-6);
    width: 56px;
    height: 56px;
    border-radius: var(--radius-full);
    background: var(--color-primary-600);
    color: white;
    border: none;
    cursor: pointer;
    box-shadow: var(--shadow-lg);
    transition: all var(--transition-fast);
    z-index: var(--z-fixed);
}

.help-button:hover {
    background: var(--color-primary-700);
    transform: scale(1.05);
    box-shadow: var(--shadow-xl);
}

.help-button:active {
    transform: scale(0.95);
}

/* Help Menu */
.help-menu {
    position: fixed;
    bottom: calc(var(--space-6) + 64px);
    right: var(--space-6);
    background: white;
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xl);
    min-width: 300px;
    max-width: 400px;
    z-index: var(--z-dropdown);
    animation: help-menu-slide-in 0.2s ease-out;
}

@keyframes help-menu-slide-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.help-menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-4);
    border-bottom: 1px solid var(--color-gray-200);
}

.help-menu-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--color-gray-900);
}

.help-menu-content {
    padding: var(--space-4);
}

.help-section {
    margin-bottom: var(--space-6);
}

.help-section:last-child {
    margin-bottom: 0;
}

.help-section h4 {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--color-gray-700);
    margin: 0 0 var(--space-3) 0;
}

.shortcut-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.shortcut-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-2);
    border-radius: var(--radius-md);
    font-size: 0.875rem;
}

.shortcut-item:hover {
    background: var(--color-gray-50);
}

.shortcut-item kbd {
    padding: var(--space-1) var(--space-2);
    background: var(--color-gray-100);
    border: 1px solid var(--color-gray-300);
    border-radius: var(--radius-sm);
    font-family: var(--font-mono);
    font-size: 0.75rem;
    color: var(--color-gray-700);
}

.quick-links,
.support-links {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.help-link {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--color-gray-700);
    font-size: 0.875rem;
    transition: all var(--transition-fast);
    border: none;
    background: none;
    cursor: pointer;
    text-align: left;
}

.help-link:hover {
    background: var(--color-gray-50);
    color: var(--color-primary-600);
}

/* Responsive Design */
@media (max-width: 768px) {
    .onboarding-container {
        margin: var(--space-4);
        max-height: calc(100vh - var(--space-8));
    }
    
    .step-actions {
        flex-direction: column;
        gap: var(--space-2);
    }
    
    .step-actions .btn {
        width: 100%;
    }
    
    .help-menu {
        right: var(--space-4);
        bottom: calc(var(--space-4) + 64px);
        left: var(--space-4);
        min-width: auto;
        max-width: none;
    }
}
</style>

<script>
let currentStep = 1;
const totalSteps = {{ count($steps) }};

function nextStep() {
    if (currentStep < totalSteps) {
        showStep(currentStep + 1);
    }
}

function previousStep() {
    if (currentStep > 1) {
        showStep(currentStep - 1);
    }
}

function goToStep(step) {
    if (step >= 1 && step <= totalSteps) {
        showStep(step);
    }
}

function showStep(step) {
    // Hide current step
    document.querySelectorAll('.onboarding-step').forEach(el => {
        el.style.display = 'none';
    });
    
    // Show new step
    const newStep = document.querySelector(`.onboarding-step[data-step="${step}"]`);
    if (newStep) {
        newStep.style.display = 'block';
    }
    
    // Update progress
    currentStep = step;
    const progress = ((step - 1) / (totalSteps - 1)) * 100;
    document.getElementById('onboarding-progress').style.width = progress + '%';
    document.getElementById('current-step').textContent = step;
    
    // Update dots
    document.querySelectorAll('.dot').forEach((dot, index) => {
        dot.classList.toggle('active', index === step - 1);
    });
}

function completeOnboarding() {
    closeOnboarding();
    
    // Mark onboarding as completed
    localStorage.setItem('onboarding-completed', 'true');
    
    // Show success message
    if (window.uxSystem) {
        window.uxSystem.showToast('¡Bienvenido! Ya puedes comenzar a usar la aplicación', 'success');
    }
}

function closeOnboarding() {
    const overlay = document.getElementById('onboarding-overlay');
    overlay.style.display = 'none';
}

function skipOnboarding() {
    if (confirm('¿Estás seguro de que deseas omitir el tutorial? Puedes verlo más tarde desde el menú de ayuda.')) {
        completeOnboarding();
    }
}

function showHelpMenu() {
    const menu = document.getElementById('help-menu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function hideHelpMenu() {
    document.getElementById('help-menu').style.display = 'none';
}

function startTour() {
    hideHelpMenu();
    
    // Reset onboarding and show from beginning
    currentStep = 1;
    showStep(1);
    
    const overlay = document.getElementById('onboarding-overlay');
    overlay.style.display = 'flex';
}

function showDocumentation() {
    hideHelpMenu();
    // Open documentation in new tab
    window.open('/docs', '_blank');
}

// Close help menu when clicking outside
document.addEventListener('click', function(e) {
    const helpButton = document.querySelector('.help-button');
    const helpMenu = document.getElementById('help-menu');
    
    if (!helpButton.contains(e.target) && !helpMenu.contains(e.target)) {
        hideHelpMenu();
    }
});

// Check if onboarding should be shown
document.addEventListener('DOMContentLoaded', function() {
    const onboardingCompleted = localStorage.getItem('onboarding-completed');
    const isFirstTime = !onboardingCompleted;
    
    // Show onboarding for first-time users or if explicitly requested
    if (isFirstTime && {{ $showOnLoad ? 'true' : 'false' }}) {
        setTimeout(() => {
            const overlay = document.getElementById('onboarding-overlay');
            overlay.style.display = 'flex';
        }, 1000);
    }
});

// Keyboard shortcuts for onboarding
document.addEventListener('keydown', function(e) {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    
    const overlay = document.getElementById('onboarding-overlay');
    const isVisible = overlay.style.display === 'flex';
    
    if (isVisible) {
        if (e.key === 'ArrowRight') {
            e.preventDefault();
            nextStep();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            previousStep();
        } else if (e.key === 'Escape') {
            e.preventDefault();
            closeOnboarding();
        }
    }
});
</script>
