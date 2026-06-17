@php
    $isCompact = $variant === 'compact';
    $cardKind = match ($doc->document_type) {
        'kk' => 'kk',
        'ktp_kepala' => 'ktp',
        default => 'lampiran',
    };
    $badgeLabel = match ($doc->document_type) {
        'kk' => 'KK',
        'ktp_kepala' => 'KTP',
        default => 'Lampiran',
    };
    $fileExists = $doc->fileExists();
    $canStream = $head !== null && $fileExists;
    $fileName = $doc->original_name ?: 'berkas';
@endphp

<article class="lw-rt-doc-card lw-rt-doc-card--{{ $cardKind }}">
    <header class="lw-rt-doc-card-head">
        <span class="lw-rt-doc-card-badge">{{ $badgeLabel }}</span>
        @if($doc->created_at)
            <time class="lw-rt-doc-card-date" datetime="{{ $doc->created_at->toDateString() }}">
                {{ $doc->created_at->timezone('Asia/Jayapura')->format('d/m/Y') }}
            </time>
        @endif
    </header>

    <div class="lw-rt-doc-card-media">
        @if($canStream && $doc->isImage())
            <button type="button"
                class="lw-rt-doc-card-preview lw-rt-doc-modal-trigger"
                data-doc-image-url="{{ route('rt.pendataan.document.view', [$head, $doc]) }}"
                data-doc-title="{{ $doc->typeLabel() }}"
                data-doc-date="{{ $doc->created_at?->timezone('Asia/Jayapura')->format('d/m/Y') }}"
                aria-label="Lihat detail foto {{ $doc->typeLabel() }}">
                <img src="{{ route('rt.pendataan.document.view', [$head, $doc]) }}"
                    alt="{{ $doc->typeLabel() }}"
                    class="lw-rt-doc-card-thumb lw-rt-doc-card-thumb--{{ $isCompact ? 'compact' : 'full' }}"
                    @if($isCompact) loading="lazy" @endif>
            </button>
        @elseif($doc->isPdf())
            <div class="lw-rt-doc-card-pdf">
                <span class="lw-rt-doc-card-pdf-badge">PDF</span>
                @if(! $fileExists)
                    <p class="lw-rt-doc-card-pdf-hint">Berkas tidak ditemukan di server.</p>
                @else
                    <p class="lw-rt-doc-card-pdf-hint">Buka dengan tombol Lihat.</p>
                @endif
            </div>
        @elseif($doc->isImage() && ! $fileExists)
            <div class="lw-rt-doc-card-pdf">
                <p class="lw-rt-doc-card-pdf-hint">Berkas gambar tidak ditemukan di server.</p>
            </div>
        @endif
    </div>

    <p class="lw-rt-doc-card-name" title="{{ $fileName }}">{{ Str::limit($fileName, 32) }}</p>

    @if($canStream)
        <footer class="lw-rt-doc-card-actions">
            <a href="{{ route('rt.pendataan.document.view', [$head, $doc]) }}" target="_blank" rel="noopener" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Lihat</a>
            @if($doc->isPdf())
                <a href="{{ route('rt.pendataan.document.print', [$head, $doc]) }}" target="_blank" rel="noopener" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Cetak</a>
            @endif
            <a href="{{ route('rt.pendataan.document.download', [$head, $doc]) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Unduh</a>
        </footer>
    @endif
</article>
