@props(['placeholder' => 'Buscar...', 'action' => null])
<form method="GET" action="{{ $action ?? request()->url() }}" class="table-search-form" style="display:flex; gap:.5rem; margin-bottom:1rem; flex-wrap:wrap; align-items:center;">
    @foreach(request()->except(['q', 'page']) as $key => $value)
        @if(is_array($value))
            @foreach($value as $v)
                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <div style="position:relative; flex:1; min-width:220px; max-width:340px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="position:absolute; left:.7rem; top:50%; transform:translateY(-50%); color:var(--color-text-secondary); pointer-events:none;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
        </svg>
        <input type="text" name="q" value="{{ request('q') }}" data-live-search class="form-input" placeholder="{{ $placeholder }}" style="padding-left:2.25rem;">
    </div>
    <button type="submit" class="btn btn-secondary">Buscar</button>
    @if(request('q'))
        <a href="{{ $action ?? request()->url() }}" class="btn btn-secondary">Limpiar filtros</a>
    @endif
</form>
