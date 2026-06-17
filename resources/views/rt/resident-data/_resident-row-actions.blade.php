@php
    /** @var \App\Models\Resident $resident */
    $filterParam = $listFilter ?? request('filter', 'aktif');
    $kategoriParam = $listKategori ?? request('kategori', 'semua');
    $listQuery = array_filter([
        'filter' => $filterParam,
        'kategori' => $kategoriParam !== 'semua' ? $kategoriParam : null,
        'q' => request('q'),
        'rt_profile_id' => request('rt_profile_id'),
        'household' => $resident->household_id,
    ], fn ($value) => filled($value));
@endphp

@php
    $monitoringMode = $monitoringMode ?? false;
@endphp

<div class="lw-panel-table-actions lw-rt-data-row-actions">
    @if($showDetail ?? true)
        @if($monitoringMode)
            <a href="{{ route('kelurahan.data-warga.show', array_merge(['resident' => $resident], $listQuery)) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Detail</a>
        @else
            <a href="{{ route('rt.residents.show', array_merge(['resident' => $resident], $listQuery)) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Detail</a>
        @endif
    @endif
</div>
