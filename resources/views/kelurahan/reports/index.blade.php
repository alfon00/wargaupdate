@extends('layouts.panel')

@section('title', 'Laporan Warga')

@section('content')
<div class="lw-kel-page">
@include('kelurahan.partials.page-head', [
    'title' => 'Laporan warga',
    'lead' => 'Semua laporan dari portal publik — kelurahan dapat menindaklanjuti status.',
])

<x-kelurahan.filter-bar :action="route('kelurahan.reports.index')" :reset-url="route('kelurahan.reports.index')">
    <x-kelurahan.filter-field label="RT" for="rt_profile_id">
        <select id="rt_profile_id" name="rt_profile_id">
            <option value="">Semua RT</option>
            @foreach($rtProfiles as $rt)
                <option value="{{ $rt->id }}" @selected(request('rt_profile_id') == $rt->id)>{{ $rt->displayName() }}</option>
            @endforeach
        </select>
    </x-kelurahan.filter-field>
    <x-kelurahan.filter-field label="Status" for="status">
        <select id="status" name="status">
            <option value="">Semua status</option>
            @foreach(\App\Enums\ReportStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </x-kelurahan.filter-field>
    <x-kelurahan.filter-field label="Jenis" for="category">
        <select id="category" name="category">
            <option value="">Semua jenis</option>
            @foreach($categories as $key => $label)
                <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </x-kelurahan.filter-field>
    <x-kelurahan.filter-field label="Tanggal dari" for="date_from">
        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}">
    </x-kelurahan.filter-field>
    <x-kelurahan.filter-field label="Tanggal sampai" for="date_to">
        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}">
    </x-kelurahan.filter-field>
    <x-kelurahan.filter-field label="Cari" for="q" class="sm:col-span-2">
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="No. laporan atau nama pelapor">
    </x-kelurahan.filter-field>
</x-kelurahan.filter-bar>

@if($reports->isEmpty())
    <x-panel.empty-state
        title="Belum ada laporan"
        :description="request()->hasAny(['q', 'status', 'category', 'rt_profile_id', 'date_from', 'date_to']) ? 'Coba ubah filter pencarian.' : 'Laporan dari portal publik akan tampil di sini.'"
    />
@else
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table">
            <thead>
                <tr>
                    <th>No. laporan</th>
                    <th>RT</th>
                    <th>Pelapor</th>
                    <th>Jenis</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                    <tr>
                        <td>
                            <a href="{{ route('kelurahan.reports.show', $report) }}" class="lw-panel-table-link">{{ $report->report_number }}</a>
                        </td>
                        <td>{{ $report->rtProfile?->displayName() ?? '—' }}</td>
                        <td>{{ $report->reporter_name }}</td>
                        <td>{{ $report->categoryLabel() }}</td>
                        <td><span class="lw-badge {{ $report->status->badgeClass() }}">{{ $report->status->label() }}</span></td>
                        <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('kelurahan.reports.show', $report) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Detail</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="lw-mt-4">{{ $reports->links() }}</div>
@endif
</div>
@endsection
