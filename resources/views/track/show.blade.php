@extends('layouts.app')

@section('title', 'Status permohonan')

@section('content')
<section class="lw-services-section lw-track-page lw-band--alt" aria-labelledby="track-status-title">
    <div class="lw-container lw-container--narrow">
        <a href="{{ route('track.form') }}" class="lw-track-back">← Lacak lagi</a>

        <article class="lw-track-card">
            <header class="lw-track-status-header">
                <p class="lw-section-tag lw-mb-0">Status permohonan</p>
                <h1 id="track-status-title" class="lw-track-status-number lw-mt-2">{{ $application->application_number }}</h1>
                <p class="lw-mt-2 lw-mb-0">
                    <span class="lw-badge {{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span>
                </p>
            </header>

            <dl class="lw-track-status-dl">
                <div>
                    <dt>Layanan</dt>
                    <dd>{{ $application->serviceType->name }}</dd>
                </div>
                <div>
                    <dt>Pemohon</dt>
                    <dd>{{ $application->applicantName() }}</dd>
                </div>
                @if($application->purpose)
                    <div>
                        <dt>Keperluan</dt>
                        <dd>{{ $application->purpose }}</dd>
                    </div>
                @endif
                @if($application->rejection_reason)
                    <div>
                        <dt>Catatan</dt>
                        <dd>{{ $application->rejection_reason }}</dd>
                    </div>
                @endif
                @if($application->status === \App\Enums\ApplicationStatus::SiapDiambil && $application->issuedLetterNumber())
                    <div>
                        <dt>Nomor surat</dt>
                        <dd>{{ $application->issuedLetterNumber() }}</dd>
                    </div>
                    <div>
                        <dt>Pengambilan</dt>
                        <dd>Ambil salinan fisik di sekretariat {{ $application->applicantRtLabel() }} bila diperlukan. Pengurus RT dapat mengirim PDF surat via WhatsApp.</dd>
                    </div>
                @endif
            </dl>

            <h2 class="lw-section-title lw-track-progress-title">Progres permohonan</h2>
            <x-timeline :steps="$timelineSteps" />
        </article>
    </div>
</section>
@endsection
