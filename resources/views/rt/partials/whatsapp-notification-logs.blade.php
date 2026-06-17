@if($logs->isNotEmpty())
<section class="lw-panel-section lw-mt-4" aria-label="Riwayat notifikasi WhatsApp">
    <h2 class="lw-panel-section-title">Notifikasi WhatsApp</h2>
    <p class="lw-panel-card-note lw-mb-3">
        {{ $contextLabel ?? 'Riwayat pengiriman notifikasi WhatsApp.' }}
        <a href="{{ route('rt.notifications.index') }}" class="lw-inline-link">Lihat semua log</a>
    </p>
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table lw-panel-table--rt-list">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Event</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $log->eventLabel() }}</td>
                        <td>
                            @if($log->status === 'sent')
                                <span class="lw-badge lw-badge--green" title="{{ $log->sent_at?->format('d/m/Y H:i') }}">Terkirim</span>
                            @elseif($log->status === 'skipped')
                                <span class="lw-badge lw-badge--muted" title="{{ $log->error_message }}">Dilewati</span>
                            @elseif($log->status === 'failed')
                                <span class="lw-badge lw-badge--red" title="{{ $log->error_message }}">Gagal</span>
                            @else
                                <span class="lw-badge lw-badge--muted">{{ $log->status }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
