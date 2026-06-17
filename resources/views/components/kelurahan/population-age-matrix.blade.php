@props([
    'buckets',
    'bucketCounts',
    'showUnclassified' => true,
    'headerOnly' => false,
    'bodyOnly' => false,
    'unclassified' => 0,
    'stickyHeader' => true,
])

@php
    $thClass = $stickyHeader ? 'lw-panel-th-sticky' : '';
@endphp

@if(! $bodyOnly)
    <thead class="lw-kel-pop-age-head">
        <tr>
            @foreach($buckets as [$min, $max])
                <th colspan="2" class="{{ $thClass }} lw-kel-pop-age-group">{{ \App\Http\Controllers\Kelurahan\PopulationController::bucketLabel($min, $max) }}</th>
            @endforeach
            @if($showUnclassified)
                <th rowspan="2" class="{{ $thClass }} lw-kel-pop-age-unclassified">Tdk terklasifikasi</th>
            @endif
        </tr>
        <tr>
            @foreach($buckets as [$min, $max])
                <th class="{{ $thClass }}">L</th>
                <th class="{{ $thClass }}">P</th>
            @endforeach
        </tr>
    </thead>
@endif

@if(! $headerOnly)
    @if($bodyOnly)
        @foreach($buckets as [$min, $max])
            @php $key = "{$min}-{$max}"; @endphp
            <td>{{ $bucketCounts[$key]['L'] ?? 0 }}</td>
            <td>{{ $bucketCounts[$key]['P'] ?? 0 }}</td>
        @endforeach
        @if($showUnclassified)
            <td>{{ $unclassified }}</td>
        @endif
    @else
        <tbody>
            <tr>
                @foreach($buckets as [$min, $max])
                    @php $key = "{$min}-{$max}"; @endphp
                    <td>{{ $bucketCounts[$key]['L'] ?? 0 }}</td>
                    <td>{{ $bucketCounts[$key]['P'] ?? 0 }}</td>
                @endforeach
                @if($showUnclassified)
                    <td>{{ $unclassified }}</td>
                @endif
            </tr>
        </tbody>
    @endif
@endif
