@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Resident> $members */
    $isMonitoring = $monitoringMode ?? false;
    $canAddMember = ! $isMonitoring && ! auth()->user()?->isKelurahan();
    $addMemberQuery = array_filter([
        'household_id' => $household->id,
        'filter' => ($filter ?? 'aktif') !== 'semua' ? ($filter ?? 'aktif') : null,
        'kategori' => ($kategori ?? 'semua') !== 'semua' ? ($kategori ?? request('kategori')) : null,
        'q' => request('q'),
        'household' => request('household', $household->id),
        'resident' => $highlightResidentId ?? null,
    ], fn ($value) => filled($value));
@endphp

@if($members->isEmpty())
    <p class="lw-panel-field-hint lw-mb-0">
        @if(request('q') || ($filter ?? 'aktif') !== 'semua')
            Tidak ada anggota yang cocok dengan filter saat ini.
        @else
            Belum ada anggota.
            @if($canAddMember)
                <a href="{{ route('rt.residents.create', $addMemberQuery) }}" class="lw-panel-link">Tambah anggota</a>
            @endif
        @endif
    </p>
@else
    @if($canAddMember)
        <div class="lw-panel-section-head lw-mb-3">
            <p class="lw-panel-field-hint lw-mb-0">{{ $members->count() }} anggota keluarga</p>
            <a href="{{ route('rt.residents.create', $addMemberQuery) }}" class="lw-panel-btn lw-panel-btn--sm">+ Tambah anggota</a>
        </div>
    @endif
    <div class="lw-panel-table-wrap lw-rt-data-member-table-wrap">
        <table class="lw-panel-table lw-panel-table--dense lw-rt-data-member-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Hubungan</th>
                    <th>NIK</th>
                    <th>TTL</th>
                    <th>JK</th>
                    <th>Pekerjaan</th>
                    <th>Pendidikan</th>
                    <th>Agama</th>
                    <th>Status perkawinan</th>
                    <th>Kewarganegaraan</th>
                    <th>Status</th>
                    <th>WA</th>
                    <th class="lw-rt-data-col-actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($members as $member)
                    @php
                        $isHighlighted = isset($highlightResidentId)
                            && (string) $highlightResidentId === (string) $member->id;
                    @endphp
                    <tr @class(['is-current-resident' => $isHighlighted])>
                        <td>
                            {{ $member->name }}
                            @if($member->is_head_of_family)
                                <span class="lw-badge lw-badge--muted" style="font-size:.625rem">KK</span>
                            @endif
                            @if($isHighlighted)
                                <span class="lw-badge lw-badge--blue" style="font-size:.625rem">Sedang dilihat</span>
                            @endif
                        </td>
                        <td>{{ $member->relationship_to_head ?: ($member->is_head_of_family ? 'Kepala Keluarga' : '—') }}</td>
                        <td>{{ $member->nik ?: '—' }}</td>
                        <td>{{ $member->birthPlaceDate() }}</td>
                        <td>{{ $member->gender ?: '—' }}</td>
                        <td>{{ $member->occupation ?: '—' }}</td>
                        <td>{{ $member->education ?: '—' }}</td>
                        <td>{{ $member->religion ?: '—' }}</td>
                        <td>{{ $member->marital_status ?: '—' }}</td>
                        <td>{{ $member->citizenship ?: '—' }}</td>
                        <td>
                            <span class="lw-badge {{ $member->domicile_status?->badgeClass() }}">
                                {{ $member->domicile_status?->label() ?? '—' }}
                            </span>
                        </td>
                        <td>
                            @if($member->hasLatestWhatsappNotificationFailed())
                                <span class="lw-badge lw-badge--amber" title="Notifikasi gagal">Gagal</span>
                            @elseif($member->whatsapp_notify)
                                <span class="lw-badge lw-badge--green">Aktif</span>
                            @else
                                <span class="lw-badge lw-badge--muted">Off</span>
                            @endif
                        </td>
                        <td class="lw-rt-data-col-actions">
                            @include('rt.resident-data._resident-row-actions', [
                                'resident' => $member,
                                'listFilter' => $filter ?? 'aktif',
                                'listKategori' => $kategori ?? request('kategori', 'semua'),
                                'monitoringMode' => $monitoringMode ?? false,
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
