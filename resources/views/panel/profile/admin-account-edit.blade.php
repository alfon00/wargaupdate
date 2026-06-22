@extends('layouts.panel')

@section('title', 'Edit profil akun')

@section('content')
<div class="lw-admin-page">
    <p class="lw-mb-4">
        <a href="{{ route('admin.profile.account.show') }}" class="lw-panel-page-back">← Kembali ke detail profil</a>
    </p>

    <header class="lw-panel-page-head">
        <div>
            <p class="lw-panel-page-eyebrow">Panel Kelurahan</p>
            <h1 class="lw-panel-page-title">Edit profil akun</h1>
            <p class="lw-panel-page-lead">Ubah foto profil, nama, email untuk masuk, dan kata sandi akun Anda.</p>
        </div>
    </header>

    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="lw-panel-profile-form lw-panel-profile-form--grid">
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
                        <p class="lw-panel-field-hint">Dipakai saat masuk di halaman Akses Pengurus.</p>
                    </div>
                    <div class="lw-panel-field">
                        <label for="phone">Telepon / WhatsApp</label>
                        <x-phone-input id="phone" name="phone" :value="old('phone', $user->phone)" />
                        @error('phone')<p class="lw-form-error">{{ $message }}</p>@enderror
                    </div>
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
                    <a href="{{ route('admin.profile.account.show') }}" class="lw-panel-btn lw-panel-btn--secondary">Batal</a>
                </div>
            </section>
    </form>
    @include('panel.partials.profile-avatar-delete', ['user' => $user])
</div>
@endsection
