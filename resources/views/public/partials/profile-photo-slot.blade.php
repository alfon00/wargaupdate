{{--
    $photoUrl — nullable, URL foto unggahan
    $name — nama untuk alt/inisial
    $variant — lurah | rt
    $chip — opsional label chip (mis. RT 001)
    $compact — layout kompak (avatar kecil)
--}}
@php
    $compact = $compact ?? false;
    $initial = mb_strtoupper(mb_substr(preg_replace('/^[^A-Za-z0-9]+/u', '', $name ?? '') ?: 'P', 0, 1));
    $photoClass = $variant === 'lurah'
        ? 'lw-profile-detail-photo lw-profile-detail-photo--lurah'
        : 'lw-profile-detail-photo';
    $wrapClass = 'lw-profile-detail-photo-wrap lw-profile-detail-photo-wrap--'.$variant;
@endphp

<div class="{{ $wrapClass }}">
    @if($photoUrl)
        <img src="{{ $photoUrl }}"
            alt="Foto {{ $name }}"
            class="{{ $photoClass }}"
            width="{{ $compact ? 112 : 280 }}"
            height="{{ $compact ? 112 : 280 }}"
            loading="lazy"
            decoding="async">
    @else
        <div class="lw-profile-photo-placeholder lw-profile-photo-placeholder--{{ $variant }}{{ $compact ? ' lw-profile-photo-placeholder--compact' : '' }}" role="img" aria-label="Belum ada foto profil {{ $name }}">
            <svg class="lw-profile-photo-placeholder-icon" xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="lw-profile-photo-placeholder-initial">{{ $initial }}</span>
            @unless($compact)
                <span class="lw-profile-photo-placeholder-label">Foto profil</span>
            @endunless
        </div>
    @endif
    @if(! empty($chip))
        <span class="lw-profile-rt-chip lw-profile-detail-chip">{{ $chip }}</span>
    @endif
</div>
