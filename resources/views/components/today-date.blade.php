@props([
    'variant' => 'labeled',
])

@php
    $formattedDate = now('Asia/Jayapura')->locale('id')->translatedFormat('l, d F Y');
@endphp

<p {{ $attributes->merge(['aria-live' => 'polite']) }}>
    @if($variant === 'labeled')
        <span class="lw-panel-date-label">Hari ini</span>
    @endif
    {{ $formattedDate }}
</p>
