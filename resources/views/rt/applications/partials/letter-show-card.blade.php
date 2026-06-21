@php
    $letter = $application->generatedLetter;
    $hasPdf = $letter && \Illuminate\Support\Facades\Storage::disk('local')->exists($letter->file_path);
    $letterNumber = $application->issuedLetterNumber();
    $composeUrl = route('rt.applications.letter.compose', $application);
@endphp

<section class="lw-panel-card lw-panel-card--full lw-mb-4 lw-letter-show-card" aria-label="Surat pengantar RT">
    <h2 class="lw-panel-card-title">Surat pengantar RT</h2>

    @if($hasPdf)
        <p class="lw-letter-show-status" role="status">
            Surat PDF diterbitkan
            @if($letterNumber)
                · Nomor <strong>{{ $letterNumber }}</strong>
            @endif
            @if($letter->issued_at)
                · {{ $letter->issued_at->locale('id')->translatedFormat('d M Y H:i') }}
            @elseif($application->completed_at)
                · {{ $application->completed_at->locale('id')->translatedFormat('d M Y H:i') }}
            @endif
        </p>

        <p class="lw-panel-card-note lw-mb-2">
            Warga dapat mengambil surat fisik di sekretariat RT. Kirim PDF via WhatsApp dari halaman susun surat jika diperlukan.
        </p>

        <div class="lw-letter-show-links lw-mb-2">
            <a href="{{ route('rt.applications.letter.print', $application) }}" target="_blank" rel="noopener" class="lw-panel-link">
                Lihat / cetak PDF
            </a>
        </div>

        <a href="{{ $composeUrl }}" class="lw-panel-link lw-letter-show-secondary-link">
            Susun ulang / kirim WhatsApp
        </a>
    @elseif($application->status === \App\Enums\ApplicationStatus::VerifikasiRt)
        <p class="lw-letter-show-status" role="status">Permohonan diterima — surat belum diterbitkan.</p>
        <p class="lw-panel-card-note lw-mb-2">Lengkapi data surat, gambar tanda tangan di kanvas, lalu terbitkan PDF.</p>

        <a href="{{ $composeUrl }}" class="lw-panel-btn lw-letter-show-primary-btn lw-mt-2">
            Susun &amp; terbitkan surat
        </a>
    @else
        <p class="lw-letter-show-status" role="status">Surat belum diterbitkan.</p>

        <a href="{{ $composeUrl }}" class="lw-panel-btn lw-letter-show-primary-btn lw-mt-2">
            Susun &amp; terbitkan surat
        </a>
    @endif
</section>
