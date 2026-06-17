@if($showRtSummary && count($rtSummaries) > 0)
<section class="lw-kel-pop-summary lw-kel-no-print" aria-labelledby="rt-summary-heading">
    <h2 id="rt-summary-heading" class="lw-panel-section-title">Ringkasan per RT</h2>
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table lw-panel-table--dense lw-kel-pop-summary-table">
            <thead>
                <tr>
                    <th>RT</th>
                    <th>KK</th>
                    <th>Warga</th>
                    <th>L</th>
                    <th>P</th>
                    <th>Tdk terklasifikasi</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($rtSummaries as $summary)
                    <tr>
                        <td>{{ $summary['rt_label'] }}</td>
                        <td>{{ $summary['households'] }}</td>
                        <td>{{ $summary['residents'] }}</td>
                        <td>{{ $summary['totals']['L'] }}</td>
                        <td>{{ $summary['totals']['P'] }}</td>
                        <td @class(['lw-kel-pop-warn' => $summary['unclassified'] > 0])>
                            {{ $summary['unclassified'] }}
                        </td>
                        <td>
                            <a href="{{ route('kelurahan.population.index', array_merge(request()->except('page'), ['rt_profile_id' => $summary['rt_profile_id']])) }}"
                                class="lw-panel-link">Lihat</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
