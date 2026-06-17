@extends('layouts.app')

@section('title', 'Layanan')

@section('content')
<div class="lw-services-page">
    @include('public.partials.services.hero')

    <div class="lw-container lw-services-board">
        <section class="lw-services-hub-section" aria-labelledby="layanan-hub-heading">
            <header class="lw-profile-section-head lw-home-section-head">
                <h2 id="layanan-hub-heading" class="lw-section-title">Pilih layanan</h2>
                <p class="lw-profile-section-lead">
                    Pilih salah satu layanan di bawah sesuai kebutuhan Anda.
                </p>
            </header>
            <div class="lw-service-hub-grid">
                <x-service-hub-card
                    :href="route('services.surat')"
                    title="Surat pengantar RT"
                    description="Pilih jenis surat, baca persyaratan, lalu ajukan permohonan — bukan penerbitan KK, KTP, atau SKTM resmi."
                    icon="document"
                    cta="Pilih jenis surat"
                />
                <x-service-hub-card
                    :href="route('services.pendataan-ulang')"
                    title="Pendataan ulang"
                    description="Unggah scan KK dan KTP/KIA setiap anggota — pengurus RT memeriksa berkas dan memperbarui data."
                    icon="refresh"
                    cta="Mulai pendataan ulang"
                />
                <x-service-hub-card
                    :href="route('services.pendataan-warga')"
                    title="Pendataan warga"
                    description="Untuk keluarga belum terdata — isi data KK, unggah identitas tiap anggota, lalu RT verifikasi."
                    icon="users"
                    cta="Mulai pendataan warga"
                />
            </div>
        </section>

        @include('public.partials.service-flow-tabs', [
            'serviceFlows' => $serviceFlows,
            'sectionId' => 'alur',
            'headingId' => 'catalog-heading-alur',
        ])
    </div>
</div>
@endsection
