@php
    /** @var \App\Models\Household $household */
    $canShowEdit = ! auth()->user()?->isKelurahan();
@endphp

<div class="lw-panel-table-actions lw-rt-data-row-actions">
    @if($canShowEdit)
        <a href="{{ route('rt.households.edit', $household) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Edit KK</a>
        <a href="{{ route('rt.residents.create', ['household_id' => $household->id]) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">+ Anggota</a>
    @endif
</div>
