@props(['icon', 'color' => 'purple', 'value', 'label'])
<div class="report-stat">
    <div class="report-stat-icon report-stat-icon-{{ $color }}">
        {!! $icon !!}
    </div>
    <div>
        <div class="report-stat-value">{{ $value }}</div>
        <div class="report-stat-label">{{ $label }}</div>
    </div>
</div>
