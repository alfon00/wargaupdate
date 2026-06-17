@php
    $kel = config('kelurahan');
@endphp
<section class="lw-services-hero lw-profile-hero lw-profile-hero--v2" aria-labelledby="services-hero-heading">
    <div class="lw-container">
        <div class="lw-profile-hero__inner">
            <p class="lw-profile-hero__eyebrow">
                <span class="lw-hero-eyebrow-dot" aria-hidden="true"></span>
                Layanan warga · {{ $kel['nama'] }}
            </p>
            <h1 id="services-hero-heading" class="lw-profile-hero__title">
                Layanan Administrasi RT
            </h1>
            <p class="lw-profile-hero__lead">
                Layanan administrasi RT untuk warga: surat pengantar, pendataan ulang, dan pendataan warga — tanpa login. Pengaduan via halaman Kontak.
            </p>
        </div>
    </div>
</section>
