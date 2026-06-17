@extends('layouts.panel')

@section('title', 'Pendataan Masuk')

@section('content')
<div class="lw-rt-page">
@include('rt.partials.page-head', [
    'eyebrow' => $rt->displayName(),
    'title' => 'Pendataan masuk',
    'lead' => 'Verifikasi pendaftaran warga dari portal publik.',
])

<x-rt.list-toolbar :form-action="route('rt.pendataan.index')">
    <x-panel.filter-field label="Cari nama atau NIK" for="q" class="lw-panel-filter-field--grow">
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Ketik untuk mencari…">
    </x-panel.filter-field>
    <div class="lw-panel-filter-actions">
        <button type="submit" class="lw-panel-btn lw-panel-btn--sm">Terapkan</button>
        @if(request('q'))
            <a href="{{ route('rt.pendataan.index') }}" class="lw-panel-btn lw-panel-btn--ghost lw-panel-btn--sm">Reset</a>
        @endif
    </div>
</x-rt.list-toolbar>

@if($residents->isEmpty())
    <x-panel.empty-state
        title="Tidak ada pendataan menunggu"
        :description="request('q') ? 'Tidak ada hasil untuk pencarian ini.' : 'Semua pendataan sudah diverifikasi atau belum ada pengajuan baru.'"
    />
@else
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table lw-panel-table--rt-list">
            <thead>
                <tr>
                    <th>Kepala KK</th>
                    <th>Kategori</th>
                    <th class="lw-rt-col-hide-sm">NIK</th>
                    <th class="lw-rt-col-hide-sm">RT</th>
                    <th>Status</th>
                    <th class="lw-rt-col-hide-sm">Lampiran</th>
                    <th class="lw-rt-col-hide-sm">Tanggal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($residents as $r)
                    <tr>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->household?->pendataanCategoryLabel() }}</td>
                        <td class="lw-rt-col-hide-sm">{{ $r->nik ?: '—' }}</td>
                        <td class="lw-rt-col-hide-sm">{{ $r->household?->rtProfile?->displayName() }}</td>
                        <td><span class="lw-badge {{ $r->domicile_status?->badgeClass() }}">{{ $r->domicile_status?->label() }}</span></td>
                        <td class="lw-rt-col-hide-sm">{{ $r->household?->pendataanDocuments?->count() ?? 0 }} berkas</td>
                        <td class="lw-rt-col-hide-sm">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                        <td class="lw-panel-table-actions">
                            <a href="{{ route('rt.pendataan.show', $r) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Verifikasi</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="lw-panel-pagination">{{ $residents->links() }}</div>
@endif
</div>
@endsection
