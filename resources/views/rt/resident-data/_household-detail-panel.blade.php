@php
    /** @var \App\Models\Household $household */
    $displayHead = $household->headResident
        ?? $household->residents->firstWhere('is_head_of_family', true)
        ?? $household->residents->first();
    $documentHead = $household->headResident;
    $allMembers = $household->residents;
@endphp

<div class="lw-rt-data-kk-detail-inner">
    <dl class="lw-rt-data-kk-dl">
        <div><dt>No. KK</dt><dd>{{ $household->family_card_number ?: '—' }}</dd></div>
        <div><dt>Alamat</dt><dd>{{ $household->address ?: '—' }}</dd></div>
        <div><dt>Status rumah</dt><dd>{{ \App\Support\HouseholdHousingOptions::statusLabel($household->status_rumah_tinggal) }}</dd></div>
        <div><dt>Suku</dt><dd>{{ $household->suku ?: '—' }}</dd></div>
        <div><dt>Kondisi rumah</dt><dd>{{ \App\Support\HouseholdHousingOptions::kondisiLabel($household->kondisi_rumah_milik) }}</dd></div>
        <div><dt>No. rumah</dt><dd>{{ $household->house_number ?: '—' }}</dd></div>
        @if($displayHead?->phone)
            <div><dt>Kontak kepala</dt><dd>{{ $displayHead->phone }}</dd></div>
        @endif
    </dl>

    <x-rt.pendataan-documents
        :household="$household"
        :head="$documentHead ?? $displayHead"
        variant="compact"
        :collapsible="false"
    />

    @php
        $faceReadinessDetail = app(\App\Services\ResidentFaceReferenceService::class)
            ->readinessForHousehold($household);
    @endphp
    @include('rt.partials.surat-readiness-callout', [
        'readiness' => $faceReadinessDetail,
        'syncUrl' => ($showSyncActions ?? true) ? route('rt.households.sync-face-references', $household) : null,
    ])

    @include('rt.resident-data._member-subtable', [
        'household' => $household,
        'members' => $allMembers,
        'filter' => 'semua',
        'kategori' => $kategori ?? request('kategori', 'semua'),
        'highlightResidentId' => $highlightResidentId ?? null,
        'monitoringMode' => $monitoringMode ?? false,
    ])
</div>
