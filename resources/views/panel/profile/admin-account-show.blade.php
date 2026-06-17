@extends('layouts.panel')

@section('title', 'Profil akun')

@section('content')
<div class="lw-admin-page">
    <p class="lw-mb-4">
        <a href="{{ route('admin.profile') }}" class="lw-panel-page-back">← Kembali ke profil</a>
    </p>

    <article class="lw-panel-card lw-panel-card--full lw-panel-profile-show">
        <div class="lw-panel-profile-show-hero">
            <div class="lw-panel-profile-show-hero__photo-wrap">
                <img src="{{ $user->avatarUrl() }}" alt="Foto profil {{ $user->name }}" class="lw-panel-profile-show-hero__photo" width="112" height="112">
            </div>
            <div class="lw-panel-profile-show-hero__content">
                <p class="lw-panel-page-eyebrow">Profil akun</p>
                <h1 class="lw-panel-page-title lw-panel-profile-show-hero__title">{{ $user->name }}</h1>
                <p class="lw-panel-profile-show-hero__role"><span class="lw-badge">{{ $user->role->label() }}</span></p>
            </div>
        </div>

        <dl class="lw-panel-dl lw-mt-4">
            <div class="lw-panel-dl-row">
                <dt>Email login</dt>
                <dd>{{ $user->email }}</dd>
            </div>
            <div class="lw-panel-dl-row">
                <dt>Telepon</dt>
                <dd>{{ $user->phone ?: '—' }}</dd>
            </div>
        </dl>

        <div class="lw-panel-profile-show-actions">
            <a href="{{ route('admin.profile.account.edit') }}" class="lw-panel-btn">Edit profil</a>
            <a href="{{ route('admin.dashboard') }}" class="lw-panel-btn lw-panel-btn--secondary">Dashboard</a>
        </div>
    </article>
</div>
@endsection
