@extends('layouts.panel')

@section('title', 'Data Warga Lengkap')

@section('content')
<div class="lw-rt-page lw-rt-data-page">
@include('rt.partials.page-head', [
    'eyebrow' => $rt->displayName(),
    'title' => 'Data warga lengkap',
    'lead' => 'Kelola KK dan anggota keluarga di wilayah RT Anda.',
    'actions' => '<a href="'.route('rt.data-warga.report', request()->query()).'" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Unduh laporan RT (PDF)</a><a href="'.route('rt.data-warga.create').'" class="lw-panel-btn lw-panel-btn--sm">+ Daftar KK &amp; warga</a>',
])

<div class="lw-panel-stats">
    <x-panel.stat-card label="Kartu keluarga" :value="$stats['households']" />
    <x-panel.stat-card label="Warga aktif" :value="$stats['residents_active']" />
    <x-panel.stat-card label="Warga arsip" :value="$stats['residents_archived']" />
</div>

<x-rt.filter-tabs label="Filter status warga" class="lw-rt-data-status-tabs">
    <a href="{{ route('rt.data-warga.index', array_merge(request()->except('filter', 'page'), ['filter' => 'aktif'])) }}"
        class="lw-rt-filter-tab {{ $filter === 'aktif' ? 'is-active' : '' }}">Aktif</a>
    <a href="{{ route('rt.data-warga.index', array_merge(request()->except('filter', 'page'), ['filter' => 'arsip'])) }}"
        class="lw-rt-filter-tab {{ $filter === 'arsip' ? 'is-active' : '' }}">Arsip</a>
    <a href="{{ route('rt.data-warga.index', array_merge(request()->except('filter', 'page'), ['filter' => 'semua'])) }}"
        class="lw-rt-filter-tab {{ $filter === 'semua' ? 'is-active' : '' }}">Semua</a>
</x-rt.filter-tabs>

<x-rt.list-toolbar
    :form-action="route('rt.data-warga.index')"
    class="lw-rt-list-toolbar--compact">
    <input type="hidden" name="filter" value="{{ $filter }}">
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
        @if(request('q'))
            <a href="{{ route('rt.data-warga.index', array_filter(['filter' => $filter, 'kategori' => $kategori !== 'semua' ? $kategori : null])) }}"
                class="lw-panel-btn lw-panel-btn--ghost lw-panel-btn--sm">Reset</a>
        @endif
    </div>
</x-rt.list-toolbar>

@php
    $filterLabels = ['aktif' => 'Aktif', 'arsip' => 'Arsip', 'semua' => 'Semua status'];
    $kategoriLabel = collect($kategoriOptions)->firstWhere('value', $kategori)['label'] ?? 'Semua';
    $statusSummary = request('q') ? 'Semua status (pencarian)' : ($filterLabels[$filter] ?? $filter);
    $activeSummary = collect([
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
    @if(request('q'))
        <x-panel.empty-state
            title="Tidak ada hasil"
            description="Coba kata kunci lain atau ubah filter status warga."
            :action-url="route('rt.data-warga.create')"
            action-label="+ Daftar KK & warga"
        />
    @elseif($filter === 'arsip' && $stats['residents_archived'] === 0)
        <x-panel.empty-state
            title="Belum ada warga arsip"
            description="Warga yang dicatat pindah atau meninggal akan tampil di sini."
            :action-url="route('rt.data-warga.index', ['filter' => 'aktif'])"
            action-label="Lihat warga aktif"
        />
    @elseif($filter === 'aktif' && $stats['residents_active'] === 0)
        <x-panel.empty-state
            title="Belum ada warga aktif"
            description="Mulai dengan mendaftarkan kartu keluarga dan anggota keluarga pertama di RT Anda."
            :action-url="route('rt.data-warga.create')"
            action-label="+ Daftar KK & warga"
        />
    @elseif($stats['households'] === 0)
        <x-panel.empty-state
            title="Belum ada data warga"
            description="Belum ada kartu keluarga terdaftar. Daftarkan KK pertama untuk mulai mengelola data warga."
            :action-url="route('rt.data-warga.create')"
            action-label="+ Daftar KK & warga"
        />
    @else
        <x-panel.empty-state
            title="Tidak ada hasil"
            description="Tidak ada warga untuk filter atau kategori yang dipilih. Coba ubah filter."
            :action-url="route('rt.data-warga.create')"
            action-label="+ Daftar KK & warga"
        />
    @endif
@else
    @include('rt.resident-data._resident-household-table', [
        'residents' => $residents,
        'filter' => $filter,
        'kategori' => $kategori,
        'focusHouseholdId' => $focusHouseholdId,
    ])

    <div class="lw-panel-pagination">{{ $residents->links() }}</div>
@endif
</div>
@endsection

@push('scripts')
    @vite(['resources/js/rt-data-warga-table.js'])
@endpush
