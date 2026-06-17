@extends('layouts.app')

@section('title', 'Portal Warga')

@section('content')
<section class="lw-services-section lw-band--alt max-w-lg mx-auto">
    <div class="lw-container lw-container--narrow">
        <article class="lw-auth-card">
            <header class="lw-auth-header">
                <p class="lw-section-tag lw-mb-0">Portal warga</p>
                <h1 class="lw-section-title lw-mt-2">Selamat datang, {{ auth()->user()->name }}</h1>
                <p class="lw-auth-hub-lead lw-mt-2 lw-mb-0">Kelola permohonan surat dan pantau status dari sini.</p>
            </header>

            <div class="lw-form-actions lw-form-actions--row lw-mt-6" style="flex-direction:column;align-items:stretch">
                <a href="{{ route('citizen.applications.index') }}" class="lw-btn-primary">Permohonan saya</a>
                <a href="{{ route('track.form') }}" class="lw-btn-secondary">Lacak permohonan</a>
            </div>
        </article>
    </div>
</section>
@endsection
