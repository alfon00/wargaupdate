@extends('layouts.app')

@section('title', 'Layanan')

@section('content')
<div class="lw-services-page">
    @include('public.partials.services.hero')

    <div class="lw-container lw-services-board">
        <section class="lw-services-hub-section" aria-label="Daftar layanan">
            <div class="lw-service-hub-grid">
                <x-service-hub-card
                    :href="route('services.surat')"
                    title="Surat pengantar RT"
                    description="Ajukan surat pengantar online. Bukan dokumen resmi Dukcapil."
                    icon="document"
                    cta="Buka surat"
                />
                <x-service-hub-card
                    :href="route('services.pendataan-ulang')"
                    title="Pendataan ulang"
                    description="Perbarui scan KK dan KTP/KIA keluarga yang sudah terdata."
                    icon="refresh"
                    cta="Mulai pendataan ulang"
                />
                <x-service-hub-card
                    :href="route('services.pendataan-warga')"
                    title="Pendataan warga"
                    description="Pencatatan awal untuk keluarga belum terdata di RT."
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
