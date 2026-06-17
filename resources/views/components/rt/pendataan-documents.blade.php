@props([
    'household',
    'head' => null,
    'variant' => 'full',
    'collapsible' => null,
])

@php
    use App\Enums\DomicileStatus;
    use App\Models\PendataanDocument;

    $isCompact = $variant === 'compact';
    $documents = $household->pendataanDocuments
        ->sortBy(fn (PendataanDocument $doc) => match ($doc->document_type) {
            'kk' => 0,
            'ktp_kepala' => 1,
            default => 2,
        })
        ->values();
    $streamHead = $head ?? $household->headResident;

    $needsVerification = $streamHead && in_array($streamHead->domicile_status, [
        DomicileStatus::MenungguVerifikasi,
        DomicileStatus::PerluLengkap,
    ], true);
    $collapsible = $collapsible ?? ($isCompact && $documents->count() > 2);
    $collapsibleOpen = $documents->count() <= 2;
    $headingTag = $isCompact ? 'h3' : 'h2';
@endphp

<section {{ $attributes->merge(['class' => 'lw-rt-doc-section lw-rt-doc-section--'.$variant]) }}>
    @if($documents->isNotEmpty() && $streamHead === null)
        <p class="lw-rt-doc-empty-alert">Kepala KK tidak ditemukan — tidak dapat membuka berkas.</p>
    @endif

    @if($isCompact && $needsVerification)
        <div class="lw-panel-alert lw-panel-alert--warn lw-rt-doc-verify-banner">
            <p class="lw-rt-doc-verify-banner-text">
                <strong>{{ $streamHead->domicile_status?->label() }}</strong>
                — berkas perlu ditinjau di halaman verifikasi.
            </p>
            @if($streamHead->domicile_status === DomicileStatus::PerluLengkap && filled($streamHead->verification_notes))
                <p class="lw-rt-doc-verify-banner-notes">{{ Str::limit($streamHead->verification_notes, 120) }}</p>
            @endif
            <a href="{{ route('rt.pendataan.show', $streamHead) }}" class="lw-panel-btn lw-panel-btn--sm">Buka verifikasi pendataan</a>
        </div>
    @endif

    @if($documents->isEmpty())
        @if($isCompact)
            <{{ $headingTag }} class="lw-panel-section-title">Lampiran berkas (0)</{{ $headingTag }}>
            <p class="lw-rt-doc-empty">
                @if($household->isRtDirectEntry())
                    Tidak ada lampiran — entri langsung dari panel RT biasanya tanpa unggah berkas.
                @else
                    Tidak ada lampiran — berkas belum diunggah warga atau pengajuan sebelum fitur unggah.
                @endif
            </p>
        @else
            <{{ $headingTag }} class="lw-panel-section-title">Lampiran berkas (0)</{{ $headingTag }}>
            <p class="lw-panel-alert lw-panel-alert--warn lw-rt-doc-empty-alert">Tidak ada lampiran — pendaftaran sebelum fitur unggah berkas, atau berkas belum diunggah warga.</p>
        @endif
    @elseif($collapsible)
        <details class="lw-rt-doc-collapsible" @if($collapsibleOpen) open @endif>
            <summary class="lw-rt-doc-collapsible-summary">Lampiran berkas · {{ $documents->count() }} berkas</summary>
            <div class="lw-rt-doc-grid lw-rt-doc-grid--compact">
                @foreach($documents as $doc)
                    @include('components.rt.partials.pendataan-document-card', [
                        'doc' => $doc,
                        'head' => $streamHead,
                        'variant' => $variant,
                    ])
                @endforeach
            </div>
        </details>
    @else
        <{{ $headingTag }} class="lw-panel-section-title">Lampiran berkas ({{ $documents->count() }})</{{ $headingTag }}>
        <div class="lw-rt-doc-grid lw-rt-doc-grid--{{ $variant }}">
            @foreach($documents as $doc)
                @include('components.rt.partials.pendataan-document-card', [
                    'doc' => $doc,
                    'head' => $streamHead,
                    'variant' => $variant,
                ])
            @endforeach
        </div>
    @endif

    @once
        <div class="lw-rt-doc-modal" data-rt-doc-modal hidden>
            <div class="lw-rt-doc-modal-backdrop" data-rt-doc-modal-close></div>
            <div class="lw-rt-doc-modal-dialog" role="dialog" aria-modal="true" aria-label="Detail foto lampiran">
                <button type="button" class="lw-rt-doc-modal-close" data-rt-doc-modal-close aria-label="Tutup detail foto">×</button>
                <img src="" alt="" class="lw-rt-doc-modal-img" data-rt-doc-modal-image>
                <p class="lw-rt-doc-modal-title" data-rt-doc-modal-title></p>
                <p class="lw-rt-doc-modal-meta" data-rt-doc-modal-date></p>
            </div>
        </div>
    @endonce
</section>
