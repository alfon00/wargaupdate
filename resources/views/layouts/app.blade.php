<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Beranda') — {{ config('kelurahan.portal_nama') }}</title>
    <x-site-trust-meta description="Portal layanan surat pengantar RT. Bukan situs Dukcapil, Kemendagri, atau bank." />
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.lw-styles')
</head>
@php
    $homeHeroBgStyle = '';
    if (request()->routeIs('home')) {
        $heroImage = config('kelurahan.hero_beranda_image');
        $heroPath = public_path($heroImage);
        $heroSrc = filled($heroImage) && file_exists($heroPath)
            ? asset($heroImage)
            : asset('images/hero/beranda-layanan-rt.svg');
        $homeHeroBgStyle = "--lw-home-hero-bg-image: url('{$heroSrc}')";
    }
@endphp
<body class="lw-shell{{ request()->routeIs('home') ? ' lw-page-home' : ' lw-page-inner' }}"@if($homeHeroBgStyle) style="{{ $homeHeroBgStyle }}"@endif>
    @include('layouts.partials.navbar')

    <main class="lw-main">
        @if(session('success'))
            <div class="lw-alert lw-alert--success">{{ session('success') }}</div>
        @endif
        @if($errors->any() && ! request()->routeIs('login', 'login.hub', 'home'))
            <div class="lw-alert lw-alert--error">
                <ul class="lw-alert-list">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>

    @include('layouts.partials.footer')
    @stack('scripts')
</body>
</html>
