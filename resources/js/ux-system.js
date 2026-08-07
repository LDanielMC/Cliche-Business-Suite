/**
 * Cliche Business Suite - UX System
 * Handles all enhanced UX interactions and features
 */

class UXSystem {
    constructor() {
        this.init();
        this.setupEventListeners();
        this.setupKeyboardShortcuts();
        this.setupGlobalSearch();
        this.setupTooltips();
        this.setupFormValidation();
    }

    init() {
        // Initialize sidebar state
        this.sidebarOpen = false;
        this.userMenuOpen = false;
        
        // Check for saved preferences
        this.loadUserPreferences();
        
        // Setup dark mode
        this.setupDarkMode();
        
        // Setup loading states
        this.setupLoadingStates();
        
        // Setup auto-save for forms
        this.setupAutoSave();
    }

    setupEventListeners() {
        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.user-menu')) {
                this.closeUserMenu();
            }
        });

        // Close modals with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });

        // Handle form submissions with loading states
        document.addEventListener('submit', (e) => {
            if (e.target.tagName === 'FORM') {
                this.handleFormSubmit(e);
            }
        });

        // Setup smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }

    // Sidebar Management
    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        
        if (this.sidebarOpen) {
            sidebar.classList.add('open');
            mainContent.style.marginLeft = '280px';
        } else {
            sidebar.classList.remove('open');
            mainContent.style.marginLeft = '0';
        }
        
        // Save preference
        localStorage.setItem('sidebarOpen', this.sidebarOpen);
    }

    closeSidebar() {
        this.sidebarOpen = false;
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        
        sidebar.classList.remove('open');
        mainContent.style.marginLeft = '0';
        localStorage.setItem('sidebarOpen', 'false');
    }

    // User Menu Management
    toggleUserMenu() {
        this.userMenuOpen = !this.userMenuOpen;
        const dropdown = document.getElementById('user-dropdown');
        const button = document.querySelector('.user-menu-button');
        
        if (this.userMenuOpen) {
            dropdown.classList.add('show');
            button.setAttribute('aria-expanded', 'true');
        } else {
            dropdown.classList.remove('show');
            button.setAttribute('aria-expanded', 'false');
        }
    }

    closeUserMenu() {
        this.userMenuOpen = false;
        const dropdown = document.getElementById('user-dropdown');
        const button = document.querySelector('.user-menu-button');
        
        dropdown.classList.remove('show');
        button.setAttribute('aria-expanded', 'false');
    }

    // Toast Notification System
    showToast(message, type = 'info', duration = 5000) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type} animate-fade-in`;
        
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        
        toast.innerHTML = `
            <div class="toast-header">
                <span class="toast-icon">${icons[type]}</span>
                <span class="toast-title">${type.charAt(0).toUpperCase() + type.slice(1)}</span>
                <button type="button" class="toast-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
            <div class="toast-message">${message}</div>
        `;
        
        container.appendChild(toast);
        
        // Auto remove after duration
        setTimeout(() => {
            toast.style.animation = 'toast-slide-out 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    // Loading States
    showLoading(message = 'Cargando...') {
        const overlay = document.getElementById('loading-overlay');
        const text = overlay.querySelector('.loading-text');
        text.textContent = message;
        overlay.style.display = 'flex';
    }

    hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        overlay.style.display = 'none';
    }

    setupLoadingStates() {
        // Add loading states to buttons
        document.addEventListener('click', (e) => {
            if (e.target.tagName === 'BUTTON' && e.target.dataset.loading) {
                const originalText = e.target.textContent;
                e.target.disabled = true;
                e.target.innerHTML = '<span class="spinner"></span> Cargando...';
                
                // Reset after 3 seconds as fallback
                setTimeout(() => {
                    e.target.disabled = false;
                    e.target.textContent = originalText;
                }, 3000);
            }
        });
    }

    // Confirmation Modal
    showConfirmModal(title, message, onConfirm) {
        const modal = document.getElementById('confirm-modal');
        const titleEl = document.getElementById('confirm-title');
        const messageEl = document.getElementById('confirm-message');
        const confirmBtn = document.getElementById('confirm-button');
        
        titleEl.textContent = title;
        messageEl.textContent = message;
        
        modal.style.display = 'flex';
        
        // Handle confirmation
        confirmBtn.onclick = () => {
            onConfirm();
            this.closeConfirmModal();
        };
        
        // Focus management
        confirmBtn.focus();
    }

    closeConfirmModal() {
        const modal = document.getElementById('confirm-modal');
        modal.style.display = 'none';
    }

    closeAllModals() {
        this.closeConfirmModal();
        this.closeUserMenu();
    }

    // Keyboard Shortcuts
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Only trigger shortcuts when not in input fields
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            
            // Ctrl/Cmd + K for global search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.focusGlobalSearch();
            }
            
            // Ctrl/Cmd + / for keyboard shortcuts help
            if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                e.preventDefault();
                this.showKeyboardShortcuts();
            }
            
            // Escape to close sidebar on mobile
            if (e.key === 'Escape' && window.innerWidth < 1024 && this.sidebarOpen) {
                this.closeSidebar();
            }
            
            // Ctrl/Cmd + B to toggle sidebar
            if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                e.preventDefault();
                this.toggleSidebar();
            }
        });
    }

    focusGlobalSearch() {
        const searchInput = document.getElementById('global-search');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }

    showKeyboardShortcuts() {
        const shortcuts = [
            { key: 'Ctrl + K', description: 'Búsqueda global' },
            { key: 'Ctrl + /', description: 'Mostrar atajos' },
            { key: 'Ctrl + B', description: 'Alternar menú lateral' },
            { key: 'Escape', description: 'Cerrar modales' }
        ];
        
        let shortcutsHtml = shortcuts.map(s => 
            `<div class="shortcut-item">
                <kbd class="shortcut-key">${s.key}</kbd>
                <span class="shortcut-desc">${s.description}</span>
            </div>`
        ).join('');
        
        this.showConfirmModal('Atajos de Teclado', shortcutsHtml, () => {});
    }

    // Global Search
    setupGlobalSearch() {
        const searchInput = document.getElementById('global-search');
        if (!searchInput) return;
        
        let searchTimeout;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) return;
            
            searchTimeout = setTimeout(() => {
                this.performGlobalSearch(query);
            }, 300);
        });
        
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.performGlobalSearch(e.target.value.trim());
            }
        });
    }

    async performGlobalSearch(query) {
        this.showLoading('Buscando...');
        
        try {
            const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
            const results = await response.json();
            
            this.displaySearchResults(results);
        } catch (error) {
            this.showToast('Error en la búsqueda', 'error');
        } finally {
            this.hideLoading();
        }
    }

    displaySearchResults(results) {
        // Implementation depends on search API response format
        console.log('Search results:', results);
        // TODO: Create search results dropdown/modal
    }

    // Form Validation
    setupFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                // Real-time validation
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
                
                input.addEventListener('input', () => {
                    if (input.classList.contains('error')) {
                        this.validateField(input);
                    }
                });
            });
            
            // Form submission validation
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
    }

    validateField(field) {
        const rules = field.dataset.rules ? field.dataset.rules.split('|') : [];
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
            errorMessage = 'Ingrese un email válido';
        }
        
        // Min length validation
        const minLength = rules.find(r => r.startsWith('min:'));
        if (minLength && value.length < parseInt(minLength.split(':')[1])) {
            isValid = false;
            errorMessage = `Mínimo ${minLength.split(':')[1]} caracteres`;
        }
        
        this.updateFieldValidation(field, isValid, errorMessage);
        return isValid;
    }

    validateForm(form) {
        const inputs = form.querySelectorAll('input, select, textarea');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    updateFieldValidation(field, isValid, errorMessage) {
        const formGroup = field.closest('.form-group');
        if (!formGroup) return;
        
        // Remove existing validation classes
        field.classList.remove('error', 'success');
        formGroup.classList.remove('error', 'success');
        
        // Remove existing error message
        const existingError = formGroup.querySelector('.form-error');
        if (existingError) existingError.remove();
        
        if (!isValid) {
            field.classList.add('error');
            formGroup.classList.add('error');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'form-error';
            errorDiv.textContent = errorMessage;
            formGroup.appendChild(errorDiv);
        } else {
            field.classList.add('success');
            formGroup.classList.add('success');
        }
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Auto-save for forms
    setupAutoSave() {
        const forms = document.querySelectorAll('form[data-autosave]');
        
        forms.forEach(form => {
            let saveTimeout;
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(() => {
                        this.autoSaveForm(form);
                    }, 2000);
                });
            });
        });
    }

    autoSaveForm(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Save to localStorage
        const formKey = `autosave_${form.id || window.location.pathname}`;
        localStorage.setItem(formKey, JSON.stringify(data));
        
        this.showToast('Borrador guardado', 'info', 2000);
    }

    loadAutoSavedForm(form) {
        const formKey = `autosave_${form.id || window.location.pathname}`;
        const savedData = localStorage.getItem(formKey);
        
        if (savedData) {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) input.value = data[key];
            });
        }
    }

    // Dark Mode
    setupDarkMode() {
        const darkModeToggle = document.querySelector('[data-dark-mode-toggle]');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => {
                this.toggleDarkMode();
            });
        }
    }

    toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', isDark);
        this.showToast(isDark ? 'Modo oscuro activado' : 'Modo claro activado', 'info', 2000);
    }

    // Tooltips
    setupTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });
            
            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    }

    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
    }

    hideTooltip() {
        const tooltip = document.querySelector('.tooltip');
        if (tooltip) tooltip.remove();
    }

    // User Preferences
    loadUserPreferences() {
        // Load sidebar state
        const sidebarOpen = localStorage.getItem('sidebarOpen') === 'true';
        if (sidebarOpen && window.innerWidth >= 1024) {
            this.toggleSidebar();
        }
        
        // Load dark mode
        const darkMode = localStorage.getItem('darkMode') === 'true';
        if (darkMode) {
            document.body.classList.add('dark-mode');
        }
    }

    // Form submission handling
    handleFormSubmit(e) {
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        
        if (submitButton && !form.dataset.noLoading) {
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner"></span> Procesando...';
            
            // Reset after 10 seconds as fallback
            setTimeout(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }, 10000);
        }
    }

    // Helper Methods
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

    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// Global functions for inline event handlers
function toggleSidebar() {
    window.uxSystem.toggleSidebar();
}

function toggleUserMenu() {
    window.uxSystem.toggleUserMenu();
}

function closeConfirmModal() {
    window.uxSystem.closeConfirmModal();
}

function showKeyboardShortcuts() {
    window.uxSystem.showKeyboardShortcuts();
}

function toggleDarkMode() {
    window.uxSystem.toggleDarkMode();
}

// Initialize UX System when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.uxSystem = new UXSystem();
});

// Export for module usage
export default UXSystem;
