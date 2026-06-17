@extends('layouts.app')

@section('title', 'Pendataan warga berhasil')

@section('content')
<section class="lw-services-section lw-pendataan-success lw-band--alt">
    <div class="lw-container lw-container--narrow">
    <div class="lw-success-card">
        <div class="lw-pendataan-success-icon lw-mx-auto lw-mb-4" aria-hidden="true">✓</div>
        <p class="lw-section-tag lw-mb-0">Pendataan warga</p>
        <h1 class="lw-section-title lw-mt-2">Pengajuan pendataan warga diterima</h1>
        <p class="lw-auth-hub-lead lw-mt-2 lw-mb-0">
            Data keluarga <strong>{{ $data['name'] ?? 'warga' }}</strong> di <strong>{{ $data['rt_label'] ?? 'RT' }}</strong>
            akan diverifikasi oleh pengurus RT. Anda akan menerima notifikasi WhatsApp setelah pengajuan diproses.
        </p>
        <div class="lw-home-hero-v2-actions lw-success-actions--center lw-success-actions">
            <a href="{{ route('services.index') }}" class="lw-btn-primary">Kembali ke layanan</a>
            <a href="{{ route('contact.create') }}" class="lw-btn-secondary">Hubungi RT</a>
        </div>
    </div>
    </div>
</section>
@endsection
