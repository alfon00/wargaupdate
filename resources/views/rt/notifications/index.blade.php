@extends('layouts.panel')

@section('title', 'Log WhatsApp')

@section('content')
<div class="lw-rt-page">
@include('rt.partials.page-head', [
    'eyebrow' => $rt->displayName(),
    'title' => 'Log Notifikasi WhatsApp',
    'lead' => 'Riwayat pengiriman notifikasi ke warga.',
])

@if($logs->isEmpty())
    <x-panel.empty-state
        title="Belum ada log notifikasi"
        description="Riwayat pengiriman WhatsApp ke warga akan tampil di sini setelah ada permohonan, pendataan, laporan, atau publikasi."
    />
@else
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table lw-panel-table--rt-list">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th class="lw-rt-col-hide-sm">Telepon</th>
                    <th>Event</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="lw-rt-col-hide-sm">{{ $log->phone }}</td>
                        <td>{{ $log->eventLabel() }}</td>
                        <td>
                            @if(strtolower((string) $log->status) === 'sent' || strtolower((string) $log->status) === 'success')
                                <span class="lw-badge lw-badge--green">Terkirim</span>
                            @elseif(strtolower((string) $log->status) === 'failed')
                                <span class="lw-badge lw-badge--red" title="{{ $log->error_message }}">Gagal</span>
                            @elseif(strtolower((string) $log->status) === 'skipped')
                                <span class="lw-badge lw-badge--muted">Dilewati</span>
                            @else
                                <span class="lw-badge lw-badge--muted">{{ $log->status }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="lw-panel-pagination">{{ $logs->links() }}</div>
@endif
</div>
@endsection
