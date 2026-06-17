@props([
    'eyebrow' => null,
    'title',
    'accent' => null,
    'lead' => null,
    'id' => 'page-hero-title',
])

<section {{ $attributes->merge(['class' => 'lw-hero lw-page-hero']) }} aria-labelledby="{{ $id }}">
    <div class="lw-hero-content">
        @if($eyebrow)
            <p class="lw-hero-eyebrow">
                <span class="lw-hero-eyebrow-dot" aria-hidden="true"></span>
                {{ $eyebrow }}
            </p>
        @endif
        <h1 id="{{ $id }}" class="lw-hero-title">
            {{ $title }}@if($accent) <span class="lw-hero-title-accent">{{ $accent }}</span>@endif
        </h1>
        @if($lead)
            <p class="lw-hero-lead max-w-2xl">{!! $lead !!}</p>
        @endif
        {{ $slot }}
    </div>
</section>
