@extends('layouts.panel')

@section('title', 'Edit profil lurah')

@section('content')
<div class="lw-admin-page">
    <p class="lw-mb-4">
        <a href="{{ route('admin.profile.kelurahan.show') }}" class="lw-panel-page-back">← Kembali ke detail profil</a>
    </p>

    @include('panel.partials.kelurahan-public-profile-form', ['lurahOfficial' => $lurahOfficial])
</div>
@endsection
