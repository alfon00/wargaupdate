@php
    use App\Support\HouseholdHousingOptions;

    $singleRt = $selectedRt !== null;
    $stickyLabelColspan = $singleRt ? 2 : 3;
@endphp

<p class="lw-kel-pop-range lw-kel-no-print">
    Menampilkan {{ $households->firstItem() }}–{{ $households->lastItem() }} dari {{ $households->total() }} KK
    · unduh CSV untuk rekap lengkap
</p>

<div class="lw-panel-table-wrap lw-panel-table-wrap--wide lw-kel-pop-table {{ $singleRt ? 'lw-kel-pop-table--single-rt' : '' }}" id="population-table-wrap">
    <table class="lw-panel-table lw-panel-table--population">
        <thead>
            <tr>
                @unless($singleRt)
                    <th rowspan="2" class="lw-panel-th-sticky lw-panel-th-sticky-left lw-kel-pop-col-rt">RT</th>
                @endunless
                <th rowspan="2" class="lw-panel-th-sticky lw-panel-th-sticky-left lw-kel-pop-col-no">No</th>
                <th rowspan="2" class="lw-panel-th-sticky lw-panel-th-sticky-left lw-kel-pop-col-name">Nama KK</th>
                <th rowspan="2">Anggota</th>
                <th rowspan="2">L</th>
                <th rowspan="2">P</th>
                <th rowspan="2">Status</th>
                @if($singleRt)
                    <th rowspan="2" class="lw-kel-pop-detail-col lw-kel-pop-detail-col--rt-mode">Agama</th>
                    <th rowspan="2" class="lw-kel-pop-detail-col lw-kel-pop-detail-col--rt-mode">Pekerjaan</th>
                    <th rowspan="2" class="lw-kel-pop-detail-col lw-kel-pop-detail-col--rt-mode">Status rumah</th>
                    <th rowspan="2" class="lw-kel-pop-detail-col lw-kel-pop-detail-col--rt-mode">Suku</th>
                @endif
                @unless($singleRt)
                    <th rowspan="2">Alamat</th>
                @endunless
                <th rowspan="2" class="lw-kel-pop-detail-col">Status KK</th>
                @unless($singleRt)
                    <th rowspan="2" class="lw-kel-pop-detail-col">Agama</th>
                    <th rowspan="2" class="lw-kel-pop-detail-col">Pekerjaan</th>
                    <th rowspan="2" class="lw-kel-pop-detail-col">Status rumah</th>
                    <th rowspan="2" class="lw-kel-pop-detail-col">Kondisi rumah</th>
                    <th rowspan="2" class="lw-kel-pop-detail-col">Suku</th>
                @else
                    <th rowspan="2" class="lw-kel-pop-detail-col">Kondisi rumah</th>
                @endunless
                @if($singleRt)
                    <th rowspan="2">Alamat</th>
                @endif
                @foreach($buckets as [$min, $max])
                    <th colspan="2" class="lw-panel-th-sticky lw-kel-pop-age-col lw-kel-pop-age-group">{{ \App\Http\Controllers\Kelurahan\PopulationController::bucketLabel($min, $max) }}</th>
                @endforeach
                <th rowspan="2" class="lw-panel-th-sticky lw-kel-pop-age-col lw-kel-pop-age-unclassified">Tdk terklasifikasi</th>
            </tr>
            <tr>
                @foreach($buckets as [$min, $max])
                    <th class="lw-panel-th-sticky lw-kel-pop-age-col">L</th>
                    <th class="lw-panel-th-sticky lw-kel-pop-age-col">P</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $row)
                @php
                    $household = $row['household'];
                    $no = ($households->firstItem() ?? 1) + $i;
                    $completenessTip = [];
                    if (($row['completeness']['missing_birth'] ?? 0) > 0) {
                        $completenessTip[] = $row['completeness']['missing_birth'].' tanpa tanggal lahir';
                    }
                    if (($row['completeness']['missing_gender'] ?? 0) > 0) {
                        $completenessTip[] = $row['completeness']['missing_gender'].' tanpa jenis kelamin';
                    }
                    $statusTips = [];
                    if ($row['recap_incomplete'] ?? false) {
                        $statusTips[] = 'Rekap: '.implode(', ', $row['missing_recap_labels'] ?? []);
                    }
                    if ($row['unclassified'] > 0) {
                        $statusTips[] = implode(' · ', $completenessTip);
                    }
                @endphp
                <tr>
                    @unless($singleRt)
                        <td class="lw-panel-th-sticky-left lw-kel-pop-col-rt">{{ $row['rt'] }}</td>
                    @endunless
                    <td class="lw-panel-th-sticky-left lw-kel-pop-col-no">{{ $no }}</td>
                    <td class="lw-panel-th-sticky-left lw-kel-pop-col-name">{{ $row['head_name'] }}</td>
                    <td>{{ $row['active_count'] }}</td>
                    <td>{{ $row['totals']['L'] }}</td>
                    <td>{{ $row['totals']['P'] }}</td>
                    <td class="lw-kel-pop-status-cell">
                        @if($row['recap_incomplete'] ?? false)
                            <span class="lw-kel-pop-badge lw-kel-pop-badge--recap" title="{{ $statusTips[0] ?? '' }}">Rekap</span>
                        @endif
                        @if($row['unclassified'] > 0)
                            <span class="lw-kel-pop-badge" title="{{ implode(' · ', $completenessTip) }}">{{ $row['unclassified'] }} usia</span>
                        @endif
                        @if(! ($row['recap_incomplete'] ?? false) && $row['unclassified'] === 0)
                            <span class="lw-kel-pop-ok" aria-hidden="true">—</span>
                        @endif
                    </td>
                    @if($singleRt)
                        <td class="lw-kel-pop-detail-col lw-kel-pop-detail-col--rt-mode">{{ $row['religion'] }}</td>
                        <td class="lw-kel-pop-detail-col lw-kel-pop-detail-col--rt-mode">{{ $row['occupation'] }}</td>
                        <td class="lw-kel-pop-detail-col lw-kel-pop-detail-col--rt-mode">{{ HouseholdHousingOptions::statusLabel($household->status_rumah_tinggal) }}</td>
                        <td class="lw-kel-pop-detail-col lw-kel-pop-detail-col--rt-mode">{{ $household->suku ?? '—' }}</td>
                    @endif
                    @unless($singleRt)
                        <td class="lw-kel-pop-address-cell">{{ $row['address'] ?: '—' }}</td>
                    @endunless
                    <td class="lw-kel-pop-detail-col">{{ \App\Http\Controllers\Kelurahan\PopulationController::householdStatusLabel($household->status) }}</td>
                    @unless($singleRt)
                        <td class="lw-kel-pop-detail-col">{{ $row['religion'] }}</td>
                        <td class="lw-kel-pop-detail-col">{{ $row['occupation'] }}</td>
                        <td class="lw-kel-pop-detail-col">{{ HouseholdHousingOptions::statusLabel($household->status_rumah_tinggal) }}</td>
                        <td class="lw-kel-pop-detail-col">{{ HouseholdHousingOptions::kondisiLabel($household->kondisi_rumah_milik) }}</td>
                        <td class="lw-kel-pop-detail-col">{{ $household->suku ?? '—' }}</td>
                    @else
                        <td class="lw-kel-pop-detail-col">{{ HouseholdHousingOptions::kondisiLabel($household->kondisi_rumah_milik) }}</td>
                    @endunless
                    @if($singleRt)
                        <td class="lw-kel-pop-address-cell">{{ $row['address'] ?: '—' }}</td>
                    @endif
                    @foreach($buckets as [$min, $max])
                        @php $key = "{$min}-{$max}"; @endphp
                        <td class="lw-kel-pop-age-col">{{ $row['buckets'][$key]['L'] ?? 0 }}</td>
                        <td class="lw-kel-pop-age-col">{{ $row['buckets'][$key]['P'] ?? 0 }}</td>
                    @endforeach
                    <td class="lw-kel-pop-age-col">{{ $row['unclassified'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="lw-kel-pop-subtotal">
                <td class="lw-panel-th-sticky-left" colspan="{{ $stickyLabelColspan }}"><strong>Subtotal halaman</strong></td>
                <td><strong>{{ $pageTotals['active_count'] }}</strong></td>
                <td><strong>{{ $pageTotals['totals']['L'] }}</strong></td>
                <td><strong>{{ $pageTotals['totals']['P'] }}</strong></td>
                <td></td>
                @if($singleRt)
                    <td colspan="4"></td>
                    <td></td>
                @else
                    <td></td>
                    <td colspan="6"></td>
                @endif
                @if($singleRt)
                    <td colspan="2"></td>
                    <td></td>
                @endif
                @foreach($buckets as [$min, $max])
                    @php $key = "{$min}-{$max}"; @endphp
                    <td class="lw-kel-pop-age-col"><strong>{{ $pageTotals['buckets'][$key]['L'] ?? 0 }}</strong></td>
                    <td class="lw-kel-pop-age-col"><strong>{{ $pageTotals['buckets'][$key]['P'] ?? 0 }}</strong></td>
                @endforeach
                <td class="lw-kel-pop-age-col"><strong>{{ $pageTotals['unclassified'] }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="lw-mt-4 lw-kel-no-print">
    {{ $households->links() }}
</div>
