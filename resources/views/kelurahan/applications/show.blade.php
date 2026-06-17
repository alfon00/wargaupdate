@extends('layouts.panel')

@section('title', $application->application_number)

@section('content')
<div class="lw-kel-page">
@include('kelurahan.partials.page-head', [
    'title' => $application->applicantName(),
    'lead' => $application->application_number.' · '.$application->serviceType->name.' · '.$application->applicantRtLabel(),
])

<p class="lw-mb-0 lw-kel-no-print">
    <a href="{{ route('kelurahan.applications.index') }}" class="lw-panel-page-back">← Kembali ke daftar permohonan</a>
</p>

<div class="lw-kel-app-show lw-panel-stack">
    @include('kelurahan.applications.partials.application-summary', [
        'application' => $application,
        'documents' => $documents,
    ])

    <article class="lw-panel-card lw-panel-card--full">
        @include('kelurahan.applications.partials.application-detail', [
            'application' => $application,
            'documents' => $documents,
            'requiredCount' => $requiredCount,
        ])
    </article>

    @include('kelurahan.applications.partials.letter-detail', ['application' => $application])
</div>
</div>
@endsection
