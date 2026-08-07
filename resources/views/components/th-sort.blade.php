@props(['field', 'label', 'class' => ''])
@php
    $currentSort = request('sort');
    $currentDir  = request('dir', 'asc');
    $isActive    = $currentSort === $field;
    $nextDir     = $isActive && $currentDir === 'asc' ? 'desc' : 'asc';
    $params      = array_merge(request()->except(['sort', 'dir', 'page']), ['sort' => $field, 'dir' => $nextDir]);
@endphp
<th class="{{ $class }}">
    <a href="{{ request()->url() . '?' . http_build_query($params) }}" class="th-sort-link" style="display:inline-flex; align-items:center; gap:.3rem; text-decoration:none; color:inherit; white-space:nowrap;">
        {{ $label }}
        <span style="font-size:.6rem; line-height:1; opacity:{{ $isActive ? '1' : '.35' }};">
            @if($isActive && $currentDir === 'asc')
                &#9650;
            @elseif($isActive && $currentDir === 'desc')
                &#9660;
            @else
                &#9650;&#9660;
            @endif
        </span>
    </a>
</th>
