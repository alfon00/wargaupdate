@extends('layouts.panel')

@section('title', 'Permintaan hapus permanen')

@section('content')
<div class="lw-admin-page">
@include('admin.partials.page-head', [
    'title' => 'Permintaan hapus permanen',
    'lead' => 'Review pengajuan hapus permanen dari pengurus RT sebelum data benar-benar dihapus.',
])

<form method="GET" action="{{ route('admin.deletion-requests.index') }}" class="lw-admin-toolbar lw-mb-4">
    <div class="lw-admin-filter-field">
        <label for="status">Status</label>
        <select id="status" name="status" onchange="this.form.submit()">
            @foreach($statuses as $statusOption)
                <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>{{ $statusOption->label() }}</option>
            @endforeach
            <option value="all" @selected($status === 'all')>Semua status</option>
        </select>
    </div>
</form>

@if($requests->isEmpty())
    <x-admin.empty-state
        title="Belum ada permintaan"
        :description="match ($status) {
            'pending' => 'Tidak ada pengajuan yang menunggu persetujuan.',
            'approved' => 'Tidak ada permintaan yang sudah disetujui.',
            'rejected' => 'Tidak ada permintaan yang ditolak.',
            'all' => 'Belum ada pengajuan hapus permanen dari RT.',
            default => 'Tidak ada permintaan dengan status ini.',
        }"
    />
@else
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table">
            <thead>
                <tr>
                    <th>No. pengajuan</th>
                    <th>RT</th>
                    <th>Target</th>
                    <th>Nama / KK</th>
                    <th>Status</th>
                    <th>Diajukan</th>
                    <th class="lw-admin-table-actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $item)
                    <tr>
                        <td><strong>{{ $item->request_number }}</strong></td>
                        <td>{{ $item->rtProfile?->displayName() ?? '—' }}</td>
                        <td>{{ $item->targetTypeLabel() }}</td>
                        <td>
                            {{ $item->target_name }}
                            @if($item->family_card_number)
                                <br><span class="lw-panel-field-hint">KK {{ $item->family_card_number }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="lw-badge {{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span>
                        </td>
                        <td>{{ $item->created_at?->timezone('Asia/Jayapura')->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="lw-admin-table-actions">
                            <a href="{{ route('admin.deletion-requests.show', $item) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">{{ $item->isPending() ? 'Review' : 'Detail' }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="lw-panel-pagination">{{ $requests->links() }}</div>
@endif
</div>
@endsection
