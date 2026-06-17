@props([
    'href',
    'title',
    'description',
    'badge' => null,
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'lw-panel-quick-card']) }}>
    <span class="lw-panel-quick-name">{{ $title }}</span>
    <span class="lw-panel-quick-desc">{{ $description }}</span>
    @if(filled($badge))
        <span class="lw-panel-quick-badge">{{ $badge }}</span>
    @endif
</a>
