@extends('layouts.app')

@section('title', $rtProfile->displayName())

@section('content')
<div class="lw-profile-page">
    <div class="lw-container lw-profile-board">
        <p class="lw-profile-back">
            <a href="{{ route('profile.index', ['rt' => $rtProfile->slug]) }}">← Kembali ke daftar RT</a>
        </p>

        @include('public.partials.profile-rt-show-card', [
            'rtProfile' => $rtProfile,
            'residentCount' => $residentCount,
        ])
    </div>
</div>
@endsection
