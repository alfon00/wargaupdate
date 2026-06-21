@extends('layouts.app')

@section('title', 'Keamanan')

@section('content')
<div class="lw-security-page">
    @include('public.partials.security.hero')

    <div class="lw-container lw-container--wide lw-security-board">
        <section class="lw-security-panel" aria-labelledby="security-main-heading">
            <header class="lw-profile-section-head lw-home-section-head">
                <h2 id="security-main-heading" class="lw-section-title">Portal resmi</h2>
                <p class="lw-profile-section-lead">
                    <strong>layananwarga.my.id</strong> adalah portal resmi {{ config('kelurahan.portal_nama') }}.
                    Situs ini <strong>bukan</strong> Dukcapil, Kemendagri, bank, atau layanan pembayaran.
                    Layanan portal gratis untuk warga dan tidak meminta kartu kredit, PIN bank, OTP pembayaran, atau transfer uang.
                </p>
            </header>

            <h3 class="lw-security-subheading">Panduan praktis</h3>
            <ul class="lw-security-list">
                <li>Form login hanya di <a href="{{ route('login.hub') }}" class="lw-inline-link">/akses-pengurus</a> untuk pengurus RT dan admin.</li>
                <li>Warga mengajukan surat dan pendataan di <a href="{{ route('services.index') }}" class="lw-inline-link">/layanan</a> tanpa login.</li>
                <li>Laporkan halaman mencurigakan atau kendala portal melalui <a href="{{ route('contact.create') }}" class="lw-inline-link">formulir Kontak &amp; laporan</a>.</li>
                <li>Pastikan alamat di bilah alamat browser adalah <strong>layananwarga.my.id</strong> sebelum mengisi data.</li>
            </ul>

            <p class="lw-security-policy lw-mb-0">
                Kebijakan keamanan: <a href="{{ url('/.well-known/security.txt') }}" class="lw-inline-link">/.well-known/security.txt</a>
            </p>
        </section>
    </div>
</div>
@endsection
