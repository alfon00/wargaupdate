@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Resident> $residents */
@endphp

<div class="lw-panel-table-wrap lw-rt-data-resident-household-table-wrap" data-rt-data-warga-table>
    <table class="lw-panel-table lw-rt-data-resident-household-table">
        <thead>
            <tr>
                <th class="lw-rt-data-col-kk">No. Kartu Keluarga</th>
                <th class="lw-rt-data-col-name">Nama</th>
                <th class="lw-rt-data-col-nik">NIK</th>
                <th class="lw-rt-data-col-status">Status</th>
                <th class="lw-rt-data-col-kategori">Kategori</th>
                <th class="lw-rt-data-col-actions">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($residents as $resident)
                @php
                    $household = $resident->household;
                    $householdId = $household?->id;
                    $isFocusedHousehold = $householdId && (string) ($focusHouseholdId ?? '') === (string) $householdId;
                    $isFocusedResident = $isFocusedHousehold && (string) request('resident', '') === (string) $resident->id;
                    $kkNumber = filled($household?->family_card_number)
                        ? $household->family_card_number
                        : '—';
                    $rowClasses = collect(['lw-rt-data-resident-row'])
                        ->when($isFocusedHousehold, fn ($c) => $c->push('is-focused'))
                        ->when($isFocusedResident, fn ($c) => $c->push('is-focused-resident'))
                        ->implode(' ');
                @endphp
                <tr class="{{ $rowClasses }}"
                    id="resident-row-{{ $resident->id }}"
                    @if($householdId) data-household-id="{{ $householdId }}" @endif>
                    <td class="lw-rt-data-col-kk">
                        <span class="lw-rt-data-kk-number">{{ $kkNumber }}</span>
                    </td>
                    <td class="lw-rt-data-col-name">
                        {{ $resident->name }}
                        @if($resident->is_head_of_family)
                            <span class="lw-badge lw-badge--muted" style="font-size:.625rem">KK</span>
                        @endif
                    </td>
                    <td class="lw-rt-data-col-nik" @if($resident->nik) title="{{ $resident->nik }}" @endif>{{ $resident->nik ?: '—' }}</td>
                    <td class="lw-rt-data-col-status">
                        <span class="lw-badge {{ $resident->domicile_status?->badgeClass() }}">
                            {{ $resident->domicile_status?->label() ?? '—' }}
                        </span>
                    </td>
                    <td class="lw-rt-data-col-kategori">
                        @if($household)
                            <span class="lw-rt-data-source-badge {{ $household->isRtDirectEntry() ? 'lw-rt-data-source-badge--rt' : 'lw-rt-data-source-badge--pendataan' }}">
                                {{ $household->dataSourceLabel() }}
                            </span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="lw-rt-data-col-actions">
                        @include('rt.resident-data._resident-row-actions', [
                            'resident' => $resident,
                            'listFilter' => $filter ?? 'aktif',
                            'listKategori' => $kategori ?? request('kategori', 'semua'),
                        ])
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
