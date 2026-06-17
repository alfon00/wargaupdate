@props([
    'label',
    'value',
    'variant' => 'default',
])

@php
    $class = match ($variant) {
        'highlight' => 'lw-panel-stat lw-panel-stat--highlight',
        'warn' => 'lw-panel-stat lw-panel-stat--warn',
        default => 'lw-panel-stat',
    };
@endphp

<div {{ $attributes->merge(['class' => $class]) }}>
    <p class="lw-panel-stat-label">{{ $label }}</p>
    <p class="lw-panel-stat-value">{{ $value }}</p>
</div>
