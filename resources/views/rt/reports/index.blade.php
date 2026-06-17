@extends('layouts.panel')

@section('title', 'Laporan Warga')

@section('content')
<div class="lw-rt-page">
@include('rt.partials.page-head', [
    'eyebrow' => $rt->displayName(),
    'title' => 'Laporan warga',
    'lead' => 'Kendala dan laporan dari formulir kontak portal.',
])

<x-rt.list-toolbar :form-action="route('rt.reports.index')">
    <x-panel.filter-field label="Cari" for="q" class="lw-panel-filter-field--grow">
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="No. laporan, pelapor, atau perihal…">
    </x-panel.filter-field>
    <x-panel.filter-field label="Status" for="status">
        <select id="status" name="status">
            <option value="">Semua status</option>
            @foreach(\App\Enums\ReportStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </x-panel.filter-field>
    <div class="lw-panel-filter-actions">
        <button type="submit" class="lw-panel-btn lw-panel-btn--sm">Terapkan</button>
        @if(request()->hasAny(['q', 'status']))
            <a href="{{ route('rt.reports.index') }}" class="lw-panel-btn lw-panel-btn--ghost lw-panel-btn--sm">Reset</a>
        @endif
    </div>
</x-rt.list-toolbar>

@if($reports->isEmpty())
    <x-panel.empty-state
        title="Belum ada laporan masuk"
        :description="request()->hasAny(['q', 'status']) ? 'Coba ubah filter pencarian.' : 'Kontak dan pengaduan dari warga akan tampil di sini.'"
    />
@else
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table lw-panel-table--rt-list">
            <thead>
                <tr>
                    <th>No. laporan</th>
                    <th>Pelapor</th>
                    <th class="lw-rt-col-hide-sm">Jenis</th>
                    <th>Ringkasan</th>
                    <th class="lw-rt-col-hide-sm">Status</th>
                    <th class="lw-rt-col-hide-sm">Tanggal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->report_number }}</td>
                        <td>{{ $report->reporter_name }}</td>
                        <td class="lw-rt-col-hide-sm">{{ $report->categoryLabel() }}</td>
                        <td title="{{ $report->subject }}">{{ \Illuminate\Support\Str::limit($report->subject, 40) }}</td>
                        <td class="lw-rt-col-hide-sm"><span class="lw-badge {{ $report->status->badgeClass() }}">{{ $report->status->label() }}</span></td>
                        <td class="lw-rt-col-hide-sm">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                        <td class="lw-panel-table-actions">
                            <a href="{{ route('rt.reports.show', $report) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Detail</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="lw-panel-pagination">{{ $reports->links() }}</div>
@endif
</div>
@endsection
