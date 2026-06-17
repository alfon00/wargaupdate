@extends('layouts.panel')

@section('title', 'Data Penduduk')

@section('content')
<div class="lw-kel-page">
<div class="lw-panel-print-target">
    <section class="lw-kel-pop-toolbar lw-kel-no-print">
        <div class="lw-kel-pop-toolbar-head">
            @include('kelurahan.partials.page-head', [
                'eyebrow' => 'Kelurahan Inauga · Rekap',
                'title' => 'Data Penduduk per RT',
                'lead' => 'Satu baris = satu KK · warga aktif · komposisi usia opsional.',
            ])
            <div class="lw-kel-pop-toolbar-actions">
                @if(! empty($rows))
                    <a href="{{ route('kelurahan.population.export', request()->query()) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Unduh CSV</a>
                @endif
                <button type="button" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm" onclick="window.print()">Cetak</button>
            </div>
        </div>

        <x-kelurahan.filter-bar :action="route('kelurahan.population.index')" :reset-url="route('kelurahan.population.index')">
            <x-kelurahan.filter-field label="RT" for="rt_profile_id">
                <select id="rt_profile_id" name="rt_profile_id">
                    <option value="">Semua RT</option>
                    @foreach($rtProfiles as $rt)
                        <option value="{{ $rt->id }}" @selected(request('rt_profile_id') == $rt->id)>{{ $rt->displayName() }}</option>
                    @endforeach
                </select>
            </x-kelurahan.filter-field>
            <x-kelurahan.filter-field label="Cari" for="q" class="sm:col-span-2">
                <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Nama KK, alamat, atau suku">
            </x-kelurahan.filter-field>
        </x-kelurahan.filter-bar>

        @if($selectedRt)
            @php $contextSummary = $rtSummaries[0] ?? null; @endphp
            <div class="lw-kel-pop-context">
                <span class="lw-kel-pop-context-chip">{{ $selectedRt->displayName() }}</span>
                <span class="lw-kel-pop-context-stats">
                    {{ $stats['households'] }} KK · {{ $stats['residents'] }} warga
                    @if($contextSummary)
                        · L {{ $contextSummary['totals']['L'] }} / P {{ $contextSummary['totals']['P'] }}
                    @endif
                </span>
                <a href="{{ route('kelurahan.population.index', request()->except('rt_profile_id', 'page')) }}" class="lw-panel-link">Lihat semua RT</a>
            </div>
        @else
            <div class="lw-kel-pop-context lw-kel-pop-context--stats">
                <span class="lw-kel-pop-context-stats">
                    <strong>{{ $stats['rt_count'] }}</strong> RT ·
                    <strong>{{ $stats['households'] }}</strong> KK ·
                    <strong>{{ $stats['residents'] }}</strong> warga
                </span>
            </div>
        @endif
    </section>

    @include('kelurahan.population._rt-summary')

    <section class="lw-panel-section" aria-labelledby="table-heading-pop">
        <div class="lw-kel-pop-table-toolbar lw-kel-no-print">
            <h2 id="table-heading-pop" class="lw-panel-section-title lw-panel-section-title--flush">Daftar KK</h2>
            @if(! empty($rows))
                <div class="lw-kel-pop-table-toggles">
                    <label class="lw-kel-pop-toggle">
                        <input type="checkbox" id="toggle-detail-cols">
                        Tampilkan detail demografi
                    </label>
                    <label class="lw-kel-pop-toggle">
                        <input type="checkbox" id="toggle-age-matrix">
                        Tampilkan komposisi usia
                    </label>
                </div>
            @endif
        </div>

        @if(empty($rows))
            <x-panel.empty-state
                title="Belum ada data penduduk"
                :description="request()->hasAny(['q', 'rt_profile_id']) ? 'Tidak ada KK yang cocok dengan filter.' : 'Data KK dari panel RT akan tampil di sini setelah pendataan diverifikasi.'"
            />
        @else
            @include('kelurahan.population._table')
        @endif
    </section>
</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var wrap = document.getElementById('population-table-wrap');
    var toggleAge = document.getElementById('toggle-age-matrix');
    var toggleDetail = document.getElementById('toggle-detail-cols');

    function setAgeVisible(visible) {
        if (! wrap) return;
        wrap.classList.toggle('lw-kel-pop-age-visible', visible);
    }

    function setDetailVisible(visible) {
        if (! wrap) return;
        wrap.classList.toggle('lw-kel-pop-detail-visible', visible);
    }

    if (toggleAge) {
        toggleAge.addEventListener('change', function () {
            setAgeVisible(toggleAge.checked);
        });
    }

    if (toggleDetail) {
        toggleDetail.addEventListener('change', function () {
            setDetailVisible(toggleDetail.checked);
        });
    }

    window.addEventListener('beforeprint', function () {
        document.body.classList.add('lw-printing');
        setAgeVisible(true);
        setDetailVisible(true);
    });

    window.addEventListener('afterprint', function () {
        document.body.classList.remove('lw-printing');
        if (toggleAge) setAgeVisible(toggleAge.checked);
        if (toggleDetail) setDetailVisible(toggleDetail.checked);
    });
});
</script>
@endpush
