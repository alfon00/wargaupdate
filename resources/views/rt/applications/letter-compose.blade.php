@extends('layouts.panel')

@section('title', 'Susun surat — '.$application->application_number)


@section('content')
<div class="lw-rt-page">
@php
    $kopEyebrow = $rtProfile
        ? 'Kop: '.$rtProfile->displayName()
            .($rtProfile->rw_number ? ' · RW '.$rtProfile->rw_number : '')
            .' · '.($rtProfile->kelurahan ?: config('kelurahan.nama'))
        : config('kelurahan.nama');

    $composeConfig = [
        'csrfToken' => csrf_token(),
        'hasPublishedPdf' => $hasPublishedPdf,
    ];
@endphp

@include('rt.partials.page-head', [
    'title' => 'Susun & terbitkan surat',
    'eyebrow' => $kopEyebrow,
    'lead' => $application->serviceType->name.' · '.$application->application_number,
    'actions' => '<a href="'.e(route('rt.applications.show', $application)).'" class="lw-panel-link">← Kembali ke permohonan</a>',
])

<div id="letter-compose-root" class="lw-letter-compose-page"
    data-compose-config="@json($composeConfig)">

<div class="lw-letter-compose-grid">
    <div class="lw-letter-compose-editor">
        <article class="lw-panel-card lw-panel-card--full lw-letter-compose-card">
            <div class="lw-letter-compose-form-section">
                <h2 class="lw-panel-card-title">Susun surat</h2>
                @if($kopProfileIncomplete ?? false)
                <div class="lw-alert lw-alert--warn lw-mb-4" role="alert">
                    <p class="lw-panel-card-note"><strong>Profil RT untuk kop surat belum lengkap.</strong>
                        Minta admin melengkapi di Profil RT atau jalankan sinkronisasi dari RT001.
                        Kosong: {{ implode(', ', $missingKopLabels ?? []) }}.</p>
                </div>
                @endif
                @if($hasPublishedPdf)
                <p class="lw-panel-card-note lw-letter-compose-lead">
                    PDF sudah terbit
                    @if($application->generatedLetter?->publishCount() > 0)
                        ({{ $application->generatedLetter->publishStatusLabel() }})
                    @endif
                    — terbitkan ulang jika data berubah. QR code mengarah ke verifikasi keaslian di portal. Kirim PDF ke warga via tombol WhatsApp di bawah.
                </p>
                @else
                <p class="lw-panel-card-note lw-letter-compose-lead">
                    Setelah diterbitkan, surat PDF memuat QR code yang mengarah ke halaman verifikasi keaslian di portal resmi. Nomor surat hanya tercantum pada dokumen surat.
                </p>
                @endif

                <form method="POST" action="{{ route('rt.applications.letter.publish', $application) }}"
                    class="lw-panel-form lw-panel-form--in-card" id="letter-compose-form">
                    @csrf

                    <fieldset class="lw-form-fieldset lw-letter-fieldset">
                        <legend class="lw-form-legend">Nomor surat</legend>
                        <p class="lw-panel-card-note lw-mb-4">
                            Isi nomor surat yang akan tercetak pada kop. Format usulan sudah terisi otomatis — sesuaikan jika RT memakai penomoran lain.
                        </p>
                        <div class="lw-panel-field">
                            <label for="letter-field-nomor-surat" class="lw-panel-field-label">
                                Nomor surat <span class="lw-form-label-required">*</span>
                            </label>
                            <input type="text" id="letter-field-nomor-surat" name="fields[nomor_surat]"
                                value="{{ old('fields.nomor_surat', $fieldValues['nomor_surat'] ?? '') }}"
                                maxlength="120"
                                class="lw-letter-compose-field-input lw-panel-field-input"
                                data-letter-field="1" data-required="1" required>
                            @error('fields.nomor_surat')
                                <p class="lw-form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </fieldset>

                    <fieldset class="lw-form-fieldset lw-letter-fieldset">
                        <legend class="lw-form-legend">Data pemohon</legend>
                        @include('rt.applications.partials.letter-compose-summary', compact('application', 'fieldSchema', 'fieldValues', 'profileIncomplete', 'missingProfileLabels'))
                    </fieldset>

                    <fieldset class="lw-form-fieldset lw-letter-fieldset">
                        <legend class="lw-form-legend">Maksud dan Keperluan</legend>
                        <p class="lw-panel-card-note lw-mb-4">Isi sesuai keperluan permohonan warga. Teks ini akan tercetak pada surat.</p>
                        <div class="lw-panel-field">
                            <label for="letter-field-keperluan" class="lw-panel-field-label">
                                Maksud dan Keperluan <span class="lw-form-label-required">*</span>
                            </label>
                            <textarea id="letter-field-keperluan" name="fields[keperluan]" rows="4"
                                class="lw-letter-compose-field-input lw-panel-field-input"
                                data-letter-field="1" data-required="1" required>{{ old('fields.keperluan', $fieldValues['keperluan'] ?? '') }}</textarea>
                            @error('fields.keperluan')
                                <p class="lw-form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </fieldset>

                    @include('rt.applications.partials.letter-compose-service-fields', compact('application', 'fieldValues'))

                    @error('letter')
                        <p class="lw-form-error">{{ $message }}</p>
                    @enderror
                </form>

                <form method="POST" action="{{ route('rt.applications.letter.draft', $application) }}"
                    id="letter-draft-form" class="lw-letter-draft-form">
                    @csrf
                    @foreach($fieldSchema as $field)
                        <input type="hidden" name="fields[{{ $field['key'] }}]"
                            value="{{ old('fields.'.$field['key'], $fieldValues[$field['key']] ?? '') }}"
                            class="draft-field-sync" data-field-key="{{ $field['key'] }}">
                    @endforeach
                    <input type="hidden" name="fields[nomor_surat]"
                        value="{{ old('fields.nomor_surat', $fieldValues['nomor_surat'] ?? '') }}"
                        class="draft-field-sync" data-field-key="nomor_surat">
                </form>

                @include('rt.applications.partials.letter-compose-actions', compact(
                    'application',
                    'hasPublishedPdf',
                    'publishedLetter',
                    'fieldSchema',
                    'fieldValues',
                    'canSendLetterWhatsApp',
                    'letterWhatsAppBlockReason',
                    'lastLetterWhatsAppLog',
                ))
            </div>
        </article>
    </div>
</div>

</div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/letter-compose.js'])
@endpush
