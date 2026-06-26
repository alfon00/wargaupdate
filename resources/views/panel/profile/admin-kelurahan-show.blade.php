@extends('layouts.panel')

@section('title', 'Profil Lurah')

@section('content')
@php
    $lurahName = $lurah['nama'] ?? 'Lurah';
    $lurahPhoto = $lurah['photo'] ?? null;
    $kel = config('kelurahan');
@endphp
<div class="lw-admin-page">
    <p class="lw-mb-4">
        <a href="{{ route('admin.profile') }}" class="lw-panel-page-back">← Kembali ke profil</a>
    </p>

    <article class="lw-panel-card lw-panel-card--full lw-panel-profile-show">
        <div class="lw-panel-profile-show-hero">
            <div class="lw-panel-profile-show-hero__photo-wrap">
                @if($lurahPhoto)
                    <img src="{{ $lurahPhoto }}" alt="Foto {{ $lurahName }}" class="lw-panel-profile-show-hero__photo" width="112" height="112">
                @else
                    <x-photo-empty :name="$lurahName" size="lg" class="lw-panel-profile-show-hero__photo-empty" />
                @endif
            </div>
            <div class="lw-panel-profile-show-hero__content">
                <p class="lw-panel-page-eyebrow">Profil lurah · Halaman publik</p>
                <h1 class="lw-panel-page-title lw-panel-profile-show-hero__title">{{ $lurahName }}</h1>
                <p class="lw-panel-profile-show-hero__role">{{ $lurah['jabatan'] ?? 'Lurah' }} · {{ $kel['distrik'] }}</p>
            </div>
        </div>

        <div class="lw-mt-4">
            <h2 class="lw-panel-section-title">Visi</h2>
            @if(filled($lurah['visi'] ?? null))
                <p class="lw-panel-profile-show-text">{{ $lurah['visi'] }}</p>
            @else
                <p class="lw-panel-profile-show-text lw-panel-profile-show-text--empty">Belum diisi</p>
            @endif
            <h2 class="lw-panel-section-title lw-mt-4">Misi</h2>
            @if(filled($lurah['misi'] ?? null))
                <p class="lw-panel-profile-show-text lw-pre-wrap">{{ $lurah['misi'] }}</p>
            @else
                <p class="lw-panel-profile-show-text lw-panel-profile-show-text--empty">Belum diisi</p>
            @endif
        </div>

        @php
            $hasContact = filled($lurah['telepon'] ?? null)
                || filled($lurah['whatsapp'] ?? null)
                || filled($lurah['email'] ?? null)
                || filled($lurah['jam_layanan'] ?? null)
                || filled($lurah['alamat_kantor'] ?? null);
        @endphp
        @if($hasContact)
        <dl class="lw-panel-dl lw-mt-4">
            @if(filled($lurah['telepon'] ?? null))
                <div class="lw-panel-dl-row">
                    <dt>Telepon</dt>
                    <dd>{{ $lurah['telepon'] }}</dd>
                </div>
            @endif
            @if(filled($lurah['whatsapp'] ?? null))
                <div class="lw-panel-dl-row">
                    <dt>WhatsApp</dt>
                    <dd>{{ $lurah['whatsapp'] }}</dd>
                </div>
            @endif
            @if(filled($lurah['email'] ?? null))
                <div class="lw-panel-dl-row">
                    <dt>Email kontak</dt>
                    <dd>{{ $lurah['email'] }}</dd>
                </div>
            @endif
            @if(filled($lurah['jam_layanan'] ?? null))
                <div class="lw-panel-dl-row">
                    <dt>Jam layanan</dt>
                    <dd>{{ $lurah['jam_layanan'] }}</dd>
                </div>
            @endif
            @if(filled($lurah['alamat_kantor'] ?? null))
                <div class="lw-panel-dl-row">
                    <dt>Alamat kantor</dt>
                    <dd>{{ $lurah['alamat_kantor'] }}</dd>
                </div>
            @endif
        </dl>
        @else
            <p class="lw-panel-profile-show-text lw-panel-profile-show-text--empty lw-mt-4">Kontak kantor belum diisi.</p>
        @endif

        <div class="lw-panel-profile-show-actions">
            <a href="{{ route('admin.profile.kelurahan.edit') }}" class="lw-panel-btn">Edit profil lurah</a>
            <a href="{{ route('profile.index') }}" class="lw-panel-btn lw-panel-btn--secondary" target="_blank" rel="noopener">Pratinjau publik</a>
        </div>
    </article>
</div>
@endsection
