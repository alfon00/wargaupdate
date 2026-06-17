@php
    $detailed = $detailed ?? false;
    $requiredCount = (int) ($requiredCount ?? 0);
@endphp

@if($detailed)
<section class="lw-rt-application-detail">
    <header class="lw-rt-application-detail__meta">
        <div class="lw-rt-application-detail__meta-primary">
            <h2 class="lw-rt-application-detail__title">Permohonan &amp; lampiran</h2>
            <p class="lw-rt-application-detail__subtitle">Data pengajuan warga</p>
        </div>
        <div class="lw-rt-application-detail__meta-chips">
            <span class="lw-rt-application-detail__meta-item">
                <span class="lw-rt-application-detail__meta-label">Layanan</span>
                <span class="lw-rt-application-detail__meta-value">{{ $application->serviceType->name }}</span>
            </span>
            <span class="lw-rt-application-detail__meta-item">
                <span class="lw-rt-application-detail__meta-label">Status</span>
                <span class="lw-badge {{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span>
            </span>
            @if($application->submitted_at)
            <span class="lw-rt-application-detail__meta-item">
                <span class="lw-rt-application-detail__meta-label">Diajukan</span>
                <span class="lw-rt-application-detail__meta-value">{{ $application->submitted_at->locale('id')->translatedFormat('d M Y H:i') }}</span>
            </span>
            @endif
            <span class="lw-rt-application-detail__meta-item">
                <span class="lw-rt-application-detail__meta-label">Lampiran</span>
                <span class="lw-rt-application-detail__meta-value">{{ $documents->count() }} berkas</span>
            </span>
        </div>
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

        <section class="lw-rt-application-detail__card">
            <h3 class="lw-rt-application-detail__card-title">Lampiran berkas</h3>

            @if($requiredCount > 0 && $documents->count() < $requiredCount)
                <div class="lw-alert lw-alert--warn lw-rt-application-detail__warn">
                    Warga mengunggah {{ $documents->count() }} berkas; persyaratan mencantumkan {{ $requiredCount }} item. Periksa kelengkapan sebelum verifikasi.
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
                                    <a href="{{ route('rt.applications.document.view', [$application, $doc]) }}" target="_blank" rel="noopener" class="lw-rt-application-detail__doc-preview-link">
                                        <img src="{{ route('rt.applications.document.view', [$application, $doc]) }}"
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
                                    <a href="{{ route('rt.applications.document.view', [$application, $doc]) }}" target="_blank" rel="noopener" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Lihat</a>
                                    @if($doc->isPdf())
                                        <a href="{{ route('rt.applications.document.print', [$application, $doc]) }}" target="_blank" rel="noopener" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Cetak</a>
                                    @endif
                                    <a href="{{ route('rt.applications.document', [$application, $doc]) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Unduh</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        @if($application->status->showsReviewActionsSection())
            @include('rt.applications.partials.review-actions', [
                'application' => $application,
                'rejectMessageTemplate' => $rejectMessageTemplate ?? '',
            ])
        @endif
    </div>
</section>
@else
<section class="lw-panel-section lw-rt-request-reference">
    <h2 class="lw-panel-section-title">Permohonan &amp; lampiran</h2>

    <div class="lw-rt-request-reference-block">
        <h3 class="lw-panel-section-subtitle">Data pemohon</h3>
        @if($application->hasDetachedApplicant())
            <p class="lw-mb-3"><span class="lw-badge lw-badge--muted">Data warga dihapus</span></p>
        @endif
        @include('partials.resident-profile-fields-grid', [
            'application' => $application,
            'variant' => 'dl',
        ])
        <dl class="lw-panel-dl lw-panel-dl--reference lw-mt-2">
            <div class="lw-panel-dl-row"><dt>HP/WA</dt><dd>{{ $application->applicantPhone() ?: '—' }}</dd></div>
            <div class="lw-panel-dl-row"><dt>RT</dt><dd>{{ $application->applicantRtLabel() }}</dd></div>
            <div class="lw-panel-dl-row"><dt>Jenis kelamin</dt><dd>{{ $application->applicantGender() ?: '—' }}</dd></div>
        </dl>
    </div>

    <div class="lw-rt-request-reference-block">
        <h3 class="lw-panel-section-subtitle">Keperluan</h3>
        <p class="lw-rt-request-reference-text">{{ $application->purpose }}</p>
    </div>

    <div class="lw-rt-request-reference-block">
        <h3 class="lw-panel-section-subtitle">Lampiran berkas ({{ $documents->count() }})</h3>
        @if($documents->isEmpty())
            <p class="lw-rt-request-reference-empty">Tidak ada lampiran diunggah.</p>
        @else
            <ul class="lw-panel-doc-list">
                @foreach($documents as $doc)
                    @php $doc->setRelation('application', $application); @endphp
                    <li class="lw-panel-doc-item">
                        <p class="lw-panel-doc-item-title">{{ $doc->typeLabel() }}</p>
                        <div class="lw-panel-doc-item-actions">
                            <a href="{{ route('rt.applications.document.view', [$application, $doc]) }}" target="_blank" rel="noopener" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Lihat</a>
                            <a href="{{ route('rt.applications.document', [$application, $doc]) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Unduh</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
@endif
