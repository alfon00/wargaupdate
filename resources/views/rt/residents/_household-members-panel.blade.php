@php
    /** @var \App\Models\Household $household */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Resident> $members */
    $members = $members ?? $household->residents;
    $listFilter = $filter ?? request('filter', 'aktif');
    $listKategori = $kategori ?? request('kategori', 'semua');
    $isMonitoring = $monitoringMode ?? false;
    $canAddMember = ! $isMonitoring && ! auth()->user()?->isKelurahan();
    $addMemberQuery = array_filter([
        'household_id' => $household->id,
        'filter' => $listFilter !== 'semua' ? $listFilter : null,
        'kategori' => $listKategori !== 'semua' ? $listKategori : null,
        'q' => $listQuery['q'] ?? request('q'),
        'household' => $listQuery['household'] ?? request('household', $household->id),
        'resident' => $highlightResidentId ?? null,
    ], fn ($value) => filled($value));
@endphp

<section class="lw-rt-household-members-panel" data-rt-household-members-panel>
    <div class="lw-panel-section-head">
        <h3 class="lw-rt-household-members-title">Daftar Anggota Keluarga</h3>
        @if($canAddMember)
            <a href="{{ route('rt.residents.create', $addMemberQuery) }}" class="lw-panel-btn lw-panel-btn--sm">+ Tambah anggota</a>
        @endif
    </div>

    @if($members->isEmpty())
        <p class="lw-panel-field-hint lw-mb-0">Belum ada anggota.</p>
    @else
        <div class="lw-panel-table-wrap lw-rt-household-members-table-wrap">
            <table class="lw-panel-table lw-rt-household-members-table">
                <thead>
                    <tr>
                        <th class="lw-rt-household-members-col-no">No</th>
                        <th class="lw-rt-household-members-col-kk">No. Kartu Keluarga</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Jenis Kelamin</th>
                        <th>Status dalam Keluarga</th>
                        <th>Status</th>
                        <th class="lw-rt-household-members-col-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody data-rt-members-tbody>
                    @foreach($members as $index => $member)
                        @php
                            $isHighlighted = isset($highlightResidentId)
                                && (string) $highlightResidentId === (string) $member->id;
                            $familyStatus = $member->relationship_to_head
                                ?: ($member->is_head_of_family ? 'Kepala Keluarga' : '—');
                        @endphp
                        <tr @class(['is-current-resident' => $isHighlighted])
                            data-rt-member-row>
                            <td class="lw-rt-household-members-col-no" data-rt-member-no>{{ $index + 1 }}</td>
                            <td class="lw-rt-household-members-col-kk">{{ $household->family_card_number ?: '—' }}</td>
                            <td>
                                {{ $member->name }}
                                @if($member->is_head_of_family)
                                    <span class="lw-badge lw-badge--muted lw-rt-household-members-kk-badge">KK</span>
                                @endif
                            </td>
                            <td>{{ $member->nik ?: '—' }}</td>
                            <td>{{ $member->gender ?: '—' }}</td>
                            <td>{{ $familyStatus }}</td>
                            <td>
                                <span class="lw-badge {{ $member->domicile_status?->badgeClass() }}">
                                    {{ $member->domicile_status?->label() ?? '—' }}
                                </span>
                                @if($member->hasPendingDeletionRequest())
                                    <span class="lw-badge lw-badge--amber lw-rt-household-members-pending-badge" title="Pengajuan hapus permanen menunggu persetujuan admin">Menunggu hapus</span>
                                @endif
                            </td>
                            <td class="lw-rt-household-members-col-actions">
                                @include('rt.resident-data._resident-row-actions', [
                                    'resident' => $member,
                                    'listFilter' => $listFilter,
                                    'listKategori' => $listKategori,
                                    'monitoringMode' => $isMonitoring,
                                ])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <footer class="lw-rt-household-members-footer">
            <p class="lw-rt-household-members-summary" data-rt-members-summary aria-live="polite">
                Menampilkan 1–{{ min($members->count(), 10) }} dari {{ $members->count() }} anggota keluarga
            </p>
            <nav class="lw-rt-household-members-pagination" data-rt-members-pagination aria-label="Halaman anggota keluarga" hidden></nav>
        </footer>
    @endif
</section>

@push('scripts')
    @vite(['resources/js/rt-household-members-panel.js'])
@endpush
