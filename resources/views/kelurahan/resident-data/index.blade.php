@extends('layouts.panel')

@section('title', 'Data Warga Lengkap')

@section('content')
<div class="lw-rt-page lw-rt-data-page">
@include('kelurahan.partials.page-head', [
    'eyebrow' => 'Kelurahan Inauga · Monitoring',
    'title' => 'Data warga lengkap',
    'lead' => 'Daftar warga seluruh RT — mode baca saja. Filter per RT atau status domisili.',
])

<div class="lw-panel-stats">
    <x-panel.stat-card label="Kartu keluarga" :value="$stats['households']" />
    <x-panel.stat-card label="Warga aktif" :value="$stats['residents_active']" />
    <x-panel.stat-card label="Warga arsip" :value="$stats['residents_archived']" />
</div>

<x-rt.filter-tabs label="Filter status warga" class="lw-rt-data-status-tabs">
    <a href="{{ route('kelurahan.population.index', array_merge(request()->except('filter', 'page'), ['filter' => 'aktif'])) }}"
        class="lw-rt-filter-tab {{ $filter === 'aktif' ? 'is-active' : '' }}">Aktif</a>
    <a href="{{ route('kelurahan.population.index', array_merge(request()->except('filter', 'page'), ['filter' => 'arsip'])) }}"
        class="lw-rt-filter-tab {{ $filter === 'arsip' ? 'is-active' : '' }}">Arsip</a>
    <a href="{{ route('kelurahan.population.index', array_merge(request()->except('filter', 'page'), ['filter' => 'semua'])) }}"
        class="lw-rt-filter-tab {{ $filter === 'semua' ? 'is-active' : '' }}">Semua</a>
</x-rt.filter-tabs>

<x-rt.list-toolbar
    :form-action="route('kelurahan.population.index')"
    class="lw-rt-list-toolbar--compact">
    <input type="hidden" name="filter" value="{{ $filter }}">
    <x-panel.filter-field label="RT" for="rt_profile_id">
        <select id="rt_profile_id" name="rt_profile_id" onchange="this.closest('form').requestSubmit()">
            <option value="">Semua RT</option>
            @foreach($rtProfiles as $rt)
                <option value="{{ $rt->id }}" @selected((int) request('rt_profile_id') === $rt->id)>{{ $rt->displayName() }}</option>
            @endforeach
        </select>
    </x-panel.filter-field>
    <x-panel.filter-field label="Kategori sumber" for="kategori">
        <select id="kategori" name="kategori" onchange="this.closest('form').requestSubmit()">
            @foreach($kategoriOptions as $option)
                <option value="{{ $option['value'] }}" @selected($kategori === $option['value'])>{{ $option['label'] }}</option>
            @endforeach
        </select>
    </x-panel.filter-field>
    <x-panel.filter-field label="Cari (opsional)" for="q" class="lw-panel-filter-field--grow">
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Persempit: No. Kartu Keluarga, nama, NIK, alamat…">
    </x-panel.filter-field>
    <div class="lw-panel-filter-actions">
        <button type="submit" class="lw-panel-btn lw-panel-btn--sm">Cari</button>
        @if(request('q') || request('rt_profile_id'))
            <a href="{{ route('kelurahan.population.index', array_filter(['filter' => $filter, 'kategori' => $kategori !== 'semua' ? $kategori : null])) }}"
                class="lw-panel-btn lw-panel-btn--ghost lw-panel-btn--sm">Reset</a>
        @endif
    </div>
</x-rt.list-toolbar>

@php
    $filterLabels = ['aktif' => 'Aktif', 'arsip' => 'Arsip', 'semua' => 'Semua status'];
    $kategoriLabel = collect($kategoriOptions)->firstWhere('value', $kategori)['label'] ?? 'Semua';
    $statusSummary = request('q') ? 'Semua status (pencarian)' : ($filterLabels[$filter] ?? $filter);
    $activeSummary = collect([
        'RT: '.($selectedRt?->displayName() ?? 'Semua RT'),
        'Status: '.$statusSummary,
        'Kategori: '.$kategoriLabel,
    ]);
    if (request('q')) {
        $activeSummary->push('Pencarian: «'.request('q').'»');
    }
@endphp
<p class="lw-rt-data-active-filters" aria-live="polite">
    Menampilkan: {{ $activeSummary->implode(' · ') }}
</p>

@if($residents->isEmpty())
    <x-panel.empty-state
        title="Tidak ada hasil"
        description="Coba ubah filter RT, status warga, atau kata kunci pencarian."
    />
@else
    @include('rt.resident-data._residents-table', [
        'residents' => $residents,
        'filter' => $filter,
        'kategori' => $kategori,
        'focusHouseholdId' => $focusHouseholdId,
        'showRtColumn' => ! $selectedRt,
        'monitoringMode' => true,
    ])

    <div class="lw-panel-pagination">{{ $residents->links() }}</div>
@endif
</div>
@endsection

@push('scripts')
    @vite(['resources/js/rt-data-warga-table.js'])
@endpush
