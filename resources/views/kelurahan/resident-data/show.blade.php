@extends('layouts.panel')

@section('title', 'Kartu Keluarga — '.($resident->household?->family_card_number ?: $resident->name))

@section('content')
@php
    $household = $resident->household;
    $backQuery = array_filter([
        'filter' => $listQuery['filter'] ?? request('filter', 'aktif'),
        'kategori' => $listQuery['kategori'] ?? request('kategori'),
        'q' => $listQuery['q'] ?? request('q'),
        'rt_profile_id' => $listQuery['rt_profile_id'] ?? request('rt_profile_id'),
        'household' => $listQuery['household'] ?? request('household', $resident->household_id),
    ], fn ($value) => filled($value) && $value !== 'semua');

    $kkTitle = filled($household?->family_card_number)
        ? 'Kartu Keluarga '.$household->family_card_number
        : 'Kartu Keluarga';
@endphp

<div class="lw-kel-page lw-rt-resident-detail-page">
@include('kelurahan.partials.page-head', [
    'title' => $kkTitle,
    'lead' => $resident->name.' · '.($resident->domicile_status?->label() ?? '—').' · '.($household?->rtProfile?->displayName() ?? '—').' — mode monitoring (baca saja)',
])

<p class="lw-mb-4">
    <a href="{{ route('kelurahan.population.index', $backQuery) }}" class="lw-panel-page-back">← Kembali ke data warga</a>
</p>

@if($household)
    <article class="lw-panel-card lw-panel-card--full lw-rt-unified-kk-card">
        @include('rt.residents._household-unified-show', [
            'household' => $household,
            'resident' => $resident,
            'listQuery' => $listQuery ?? [],
            'showSyncActions' => false,
            'monitoringMode' => true,
        ])
    </article>
@else
    <article class="lw-panel-card lw-panel-card--full">
        <h2 class="lw-panel-card-title">Detail warga</h2>
        @include('rt.residents._resident-identity-show', ['resident' => $resident])
    </article>
@endif
</div>
@endsection
