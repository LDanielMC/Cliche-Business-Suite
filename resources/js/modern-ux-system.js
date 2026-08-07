/**
 * MODERN UX SYSTEM - Cliche Business Suite
 * Sistema completo de interacciones UX inspirado en Linear, Stripe, Vercel
 */

class ModernUXSystem {
    constructor() {
        this.isInitialized = false;
        this.sidebarCollapsed = false;
        this.userMenuOpen = false;
        this.darkMode = false;
        this.keyboardShortcuts = new Map();
        this.toastContainer = null;
        this.loadingOverlay = null;
        this.confirmModal = null;
        this.searchResults = [];
        this.currentToastId = 0;
        
        this.init();
    }
    
    init() {
        if (this.isInitialized) return;
        
        document.addEventListener('DOMContentLoaded', () => {
            this.setupEventListeners();
            this.setupKeyboardShortcuts();
            this.setupGlobalSearch();
            this.loadUserPreferences();
            this.setupFormValidation();
            this.setupTooltips();
            this.setupAutoSave();
            
            this.isInitialized = true;
            console.log('🚀 Modern UX System initialized');
        });
    }
    
    // ==========================================
    // EVENT LISTENERS
    // ==========================================
    
    setupEventListeners() {
        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.user-menu')) {
                this.closeUserMenu();
            }
            if (!e.target.closest('.dropdown')) {
                this.closeAllDropdowns();
            }
        });
        
        // Handle escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeUserMenu();
                this.closeAllDropdowns();
                this.closeConfirmModal();
                this.closeMobileSidebar();
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            this.handleResize();
        });
        
        // Handle beforeunload for unsaved forms
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges()) {
                e.preventDefault();
                e.returnValue = 'Tienes cambios sin guardar. ¿Estás seguro de que deseas salir?';
                return e.returnValue;
            }
        });
    }
    
    // ==========================================
    // SIDEBAR NAVIGATION
    // ==========================================
    
    toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;
        
        this.sidebarCollapsed = !this.sidebarCollapsed;
        sidebar.classList.toggle('collapsed', this.sidebarCollapsed);
        
        this.saveUserPreference('sidebarCollapsed', this.sidebarCollapsed);
    }
    
    toggleMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        
        if (!sidebar || !overlay) return;
        
        const isVisible = sidebar.classList.contains('mobile-visible');
        
        if (isVisible) {
            this.closeMobileSidebar();
        } else {
            sidebar.classList.add('mobile-visible');
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }
    
    closeMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        
        if (!sidebar || !overlay) return;
        
        sidebar.classList.remove('mobile-visible');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    handleResize() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;
        
        if (window.innerWidth > 1024) {
            this.closeMobileSidebar();
        }
    }
    
    // ==========================================
    // USER MENU
    // ==========================================
    
    toggleUserMenu() {
        const dropdown = document.getElementById('user-dropdown');
        const trigger = document.querySelector('.user-menu-trigger');
        
        if (!dropdown || !trigger) return;
        
        this.userMenuOpen = !this.userMenuOpen;
        
        if (this.userMenuOpen) {
            dropdown.classList.add('show');
            trigger.setAttribute('aria-expanded', 'true');
        } else {
            dropdown.classList.remove('show');
            trigger.setAttribute('aria-expanded', 'false');
        }
    }
    
    closeUserMenu() {
        const dropdown = document.getElementById('user-dropdown');
        const trigger = document.querySelector('.user-menu-trigger');
        
        if (!dropdown || !trigger) return;
        
        dropdown.classList.remove('show');
        trigger.setAttribute('aria-expanded', 'false');
        this.userMenuOpen = false;
    }
    
    // ==========================================
    // GLOBAL SEARCH
    // ==========================================
    
    setupGlobalSearch() {
        const searchInput = document.getElementById('global-search');
        if (!searchInput) return;
        
        let searchTimeout;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                this.hideSearchResults();
                return;
            }
            
            searchTimeout = setTimeout(() => {
                this.performSearch(query);
            }, 300);
        });
        
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.selectSearchResult(0);
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.navigateSearchResults(1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.navigateSearchResults(-1);
            } else if (e.key === 'Escape') {
                this.hideSearchResults();
                searchInput.blur();
            }
        });
    }
    
    async performSearch(query) {
        try {
            // Simulate search API call
            const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            this.searchResults = data.results || [];
            this.showSearchResults();
        } catch (error) {
            console.error('Search error:', error);
            this.searchResults = [];
            this.showSearchResults();
        }
    }
    
    showSearchResults() {
        const searchInput = document.getElementById('global-search');
        if (!searchInput) return;
        
        // Remove existing results
        const existingResults = document.getElementById('search-results');
        if (existingResults) {
            existingResults.remove();
        }
        
        if (this.searchResults.length === 0) {
            return;
        }
        
        const resultsContainer = document.createElement('div');
        resultsContainer.id = 'search-results';
        resultsContainer.className = 'search-results';
        
        this.searchResults.forEach((result, index) => {
            const resultItem = document.createElement('div');
            resultItem.className = 'search-result-item';
            resultItem.setAttribute('data-index', index);
            resultItem.innerHTML = `
                <div class="search-result-icon">${this.getSearchResultIcon(result.type)}</div>
                <div class="search-result-content">
                    <div class="search-result-title">${result.title}</div>
                    <div class="search-result-description">${result.description}</div>
                </div>
            `;
            
            resultItem.addEventListener('click', () => {
                this.selectSearchResult(index);
            });
            
            resultsContainer.appendChild(resultItem);
        });
        
        searchInput.parentElement.appendChild(resultsContainer);
    }
    
    hideSearchResults() {
        const resultsContainer = document.getElementById('search-results');
        if (resultsContainer) {
            resultsContainer.remove();
        }
    }
    
    navigateSearchResults(direction) {
        const items = document.querySelectorAll('.search-result-item');
        if (items.length === 0) return;
        
        const currentIndex = Array.from(items).findIndex(item => 
            item.classList.contains('selected')
        );
        
        let newIndex = currentIndex + direction;
        if (newIndex < 0) newIndex = items.length - 1;
        if (newIndex >= items.length) newIndex = 0;
        
        items.forEach(item => item.classList.remove('selected'));
        items[newIndex].classList.add('selected');
        items[newIndex].scrollIntoView({ block: 'nearest' });
    }
    
    selectSearchResult(index) {
        if (this.searchResults[index]) {
            window.location.href = this.searchResults[index].url;
        }
    }
    
    getSearchResultIcon(type) {
        const icons = {
            client: '👤',
            payment: '💳',
            expense: '📊',
            calendar: '📅',
            report: '📈',
            user: '👥',
            settings: '⚙️'
        };
        return icons[type] || '📄';
    }
    
    // ==========================================
    // KEYBOARD SHORTCUTS
    // ==========================================
    
    setupKeyboardShortcuts() {
        this.keyboardShortcuts.set('ctrl+k', () => {
            const searchInput = document.getElementById('global-search');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        });
        
        this.keyboardShortcuts.set('ctrl+/', () => {
            this.showKeyboardShortcuts();
        });
        
        this.keyboardShortcuts.set('ctrl+b', () => {
            this.toggleSidebar();
        });
        
        this.keyboardShortcuts.set('ctrl+n', () => {
            this.createNew();
        });
        
        this.keyboardShortcuts.set('ctrl+s', () => {
            this.saveCurrentForm();
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }
            
            const key = this.getShortcutKey(e);
            const handler = this.keyboardShortcuts.get(key);
            
            if (handler) {
                e.preventDefault();
                handler();
            }
        });
    }
    
    getShortcutKey(e) {
        const parts = [];
        if (e.ctrlKey || e.metaKey) parts.push('ctrl');
        if (e.altKey) parts.push('alt');
        if (e.shiftKey) parts.push('shift');
        parts.push(e.key.toLowerCase());
        return parts.join('+');
    }
    
    showKeyboardShortcuts() {
        const shortcuts = [
            { key: 'Ctrl+K', description: 'Búsqueda global' },
            { key: 'Ctrl+/', description: 'Mostrar atajos' },
            { key: 'Ctrl+B', description: 'Alternar menú' },
            { key: 'Ctrl+N', description: 'Crear nuevo' },
            { key: 'Ctrl+S', description: 'Guardar formulario' },
            { key: 'Escape', description: 'Cerrar modales' }
        ];
        
        let shortcutsHtml = shortcuts.map(shortcut => 
            `<div class="shortcut-item">
                <kbd>${shortcut.key}</kbd>
                <span>${shortcut.description}</span>
            </div>`
        ).join('');
        
        this.showModal('Atajos de Teclado', shortcutsHtml);
    }
    
    // ==========================================
    // TOAST NOTIFICATIONS
    // ==========================================
    
    showToast(message, type = 'info', options = {}) {
        if (!this.toastContainer) {
            this.toastContainer = document.getElementById('toast-container');
        }
        
        const toastId = `toast-${++this.currentToastId}`;
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast toast-${type} animate-slideInFromRight`;
        
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        
        toast.innerHTML = `
            <div class="toast-icon">${icons[type]}</div>
            <div class="toast-content">
                ${options.title ? `<div class="toast-title">${options.title}</div>` : ''}
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="window.uxSystem.closeToast('${toastId}')">×</button>
        `;
        
        this.toastContainer.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Auto remove
        const duration = options.duration || 5000;
        setTimeout(() => {
            this.closeToast(toastId);
        }, duration);
        
        return toastId;
    }
    
    closeToast(toastId) {
        const toast = document.getElementById(toastId);
        if (!toast) return;
        
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
    
    // ==========================================
    // MODALS
    // ==========================================
    
    showModal(title, content, options = {}) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay show';
        modal.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">${title}</h3>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="this.closest('.modal-overlay').remove()">×</button>
                </div>
                <div class="modal-body">${content}</div>
                ${options.footer ? `<div class="modal-footer">${options.footer}</div>` : ''}
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
        
        return modal;
    }
    
    showConfirmModal(title, message, onConfirm, options = {}) {
        const modal = document.getElementById('confirm-modal');
        const titleEl = document.getElementById('confirm-title');
        const messageEl = document.getElementById('confirm-message');
        const buttonEl = document.getElementById('confirm-button');
        
        if (!modal) return;
        
        titleEl.textContent = title;
        messageEl.textContent = message;
        
        buttonEl.onclick = () => {
            onConfirm();
            this.closeConfirmModal();
        };
        
        if (options.danger) {
            buttonEl.className = 'btn btn-danger';
        } else {
            buttonEl.className = 'btn btn-primary';
        }
        
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('show'), 10);
    }
    
    closeConfirmModal() {
        const modal = document.getElementById('confirm-modal');
        if (!modal) return;
        
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 200);
    }
    
    // ==========================================
    // LOADING STATES
    // ==========================================
    
    showLoading(message = 'Cargando...') {
        if (!this.loadingOverlay) {
            this.loadingOverlay = document.getElementById('loading-overlay');
        }
        
        const textEl = this.loadingOverlay.querySelector('.loading-text');
        if (textEl) {
            textEl.textContent = message;
        }
        
        this.loadingOverlay.style.display = 'flex';
        setTimeout(() => this.loadingOverlay.classList.add('show'), 10);
    }
    
    hideLoading() {
        if (!this.loadingOverlay) return;
        
        this.loadingOverlay.classList.remove('show');
        setTimeout(() => {
            this.loadingOverlay.style.display = 'none';
        }, 200);
    }
    
    // ==========================================
    // FORM VALIDATION
    // ==========================================
    
    setupFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
            
            // Real-time validation
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
                
                input.addEventListener('input', () => {
                    if (input.classList.contains('error')) {
                        this.validateField(input);
                    }
                });
            });
        });
    }
    
    validateForm(form) {
        const inputs = form.querySelectorAll('input, textarea, select');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    validateField(field) {
        const rules = field.dataset.rules || '';
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';
        
        // Required validation
        if (rules.includes('required') && !value) {
            isValid = false;
            errorMessage = 'Este campo es requerido';
        }
        
        // Email validation
        if (rules.includes('email') && value && !this.isValidEmail(value)) {
            isValid = false;
            errorMessage = 'Ingresa un email válido';
        }
        
        // Min length validation
        const minLength = rules.match(/min:(\d+)/);
        if (minLength && value.length < parseInt(minLength[1])) {
            isValid = false;
            errorMessage = `Mínimo ${minLength[1]} caracteres`;
        }
        
        // Max length validation
        const maxLength = rules.match(/max:(\d+)/);
        if (maxLength && value.length > parseInt(maxLength[1])) {
            isValid = false;
            errorMessage = `Máximo ${maxLength[1]} caracteres`;
        }
        
        this.updateFieldValidation(field, isValid, errorMessage);
        return isValid;
    }
    
    updateFieldValidation(field, isValid, errorMessage) {
        const formGroup = field.closest('.form-group');
        if (!formGroup) return;
        
        const existingError = formGroup.querySelector('.form-error');
        if (existingError) {
            existingError.remove();
        }
        
        if (isValid) {
            formGroup.classList.remove('error');
            formGroup.classList.add('success');
        } else {
            formGroup.classList.add('error');
            formGroup.classList.remove('success');
            
            const errorEl = document.createElement('div');
            errorEl.className = 'form-error';
            errorEl.textContent = errorMessage;
            formGroup.appendChild(errorEl);
        }
    }
    
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    // ==========================================
    // AUTO-SAVE
    // ==========================================
    
    setupAutoSave() {
        const forms = document.querySelectorAll('form[data-auto-save]');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            let saveTimeout;
            
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    clearTimeout(saveTimeout);
                    form.classList.add('has-unsaved-changes');
                    
                    saveTimeout = setTimeout(() => {
                        this.autoSaveForm(form);
                    }, 2000);
                });
            });
        });
    }
    
    async autoSaveForm(form) {
        const formData = new FormData(form);
        const url = form.dataset.autoSave;
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            if (response.ok) {
                form.classList.remove('has-unsaved-changes');
                this.showToast('Cambios guardados automáticamente', 'success', { duration: 3000 });
            }
        } catch (error) {
            console.error('Auto-save error:', error);
        }
    }
    
    hasUnsavedChanges() {
        return document.querySelector('.has-unsaved-changes') !== null;
    }
    
    // ==========================================
    // TOOLTIPS
    // ==========================================
    
    setupTooltips() {
        const elements = document.querySelectorAll('[data-tooltip]');
        
        elements.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target);
            });
            
            element.addEventListener('mouseleave', (e) => {
                this.hideTooltip();
            });
            
            element.addEventListener('focus', (e) => {
                this.showTooltip(e.target);
            });
            
            element.addEventListener('blur', () => {
                this.hideTooltip();
            });
        });
    }
    
    showTooltip(element) {
        this.hideTooltip();
        
        const text = element.dataset.tooltip;
        if (!text) return;
        
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
        
        setTimeout(() => tooltip.classList.add('show'), 10);
    }
    
    hideTooltip() {
        const tooltip = document.querySelector('.tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }
    
    // ==========================================
    // USER PREFERENCES
    // ==========================================
    
    loadUserPreferences() {
        try {
            const preferences = JSON.parse(localStorage.getItem('userPreferences') || '{}');
            
            // Apply sidebar state
            this.sidebarCollapsed = preferences.sidebarCollapsed || false;
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('collapsed', this.sidebarCollapsed);
            }
            
            // Apply dark mode
            this.darkMode = preferences.darkMode || false;
            if (this.darkMode) {
                document.documentElement.classList.add('dark-mode');
            }
            
        } catch (error) {
            console.error('Error loading user preferences:', error);
        }
    }
    
    saveUserPreference(key, value) {
        try {
            const preferences = JSON.parse(localStorage.getItem('userPreferences') || '{}');
            preferences[key] = value;
            localStorage.setItem('userPreferences', JSON.stringify(preferences));
        } catch (error) {
            console.error('Error saving user preference:', error);
        }
    }
    
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        document.documentElement.classList.toggle('dark-mode', this.darkMode);
        this.saveUserPreference('darkMode', this.darkMode);
        
        this.showToast(
            this.darkMode ? 'Modo oscuro activado' : 'Modo claro activado',
            'success',
            { duration: 2000 }
        );
    }
    
    // ==========================================
    // UTILITY METHODS
    // ==========================================
    
    createNew() {
        // Determine what to create based on current page
        const currentPath = window.location.pathname;
        
        if (currentPath.includes('clientes')) {
            window.location.href = '/clientes/create';
        } else if (currentPath.includes('gastos')) {
            window.location.href = '/gastos/create';
        } else if (currentPath.includes('pagos')) {
            window.location.href = '/pagos/create';
        } else {
            this.showToast('No hay opción de crear disponible en esta página', 'info');
        }
    }
    
    saveCurrentForm() {
        const form = document.querySelector('form');
        if (form) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.click();
            } else {
                this.showToast('No hay formulario para guardar', 'info');
            }
        }
    }
    
    closeAllDropdowns() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
    
    formatCurrency(amount) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
        }).format(amount);
    }
    
    formatDate(date) {
        return new Intl.DateTimeFormat('es-MX', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(new Date(date));
    }
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize the system
window.uxSystem = new ModernUXSystem();

// Global helper functions for backward compatibility
window.showToast = (message, type, options) => window.uxSystem.showToast(message, type, options);
window.showConfirmModal = (title, message, onConfirm, options) => window.uxSystem.showConfirmModal(title, message, onConfirm, options);
window.showLoading = (message) => window.uxSystem.showLoading(message);
window.hideLoading = () => window.uxSystem.hideLoading();
window.toggleUserMenu = () => window.uxSystem.toggleUserMenu();
window.closeUserMenu = () => window.uxSystem.closeUserMenu();
window.toggleSidebar = () => window.uxSystem.toggleSidebar();
window.toggleMobileSidebar = () => window.uxSystem.toggleMobileSidebar();
window.closeMobileSidebar = () => window.uxSystem.closeMobileSidebar();
window.showKeyboardShortcuts = () => window.uxSystem.showKeyboardShortcuts();
window.toggleDarkMode = () => window.uxSystem.toggleDarkMode();
window.closeConfirmModal = () => window.uxSystem.closeConfirmModal();

// Export for module usage
export default ModernUXSystem;
