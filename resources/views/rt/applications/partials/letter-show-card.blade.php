@php
    $letter = $application->generatedLetter;
    $hasPdf = $letter && \Illuminate\Support\Facades\Storage::disk('local')->exists($letter->file_path);
    $letterNumber = $application->issuedLetterNumber();
    $composeUrl = route('rt.applications.letter.compose', $application);
    $issuedAt = $letter?->issued_at ?? $application->completed_at;
@endphp

<section class="lw-panel-card lw-panel-card--full lw-mb-4 lw-letter-show-card" aria-label="Surat pengantar RT">
    <header class="lw-letter-show-head">
        <div class="lw-letter-show-head-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
        </div>
        <div class="lw-letter-show-head-text">
            <h2 class="lw-panel-card-title lw-letter-show-title">Surat pengantar RT</h2>
            @if($hasPdf)
                <p class="lw-letter-show-status" role="status">Surat PDF diterbitkan</p>
            @elseif($application->status === \App\Enums\ApplicationStatus::VerifikasiRt)
                <p class="lw-letter-show-status lw-letter-show-status--pending" role="status">Permohonan diterima — surat belum diterbitkan</p>
            @else
                <p class="lw-letter-show-status lw-letter-show-status--pending" role="status">Surat belum diterbitkan</p>
            @endif
        </div>
    </header>

    @if($hasPdf)
        <dl class="lw-letter-show-meta">
            @if($letter->publishCount() > 0)
                <div>
                    <dt>Penerbitan</dt>
                    <dd>{{ $letter->publishStatusLabel() }}</dd>
                </div>
            @endif
            @if($letterNumber)
                <div>
                    <dt>Nomor surat</dt>
                    <dd><strong>{{ $letterNumber }}</strong></dd>
                </div>
            @endif
            @if($issuedAt)
                <div>
                    <dt>Terakhir diterbitkan</dt>
                    <dd>{{ $issuedAt->locale('id')->translatedFormat('d M Y, H:i') }}</dd>
                </div>
            @endif
        </dl>

        <p class="lw-panel-card-note lw-letter-show-note">
            Warga dapat mengambil salinan fisik di sekretariat RT. Kirim PDF via WhatsApp dari halaman susun surat bila diperlukan.
        </p>

        <div class="lw-letter-show-actions">
            <a href="{{ route('rt.applications.letter.print', $application) }}" target="_blank" rel="noopener" class="lw-letter-show-action lw-letter-show-action--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/></svg>
                <span>Lihat / unduh PDF</span>
            </a>
            <a href="{{ $composeUrl }}" class="lw-letter-show-action lw-letter-show-action--secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <span>Susun ulang / WhatsApp</span>
            </a>
        </div>
    @elseif($application->status === \App\Enums\ApplicationStatus::VerifikasiRt)
        <p class="lw-panel-card-note lw-letter-show-note">Lengkapi data surat, gambar tanda tangan di kanvas, lalu terbitkan PDF.</p>

        <a href="{{ $composeUrl }}" class="lw-letter-show-action lw-letter-show-action--primary lw-letter-show-action--solo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            <span>Susun &amp; terbitkan surat</span>
        </a>
    @else
        <a href="{{ $composeUrl }}" class="lw-letter-show-action lw-letter-show-action--primary lw-letter-show-action--solo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            <span>Susun &amp; terbitkan surat</span>
        </a>
    @endif
</section>
