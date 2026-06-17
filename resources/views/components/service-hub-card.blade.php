@props([
    'href',
    'title',
    'description',
    'icon' => 'document',
    'cta' => 'Selengkapnya',
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'lw-service-hub-card']) }}>
    <span class="lw-service-hub-card-icon" aria-hidden="true">
        @include('components.icons.'.$icon)
    </span>
    <span class="lw-service-hub-card-body">
        <span class="lw-service-hub-card-title">{{ $title }}</span>
        <span class="lw-service-hub-card-desc">{{ $description }}</span>
        <span class="lw-service-hub-card-cta">{{ $cta }} →</span>
    </span>
</a>
