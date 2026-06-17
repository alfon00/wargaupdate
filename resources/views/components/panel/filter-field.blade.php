@props([
    'label',
    'for',
])

<div {{ $attributes->merge(['class' => 'lw-panel-filter-field']) }}>
    <label for="{{ $for }}">{{ $label }}</label>
    {{ $slot }}
</div>
