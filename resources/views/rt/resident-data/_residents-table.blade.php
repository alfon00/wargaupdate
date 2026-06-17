@php
    use App\Services\ResidentFaceReferenceService;

    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Resident> $residents */
    $faceReferenceService = app(ResidentFaceReferenceService::class);
    $previousHouseholdId = null;
    $isFirstRow = true;
@endphp

<div class="lw-rt-data-residents-table-outer" data-rt-data-warga-table>
    <div class="lw-rt-data-residents-table-scroll">
        <table class="lw-panel-table lw-rt-data-residents-table{{ ($showRtColumn ?? false) ? ' lw-rt-data-residents-table--with-rt' : '' }}">
            <thead>
                <tr>
                    @if($showRtColumn ?? false)
                        <th class="lw-rt-data-col-rt">RT</th>
                    @endif
                    <th class="lw-rt-data-col-kk">No. Kartu Keluarga</th>
                    <th class="lw-rt-data-col-name">Nama</th>
                    <th class="lw-rt-data-col-nik">NIK</th>
                    <th class="lw-rt-data-col-ttl">TTL</th>
                    <th class="lw-rt-data-col-status">Status</th>
                    <th class="lw-rt-data-col-actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($residents as $resident)
                    @php
                        $household = $resident->household;
                        $isFocused = (string) ($focusHouseholdId ?? '') === (string) $resident->household_id;
                        $isKkGroupStart = $previousHouseholdId !== $resident->household_id;
                        $previousHouseholdId = $resident->household_id;
                        $nikDisplay = $resident->nik ?: '—';
                        $ttlDisplay = $resident->birthPlaceDate();
                        $kkNumber = filled($household?->family_card_number)
                            ? $household->family_card_number
                            : 'Belum diisi';
                        $rowClasses = collect(['lw-rt-data-resident-row'])
                            ->when($isFocused, fn ($c) => $c->push('is-focused'))
                            ->when($isKkGroupStart && ! $isFirstRow, fn ($c) => $c->push('lw-rt-data-resident-row--kk-start'))
                            ->implode(' ');
                        $isFirstRow = false;
                    @endphp
                    <tr class="{{ $rowClasses }}"
                        id="resident-row-{{ $resident->id }}">
                        @if($showRtColumn ?? false)
                            <td class="lw-rt-data-col-rt">
                                @if($isKkGroupStart)
                                    {{ $household?->rtProfile?->displayName() ?? '—' }}
                                @endif
                            </td>
                        @endif
                        <td class="lw-rt-data-col-kk">
                            <div class="lw-rt-data-kk-head-cell">
                                <span class="lw-rt-data-kk-number">{{ $kkNumber }}</span>
                                @if($isKkGroupStart && $household)
                                    @include('rt.partials.surat-readiness-badge', [
                                        'readiness' => $faceReferenceService->readinessForHousehold($household),
                                    ])
                                @endif
                            </div>
                        </td>
                        <td class="lw-rt-data-col-name">
                            {{ $resident->name }}
                            @if($resident->is_head_of_family)
                                <span class="lw-badge lw-badge--muted" style="font-size:.625rem">KK</span>
                            @endif
                        </td>
                        <td class="lw-rt-data-col-nik" @if($resident->nik) title="{{ $resident->nik }}" @endif>{{ $nikDisplay }}</td>
                        <td class="lw-rt-data-col-ttl" @if(filled($ttlDisplay) && $ttlDisplay !== '—') title="{{ $ttlDisplay }}" @endif><span class="lw-rt-data-ttl-text">{{ $ttlDisplay }}</span></td>
                        <td class="lw-rt-data-col-status">
                            <span class="lw-badge {{ $resident->domicile_status?->badgeClass() }}">
                                {{ $resident->domicile_status?->label() ?? '—' }}
                            </span>
                        </td>
                        <td class="lw-rt-data-col-actions">
                            @include('rt.resident-data._resident-row-actions', [
                                'resident' => $resident,
                                'listFilter' => $filter ?? 'aktif',
                                'listKategori' => $kategori ?? request('kategori', 'semua'),
                                'showHouseholdDeparture' => $showHouseholdDeparture ?? false,
                                'showDetail' => $showDetail ?? true,
                                'monitoringMode' => $monitoringMode ?? false,
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
