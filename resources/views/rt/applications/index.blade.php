@extends('layouts.panel')

@section('title', 'Permohonan')

@section('content')
<div class="lw-rt-page">
@include('rt.partials.page-head', [
    'eyebrow' => $rt->displayName(),
    'title' => 'Daftar Permohonan',
    'lead' => 'Terima atau tolak permohonan, susun dan terbitkan surat PDF dengan QR code verifikasi keaslian, lalu kirim PDF ke warga via WhatsApp bila diperlukan.',
])

<x-rt.list-toolbar :form-action="route('rt.applications.index')">
    <x-panel.filter-field label="Cari" for="q" class="lw-panel-filter-field--grow">
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="No. permohonan atau nama warga…">
    </x-panel.filter-field>
    <x-panel.filter-field label="Status" for="status">
        <select id="status" name="status">
            <option value="">Semua status</option>
            @foreach(\App\Enums\ApplicationStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </x-panel.filter-field>
    <div class="lw-panel-filter-actions">
        <button type="submit" class="lw-panel-btn lw-panel-btn--sm">Terapkan</button>
        @if(request()->hasAny(['q', 'status']))
            <a href="{{ route('rt.applications.index') }}" class="lw-panel-btn lw-panel-btn--ghost lw-panel-btn--sm">Reset</a>
        @endif
    </div>
</x-rt.list-toolbar>

@if($applications->isEmpty())
    <x-panel.empty-state
        title="Belum ada permohonan"
        :description="request()->hasAny(['q', 'status']) ? 'Coba ubah filter pencarian.' : 'Belum ada permohonan surat untuk '.$rt->displayName().'.'"
    />
@else
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table lw-panel-table--rt-list">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Warga</th>
                    <th>Layanan</th>
                    <th class="lw-rt-col-hide-sm">RT</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $app)
                    <tr>
                        <td><a href="{{ route('rt.applications.show', $app) }}" class="lw-panel-table-link">{{ $app->application_number }}</a></td>
                        <td>{{ $app->applicantName() }}</td>
                        <td>{{ $app->serviceType->name }}</td>
                        <td class="lw-rt-col-hide-sm">{{ $app->applicantRtLabel() }}</td>
                        <td><span class="lw-badge {{ $app->status->badgeClass() }}">{{ $app->status->label() }}</span></td>
                        <td class="lw-panel-table-actions">
                            <a href="{{ route($app->status->rtListActionRouteName(), $app) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">
                                {{ $app->status->rtListActionLabel() }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="lw-panel-pagination">{{ $applications->links() }}</div>
@endif
</div>
@endsection
