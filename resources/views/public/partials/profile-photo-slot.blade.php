{{--
    $photoUrl — nullable, URL foto unggahan
    $name — nama untuk alt/inisial
    $variant — lurah | rt
    $chip — opsional label chip (mis. RT 001)
    $compact — layout kompak (avatar kecil)
--}}
@php
    $compact = $compact ?? false;
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
        <x-photo-empty
            :name="$name"
            :size="$compact ? 'sm' : 'md'"
            class="lw-profile-detail-photo-empty lw-profile-detail-photo-empty--{{ $variant }}{{ $compact ? ' lw-profile-detail-photo-empty--compact' : '' }}"
        />
    @endif
    @if(! empty($chip))
        <span class="lw-profile-rt-chip lw-profile-detail-chip">{{ $chip }}</span>
    @endif
</div>
