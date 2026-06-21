@extends('layouts.app')

@section('title', $user->name)

@section('content')
<section class="lw-services-section lw-band--alt">
    <div class="lw-container lw-container--narrow">
        <p class="lw-mb-4">
            <a href="{{ route('profile.index', ['rt' => $rtProfile->slug]) }}" class="lw-auth-back">← Kembali ke profil {{ $rtProfile->displayName() }}</a>
        </p>

        <article class="lw-form-card lw-staff-detail-card">
            <div class="lw-staff-card-inner">
                <div class="lw-staff-card-photo-wrap">
                    <img
                        src="{{ $user->avatarUrl() }}"
                        alt="Foto {{ $user->name }}"
                        class="lw-staff-card-photo"
                        width="120"
                        height="120"
                    >
                </div>
                <span class="lw-staff-card-role">{{ $roleLabel }}</span>
                <h1 class="lw-staff-card-name">{{ $user->name }}</h1>
                <p class="lw-profile-detail-meta lw-profile-detail-meta--compact lw-mb-0">
                    {{ $rtProfile->displayName() }} · {{ config('kelurahan.distrik') }}
                </p>
                @if(filled($user->public_bio))
                    <p class="lw-staff-card-bio">{{ $user->public_bio }}</p>
                @endif
                @if(filled($user->phone))
                    <div class="lw-staff-card-footer">
                        <x-wa-button :phone="$user->phone" label="Hubungi via WhatsApp" class="lw-staff-card-wa" />
                        <p class="lw-staff-wa-note">{{ config('kelurahan.whatsapp_admin_note') }}</p>
                    </div>
                @endif
                @if(filled($rtProfile->jam_layanan))
                    <p class="lw-form-hint lw-mt-4 lw-mb-0"><strong>Jam layanan RT:</strong> {{ $rtProfile->jam_layanan }}</p>
                @endif
            </div>
        </article>
    </div>
</section>
@endsection
