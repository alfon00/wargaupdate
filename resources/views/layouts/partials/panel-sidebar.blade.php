@php
    $rtProfile = auth()->user()->isRtStaff()
        ? \App\Models\RtProfile::forRtStaffUser(auth()->user())
        : null;
    $pendingPendataan = $rtProfile
        ? \App\Models\Resident::forRtProfile($rtProfile)
            ->where('is_head_of_family', true)
            ->pendingPendataan()
            ->count()
        : 0;
    $pendingApps = match (true) {
        $rtProfile !== null => \App\Models\Application::forRtProfile($rtProfile)
            ->pendingRtSidebar()
            ->count(),
        auth()->user()->isKelurahan(), auth()->user()->isSuperAdmin() => \App\Models\Application::pendingRtSidebar()->count(),
        default => 0,
    };
    $newReports = match (true) {
        $rtProfile !== null => \App\Models\CitizenReport::forRtProfile($rtProfile)
            ->where('status', \App\Enums\ReportStatus::Baru)
            ->count(),
        auth()->user()->isKelurahan() => \App\Models\CitizenReport::where('status', \App\Enums\ReportStatus::Baru)->count(),
        default => 0,
    };

    $rtStaffWithoutProfile = auth()->user()->isSuperAdmin()
        ? \App\Models\User::query()
            ->whereIn('role', [\App\Enums\UserRole::KetuaRt, \App\Enums\UserRole::SekretarisRt])
            ->whereNull('rt_profile_id')
            ->count()
        : 0;

    $pendingDeletionRequests = auth()->user()->isSuperAdmin()
        ? \App\Models\PermanentDeletionRequest::query()->pending()->count()
        : 0;

    $linkClass = function (bool $active): string {
        return 'lw-panel-nav-link'.($active ? ' lw-panel-nav-link--active' : '');
    };
@endphp

<aside class="lw-panel-sidebar" aria-label="Menu panel pengurus">
    <div class="lw-panel-sidebar-inner">
        <div class="lw-panel-brand">
            <p class="lw-panel-brand-eyebrow">@if(auth()->user()->isSuperAdmin())Admin sistem @elseif(auth()->user()->isRtStaff())Panel RT @elseif(auth()->user()->isKelurahan())Monitoring @else Panel pengurus @endif</p>
            <p class="lw-panel-brand-title">{{ config('kelurahan.portal_nama', 'Layanan Warga RT') }}</p>
            @if($rtProfile)
                <p class="lw-panel-brand-sub">{{ $rtProfile->displayName() }}</p>
            @endif
            <x-today-date variant="labeled" class="lw-panel-date" />
        </div>

        <nav class="lw-panel-nav" aria-label="Navigasi panel">
            @if(auth()->user()->isRtStaff())
                <a href="{{ route('rt.dashboard') }}" class="{{ $linkClass(request()->routeIs('rt.dashboard')) }}">
                    <span class="lw-panel-nav-link-inner">
                        <svg class="lw-panel-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        Dashboard
                    </span>
                </a>
                <div class="lw-panel-nav-group">
                    <p class="lw-panel-nav-group-label">Data warga</p>
                    <a href="{{ route('rt.pendataan.index') }}" class="{{ $linkClass(request()->routeIs('rt.pendataan.*')) }}">
                        <span class="lw-panel-nav-link-inner">
                            <svg class="lw-panel-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                            Verifikasi pendataan
                        </span>
                        @if($pendingPendataan > 0)
                            <span class="lw-panel-badge">{{ $pendingPendataan }}</span>
                        @endif
                    </a>
                    <a href="{{ route('rt.data-warga.index') }}" class="{{ $linkClass(request()->routeIs('rt.data-warga.*') || request()->routeIs('rt.residents.*') || request()->routeIs('rt.households.*')) }}">
                        <span class="lw-panel-nav-link-inner">
                            <svg class="lw-panel-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Data warga lengkap
                        </span>
                    </a>
                </div>
                <div class="lw-panel-nav-group">
                    <p class="lw-panel-nav-group-label">Layanan</p>
                    <a href="{{ route('rt.applications.index') }}" class="{{ $linkClass(request()->routeIs('rt.applications.*')) }}">
                        <span class="lw-panel-nav-link-inner">
                            <svg class="lw-panel-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                            Permohonan surat
                        </span>
                        @if($pendingApps > 0)
                            <span class="lw-panel-badge lw-panel-badge--muted">{{ $pendingApps }}</span>
                        @endif
                    </a>
                    <a href="{{ route('rt.reports.index') }}" class="{{ $linkClass(request()->routeIs('rt.reports.*')) }}">
                        <span class="lw-panel-nav-link-inner">
                            <svg class="lw-panel-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            Kontak &amp; laporan
                        </span>
                        @if($newReports > 0)
                            <span class="lw-panel-badge">{{ $newReports }}</span>
                        @endif
                    </a>
                    <a href="{{ route('rt.notifications.index') }}" class="{{ $linkClass(request()->routeIs('rt.notifications.*')) }}">
                        <span class="lw-panel-nav-link-inner">
                            <svg class="lw-panel-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            Notifikasi WA
                        </span>
                    </a>
                </div>
                <div class="lw-panel-nav-group">
                    <p class="lw-panel-nav-group-label">Publikasi</p>
                    <a href="{{ route('rt.kegiatan.index') }}" class="{{ $linkClass(request()->routeIs('rt.kegiatan.*') || request()->routeIs('rt.pengumuman.*')) }}">
                        <span class="lw-panel-nav-link-inner">
                            <svg class="lw-panel-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Kegiatan &amp; pengumuman
                        </span>
                    </a>
                </div>
            @elseif(auth()->user()->isKelurahan())
                <a href="{{ route('kelurahan.dashboard') }}" class="{{ $linkClass(request()->routeIs('kelurahan.dashboard')) }}">
                    <span class="lw-panel-nav-link-inner">
                        <svg class="lw-panel-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        Dashboard
                    </span>
                </a>
                <div class="lw-panel-nav-group">
                    <p class="lw-panel-nav-group-label">Monitoring</p>
                    <a href="{{ route('kelurahan.applications.index') }}" class="{{ $linkClass(request()->routeIs('kelurahan.applications.*')) }}">
                        <span class="lw-panel-nav-link-inner">
                            <svg class="lw-panel-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                            Permohonan
                        </span>
                        @if($pendingApps > 0)
                            <span class="lw-panel-badge lw-panel-badge--muted">{{ $pendingApps }}</span>
                        @endif
                    </a>
                </div>
                <div class="lw-panel-nav-group">
                    <p class="lw-panel-nav-group-label">Publikasi</p>
                    <a href="{{ route('kelurahan.kegiatan.index') }}" class="{{ $linkClass(request()->routeIs('kelurahan.kegiatan.*') || request()->routeIs('kelurahan.pengumuman.*')) }}">
                        <span class="lw-panel-nav-link-inner">
                            <svg class="lw-panel-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Kegiatan &amp; pengumuman
                        </span>
                    </a>
                </div>
                <div class="lw-panel-nav-group">
                    <p class="lw-panel-nav-group-label">Data</p>
                    <a href="{{ route('kelurahan.population.index') }}" class="{{ $linkClass(request()->routeIs('kelurahan.population.*')) }}">
                        <span class="lw-panel-nav-link-inner">
                            <svg class="lw-panel-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Data warga lengkap
                        </span>
                    </a>
                    <a href="{{ route('kelurahan.reports.index') }}" class="{{ $linkClass(request()->routeIs('kelurahan.reports.*')) }}">
                        <span class="lw-panel-nav-link-inner">
                            <svg class="lw-panel-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            Laporan warga
                        </span>
                        @if($newReports > 0)
                            <span class="lw-panel-badge">{{ $newReports }}</span>
                        @endif
                    </a>
                </div>
            @elseif(auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.dashboard') }}" class="{{ $linkClass(request()->routeIs('admin.dashboard')) }}">
                    <span class="lw-admin-nav-link-inner">
                        <svg class="lw-admin-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        Dashboard
                    </span>
                </a>
                <div class="lw-admin-nav-group">
                    <p class="lw-admin-nav-group-label">Monitoring operasional</p>
                    <a href="{{ route('kelurahan.applications.index') }}" class="{{ $linkClass(request()->routeIs('kelurahan.applications.*')) }}">
                        <span class="lw-admin-nav-link-inner">
                            <svg class="lw-admin-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                            Permohonan surat
                        </span>
                        @if($pendingApps > 0)
                            <span class="lw-panel-badge lw-panel-badge--muted">{{ $pendingApps }}</span>
                        @endif
                    </a>
                    <a href="{{ route('kelurahan.population.index') }}" class="{{ $linkClass(request()->routeIs('kelurahan.population.*')) }}">
                        <span class="lw-admin-nav-link-inner">
                            <svg class="lw-admin-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Data warga lengkap
                        </span>
                    </a>
                </div>
                <div class="lw-admin-nav-group">
                    <p class="lw-admin-nav-group-label">Manajemen</p>
                    <a href="{{ route('admin.users.index') }}" class="{{ $linkClass(request()->routeIs('admin.users.*')) }}">
                        <span class="lw-admin-nav-link-inner">
                            <svg class="lw-admin-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Pengguna
                        </span>
                        @if($rtStaffWithoutProfile > 0)
                            <span class="lw-panel-badge">{{ $rtStaffWithoutProfile }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.rt-profiles.index') }}" class="{{ $linkClass(request()->routeIs('admin.rt-profiles.*')) }}">
                        <span class="lw-admin-nav-link-inner">
                            <svg class="lw-admin-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                            Profil RT
                        </span>
                    </a>
                    <a href="{{ route('admin.deletion-requests.index') }}" class="{{ $linkClass(request()->routeIs('admin.deletion-requests.*')) }}">
                        <span class="lw-admin-nav-link-inner">
                            <svg class="lw-admin-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                            Hapus permanen
                        </span>
                        @if($pendingDeletionRequests > 0)
                            <span class="lw-panel-badge">{{ $pendingDeletionRequests }}</span>
                        @endif
                    </a>
                </div>
                <div class="lw-admin-nav-group">
                    <p class="lw-admin-nav-group-label">Konten portal</p>
                    <a href="{{ route('admin.services.index') }}" class="{{ $linkClass(request()->routeIs('admin.services.*')) }}">
                        <span class="lw-admin-nav-link-inner">
                            <svg class="lw-admin-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                            Katalog layanan
                        </span>
                    </a>
                    <a href="{{ route('kelurahan.kegiatan.index') }}" class="{{ $linkClass(request()->routeIs('kelurahan.kegiatan.*') || request()->routeIs('kelurahan.pengumuman.*')) }}">
                        <span class="lw-admin-nav-link-inner">
                            <svg class="lw-admin-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Kegiatan &amp; pengumuman
                        </span>
                    </a>
                </div>
            @endif
        </nav>

        <div class="lw-panel-user">
            <a href="{{ auth()->user()->profileRoute() }}"
               class="lw-panel-user-link{{ request()->routeIs('admin.profile*', 'rt.profile', 'kelurahan.profile') ? ' lw-panel-user-link--active' : '' }}"
               aria-label="Profil saya">
                <img src="{{ auth()->user()->avatarUrl() }}" alt="" class="lw-panel-user-avatar" width="36" height="36">
                <div class="lw-panel-user-meta">
                <p class="lw-panel-user-name" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</p>
                <p class="lw-panel-user-role">{{ auth()->user()->role->label() }}</p>
                @if(auth()->user()->email)
                    <p class="lw-panel-user-email" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</p>
                @endif
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="lw-panel-logout-btn">Keluar</button>
            </form>
        </div>
    </div>
</aside>
