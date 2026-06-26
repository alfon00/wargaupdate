@props([
    'tag' => 'p',
])

<{{ $tag }} {{ $attributes->merge(['class' => 'lw-profile-content-empty']) }}>
    {{ $slot }}
</{{ $tag }}>
