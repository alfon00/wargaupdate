@php
    $manualLetter = $application->manualLetter();
@endphp

<section class="lw-kel-letter-card @if($application->hasManualLetterIssued()) lw-kel-letter-card--issued @elseif($application->generatedLetter) lw-kel-letter-card--pdf @else lw-kel-letter-card--pending @endif" aria-label="Surat pengantar RT">
    <header class="lw-kel-letter-card__head">
        <h2 class="lw-kel-letter-card__title">Surat pengantar RT</h2>
    </header>

    @if($application->hasManualLetterIssued())
        <p class="lw-kel-letter-card__number">{{ $application->issuedLetterNumber() }}</p>
        <p class="lw-kel-letter-card__note">Surat fisik di sekretariat {{ $application->applicantRtLabel() }} — tidak ada PDF di portal.</p>
        <dl class="lw-kel-letter-card__meta">
            <div class="lw-kel-letter-card__meta-row">
                <dt>Terbit</dt>
                <dd>
                    @if(! empty($manualLetter['issued_at']))
                        {{ \Illuminate\Support\Carbon::parse($manualLetter['issued_at'])->locale('id')->translatedFormat('d M Y H:i') }}
                    @elseif($application->completed_at)
                        {{ $application->completed_at->locale('id')->translatedFormat('d M Y H:i') }}
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div class="lw-kel-letter-card__meta-row">
                <dt>RT</dt>
                <dd>{{ $application->applicantRtLabel() }}</dd>
            </div>
            <div class="lw-kel-letter-card__meta-row">
                <dt>Status</dt>
                <dd><span class="lw-badge {{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span></dd>
            </div>
        </dl>
    @elseif($application->generatedLetter)
        @php $letter = $application->generatedLetter; @endphp
        <p class="lw-kel-letter-card__note">Surat PDF diterbitkan RT melalui portal — salinan digital untuk arsip kelurahan.</p>
        <dl class="lw-kel-letter-card__meta lw-mb-3">
            <div class="lw-kel-letter-card__meta-row">
                <dt>Nomor surat</dt>
                <dd class="lw-kel-letter-card__number lw-kel-letter-card__number--inline">{{ $letter->letter_number ?? '—' }}</dd>
            </div>
            <div class="lw-kel-letter-card__meta-row">
                <dt>Terbit</dt>
                <dd>{{ $letter->issued_at?->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}</dd>
            </div>
            @if($letter->signer)
                <div class="lw-kel-letter-card__meta-row">
                    <dt>Penandatangan</dt>
                    <dd>{{ $letter->signer->name }}</dd>
                </div>
            @endif
            @if($letter->signed_at)
                <div class="lw-kel-letter-card__meta-row">
                    <dt>Ditandatangani RT</dt>
                    <dd>{{ $letter->signed_at->locale('id')->translatedFormat('d M Y H:i') }}</dd>
                </div>
            @endif
        </dl>

        @if($letter->letter_fields && count($letter->letter_fields) > 0)
            <div class="lw-panel-snapshot lw-surface-muted lw-mb-3">
                <p class="lw-panel-snapshot-title">Snapshot data surat</p>
                <dl class="lw-panel-dl">
                    @foreach($letter->letter_fields as $key => $value)
                        @if($value !== '' && $value !== null)
                            <div class="lw-panel-dl-row">
                                <dt>{{ str_replace('_', ' ', ucfirst($key)) }}</dt>
                                <dd class="lw-panel-pre">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>
        @endif
        <div class="lw-panel-actions-row">
            <a href="{{ route('kelurahan.applications.letter.print', $application) }}" target="_blank" rel="noopener" class="lw-panel-btn">Lihat / cetak PDF</a>
            <a href="{{ route('kelurahan.applications.letter.download', $application) }}" class="lw-panel-btn lw-panel-btn--secondary">Unduh PDF</a>
        </div>
    @else
        <p class="lw-kel-letter-card__pending">Surat pengantar belum diterbitkan RT.</p>
        <p class="lw-kel-letter-card__pending-meta">Status permohonan: <span class="lw-badge {{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span></p>
    @endif
</section>
