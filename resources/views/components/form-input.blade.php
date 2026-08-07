@props([
    'name' => '',
    'label' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'help' => '',
    'rules' => '',
    'error' => null,
    'class' => ''
])

@php
    $inputId = $name . '-' . uniqid();
    $errorClass = $error ? 'error' : '';
    $requiredAttr = $required ? 'required' : '';
    $disabledAttr = $disabled ? 'disabled' : '';
    $readonlyAttr = $readonly ? 'readonly' : '';
@endphp

<div class="form-group {{ $errorClass }} {{ $class }}">
    @if($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="required-indicator" aria-label="Requerido">*</span>
            @endif
        </label>
    @endif
    
    <div class="input-wrapper">
        @if($type === 'textarea')
            <textarea
                id="{{ $inputId }}"
                name="{{ $name }}"
                placeholder="{{ $placeholder }}"
                class="form-input"
                {{ $requiredAttr }}
                {{ $disabledAttr }}
                {{ $readonlyAttr }}
                @if($rules) data-rules="{{ $rules }}" @endif
                @if($error) aria-invalid="true" aria-describedby="{{ $inputId }}-error" @endif
            >{{ $value }}</textarea>
        @elseif($type === 'select')
            <select
                id="{{ $inputId }}"
                name="{{ $name }}"
                class="form-select"
                {{ $requiredAttr }}
                {{ $disabledAttr }}
                @if($rules) data-rules="{{ $rules }}" @endif
                @if($error) aria-invalid="true" aria-describedby="{{ $inputId }}-error" @endif
            >
                <option value="">Selecciona una opción</option>
                {{ $slot }}
            </select>
        @else
            <input
                type="{{ $type }}"
                id="{{ $inputId }}"
                name="{{ $name }}"
                value="{{ $value }}"
                placeholder="{{ $placeholder }}"
                class="form-input"
                {{ $requiredAttr }}
                {{ $disabledAttr }}
                {{ $readonlyAttr }}
                @if($rules) data-rules="{{ $rules }}" @endif
                @if($error) aria-invalid="true" aria-describedby="{{ $inputId }}-error" @endif
            >
        @endif
        
        @if($type === 'password')
            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('{{ $inputId }}')" aria-label="Mostrar contraseña">
                <svg class="eye-icon eye-open" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <svg class="eye-icon eye-closed" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                </svg>
            </button>
        @endif
    </div>
    
    @if($error)
        <div id="{{ $inputId }}-error" class="form-error" role="alert">
            {{ $error }}
        </div>
    @endif
    
    @if($help)
        <div class="form-help">
            {{ $help }}
        </div>
    @endif
</div>

<style>
.form-group {
    margin-bottom: var(--space-4);
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--color-gray-700);
    margin-bottom: var(--space-2);
}

.required-indicator {
    color: var(--color-error);
    font-weight: 700;
    margin-left: var(--space-1);
}

.input-wrapper {
    position: relative;
}

.form-input,
.form-select {
    display: block;
    width: 100%;
    padding: var(--space-3);
    font-family: var(--font-sans);
    font-size: 0.875rem;
    line-height: 1.5;
    color: var(--color-gray-900);
    background-color: white;
    border: 1px solid var(--color-gray-300);
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: var(--color-primary-500);
    box-shadow: 0 0 0 3px rgb(139 109 255 / 0.1);
}

.form-input:disabled,
.form-select:disabled {
    background-color: var(--color-gray-100);
    color: var(--color-gray-500);
    cursor: not-allowed;
}

.form-input:readonly,
.form-select:readonly {
    background-color: var(--color-gray-50);
    color: var(--color-gray-700);
}

.form-group.error .form-input,
.form-group.error .form-select {
    border-color: var(--color-error);
}

.form-group.error .form-input:focus,
.form-group.error .form-select:focus {
    box-shadow: 0 0 0 3px rgb(239 68 68 / 0.1);
}

.form-group.success .form-input,
.form-group.success .form-select {
    border-color: var(--color-success);
}

.form-group.success .form-input:focus,
.form-group.success .form-select:focus {
    box-shadow: 0 0 0 3px rgb(16 185 129 / 0.1);
}

.password-toggle {
    position: absolute;
    right: var(--space-3);
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--color-gray-400);
    cursor: pointer;
    padding: var(--space-1);
    border-radius: var(--radius-sm);
    transition: color var(--transition-fast);
}

.password-toggle:hover {
    color: var(--color-gray-600);
}

.form-error {
    font-size: 0.75rem;
    color: var(--color-error);
    margin-top: var(--space-1);
    display: flex;
    align-items: center;
    gap: var(--space-1);
}

.form-error::before {
    content: '⚠';
    font-size: 0.875rem;
}

.form-help {
    font-size: 0.75rem;
    color: var(--color-gray-500);
    margin-top: var(--space-1);
}

/* Loading state */
.form-input.loading,
.form-select.loading {
    background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='10' cy='10' r='8' stroke='%237c3aed' stroke-width='2' fill='none' stroke-dasharray='31.416 31.416' stroke-dashoffset='31.416' stroke-linecap='round'%3E%3Canimate attributeName='stroke-dasharray' dur='2s' values='0 31.416;15.708 15.708;0 31.416' repeatCount='indefinite'/%3E%3C/circle%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right var(--space-3) center;
    background-size: 20px;
    padding-right: var(--space-10);
}

/* Character counter */
.char-counter {
    font-size: 0.75rem;
    color: var(--color-gray-500);
    text-align: right;
    margin-top: var(--space-1);
}

.char-counter.warning {
    color: var(--color-warning);
}

.char-counter.error {
    color: var(--color-error);
}
</style>

<script>
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const eyeOpen = input.parentElement.querySelector('.eye-open');
    const eyeClosed = input.parentElement.querySelector('.eye-closed');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
    } else {
        input.type = 'password';
        eyeOpen.style.display = 'block';
        eyeClosed.style.display = 'none';
    }
}
</script>
