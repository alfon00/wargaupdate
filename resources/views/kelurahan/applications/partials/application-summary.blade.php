@php
    $documentCount = $documents->count();
@endphp

<section class="lw-kel-app-summary" aria-label="Ringkasan permohonan">
    <div class="lw-kel-app-summary__item">
        <span class="lw-kel-app-summary__label">Status</span>
        <span class="lw-kel-app-summary__value">
            <span class="lw-badge {{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span>
        </span>
    </div>
    <div class="lw-kel-app-summary__item">
        <span class="lw-kel-app-summary__label">Diajukan</span>
        <span class="lw-kel-app-summary__value">
            {{ $application->submitted_at?->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}
        </span>
    </div>
    <div class="lw-kel-app-summary__item">
        <span class="lw-kel-app-summary__label">Lampiran</span>
        <span class="lw-kel-app-summary__value">{{ $documentCount }} berkas</span>
    </div>
    <div class="lw-kel-app-summary__item">
        <span class="lw-kel-app-summary__label">Surat RT</span>
        <span class="lw-kel-app-summary__value">
            @if($application->hasManualLetterIssued())
                <span class="lw-badge lw-badge--green">Sudah diterbitkan</span>
                <span class="lw-kel-app-summary__letter-no">{{ $application->issuedLetterNumber() }}</span>
            @elseif($application->generatedLetter)
                <span class="lw-badge lw-badge--green">PDF</span>
                @if($application->issuedLetterNumber())
                    <span class="lw-kel-app-summary__letter-no">{{ $application->issuedLetterNumber() }}</span>
                @endif
            @else
                <span class="lw-badge lw-badge--muted">Belum</span>
            @endif
        </span>
    </div>
</section>
