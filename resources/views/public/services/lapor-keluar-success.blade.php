@extends('layouts.app')

@section('title', 'Laporan diterima')

@section('content')
<section class="lw-services-section lw-pendataan-success lw-band--alt">
    <div class="lw-container lw-container--narrow">
    <div class="lw-success-card">
        <div class="lw-pendataan-success-icon lw-mx-auto lw-mb-4" aria-hidden="true">✓</div>
        <p class="lw-section-tag lw-mb-0">Lapor keluar</p>
        <h1 class="lw-section-title lw-mt-2">Laporan diterima</h1>
        <p class="lw-auth-hub-lead lw-mt-2 lw-mb-0">
            Laporan untuk <strong>{{ $data['name'] ?? 'warga' }}</strong> di <strong>{{ $data['rt_label'] ?? 'RT' }}</strong>
            telah dikirim. Nomor laporan: <strong>{{ $data['report_number'] ?? '—' }}</strong>.
            Pengurus RT akan memverifikasi sebelum status diarsipkan.
        </p>
        <div class="lw-home-hero-v2-actions lw-success-actions lw-success-actions--center lw-mt-6">
            <a href="{{ route('contact.create') }}" class="lw-btn-primary">Hubungi RT</a>
            <a href="{{ route('services.index') }}" class="lw-btn-secondary">Kembali ke layanan</a>
        </div>
    </div>
    </div>
</section>
@endsection
