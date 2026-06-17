@extends('layouts.panel')

@section('title', $type->label())

@section('content')
<div class="lw-kel-page">
@php
    $isKegiatan = $type === \App\Enums\RtPublicationType::Kegiatan;
    $indexRoute = $isKegiatan ? route('kelurahan.kegiatan.index') : route('kelurahan.pengumuman.index');
@endphp

@include('kelurahan.partials.page-head', [
    'title' => $type->label(),
    'lead' => 'Monitoring '.strtolower($type->label()).' dari seluruh RT — hanya baca.',
    'actions' => '<a href="'.route('activities.index').'" target="_blank" rel="noopener" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Lihat di portal</a>',
])

<x-rt.filter-tabs label="Jenis publikasi" class="lw-panel-publications-nav">
    <a href="{{ route('kelurahan.kegiatan.index', request()->only(['rt_profile_id', 'q'])) }}" class="lw-rt-filter-tab {{ $isKegiatan ? 'is-active' : '' }}">Kegiatan</a>
    <a href="{{ route('kelurahan.pengumuman.index', request()->only(['rt_profile_id', 'q'])) }}" class="lw-rt-filter-tab {{ ! $isKegiatan ? 'is-active' : '' }}">Pengumuman</a>
</x-rt.filter-tabs>

<x-kelurahan.filter-bar :action="$indexRoute" :reset-url="$indexRoute">
    <x-kelurahan.filter-field label="RT" for="rt_profile_id">
        <select id="rt_profile_id" name="rt_profile_id">
            <option value="">Semua RT</option>
            @foreach($rtProfiles as $rt)
                <option value="{{ $rt->id }}" @selected(request('rt_profile_id') == $rt->id)>{{ $rt->displayName() }}</option>
            @endforeach
        </select>
    </x-kelurahan.filter-field>
    <x-kelurahan.filter-field label="Cari judul" for="q" class="sm:col-span-2">
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Judul {{ strtolower($type->label()) }}">
    </x-kelurahan.filter-field>
</x-kelurahan.filter-bar>

@if($publications->isEmpty())
    <x-panel.empty-state
        :title="'Belum ada '.strtolower($type->label())"
        :description="request()->hasAny(['q', 'rt_profile_id']) ? 'Coba ubah filter pencarian.' : 'Publikasi dari RT akan tampil di sini setelah dipublikasikan pengurus RT.'"
    />
@else
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>RT</th>
                    <th>Tanggal</th>
                    @if($isKegiatan)
                        <th>Lokasi</th>
                    @else
                        <th>Berlaku hingga</th>
                        <th>Status</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($publications as $p)
                    <tr>
                        <td>{{ $p->judul }}</td>
                        <td>{{ $p->rtProfile?->displayName() ?? '—' }}</td>
                        <td>{{ $p->tanggal?->locale('id')->translatedFormat('d M Y') ?? '—' }}</td>
                        @if($isKegiatan)
                            <td>{{ $p->lokasi ?? '—' }}</td>
                        @else
                            <td>{{ $p->effectiveExpiresAt()?->locale('id')->translatedFormat('d M Y') ?? '—' }}</td>
                            <td>
                                @if($p->isExpiredOnPublic())
                                    <span class="lw-badge lw-badge--amber">Kedaluwarsa</span>
                                @else
                                    <span class="lw-badge lw-badge--green">Aktif</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="lw-mt-4">{{ $publications->links() }}</div>
@endif
</div>
@endsection
