@extends('layouts.app')

@section('title', 'Keaslian surat')

@section('content')
<section class="lw-services-section lw-track-page lw-band--alt lw-letter-verify-page" aria-labelledby="letter-verify-title">
    <div class="lw-container lw-container--narrow">
        <a href="{{ route('security') }}" class="lw-track-back">← Keamanan portal</a>

        <article class="lw-track-card lw-letter-verify-card">
            <header class="lw-letter-verify-hero">
                <div class="lw-letter-verify-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                </div>
                <h1 id="letter-verify-title" class="lw-letter-verify-title">Surat dinyatakan asli</h1>
                <p class="lw-letter-verify-lead">
                    Dokumen ini diterbitkan resmi melalui portal
                    <strong>{{ config('kelurahan.portal_nama') }}</strong>.
                    Nomor surat hanya tercantum pada PDF atau salinan fisik asli — bukan di halaman ini.
                </p>
            </header>

            <section class="lw-letter-verify-details" aria-labelledby="letter-verify-details-heading">
                <h2 id="letter-verify-details-heading" class="lw-letter-verify-details-title">Rincian surat</h2>
                <dl class="lw-letter-verify-dl">
                    <div>
                        <dt>Jenis layanan</dt>
                        <dd>{{ $application->serviceType->name }}</dd>
                    </div>
                    <div>
                        <dt>Pemohon</dt>
                        <dd>{{ $application->applicantName() }}</dd>
                    </div>
                    <div>
                        <dt>Wilayah RT</dt>
                        <dd>{{ $application->applicantRtLabel() }}</dd>
                    </div>
                    <div>
                        <dt>Tanggal terbit</dt>
                        <dd>{{ $letter->issued_at?->translatedFormat('d F Y') ?? '—' }}</dd>
                    </div>
                    <div class="lw-letter-verify-dl__full">
                        <dt>Portal resmi</dt>
                        <dd>
                            <a href="{{ route('home') }}" class="lw-inline-link">{{ parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url') }}</a>
                        </dd>
                    </div>
                </dl>
            </section>

            <footer class="lw-letter-verify-foot">
                <p class="lw-mb-0">
                    Informasi tidak sesuai atau Anda curiga dokumen palsu?
                    Baca panduan di <a href="{{ route('security') }}" class="lw-inline-link">halaman Keamanan</a>
                    atau laporkan melalui <a href="{{ route('contact.create') }}" class="lw-inline-link">formulir kontak</a>.
                </p>
            </footer>
        </article>
    </div>
</section>
@endsection
