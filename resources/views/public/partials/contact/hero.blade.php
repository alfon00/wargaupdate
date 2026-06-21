@php
    $kel = config('kelurahan');
@endphp
<section class="lw-contact-hero lw-profile-hero lw-profile-hero--v2" aria-labelledby="contact-hero-heading">
    <div class="lw-container">
        <div class="lw-profile-hero__inner">
            <p class="lw-profile-hero__eyebrow">
                <span class="lw-hero-eyebrow-dot" aria-hidden="true"></span>
                Komunikasi warga
            </p>
            <h1 id="contact-hero-heading" class="lw-profile-hero__title">
                Pengaduan
            </h1>
            <p class="lw-profile-hero__lead">
                Kirim laporan atau pertanyaan terkait layanan portal.
                Untuk kontak ketua atau sekretaris RT, lihat halaman
                <a href="{{ route('profile.index') }}" class="lw-inline-link">Profil</a>.
            </p>
        </div>
    </div>
</section>
