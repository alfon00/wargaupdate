@extends('layouts.panel')

@section('title', 'Profil')

@section('content')
<div class="lw-admin-page">
    <header class="lw-panel-page-head">
        <div>
            <p class="lw-panel-page-eyebrow">Panel Kelurahan</p>
            <h1 class="lw-panel-page-title">Profil</h1>
            <p class="lw-panel-page-lead">Kelola profil akun admin dan profil lurah yang ditampilkan di halaman publik.</p>
        </div>
    </header>

    <div class="lw-panel-profile-hub">
        <article class="lw-panel-profile-card">
            <a href="{{ route('admin.profile.account.show') }}" class="lw-panel-profile-card__photo-link" aria-label="Detail profil akun {{ $user->name }}">
                <img src="{{ $user->avatarUrl() }}" alt="" class="lw-panel-profile-card__photo" width="96" height="96">
                <span class="lw-panel-profile-card__photo-hint">Detail</span>
            </a>
            <div class="lw-panel-profile-card__body">
                <p class="lw-panel-profile-card__eyebrow">Profil akun saya</p>
                <h2 class="lw-panel-profile-card__title">{{ $user->name }}</h2>
                <p class="lw-panel-profile-card__meta">{{ $user->role->label() }}</p>
                @if($user->email)
                    <p class="lw-panel-profile-card__meta lw-panel-profile-card__meta--muted">{{ $user->email }}</p>
                @endif
                <div class="lw-panel-profile-card__actions">
                    <a href="{{ route('admin.profile.account.show') }}" class="lw-panel-btn lw-panel-btn--sm">Detail</a>
                </div>
            </div>
        </article>

        <article class="lw-panel-profile-card">
            @php
                $lurahName = $lurah['nama'] ?? 'Lurah';
                $lurahPhoto = $lurah['photo'] ?? null;
            @endphp
            <a href="{{ route('admin.profile.kelurahan.show') }}" class="lw-panel-profile-card__photo-link" aria-label="Detail profil lurah {{ $lurahName }}">
                @if($lurahPhoto)
                    <img src="{{ $lurahPhoto }}" alt="" class="lw-panel-profile-card__photo" width="96" height="96">
                @else
                    <x-photo-empty :name="$lurahName" size="xs" class="lw-panel-profile-card__photo lw-panel-profile-card__photo--empty" />
                @endif
                <span class="lw-panel-profile-card__photo-hint">Detail</span>
            </a>
            <div class="lw-panel-profile-card__body">
                <p class="lw-panel-profile-card__eyebrow">Profil lurah publik</p>
                <h2 class="lw-panel-profile-card__title">{{ $lurahName }}</h2>
                <p class="lw-panel-profile-card__meta">{{ $lurah['jabatan'] ?? 'Lurah' }}</p>
                <p class="lw-panel-profile-card__meta lw-panel-profile-card__meta--muted">Ditampilkan di <a href="{{ route('profile.index') }}" class="lw-panel-link" target="_blank" rel="noopener">Profil</a> warga</p>
                <div class="lw-panel-profile-card__actions">
                    <a href="{{ route('admin.profile.kelurahan.show') }}" class="lw-panel-btn lw-panel-btn--sm">Detail</a>
                </div>
            </div>
        </article>
    </div>
</div>
@endsection
