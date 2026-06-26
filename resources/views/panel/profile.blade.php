@extends('layouts.panel')

@section('title', 'Profil saya')

@section('content')
@php
    $linkedRt = $user->isRtStaff() ? $user->resolvedRtProfile() : null;
@endphp

@if($user->isRtStaff() && ! $linkedRt)
    <p class="lw-panel-profile-warn lw-mb-4">Akun belum terhubung ke profil RT di halaman publik. Hubungi admin untuk menetapkan RT.</p>
@endif

<p class="lw-panel-page-lead lw-mb-6">
    Ubah foto profil, nama, email untuk masuk, dan kata sandi akun Anda.
    @if($user->isRtStaff() || $user->isKelurahan())
        Perubahan nama, foto, dan telepon juga tampil di halaman
        <a href="{{ route('profile.index') }}" class="lw-panel-link" target="_blank" rel="noopener">Profil</a> publik.
    @endif
</p>

<form method="POST" action="{{ $user->profileUpdateRoute() }}" enctype="multipart/form-data" class="lw-panel-profile-form lw-panel-profile-block lw-panel-profile-form--grid">
    @csrf
    @method('PUT')

    <div class="lw-panel-profile-photo-column">
        @include('panel.partials.profile-avatar-fields', ['user' => $user])
    </div>

    <section class="lw-panel-profile-fields lw-panel-profile-fields--grid" aria-labelledby="profile-data-heading">
            <h2 id="profile-data-heading" class="lw-panel-section-title">Data akun</h2>
            <div class="lw-panel-form lw-panel-form--wide">
                <div class="lw-panel-field">
                    <label for="name">Nama lengkap</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                </div>
                <div class="lw-panel-field">
                    <label for="email">Email (username masuk)</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                    <p class="lw-panel-field-hint">Dipakai saat masuk di halaman Akses Pengurus. Tidak ditampilkan di halaman Profil publik.</p>
                </div>
                @if($user->isRtStaff() && $linkedRt)
                <div class="lw-panel-field">
                    <label for="contact_email">Email kontak RT (halaman publik)</label>
                    <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $linkedRt->email) }}" autocomplete="email" placeholder="contoh: rt001@kelurahan.example">
                    <p class="lw-panel-field-hint">Ditampilkan di halaman Profil publik sebagai kontak RT. Bukan email untuk masuk panel.</p>
                </div>
                @endif
                <div class="lw-panel-field">
                    <label for="phone">Telepon / WhatsApp</label>
                    <x-phone-input id="phone" name="phone" :value="old('phone', $user->phone)" />
                    @error('phone')<p class="lw-form-error">{{ $message }}</p>@enderror
                    <p class="lw-panel-field-hint">Ditampilkan di halaman Profil publik (RT atau Lurah).</p>
                </div>
                @if($user->isRtStaff())
                <div class="lw-panel-field">
                    <label for="public_bio">Bio singkat (halaman profil pengurus)</label>
                    <textarea id="public_bio" name="public_bio" rows="3" maxlength="1000" placeholder="Tugas, pengalaman, atau pesan untuk warga.">{{ old('public_bio', $user->public_bio) }}</textarea>
                </div>
                @endif
                <div class="lw-panel-field">
                    <label for="password">Kata sandi baru</label>
                    <input type="password" id="password" name="password" autocomplete="new-password" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div class="lw-panel-field">
                    <label for="password_confirmation">Konfirmasi kata sandi baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                </div>
                <div class="lw-panel-field">
                    <label for="current_password">Kata sandi saat ini</label>
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password" placeholder="Wajib jika mengganti kata sandi">
                    <p class="lw-panel-field-hint">Isi hanya ketika Anda mengubah kata sandi.</p>
                </div>
            </div>
            <div class="lw-panel-form-actions">
                <button type="submit" class="lw-panel-btn">Simpan perubahan</button>
                <a href="{{ $user->dashboardRoute() }}" class="lw-panel-btn lw-panel-btn--secondary">Batal</a>
            </div>
        </section>
</form>
@include('panel.partials.profile-avatar-delete', ['user' => $user])
@endsection
