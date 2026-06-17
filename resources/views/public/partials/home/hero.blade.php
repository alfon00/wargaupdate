@php
    $kel = config('kelurahan');
@endphp
<section class="lw-home-hero-v2 lw-home-hero-v3 lw-home-hero-v3--bg" aria-labelledby="home-hero-title">
    <div class="lw-home-hero-v3-shell lw-home-hero-v3-shell--bg">
        <div class="lw-home-hero-v3-overlay" aria-hidden="true"></div>
        <div class="lw-home-hero-v2-content lw-home-hero-v2-content--modern">
            <p class="lw-home-hero-v2-eyebrow">
                <span class="lw-hero-eyebrow-dot" aria-hidden="true"></span>
                Portal warga · {{ $kel['nama'] }}
            </p>
            <h1 id="home-hero-title" class="lw-home-hero-v2-title">
                <span class="lw-home-hero-v2-headline">Layanan Administrasi RT</span>
                <span class="lw-home-hero-v2-tagline lw-home-hero-v2-tagline--short">{{ $heroTagline }}</span>
            </h1>
            <div class="lw-home-hero-v2-actions lw-home-hero-v2-actions--modern">
                <a href="{{ route('services.index') }}" class="lw-home-hero-btn lw-home-hero-btn--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
                    </svg>
                    Ajukan layanan
                </a>
                <a href="{{ route('activities.index') }}#activities-announce-heading" class="lw-home-hero-btn lw-home-hero-btn--secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    Lihat pengumuman
                </a>
            </div>
            <p class="lw-home-hero-v2-link">
                Sudah mengajukan?
                <a href="{{ route('track.form') }}" class="lw-inline-link">Lacak permohonan</a>
            </p>
        </div>
    </div>
</section>
