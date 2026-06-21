@php
    $kel = config('kelurahan');
@endphp

<section class="lw-profile-wilayah" aria-labelledby="wilayah-info-heading">
    <div class="lw-profile-wilayah__inner">
        <div class="lw-profile-wilayah__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <div class="lw-profile-wilayah__content">
            <h2 id="wilayah-info-heading" class="lw-profile-wilayah__title">Informasi Wilayah</h2>
            <dl class="lw-profile-wilayah__list">
                <div>
                    <dt>Distrik</dt>
                    <dd>{{ $kel['distrik'] }}</dd>
                </div>
                <div>
                    <dt>Provinsi</dt>
                    <dd>{{ $kel['provinsi'] }}</dd>
                </div>
            </dl>
            <p class="lw-profile-wilayah__desc">{{ $kel['penjelasan_wilayah'] }}</p>
            @if(empty($kel['maps_embed_url']))
                <p class="lw-profile-wilayah__map-note">Peta lokasi akan ditampilkan di sini setelah tersedia.</p>
            @endif
        </div>
    </div>
</section>
