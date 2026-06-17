@props([
    'title' => 'Belum ada data',
    'description' => null,
    'actionUrl' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'lw-panel-empty']) }}>
    <p class="lw-panel-empty-title">{{ $title }}</p>
    @if($description)
        <p class="lw-panel-empty-desc">{{ $description }}</p>
    @endif
    @if($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm lw-mt-3">{{ $actionLabel }}</a>
    @endif
</div>
