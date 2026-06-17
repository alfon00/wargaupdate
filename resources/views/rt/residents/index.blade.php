@extends('layouts.panel')

@section('title', 'Data Warga')

@section('content')
<div class="lw-rt-page">
@include('rt.partials.page-head', [
    'title' => 'Data Warga',
    'lead' => 'Daftar warga di wilayah RT Anda. Untuk kelola KK dan anggota lengkap, gunakan Data warga lengkap.',
    'actions' => '<a href="'.route('rt.data-warga.create').'" class="lw-panel-btn">+ Daftar KK &amp; warga</a>',
])

@php
    $filter = $filter ?? 'aktif';
@endphp

<x-rt.list-toolbar :form-action="route('rt.residents.index')">
    <x-slot:tabs>
        <x-rt.filter-tabs label="Filter status warga">
            <a href="{{ route('rt.residents.index', ['filter' => 'aktif']) }}"
                class="lw-rt-filter-tab {{ $filter === 'aktif' ? 'is-active' : '' }}">Aktif</a>
            <a href="{{ route('rt.residents.index', ['filter' => 'arsip']) }}"
                class="lw-rt-filter-tab {{ $filter === 'arsip' ? 'is-active' : '' }}">Arsip</a>
            <a href="{{ route('rt.residents.index', ['filter' => 'semua']) }}"
                class="lw-rt-filter-tab {{ $filter === 'semua' ? 'is-active' : '' }}">Semua</a>
        </x-rt.filter-tabs>
    </x-slot:tabs>
    <input type="hidden" name="filter" value="{{ $filter }}">
</x-rt.list-toolbar>

@if($residents->isEmpty())
    <x-panel.empty-state
        title="Tidak ada data warga"
        description="Belum ada warga untuk filter ini. Tambahkan dari Data warga lengkap atau ubah tab filter."
        :action-url="route('rt.data-warga.index')"
        action-label="Buka data warga lengkap"
    />
@else
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table lw-panel-table--rt-list">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>NIK</th>
                    <th>Status</th>
                    <th>RT</th>
                    <th>Telepon</th>
                    <th>WA</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($residents as $r)
                    <tr>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->nik ?: '—' }}</td>
                        <td>
                            <span class="lw-badge {{ $r->domicile_status?->badgeClass() }}">
                                {{ $r->domicile_status?->label() ?? '—' }}
                            </span>
                        </td>
                        <td>{{ $r->household?->rtProfile?->displayName() }}</td>
                        <td>{{ $r->phone ?: '—' }}</td>
                        <td>
                            @if($r->hasLatestWhatsappNotificationFailed())
                                <span class="lw-badge lw-badge--amber" title="Notifikasi WhatsApp terakhir gagal">Gagal</span>
                            @elseif($r->whatsapp_notify)
                                <span class="lw-badge lw-badge--green">Aktif</span>
                            @else
                                <span class="lw-badge lw-badge--muted">Off</span>
                            @endif
                        </td>
                        <td class="lw-panel-table-actions">
                            @unless(auth()->user()?->isKelurahan())
                                <a href="{{ route('rt.residents.edit', $r) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Edit</a>
                            @endunless
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
