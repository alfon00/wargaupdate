@php
    $canIssue = $application->status->canIssueManualLetter();
    $letterNumber = $application->issuedLetterNumber();
    $manualLetter = $application->manualLetter();
@endphp

<section class="lw-panel-card lw-panel-card--full lw-mb-4 lw-letter-issue-card" aria-label="Terbitkan surat pengantar">
    <h2 class="lw-panel-card-title">Terbitkan surat pengantar</h2>

    @if($canIssue)
        <p class="lw-panel-card-note lw-letter-issue-lead">
            Surat dicetak dan ditandatangani manual di sekretariat RT. Portal hanya mengirim notifikasi WhatsApp agar warga mengambil surat di RT.
        </p>

        <form method="POST" action="{{ route('rt.applications.letter.issue', $application) }}" class="lw-letter-issue-form">
            @csrf
            <div class="lw-panel-field">
                <label for="letter_number" class="lw-panel-field-label">
                    Nomor surat <span class="lw-form-label-required">*</span>
                </label>
                <input id="letter_number" name="letter_number" type="text" required maxlength="100"
                    class="lw-panel-input" placeholder="Contoh: RT008/SK/06/2026/008"
                    value="{{ old('letter_number') }}">
                @error('letter_number')
                    <p class="lw-form-error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="lw-panel-btn lw-letter-issue-primary-btn"
                onclick="return confirm('Catat nomor surat dan kirim notifikasi teks WhatsApp ke warga?');">
                Catat nomor surat &amp; kirim notifikasi
            </button>
        </form>
    @else
        <p class="lw-letter-issue-status" role="status">
            Surat diterbitkan
            @if($letterNumber)
                · Nomor <strong>{{ $letterNumber }}</strong>
            @endif
            @if(! empty($manualLetter['issued_at']))
                · {{ \Illuminate\Support\Carbon::parse($manualLetter['issued_at'])->locale('id')->translatedFormat('d M Y H:i') }}
            @elseif($application->completed_at)
                · {{ $application->completed_at->locale('id')->translatedFormat('d M Y H:i') }}
            @endif
        </p>
        <p class="lw-panel-card-note lw-mb-0">
            Warga telah menerima notifikasi WhatsApp untuk mengambil surat fisik di sekretariat RT.
        </p>
        @if($application->hasManualLetterIssued())
        <form method="POST" action="{{ route('rt.applications.letter.resend', $application) }}" class="lw-mt-2">
            @csrf
            <button type="submit" class="lw-panel-btn lw-panel-btn--secondary lw-letter-issue-resend-btn">
                Kirim ulang WhatsApp
            </button>
        </form>
        @endif
    @endif
</section>
