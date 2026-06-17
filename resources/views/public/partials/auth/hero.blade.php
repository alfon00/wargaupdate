@php
    $kel = config('kelurahan');
@endphp
<section class="lw-auth-hero lw-profile-hero lw-profile-hero--v2" aria-labelledby="auth-hero-heading">
    <div class="lw-container">
        <div class="lw-profile-hero__inner">
            <p class="lw-profile-hero__eyebrow">
                <span class="lw-hero-eyebrow-dot" aria-hidden="true"></span>
                Panel pengurus · {{ $kel['nama'] }}
            </p>
            <h1 id="auth-hero-heading" class="lw-profile-hero__title">
                Akses Pengurus RT
            </h1>
            <p class="lw-profile-hero__lead">
                Masuk dengan akun terdaftar untuk mengelola permohonan surat, verifikasi pendataan, dan data warga di {{ $kel['nama'] }}.
            </p>
        </div>
    </div>
</section>
