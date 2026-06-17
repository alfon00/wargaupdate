@extends('layouts.panel')

@section('title', 'Kartu Keluarga')

@section('content')
<div class="lw-rt-page">
@include('rt.partials.page-head', [
    'title' => 'Kartu Keluarga',
    'lead' => 'Data kartu keluarga di wilayah RT Anda.',
])

<x-rt.list-toolbar
    :cta-url="route('rt.data-warga.create')"
    cta-label="+ Daftar KK &amp; warga"
/>

@if($households->isEmpty())
    <x-panel.empty-state
        title="Belum ada data KK"
        description="Tambahkan kartu keluarga untuk mulai mencatat warga di RT Anda."
        :action-url="route('rt.data-warga.create')"
        action-label="+ Daftar KK & warga"
    />
@else
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table">
            <thead>
                <tr>
                    <th>No. KK</th>
                    <th>RT</th>
                    <th>Alamat</th>
                    <th>Anggota</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($households as $h)
                    <tr>
                        <td>{{ $h->family_card_number }}</td>
                        <td>{{ $h->rtProfile?->displayName() }}</td>
                        <td>{{ $h->address }}</td>
                        <td>{{ $h->residents->count() }}</td>
                        <td class="lw-panel-table-actions">
                            @unless(auth()->user()?->isKelurahan())
                                <a href="{{ route('rt.households.edit', $h) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Edit</a>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="lw-panel-pagination">{{ $households->links() }}</div>
@endif
</div>
@endsection
