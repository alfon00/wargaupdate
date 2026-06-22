<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — {{ config('kelurahan.portal_nama', 'Layanan Warga') }}</title>
    <meta name="robots" content="noindex, nofollow">
    @include('layouts.partials.favicon')
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.lw-styles')
</head>
<body class="lw-panel-body lw-panel-theme @if(auth()->user()?->isRtStaff()) lw-panel--rt @elseif(auth()->user()?->isKelurahan()) lw-panel--kelurahan @endif">
    <div class="lw-panel-layout">
        <input type="checkbox" id="lw-panel-menu-toggle" class="lw-panel-menu-toggle" aria-hidden="true">
        <label for="lw-panel-menu-toggle" class="lw-panel-backdrop" aria-label="Tutup menu"></label>

        @include('layouts.partials.panel-sidebar')

        <div class="lw-panel-main">
            <header class="lw-panel-topbar">
                <label for="lw-panel-menu-toggle" class="lw-panel-menu-btn" aria-label="Buka menu panel">
                    <span class="lw-panel-menu-icon" aria-hidden="true"></span>
                </label>
                <div class="lw-panel-topbar-center">
                    <p class="lw-panel-topbar-title">
                        @yield('title', 'Panel')
                        @if(auth()->user()?->isKelurahan())
                            <span class="lw-panel-topbar-role">{{ auth()->user()->role->label() }}</span>
                        @elseif(auth()->user()?->isRtStaff())
                            <span class="lw-panel-topbar-role">RT</span>
                        @endif
                    </p>
                </div>
                <div class="lw-panel-topbar-actions">
                    <form method="POST" action="{{ route('logout') }}" class="lw-panel-topbar-logout">
                        @csrf
                        <button type="submit" class="lw-panel-logout-btn lw-panel-logout-btn--compact">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="lw-panel-content">
                @if(session('success'))
                    <div class="lw-panel-alert lw-panel-alert--success">{{ session('success') }}</div>
                @endif
                @if(session('info'))
                    <div class="lw-panel-alert lw-panel-alert--info">{{ session('info') }}</div>
                @endif
                @if(session('warning'))
                    <div class="lw-panel-alert lw-panel-alert--warn">{{ session('warning') }}</div>
                @endif
                @if(session('face_sync_warning'))
                    <div class="lw-panel-alert lw-panel-alert--warn">{{ session('face_sync_warning') }}</div>
                @endif
                @if($errors->any())
                    <div class="lw-panel-alert lw-panel-alert--error">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @if(auth()->user()?->isRtStaff())
        <x-rt.delete-signature-modal />
        @vite('resources/js/rt-delete-signature.js')
    @endif
    @stack('scripts')
</body>
</html>
