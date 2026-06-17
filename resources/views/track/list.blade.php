@extends('layouts.app')

@section('title', 'Hasil pencarian')

@section('content')
<section class="lw-services-section lw-track-page lw-band--alt">
    <div class="lw-container lw-container--narrow">
        <a href="{{ route('track.form') }}" class="lw-track-back">← Lacak lagi</a>

        <article class="lw-track-card">
            <header class="lw-track-status-header">
                <p class="lw-section-tag lw-mb-0">Hasil pencarian</p>
                <h1 class="lw-section-title lw-mt-2">Permohonan ditemukan</h1>
                <p class="lw-auth-hub-lead lw-mt-2 lw-mb-0">{{ $searchLabel }}</p>
            </header>

            <ul class="lw-track-result-list">
                @foreach($applications as $app)
                    <li class="lw-track-result-card">
                        <p class="lw-track-result-card__number">{{ $app->application_number }}</p>
                        <p class="lw-track-result-card__meta">
                            {{ $app->serviceType->name }}
                            <span class="lw-badge {{ $app->status->badgeClass() }}">{{ $app->status->label() }}</span>
                        </p>
                        @if($app->submitted_at)
                            <p class="lw-track-result-card__date">{{ $app->submitted_at->locale('id')->translatedFormat('d F Y') }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>

            <p class="lw-form-hint lw-mt-4">Untuk detail lengkap, gunakan pencarian berdasarkan <strong>nomor permohonan</strong>.</p>
        </article>
    </div>
</section>
@endsection
