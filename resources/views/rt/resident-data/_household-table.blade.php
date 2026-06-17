@php
    use App\Enums\DomicileStatus;
@endphp

<div class="lw-panel-table-wrap lw-rt-data-kk-table-wrap" data-rt-data-warga-table>
    <table class="lw-panel-table lw-rt-data-kk-table">
        <thead>
            <tr>
                <th>No. KK</th>
                <th>Kepala KK</th>
                <th>No. rumah</th>
                <th>Anggota</th>
                <th>Kategori</th>
                <th class="lw-rt-data-col-address">Alamat</th>
                <th class="lw-rt-data-col-actions">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($households as $household)
                @php
                    $members = $household->residents;
                    $displayHead = $members->firstWhere('is_head_of_family', true);
                    $documentHead = $household->headResident;
                    $isFocused = (string) ($focusHouseholdId ?? '') === (string) $household->id;
                @endphp
                <tr class="lw-rt-data-kk-row {{ $isFocused ? 'is-focused' : '' }}"
                    id="kk-row-{{ $household->id }}"
                    data-household-id="{{ $household->id }}">
                    <td><strong>{{ $household->family_card_number ?: '—' }}</strong></td>
                    <td>
                        <div class="lw-rt-data-kk-head-cell">
                            <span>{{ $displayHead?->name ?? '—' }}</span>
                            @php
                                $faceReadiness = app(\App\Services\ResidentFaceReferenceService::class)
                                    ->readinessForHousehold($household);
                            @endphp
                            @include('rt.partials.surat-readiness-badge', ['readiness' => $faceReadiness])
                            @if($household->pendataanDocuments->isNotEmpty())
                                @php
                                    $docChipPending = $documentHead && in_array($documentHead->domicile_status, [
                                        DomicileStatus::MenungguVerifikasi,
                                        DomicileStatus::PerluLengkap,
                                    ], true);
                                @endphp
                                <span class="lw-rt-doc-chip {{ $docChipPending ? 'lw-rt-doc-chip--pending' : '' }}">
                                    {{ $household->pendataanDocuments->count() }} berkas
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>{{ $household->house_number ?: '—' }}</td>
                    <td>{{ $members->count() }}</td>
                    <td>
                        <span class="lw-rt-data-source-badge {{ $household->isRtDirectEntry() ? 'lw-rt-data-source-badge--rt' : 'lw-rt-data-source-badge--pendataan' }}">
                            {{ $household->dataSourceLabel() }}
                        </span>
                    </td>
                    <td class="lw-rt-data-col-address">{{ Str::limit($household->address ?: '—', 40) }}</td>
                    <td class="lw-rt-data-col-actions">
                        @if($displayHead)
                            <a href="{{ route('rt.residents.show', array_merge(['resident' => $displayHead], array_filter([
                                'filter' => $filter ?? 'aktif',
                                'kategori' => ($kategori ?? request('kategori', 'semua')) !== 'semua' ? ($kategori ?? request('kategori')) : null,
                                'q' => request('q'),
                                'household' => $household->id,
                            ], fn ($v) => filled($v)))) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Detail</a>
                        @endif
                        @include('rt.resident-data._household-row-actions', [
                            'household' => $household,
                            'listFilter' => $filter ?? 'aktif',
                            'listKategori' => $kategori ?? request('kategori', 'semua'),
                        ])
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
