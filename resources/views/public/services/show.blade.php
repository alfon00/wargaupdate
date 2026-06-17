@extends('layouts.app')

@section('title', $service->catalogLabel())

@section('content')
<section class="lw-services-section lw-band--alt">
    <div class="lw-container lw-container--narrow">
<a href="{{ route('services.surat') }}" class="lw-auth-back">← Pilih jenis surat</a>

<div class="lw-form-card">
    <span class="lw-section-tag">Surat pengantar RT</span>
    <h1 class="lw-section-title">{{ $service->catalogLabel() }}</h1>
    <p class="lw-section-desc">{{ $service->catalogDescription() }}</p>

    <x-service-requirements
        title="Persyaratan umum"
        :items="config('kelurahan.layanan_persyaratan.surat', [])"
        class="lw-service-show-requirements"
    />

    @if($service->required_fields)
    <x-service-requirements
        title="Persyaratan berkas"
        :items="$service->required_fields"
        class="lw-service-show-requirements"
    />
    @endif

    <div class="lw-alert lw-alert--warn lw-service-show-warn">
        <strong>Perhatian:</strong> Portal memfasilitasi <strong>permohonan surat pengantar</strong> RT. Surat dicetak dan ditandatangani di sekretariat RT — bukan melalui unduhan PDF di portal ini.
        Dokumen resmi (KK, KTP-el, SKTM/SKU, SKCK, dll.) diterbitkan oleh instansi berwenang setelah Anda melengkapi persyaratan mereka.
    </div>

    <div class="lw-home-hero-v2-actions lw-service-show-actions">
        <a href="{{ route('services.apply', $service) }}" class="lw-btn-primary">Ajukan surat pengantar</a>
    </div>
    <p class="lw-hero-note lw-service-show-note">
        Butuh bantuan? <a href="{{ route('contact.create') }}" class="lw-inline-link">Kontak &amp; laporan</a>
    </p>
</div>
    </div>
</section>
@endsection
