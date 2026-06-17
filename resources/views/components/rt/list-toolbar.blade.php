@props([
    'formAction' => null,
    'ctaUrl' => null,
    'ctaLabel' => null,
])

<div {{ $attributes->merge(['class' => 'lw-panel-toolbar lw-rt-list-toolbar']) }}>
    @if($ctaUrl && $ctaLabel)
        <div class="lw-rt-list-toolbar-cta">
            <a href="{{ $ctaUrl }}" class="lw-panel-btn">{{ $ctaLabel }}</a>
        </div>
    @endif

    <div class="lw-rt-list-toolbar-stack">
        @isset($tabs)
            <div class="lw-rt-list-toolbar-tabs">
                {{ $tabs }}
            </div>
        @endisset

        @if($formAction && ! $slot->isEmpty())
            <form method="GET" action="{{ $formAction }}" class="lw-panel-toolbar-filters lw-rt-list-toolbar-filters">
                {{ $slot }}
            </form>
        @elseif(! $slot->isEmpty())
            <div class="lw-panel-toolbar-filters lw-rt-list-toolbar-filters">{{ $slot }}</div>
        @endif
    </div>
</div>
