@props(['items' => []])

@if(count($items) > 0)
    <nav aria-label="Breadcrumb" class="breadcrumbs">
        <ol class="breadcrumbs-list">
            @foreach($items as $index => $item)
                <li class="breadcrumb-item">
                    @if($index === count($items) - 1)
                        <span class="breadcrumb-current" aria-current="page">
                            {{ $item['title'] }}
                        </span>
                    @else
                        <a href="{{ $item['url'] }}" class="breadcrumb-link">
                            {{ $item['title'] }}
                        </a>
                        <span class="breadcrumb-separator" aria-hidden="true">/</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif

<style>
.breadcrumbs {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: 0.875rem;
    color: var(--color-gray-500);
    margin: 0;
}

.breadcrumbs-list {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    list-style: none;
    margin: 0;
    padding: 0;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.breadcrumb-link {
    color: var(--color-gray-500);
    text-decoration: none;
    transition: color var(--transition-fast);
    font-weight: 500;
}

.breadcrumb-link:hover {
    color: var(--color-primary-600);
}

.breadcrumb-separator {
    color: var(--color-gray-400);
    font-weight: 400;
}

.breadcrumb-current {
    color: var(--color-gray-900);
    font-weight: 600;
}

@media (max-width: 768px) {
    .breadcrumbs {
        font-size: 0.75rem;
    }
    
    .breadcrumb-item:not(:last-child):not(:first-child) {
        display: none;
    }
    
    .breadcrumb-item:not(:last-child)::after {
        content: "...";
        color: var(--color-gray-400);
        font-weight: 500;
    }
}
</style>
