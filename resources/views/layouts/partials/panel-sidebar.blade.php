<aside class="lw-panel-sidebar" id="lw-panel-sidebar" aria-label="Menu panel pengurus">
    <div class="lw-panel-sidebar-inner">
        <div class="lw-panel-brand">
            <p class="lw-panel-brand-eyebrow">{{ auth()->user()->role?->panelEyebrow() ?? 'Panel pengurus' }}</p>
            <p class="lw-panel-brand-title">{{ config('kelurahan.portal_nama', 'Layanan Warga RT') }}</p>
            @if($rtProfile)
                <p class="lw-panel-brand-sub">{{ $rtProfile->displayName() }}</p>
            @endif
        </div>

        <div class="lw-panel-sidebar-nav">
            @include('layouts.partials.panel-nav-links')
        </div>

        <div class="lw-panel-user">
            <a href="{{ auth()->user()->profileRoute() }}" class="lw-panel-user-link @if(request()->routeIs('rt.profile', 'admin.profile*')) lw-panel-user-link--active @endif" aria-label="Pengaturan akun">
                <img src="{{ auth()->user()->avatarUrl() }}" alt="" class="lw-panel-user-avatar" width="32" height="32">
                <div class="lw-panel-user-meta">
                    <p class="lw-panel-user-name" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</p>
                    <p class="lw-panel-user-role">{{ auth()->user()->role->label() }}</p>
                    @if(auth()->user()->email)
                        <p class="lw-panel-user-email" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</p>
                    @endif
                </div>
            </a>
            <div class="lw-panel-user-actions">
                <form method="POST" action="{{ route('logout') }}" class="lw-panel-user-action-form">
                    @csrf
                    <button type="submit" class="lw-panel-user-action lw-panel-user-action--button lw-panel-user-action--danger">Keluar</button>
                </form>
            </div>
        </div>
    </div>
</aside>
