@extends('layouts.app')

@section('title', 'Pilih jenis surat')

@section('content')
<section class="lw-services-section lw-band--alt">
    <div class="lw-container lw-container--narrow">
    <a href="{{ route('services.index') }}" class="lw-auth-back">← Semua layanan</a>

    <header class="lw-auth-hub-head lw-auth-hub-head--compact">
        <p class="lw-section-tag">Layanan Surat</p>
        <h1 class="lw-section-title">Pilih jenis surat</h1>
        <p class="lw-auth-hub-lead lw-mb-0">
            Pilih jenis surat pengantar RT yang Anda butuhkan. Baca persyaratan di halaman berikutnya sebelum mengajukan.
        </p>
    </header>

    @if(session('info'))
    <div class="lw-alert lw-alert--info lw-mt-4" role="status">{{ session('info') }}</div>
    @endif

    @if($services->isEmpty())
        <div class="lw-empty-state lw-surface-muted lw-mt-6">Belum ada jenis surat aktif.</div>
    @else
        <div class="lw-catalog-grid lw-mt-6">
            @foreach($services as $service)
                <a href="{{ route('services.show', $service) }}" class="lw-service-card">
                    <span class="lw-service-card-inner">
                        <span class="lw-service-card-name">{{ $service->catalogLabel() }}</span>
                        <span class="lw-service-card-desc">{{ \Illuminate\Support\Str::limit($service->catalogDescription(), 140) }}</span>
                        <span class="lw-service-card-btn-wrap">
                            <span class="lw-service-card-arrow">Lihat persyaratan →</span>
                        </span>
                    </span>
                </a>
            @endforeach
        </div>
    @endif

    <p class="lw-form-hint lw-mt-6">
        <a href="{{ route('track.form') }}" class="lw-inline-link">Lacak permohonan</a>
        ·
        <a href="{{ route('services.index') }}" class="lw-inline-link">Semua layanan</a>
    </p>
    </div>
</section>
@endsection
