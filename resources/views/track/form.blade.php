@extends('layouts.app')

@section('title', 'Lacak Permohonan')

@section('content')
<div class="lw-track-page lw-track-split">
    @include('public.partials.track.hero')

    <div class="lw-container lw-container--wide lw-track-board lw-track-board--centered">
        <div class="lw-track-hero-grid lw-track-hero-grid--solo" aria-labelledby="track-hero-heading">
            <div class="lw-track-forms">
                <article class="lw-form-card lw-track-form-card">
                    <header class="lw-track-split__head">
                        <p class="lw-track-split__lead lw-mb-0">{{ $formLead }}</p>
                    </header>

                    @if(isset($error))
                        <div class="lw-alert lw-alert--error lw-track-split__alert" role="alert">{{ $error }}</div>
                    @endif

                    <form method="POST" action="{{ route('track.show') }}" class="lw-track-split__form" aria-labelledby="track-hero-heading">
                        @csrf
                        <input type="hidden" name="mode" value="number">
                        <div class="lw-form-field">
                            <label for="application_number" class="lw-form-label">Nomor permohonan <span class="lw-form-label-required">*</span></label>
                            <input id="application_number" type="text" name="application_number" required autocomplete="off"
                                value="{{ old('application_number', $application_number ?? '') }}"
                                class="lw-form-input lw-track-split__mono">
                        </div>
                        <button type="submit" class="lw-track-split__submit">Cari</button>
                    </form>

                    <p class="lw-track-split__foot">
                        Ada kendala atau nomor tidak ditemukan?
                        <a href="{{ route('contact.create') }}" class="lw-inline-link">Kirim laporan</a>
                    </p>
                </article>
            </div>
        </div>

        @if(! empty($trackFaq))
            <div class="lw-track-bottom-grid lw-track-bottom-grid--solo">
                <article class="lw-track-info-card lw-track-info-card--faq" aria-labelledby="track-faq-heading">
                    <x-faq-accordion
                        variant="track"
                        heading-id="track-faq-heading"
                        heading="Pertanyaan Seputar Pelacakan"
                        :items="$trackFaq"
                        :open-first="false"
                    />
                </article>
            </div>
        @endif
    </div>
</div>
@endsection
