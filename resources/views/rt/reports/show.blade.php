@extends('layouts.panel')

@section('title', $report->report_number)

@section('content')
<div class="lw-rt-page">
@include('rt.partials.page-head', [
    'title' => $report->report_number,
    'lead' => $report->categoryLabel().' · Status: '.$report->status->label(),
])

<p class="lw-mb-4">
    <a href="{{ route('rt.reports.index') }}" class="lw-panel-page-back">← Daftar laporan</a>
</p>

<div class="lw-panel-grid-2">
    <article class="lw-panel-card lw-panel-card--full">
        <section class="lw-panel-section">
            <h2 class="lw-panel-card-title">Data pelapor</h2>
            <dl class="lw-panel-dl">
                <div class="lw-panel-dl-row"><dt>Nama</dt><dd>{{ $report->reporter_name }}</dd></div>
                <div class="lw-panel-dl-row"><dt>HP/WA</dt><dd>{{ $report->phone }}</dd></div>
                <div class="lw-panel-dl-row"><dt>NIK</dt><dd>{{ $report->nik ?: '—' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>Email</dt><dd>{{ $report->email ?: '—' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>RT</dt><dd>{{ $report->rtProfile?->displayName() }}</dd></div>
                @if($report->application_number)
                <div class="lw-panel-dl-row"><dt>No. permohonan</dt><dd>{{ $report->application_number }}</dd></div>
                @endif
            </dl>
        </section>
        <section class="lw-panel-section">
            <h2 class="lw-panel-section-title">{{ $report->subject }}</h2>
            <p class="lw-panel-card-note lw-pre-wrap-block">{{ $report->message }}</p>
        </section>
        <p class="lw-panel-card-note">Dikirim {{ $report->created_at->format('d/m/Y H:i') }} WIT</p>

        @include('rt.partials.whatsapp-notification-logs', [
            'logs' => $notificationLogs,
            'contextLabel' => 'Riwayat notifikasi WhatsApp ke nomor pelapor.',
        ])
    </article>

    <div class="lw-panel-stack">
        <x-rt.sidebar-action-card
            title="Tindak lanjut"
            tag="form"
            method="POST"
            action="{{ route('rt.reports.status', $report) }}">
            @csrf
            <div class="lw-panel-field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="ditindak" @selected(old('status', $report->status->value) === 'ditindak')>Sedang ditindak</option>
                    <option value="selesai" @selected(old('status', $report->status->value) === 'selesai')>Selesai</option>
                </select>
            </div>
            <div class="lw-panel-field">
                <label for="response_note">Catatan internal (opsional)</label>
                <textarea id="response_note" name="response_note" rows="4" maxlength="2000"
                    placeholder="Catatan untuk arsip pengurus RT.">{{ old('response_note', $report->response_note) }}</textarea>
            </div>
            @if($report->handler)
                <p class="lw-panel-card-note">Terakhir ditangani {{ $report->handled_at?->format('d/m/Y H:i') }} oleh {{ $report->handler->name }}.</p>
            @endif
            <button type="submit" class="lw-panel-btn lw-panel-btn--block">Simpan status</button>
        </x-rt.sidebar-action-card>

        <x-rt.sidebar-action-card
            title="Kirim WhatsApp"
            note="Kirim ulang konfirmasi atau pembaruan status ke nomor pelapor."
            tag="form"
            method="POST"
            action="{{ route('rt.reports.whatsapp', $report) }}"
            onsubmit="return confirm('Kirim notifikasi WhatsApp ke {{ $report->phone }}?');">
            @csrf
            <button type="submit" class="lw-panel-btn lw-panel-btn--block lw-panel-btn--secondary">
                Kirim WhatsApp ke pelapor
            </button>
            @if(! empty($lastFailedLog))
                <p class="lw-panel-card-note lw-mb-0">Pengiriman terakhir gagal: {{ $lastFailedLog->error_message }}</p>
            @endif
        </x-rt.sidebar-action-card>

        @include('rt.partials.instant-delete-zone', [
            'action' => route('rt.reports.destroy', $report),
            'label' => 'Hapus laporan',
            'description' => 'Menghapus laporan kontak/pengaduan beserta lampiran foto dari arsip RT.',
            'confirm' => 'Hapus laporan '.$report->report_number.'? Tindakan ini tidak dapat dibatalkan.',
        ])
    </div>
</div>
</div>
@endsection
