@php
    $lurah = config('kelurahan.lurah', []);
    $officeAddress = $lurah['alamat_kantor'] ?? null;
    $mapsUrl = filled(config('kelurahan.maps_embed_url')) ? config('kelurahan.maps_embed_url') : null;
@endphp

<nav class="lw-footer-contact" aria-label="Kontak Kelurahan Inauga">
    <div class="lw-footer-contact-item">
        <span class="lw-footer-contact-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
        </span>
        <div class="lw-footer-contact-body">
            <span class="lw-footer-contact-label">Lokasi</span>
            @if(filled($mapsUrl))
                <a href="{{ $mapsUrl }}" class="lw-footer-link" target="_blank" rel="noopener noreferrer">{{ $officeAddress }}</a>
            @else
                <span class="lw-footer-contact-value">{{ $officeAddress }}</span>
            @endif
        </div>
    </div>
</nav>
