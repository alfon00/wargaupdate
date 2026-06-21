@php
    $kel = config('kelurahan');
@endphp
<section class="lw-profile-hero lw-profile-hero--v2" aria-labelledby="profile-hero-heading">
    <div class="lw-container">
        <div class="lw-profile-hero__inner">
            <p class="lw-profile-hero__eyebrow">
                <span class="lw-hero-eyebrow-dot" aria-hidden="true"></span>
                Profil wilayah
            </p>
            <h1 id="profile-hero-heading" class="lw-profile-hero__title">
                Profil &amp; RT
            </h1>
            <p class="lw-profile-hero__lead">
                {{ $kel['penjelasan_wilayah'] }}
            </p>
        </div>
    </div>
</section>
