@props([
    'expandable' => false,
    'variant' => 'rt',
])

<article {{ $attributes->merge(['class' => 'lw-profile-detail lw-profile-detail-inner lw-profile-detail--'.$variant]) }}>
    {{ $slot }}
</article>
