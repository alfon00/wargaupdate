@props([
    'label' => 'Filter',
])

<nav {{ $attributes->merge(['class' => 'lw-rt-filter-tabs']) }} aria-label="{{ $label }}">
    {{ $slot }}
</nav>
