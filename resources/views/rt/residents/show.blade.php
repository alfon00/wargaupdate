@extends('layouts.panel')

@section('title', 'Kartu Keluarga — '.($resident->household?->family_card_number ?: $resident->name))

@section('content')
@php
    $household = $resident->household;
    $headActions = null;

    if (! auth()->user()?->isKelurahan() && ! $household) {
        ob_start();
@endphp
        <a href="{{ route('rt.residents.edit', array_merge(['resident' => $resident], $listQuery ?? [])) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Edit</a>
@php
        $headActions = ob_get_clean();
    }

    $kkTitle = filled($household?->family_card_number)
        ? 'Kartu Keluarga '.$household->family_card_number
        : 'Kartu Keluarga';
@endphp

<div class="lw-rt-page lw-rt-resident-detail-page">
@include('rt.partials.page-head', [
    'title' => $kkTitle,
    'lead' => $resident->name.' · '.($resident->domicile_status?->label() ?? '—').' · '.($household?->rtProfile?->displayName() ?? '—'),
    'actions' => $headActions,
])

@php
    $dataWargaQuery = array_filter([
        'filter' => $listQuery['filter'] ?? request('filter', 'aktif'),
        'kategori' => $listQuery['kategori'] ?? request('kategori'),
        'q' => $listQuery['q'] ?? request('q'),
        'household' => $listQuery['household'] ?? request('household', $resident->household_id),
    ], fn ($value) => filled($value) && $value !== 'semua');
@endphp

<p class="lw-mb-4">
    <a href="{{ route('rt.data-warga.index', $dataWargaQuery) }}" class="lw-panel-page-back">← Kembali ke data warga</a>
</p>

@if($household)
    <article class="lw-panel-card lw-panel-card--full lw-rt-unified-kk-card">
        @include('rt.residents._household-unified-show', [
            'household' => $household,
            'resident' => $resident,
            'listQuery' => $listQuery ?? [],
            'showSyncActions' => true,
            'monitoringMode' => false,
        ])
    </article>
@else
    <article class="lw-panel-card lw-panel-card--full">
        <h2 class="lw-panel-card-title">Detail warga</h2>
        @include('rt.residents._resident-identity-show', ['resident' => $resident])
    </article>

    @if(! auth()->user()?->isKelurahan())
        <article class="lw-panel-card lw-panel-card--full lw-mt-4">
            @include('rt.residents._resident-manage-zone', [
                'resident' => $resident,
                'listQuery' => $listQuery ?? [],
            ])
        </article>
    @endif
@endif
</div>
@endsection
