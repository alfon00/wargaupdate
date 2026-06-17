@props(['variant' => 'rt'])

<details class="lw-profile-detail-more {{ $variant === 'lurah' ? 'lw-profile-detail-more--lurah' : '' }}">
    <summary class="lw-profile-detail-more-link">
        <span class="lw-profile-detail-more-label when-closed">Lihat detail</span>
        <span class="lw-profile-detail-more-label when-open" hidden>Sembunyikan</span>
        <span class="lw-profile-detail-more-chevron" aria-hidden="true"></span>
    </summary>
    <div class="lw-profile-detail-more-inner">
        {{ $slot }}
    </div>
</details>
