@props([
    'items' => [],
    'heading' => null,
    'headingId' => null,
    'description' => null,
    'tag' => null,
    'openFirst' => true,
    'variant' => 'default',
])

@php
    $sectionClass = 'lw-faq-section' . ($variant === 'track' ? ' lw-faq-section--track' : '');
@endphp

<section {{ $attributes->merge(['class' => $sectionClass]) }}>
    @if($heading)
        <header class="lw-profile-section-head lw-home-section-head">
            @if(filled($tag))
                <span class="lw-section-tag">{{ $tag }}</span>
            @endif
            <h2 @if(filled($headingId)) id="{{ $headingId }}" @endif class="lw-section-title">{{ $heading }}</h2>
            @if($description)
                <p class="lw-profile-section-lead">{{ $description }}</p>
            @endif
        </header>
    @endif
    <div class="lw-home-faq-list">
        @foreach($items as $index => $item)
            <details class="lw-home-faq-item" @if($openFirst && $index === 0) open @endif>
                <summary class="lw-home-faq-question">
                    <span class="lw-home-faq-q-text">{{ $item['question'] ?? $item['q'] ?? '' }}</span>
                    <span class="lw-home-faq-chevron" aria-hidden="true"></span>
                </summary>
                <div class="lw-home-faq-answer">
                    <p>{{ $item['answer'] ?? $item['a'] ?? '' }}</p>
                </div>
            </details>
        @endforeach
    </div>
</section>
