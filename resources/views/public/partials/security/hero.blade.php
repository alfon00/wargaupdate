@php
    $kel = config('kelurahan');
@endphp
<section class="lw-security-hero lw-profile-hero lw-profile-hero--v2" aria-labelledby="security-hero-heading">
    <div class="lw-container">
        <div class="lw-profile-hero__inner">
            <p class="lw-profile-hero__eyebrow">
                <span class="lw-hero-eyebrow-dot" aria-hidden="true"></span>
                Kepercayaan warga · {{ $kel['nama'] }}
            </p>
            <h1 id="security-hero-heading" class="lw-profile-hero__title">
                Keamanan &amp; keaslian situs
            </h1>
            <p class="lw-profile-hero__lead">
                Panduan singkat agar warga mengakses portal resmi {{ $kel['nama'] }} dengan aman.
            </p>
        </div>
    </div>
</section>
