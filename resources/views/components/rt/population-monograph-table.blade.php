@props([
    'monograph' => null,
    'compact' => false,
])

@php
    $emptyRow = [
        'L' => 0,
        'P' => 0,
        'jiwa' => 0,
        'kk' => 0,
        'TK' => 0,
        'SD' => 0,
        'SLTA' => 0,
        'SLTP' => 0,
        'PT' => 0,
        'jumlah' => 0,
    ];
    $rows = $monograph['rows'] ?? [];
    $totals = $monograph['totals'] ?? $emptyRow;
    $highlightRow = $monograph['highlight_row'] ?? null;
    $displayRows = $compact && $highlightRow
        ? [$highlightRow]
        : range(1, 8);

    $formatCount = static fn (int $value): string => $value === 0 ? '—' : number_format($value, 0, ',', '.');
@endphp

<div @class([
    'lw-rt-monograph-wrap lw-panel-table-wrap lw-panel-table-wrap--wide',
    'lw-rt-monograph-wrap--compact' => $compact,
])>
    <table class="lw-rt-monograph-table" aria-label="Tabel monografi kependudukan per RT">
        <thead>
            <tr>
                <th rowspan="2" scope="col" class="lw-rt-monograph-col-rt">RT</th>
                <th colspan="2" scope="colgroup">Jumlah Penduduk</th>
                <th colspan="2" scope="colgroup">Penduduk</th>
                <th colspan="5" scope="colgroup">Tingkat Pendidikan</th>
                <th rowspan="2" scope="col" class="lw-rt-monograph-col-total">Jumlah</th>
            </tr>
            <tr>
                <th scope="col">L</th>
                <th scope="col">P</th>
                <th scope="col">Jiwa</th>
                <th scope="col">KK</th>
                <th scope="col">TK</th>
                <th scope="col">SD</th>
                <th scope="col">SLTA</th>
                <th scope="col">SLTP</th>
                <th scope="col">PT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($displayRows as $i)
                @php
                    $row = $rows[$i] ?? $emptyRow;
                    $rtLabel = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                    $rowClass = ($highlightRow === $i || $compact) ? 'is-highlighted' : '';
                @endphp
                <tr class="{{ $rowClass }}">
                    <th scope="row" class="lw-rt-monograph-col-rt">{{ $rtLabel }}</th>
                    <td aria-label="Laki-laki RT {{ $rtLabel }}">{{ $formatCount((int) $row['L']) }}</td>
                    <td aria-label="Perempuan RT {{ $rtLabel }}">{{ $formatCount((int) $row['P']) }}</td>
                    <td aria-label="Jiwa RT {{ $rtLabel }}">{{ $formatCount((int) $row['jiwa']) }}</td>
                    <td aria-label="KK RT {{ $rtLabel }}">{{ $formatCount((int) $row['kk']) }}</td>
                    <td aria-label="TK RT {{ $rtLabel }}">{{ $formatCount((int) $row['TK']) }}</td>
                    <td aria-label="SD RT {{ $rtLabel }}">{{ $formatCount((int) $row['SD']) }}</td>
                    <td aria-label="SLTA RT {{ $rtLabel }}">{{ $formatCount((int) $row['SLTA']) }}</td>
                    <td aria-label="SLTP RT {{ $rtLabel }}">{{ $formatCount((int) $row['SLTP']) }}</td>
                    <td aria-label="PT RT {{ $rtLabel }}">{{ $formatCount((int) $row['PT']) }}</td>
                    <td aria-label="Jumlah RT {{ $rtLabel }}">{{ $formatCount((int) $row['jumlah']) }}</td>
                </tr>
            @endforeach
        </tbody>
        @unless($compact)
            <tfoot>
                <tr class="lw-rt-monograph-row--total">
                    <th scope="row" class="lw-rt-monograph-col-rt">Total</th>
                    <td aria-label="Total laki-laki">{{ $formatCount((int) $totals['L']) }}</td>
                    <td aria-label="Total perempuan">{{ $formatCount((int) $totals['P']) }}</td>
                    <td aria-label="Total jiwa">{{ $formatCount((int) $totals['jiwa']) }}</td>
                    <td aria-label="Total KK">{{ $formatCount((int) $totals['kk']) }}</td>
                    <td aria-label="Total TK">{{ $formatCount((int) $totals['TK']) }}</td>
                    <td aria-label="Total SD">{{ $formatCount((int) $totals['SD']) }}</td>
                    <td aria-label="Total SLTA">{{ $formatCount((int) $totals['SLTA']) }}</td>
                    <td aria-label="Total SLTP">{{ $formatCount((int) $totals['SLTP']) }}</td>
                    <td aria-label="Total PT">{{ $formatCount((int) $totals['PT']) }}</td>
                    <td aria-label="Total jumlah">{{ $formatCount((int) $totals['jumlah']) }}</td>
                </tr>
            </tfoot>
        @endunless
    </table>
</div>
