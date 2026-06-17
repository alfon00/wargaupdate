@props([
    'title',
    'note' => null,
    'variant' => 'default',
    'tag' => 'article',
])

<{{ $tag }} {{ $attributes->class([
    'lw-panel-card',
    'lw-panel-form',
    'lw-panel-form--sidebar',
    'lw-panel-form--'.$variant,
]) }}>
    <h2 class="lw-panel-card-title">{{ $title }}</h2>
    @if($note)
        <p class="lw-panel-card-note">{{ $note }}</p>
    @endif
    {{ $slot }}
</{{ $tag }}>
