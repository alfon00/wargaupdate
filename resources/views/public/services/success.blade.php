@extends('layouts.app')

@section('title', 'Permohonan Terkirim')

@section('content')
<section class="lw-page-narrow lw-services-section lw-pendataan-success" aria-labelledby="apply-success-title">
    <div class="lw-success-card">
        <div class="lw-pendataan-success-icon lw-mx-auto lw-mb-4" aria-hidden="true">✓</div>
        <p class="lw-section-tag lw-mb-0">Berhasil</p>
        <h1 id="apply-success-title" class="lw-section-title lw-mt-2">Permohonan Berhasil Dikirim</h1>

        <p class="lw-mt-4 lw-mb-0">
            <span class="lw-badge {{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span>
        </p>
        <p class="lw-form-hint lw-mt-2">
            Permohonan Anda telah diterima dan menunggu verifikasi pengurus RT. Anda akan mendapat notifikasi jika status berubah (jika mengaktifkan WhatsApp saat pengajuan).
        </p>
        <p class="lw-form-hint lw-mt-2 lw-mb-0">
            Untuk mengajukan surat pengantar lain, pilih jenis surat di halaman <a href="{{ route('services.surat') }}" class="lw-inline-link">Layanan Surat</a>.
        </p>

        <div class="lw-alert lw-alert--info lw-mt-4 lw-text-left" role="note">
            <p class="lw-mb-0"><strong>Simpan nomor permohonan</strong></p>
            <p class="lw-form-hint lw-mt-2 lw-mb-0">
                Catat atau salin nomor di bawah. Nomor ini wajib untuk lacak status di menu Lacak — tanpa nomor, status tidak dapat dicek.
            </p>
        </div>

        <div class="lw-application-number-card lw-mt-4 lw-text-left">
            <p class="lw-form-hint lw-mb-2 lw-success-label">Nomor permohonan</p>
            <div class="lw-application-number-block">
                <p id="application-number-value" class="lw-application-number-display">{{ $application->application_number }}</p>
                <button type="button" class="lw-btn-secondary lw-copy-text-btn"
                    data-copy-text="{{ $application->application_number }}"
                    data-copy-default-label="Salin nomor"
                    data-copy-done-label="Tersalin!">
                    Salin nomor
                </button>
            </div>
            <p class="lw-copy-text-feedback" aria-live="polite" hidden></p>
            <p class="lw-section-desc lw-mb-0 lw-mt-3">{{ $application->serviceType->name }}</p>
            <p class="lw-form-hint lw-mt-2 lw-mb-0">Pemohon: {{ $application->applicantName() }}</p>
        </div>

        <div class="lw-form-actions lw-form-actions--row lw-mt-6 lw-success-actions">
            <a href="{{ route('track.form') }}?application_number={{ urlencode($application->application_number) }}" class="lw-btn-primary">Lacak Status</a>
            <a href="{{ route('services.index') }}" class="lw-btn-secondary">Kembali ke Layanan</a>
        </div>
    </div>
</section>
@endsection
