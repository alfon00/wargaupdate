@php
    $navLinks = [
        ['route' => 'home', 'label' => 'Beranda', 'active' => request()->routeIs('home')],
        ['route' => 'profile.index', 'label' => 'Profil', 'active' => request()->routeIs('profile.*')],
        ['route' => 'activities.index', 'label' => 'Kegiatan & Pengumuman', 'active' => request()->routeIs('activities.*')],
        ['route' => 'services.index', 'label' => 'Layanan', 'active' => request()->routeIs('services.*')],
        ['route' => 'contact.create', 'label' => 'Pengaduan', 'active' => request()->routeIs('contact.*')],
        ['route' => 'track.form', 'label' => 'Lacak Permohonan', 'active' => request()->routeIs('track.*')],
    ];
@endphp

<nav class="lw-nav" aria-label="Navigasi utama">
    <div class="lw-nav-inner">
        <a href="{{ route('home') }}" class="lw-nav-logo-wrap" title="Beranda — {{ config('kelurahan.portal_nama') }}">
            <img src="{{ asset(config('kelurahan.portal_logo')) }}" alt="{{ config('kelurahan.portal_nama') }}" class="lw-nav-portal-icon" width="48" height="48" decoding="async">
        </a>

        <div class="lw-nav-text">
            <a href="{{ route('home') }}" class="lw-nav-title">{{ config('kelurahan.portal_nama') }}</a>
            @if(filled(config('kelurahan.portal_subtitle_nav')))
                <span class="lw-nav-subtitle">{{ config('kelurahan.portal_subtitle_nav') }}</span>
            @endif
        </div>

        <input type="checkbox" id="lw-nav-toggle" class="lw-nav-toggle" aria-hidden="true">
        <label for="lw-nav-toggle" class="lw-nav-menu-btn" aria-label="Buka menu navigasi">
            <span class="lw-nav-menu-icon" aria-hidden="true"></span>
        </label>

        <div class="lw-nav-panel">
            <div class="lw-nav-links">
                @foreach($navLinks as $link)
                    <a href="{{ route($link['route']) }}"
                        class="lw-nav-link {{ $link['active'] ? 'lw-nav-link-active' : '' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach

                @auth
                    @if(auth()->user()->isWarga())
                        <a href="{{ route('portal.dashboard') }}" class="lw-nav-link">Portal Warga</a>
                    @endif
                @else
                    <a href="{{ route('login.hub') }}" class="lw-nav-cta {{ request()->routeIs('login.hub') ? 'lw-nav-cta-active' : '' }}">Akses Pengurus</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
