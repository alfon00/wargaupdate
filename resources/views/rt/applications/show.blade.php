@extends('layouts.panel')

@section('title', $application->application_number)

@section('content')
<div class="lw-rt-page">
@include('rt.partials.page-head', [
    'title' => $application->application_number,
    'lead' => $application->serviceType->name.' · Status: '.$application->status->label(),
])

<p class="lw-mb-4">
    <a href="{{ route('rt.applications.index') }}" class="lw-panel-page-back">← Daftar permohonan</a>
</p>

<article class="lw-panel-card lw-panel-card--full">
    @include('rt.partials.application-request-reference', [
        'application' => $application,
        'documents' => $documents,
        'detailed' => true,
        'requiredCount' => $requiredCount,
        'rejectMessageTemplate' => $rejectMessageTemplate,
    ])

    @if($application->rejection_reason && $application->status === \App\Enums\ApplicationStatus::PerluLengkap)
        <div class="lw-alert lw-alert--warn">
            <p class="lw-alert__title">Catatan untuk warga:</p>
            <p class="lw-pre-wrap-block">{{ $application->rejection_reason }}</p>
        </div>
    @endif

    @if($application->suratIdentityVerification)
    <section class="lw-panel-section">
        <h2 class="lw-panel-section-title">Verifikasi wajah saat pengajuan</h2>
        <p class="lw-panel-card-note">
            Diverifikasi {{ $application->suratIdentityVerification->verified_at?->translatedFormat('d F Y H:i') }}
            · sumber referensi {{ strtoupper($application->suratIdentityVerification->match_source) }}
            · jarak {{ number_format($application->suratIdentityVerification->match_distance, 4) }}
        </p>
        @if($application->suratIdentityVerification->selfieExists())
        <p class="lw-mt-2">
            <a href="{{ route('rt.applications.identity-selfie', $application) }}" class="lw-inline-link" target="_blank" rel="noopener">Lihat foto selfie verifikasi</a>
        </p>
        @endif
    </section>
    @endif

    @include('rt.applications.partials.notification-logs', ['notificationLogs' => $notificationLogs])
</article>

<div class="lw-rt-application-actions lw-panel-stack">
    @if($application->status->showsManualLetterSection())
        @include('rt.applications.partials.letter-issue-card', ['application' => $application])
    @endif

</div>
</div>
@endsection
