@props([
    'name' => '',
    'label' => 'Belum ada foto',
    'size' => 'md',
])

@php
    $initial = mb_strtoupper(mb_substr(preg_replace('/^[^A-Za-z0-9]+/u', '', $name) ?: 'P', 0, 1));
@endphp

<div {{ $attributes->merge(['class' => 'lw-photo-empty lw-photo-empty--'.$size]) }} role="img" aria-label="{{ trim($label.' '.$name) }}">
    <svg class="lw-photo-empty__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
    <span class="lw-photo-empty__initial">{{ $initial }}</span>
    <span class="lw-photo-empty__label">{{ $label }}</span>
</div>
