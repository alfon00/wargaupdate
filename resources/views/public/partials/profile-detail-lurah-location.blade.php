{{-- Expects $lurah array — peta kantor (alamat ada di meta grid) --}}
@php
    $mapsEmbedUrl = config('kelurahan.maps_embed_url');
@endphp
@if(filled($mapsEmbedUrl))
    <div class="lw-profile-location-maps lw-maps-wrap">
        <iframe src="{{ $mapsEmbedUrl }}" title="Peta lokasi kantor kelurahan" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
@else
    <div class="lw-profile-location-maps lw-profile-map-placeholder" role="status">
        <p class="lw-profile-map-placeholder-text lw-mb-0">Peta lokasi akan ditampilkan di sini setelah tersedia dari kelurahan.</p>
    </div>
@endif
