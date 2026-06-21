@php
    /** @var \App\Models\Household $household */
    /** @var \App\Models\Resident $resident */
    $displayHead = $household->headResident
        ?? $household->residents->firstWhere('is_head_of_family', true)
        ?? $household->residents->first();
    $documentHead = $household->headResident;
    $allMembers = $household->residents;
    $listFilter = $listQuery['filter'] ?? request('filter', 'aktif');
    $listKategori = $listQuery['kategori'] ?? request('kategori', 'semua');
@endphp

<div class="lw-rt-unified-kk">
    @php
        $faceReadinessDetail = app(\App\Services\ResidentFaceReferenceService::class)
            ->readinessForHousehold($household);
    @endphp
    @include('rt.partials.surat-readiness-callout', [
        'readiness' => $faceReadinessDetail,
        'syncUrl' => ($showSyncActions ?? true) ? route('rt.households.sync-face-references', $household) : null,
    ])

    @include('rt.residents._household-members-panel', [
        'household' => $household,
        'members' => $allMembers,
        'filter' => $listFilter,
        'kategori' => $listKategori,
        'highlightResidentId' => $resident->id,
        'monitoringMode' => $monitoringMode ?? false,
    ])

    @if($resident->hasPendingDeletionRequest())
        <div class="lw-panel-alert lw-panel-alert--warn lw-mb-4" role="status">
            {{ $resident->deletionBlockReason() }}
        </div>
    @endif

    <section class="lw-rt-unified-kk-section lw-rt-unified-kk-section--member-detail">
        <div class="lw-panel-section-head">
            <h3 class="lw-rt-unified-kk-section-title">Detail anggota: {{ $resident->name }}</h3>
            @if(! ($monitoringMode ?? false) && ! auth()->user()?->isKelurahan())
                <a href="{{ route('rt.residents.edit', array_merge(['resident' => $resident], $listQuery ?? [])) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Edit</a>
            @endif
        </div>
        @include('rt.residents._resident-identity-show', ['resident' => $resident])
    </section>

    <x-rt.pendataan-documents
        :household="$household"
        :head="$documentHead ?? $displayHead"
        variant="compact"
        :collapsible="false"
        class="lw-rt-unified-kk-docs lw-rt-unified-kk-section--docs"
    />

    @if(! ($monitoringMode ?? false) && ! auth()->user()?->isKelurahan())
        <p class="lw-panel-field-hint lw-rt-doc-edit-hint lw-mt-2">
            Berkas identitas anggota dapat diperbarui lewat tombol
            <a href="{{ route('rt.residents.edit', array_merge(['resident' => $resident], $listQuery ?? [])) }}" class="lw-inline-link">Edit</a>
            di section Detail anggota di atas.
            @if($resident->is_head_of_family)
                Sebagai kepala keluarga, scan KK dan lampiran tambahan juga dapat diperbarui dari halaman Edit yang sama.
            @endif
        </p>
    @endif
</div>
