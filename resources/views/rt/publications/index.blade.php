@extends('layouts.panel')

@section('title', $type->label())

@section('content')
<div class="lw-rt-page">
@php
    $isKegiatan = $type === \App\Enums\RtPublicationType::Kegiatan;
    $createRoute = $isKegiatan ? route('rt.kegiatan.create') : route('rt.pengumuman.create');
    $editRouteName = $isKegiatan ? 'rt.kegiatan.edit' : 'rt.pengumuman.edit';
@endphp

@include('rt.partials.page-head', [
    'eyebrow' => 'Panel RT · '.$rt->displayName(),
    'title' => $type->label(),
    'lead' => 'Kelola '.strtolower($type->label()).' yang tampil di halaman Kegiatan publik.',
    'actions' => '<a href="'.$createRoute.'" class="lw-panel-btn lw-panel-btn--sm">+ Tambah '.strtolower($type->label()).'</a>',
])

<x-rt.filter-tabs label="Jenis publikasi" class="lw-panel-publications-nav">
    <a href="{{ route('rt.kegiatan.index') }}" class="lw-rt-filter-tab {{ $isKegiatan ? 'is-active' : '' }}">Kegiatan</a>
    <a href="{{ route('rt.pengumuman.index') }}" class="lw-rt-filter-tab {{ ! $isKegiatan ? 'is-active' : '' }}">Pengumuman</a>
</x-rt.filter-tabs>

@if($publications->isEmpty())
    <x-panel.empty-state
        :title="'Belum ada '.strtolower($type->label())"
        description="Klik tombol tambah untuk mempublikasikan ke halaman warga."
        :action-url="$createRoute"
        :action-label="'+ Tambah '.strtolower($type->label())"
    />
@else
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table lw-panel-table--rt-list">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Tanggal</th>
                    @if($isKegiatan)
                        <th>Lokasi</th>
                    @else
                        <th>Berlaku hingga</th>
                        <th>Status</th>
                    @endif
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($publications as $p)
                    <tr>
                        <td>{{ $p->judul }}</td>
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
                        <td class="lw-panel-table-actions">
                            <a href="{{ route($editRouteName, $p) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="lw-panel-pagination">{{ $publications->links() }}</div>
@endif
</div>
@endsection
