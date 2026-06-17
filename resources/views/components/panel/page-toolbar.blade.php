@props([
    'actionUrl' => null,
    'buttonUrl' => null,
    'buttonLabel' => null,
])

<div {{ $attributes->merge(['class' => 'lw-panel-toolbar']) }}>
    @if($slot->isNotEmpty() && $actionUrl)
        <form method="GET" action="{{ $actionUrl }}" class="lw-panel-toolbar-filters">
            {{ $slot }}
        </form>
    @elseif($slot->isNotEmpty())
        <div class="lw-panel-toolbar-filters">{{ $slot }}</div>
    @endif
    @if($buttonUrl)
        <a href="{{ $buttonUrl }}" class="lw-panel-btn lw-panel-toolbar-action">{{ $buttonLabel }}</a>
    @endif
</div>
