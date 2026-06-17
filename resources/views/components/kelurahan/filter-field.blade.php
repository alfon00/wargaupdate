@props(['label', 'for' => null])

<div {{ $attributes->merge(['class' => 'lw-kel-filter-field']) }}>
    <label @if($for) for="{{ $for }}" @endif>{{ $label }}</label>
    {{ $slot }}
</div>
