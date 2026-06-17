@php
    /** @var \App\Support\SuratFaceReadiness $readiness */
    $editUrl = $editUrl ?? null;
    $syncUrl = $syncUrl ?? null;
@endphp
@if(! $readiness->canVerify)
    <div class="lw-rt-surat-readiness-callout {{ $readiness->adminBadgeClass() }}" role="status">
        <p class="lw-rt-surat-readiness-callout__title">Verifikasi surat online: {{ $readiness->adminLabel }}</p>
        <p class="lw-rt-surat-readiness-callout__text">{{ $readiness->message }}</p>
        @if($readiness->detail)
            <p class="lw-rt-surat-readiness-callout__detail"><strong>Detail:</strong> {{ $readiness->detail }}</p>
        @endif
        @if($editUrl || $syncUrl)
            <div class="lw-rt-surat-readiness-callout__actions">
                @if($syncUrl)
                    <form method="POST" action="{{ $syncUrl }}" class="lw-rt-surat-readiness-callout__sync-form">
                        @csrf
                        <button type="submit" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">
                            Sinkronkan ulang wajah
                        </button>
                    </form>
                @endif
                @if($editUrl)
                    <a href="{{ $editUrl }}" class="lw-panel-link">Unggah KTP/KIA anggota</a>
                @endif
            </div>
        @endif
    </div>
@endif
