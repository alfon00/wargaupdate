@extends('layouts.app')

@section('title', 'Laporan Terkirim')

@section('content')
<section class="lw-services-section lw-band--alt">
    <div class="lw-container lw-container--narrow">
    <div class="lw-success-card">
        <p class="lw-section-tag lw-mb-0">Terkirim</p>
        <h1 class="lw-section-title lw-mt-2">Laporan berhasil dikirim</h1>
        <p class="lw-section-desc">
            Nomor laporan Anda:
            <strong>{{ $data['report_number'] }}</strong>
        </p>
        <p class="lw-section-desc lw-mt-2">
            Pengurus <strong>{{ $data['rt_label'] }}</strong> akan menindaklanjuti melalui kontak yang Anda berikan.
        </p>
        <p class="lw-section-desc lw-mt-2">
            Konfirmasi juga dikirim ke WhatsApp Anda jika nomor yang diisi valid.
        </p>
        <div class="lw-form-actions lw-form-actions--row lw-success-actions lw-mt-5">
            <a href="{{ route('home') }}" class="lw-btn-secondary">Beranda</a>
            <a href="{{ route('contact.create') }}" class="lw-btn-secondary">Kirim laporan lain</a>
        </div>
    </div>
    </div>
</section>
@endsection
