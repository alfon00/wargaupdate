@props([
    'label',
    'value',
    'variant' => 'default',
])

<x-panel.stat-card :label="$label" :value="$value" :variant="$variant" {{ $attributes }} />
