@if ($paginator->hasPages())
<nav style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem; padding-top:1rem;">

    {{-- Conteo de resultados --}}
    <p style="font-size:.8rem; color:var(--color-gray-500); margin:0;">
        Mostrando
        @if ($paginator->firstItem())
            <strong>{{ $paginator->firstItem() }}</strong> – <strong>{{ $paginator->lastItem() }}</strong>
        @else
            {{ $paginator->count() }}
        @endif
        de <strong>{{ $paginator->total() }}</strong> resultados
    </p>

    {{-- Botones de página --}}
    <div style="display:flex; align-items:center; gap:.25rem;">

        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm btn-secondary" style="opacity:.4; cursor:not-allowed; pointer-events:none;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-sm btn-secondary" title="Anterior">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
        @endif

        {{-- Números de página --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="padding:.25rem .5rem; font-size:.8rem; color:var(--color-gray-400);">…</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="btn btn-sm btn-primary" style="min-width:2rem; cursor:default;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="btn btn-sm btn-secondary" style="min-width:2rem;">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Siguiente --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-sm btn-secondary" title="Siguiente">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        @else
            <span class="btn btn-sm btn-secondary" style="opacity:.4; cursor:not-allowed; pointer-events:none;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </span>
        @endif

    </div>
</nav>
@endif
