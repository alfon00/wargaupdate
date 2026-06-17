@php
    $requiredCount = (int) ($requiredCount ?? 0);
@endphp

<section class="lw-rt-application-detail lw-kel-app-detail">
    <header class="lw-kel-app-detail__head">
        <h2 class="lw-rt-application-detail__title">Detail permohonan</h2>
        <p class="lw-rt-application-detail__subtitle">Data pemohon dan berkas — mode baca saja</p>
    </header>

    <div class="lw-rt-application-detail__body">
        <section class="lw-rt-application-detail__card">
            <h3 class="lw-rt-application-detail__card-title">Data pemohon</h3>
            @if($application->hasDetachedApplicant())
                <p class="lw-rt-application-detail__detached"><span class="lw-badge lw-badge--muted">Data warga dihapus</span></p>
            @endif
            @include('partials.resident-profile-fields-grid', ['application' => $application])
            <div class="lw-rt-application-detail__fields lw-mt-3">
                <div class="lw-rt-application-detail__field">
                    <span class="lw-rt-application-detail__field-label">HP/WA</span>
                    <span class="lw-rt-application-detail__field-value">{{ $application->applicantPhone() ?: '—' }}</span>
                </div>
                <div class="lw-rt-application-detail__field">
                    <span class="lw-rt-application-detail__field-label">RT</span>
                    <span class="lw-rt-application-detail__field-value">{{ $application->applicantRtLabel() }}</span>
                </div>
                <div class="lw-rt-application-detail__field">
                    <span class="lw-rt-application-detail__field-label">Jenis kelamin</span>
                    <span class="lw-rt-application-detail__field-value">{{ $application->applicantGender() ?: '—' }}</span>
                </div>
            </div>
        </section>

        <section class="lw-rt-application-detail__card">
            <h3 class="lw-rt-application-detail__card-title">Keperluan</h3>
            <div class="lw-rt-application-detail__purpose">{{ $application->purpose }}</div>
        </section>

        @if($application->rejection_reason)
        <section class="lw-rt-application-detail__card">
            <h3 class="lw-rt-application-detail__card-title">Catatan RT</h3>
            <div class="lw-rt-application-detail__purpose">{{ $application->rejection_reason }}</div>
        </section>
        @endif

        <section class="lw-rt-application-detail__card">
            <h3 class="lw-rt-application-detail__card-title">Lampiran berkas</h3>

            @if($requiredCount > 0 && $documents->count() < $requiredCount)
                <div class="lw-alert lw-alert--warn lw-rt-application-detail__warn">
                    Warga mengunggah {{ $documents->count() }} berkas; persyaratan layanan mencantumkan {{ $requiredCount }} item.
                </div>
            @endif

            @if($documents->isEmpty())
                <div class="lw-rt-application-detail__empty">
                    <p class="lw-rt-application-detail__empty-title">Belum ada lampiran</p>
                    <p class="lw-rt-application-detail__empty-note">Warga belum mengunggah berkas pendukung untuk permohonan ini.</p>
                </div>
            @else
                <div class="lw-rt-application-detail__doc-grid">
                    @foreach($documents as $doc)
                        @php $doc->setRelation('application', $application); @endphp
                        <article class="lw-rt-application-detail__doc-card">
                            <div class="lw-rt-application-detail__doc-preview">
                                @if($doc->isImage())
                                    <a href="{{ route('kelurahan.applications.document.view', [$application, $doc]) }}" target="_blank" rel="noopener" class="lw-rt-application-detail__doc-preview-link">
                                        <img src="{{ route('kelurahan.applications.document.view', [$application, $doc]) }}"
                                            alt="{{ $doc->typeLabel() }}"
                                            class="lw-rt-application-detail__doc-preview-img">
                                    </a>
                                @else
                                    <div class="lw-rt-application-detail__doc-placeholder" aria-hidden="true">
                                        <span class="lw-rt-application-detail__doc-placeholder-label">{{ $doc->isPdf() ? 'PDF' : 'Berkas' }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="lw-rt-application-detail__doc-body">
                                <div class="lw-rt-application-detail__doc-head">
                                    <span class="lw-badge lw-badge--muted lw-rt-application-detail__doc-type">{{ $doc->isImage() ? 'Gambar' : ($doc->isPdf() ? 'PDF' : 'Berkas') }}</span>
                                    <h4 class="lw-rt-application-detail__doc-title">{{ $doc->typeLabel() }}</h4>
                                </div>
                                @if($doc->original_name)
                                    <p class="lw-rt-application-detail__doc-meta">{{ $doc->original_name }}</p>
                                @elseif($doc->isPdf())
                                    <p class="lw-rt-application-detail__doc-meta">Berkas PDF — gunakan Lihat untuk membuka di tab baru.</p>
                                @endif
                                <div class="lw-rt-application-detail__doc-actions">
                                    <a href="{{ route('kelurahan.applications.document.view', [$application, $doc]) }}" target="_blank" rel="noopener" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Lihat</a>
                                    @if($doc->isPdf())
                                        <a href="{{ route('kelurahan.applications.document.print', [$application, $doc]) }}" target="_blank" rel="noopener" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Cetak</a>
                                    @endif
                                    <a href="{{ route('kelurahan.applications.document', [$application, $doc]) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Unduh</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</section>
