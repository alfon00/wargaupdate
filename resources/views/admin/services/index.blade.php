@extends('layouts.panel')

@section('title', 'Katalog layanan')

@section('content')
<div class="lw-admin-page">
@include('admin.partials.page-head', [
    'title' => 'Katalog layanan',
    'lead' => 'Kelola jenis surat pengantar RT yang tampil di halaman publik /layanan.',
])

@if($services->isEmpty())
    <x-admin.empty-state
        title="Belum ada layanan di katalog"
        description="Jalankan seeder katalog atau tambahkan jenis layanan melalui database."
    />
@else
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $service)
                    <tr>
                        <td><code class="text-xs">{{ $service->code }}</code></td>
                        <td>{{ $service->catalogLabel() }}</td>
                        <td>
                            @if($service->is_active)
                                <span class="lw-badge lw-badge--green">Aktif</span>
                            @else
                                <span class="lw-badge lw-badge--muted">Nonaktif</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap">
                            <div class="lw-admin-table-actions">
                                <a href="{{ route('admin.services.edit', $service) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Edit</a>
                                @if($service->applications_count === 0)
                                    @include('admin.partials.delete-form', [
                                        'action' => route('admin.services.destroy', $service),
                                        'confirm' => 'Hapus layanan '.$service->catalogLabel().' dari katalog?',
                                    ])
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
</div>
@endsection
