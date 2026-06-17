@props([
    'title',
    'eyebrow' => null,
    'lead' => null,
])

@php
    $hasActions = isset($actions) && $actions->isNotEmpty();
@endphp

<header {{ $attributes->merge(['class' => 'lw-panel-page-head'.($hasActions ? ' lw-panel-page-head--row' : '')]) }}>
    <div>
        @if($eyebrow)
            <p class="lw-panel-page-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="lw-panel-page-title">{{ $title }}</h1>
        @if($lead)
            <p class="lw-panel-page-lead">{{ $lead }}</p>
        @endif
    </div>
    @if($hasActions)
        <div class="lw-panel-actions">{{ $actions }}</div>
    @endif
</header>
