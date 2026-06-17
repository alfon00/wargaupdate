@extends('layouts.panel')

@section('title', 'Review hapus permanen')

@section('content')
<div class="lw-admin-page">
@include('admin.partials.page-head', [
    'title' => 'Review permintaan hapus permanen',
    'lead' => $deletionRequest->request_number,
])

<p class="lw-mb-4"><a href="{{ route('admin.deletion-requests.index') }}" class="lw-panel-link">← Daftar permintaan</a></p>

@if($deletionRequest->status === \App\Enums\PermanentDeletionRequestStatus::Approved)
    <p class="lw-panel-field-hint lw-mb-4">Data target telah dihapus permanen. Informasi di bawah adalah snapshot saat pengajuan.</p>
@endif

<article class="lw-panel-card lw-panel-card--full">
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table lw-rt-resident-detail-table">
            <tbody>
                <tr><th scope="row">Status</th><td><span class="lw-badge {{ $deletionRequest->status->badgeClass() }}">{{ $deletionRequest->status->label() }}</span></td></tr>
                <tr><th scope="row">RT</th><td>{{ $deletionRequest->rtProfile?->displayName() ?? '—' }}</td></tr>
                <tr><th scope="row">Target</th><td>{{ $deletionRequest->targetTypeLabel() }}</td></tr>
                <tr><th scope="row">Nama</th><td>{{ $deletionRequest->target_name }}</td></tr>
                <tr><th scope="row">NIK</th><td>{{ $deletionRequest->target_nik ?: '—' }}</td></tr>
                <tr><th scope="row">No. KK</th><td>{{ $deletionRequest->family_card_number ?: '—' }}</td></tr>
                <tr><th scope="row">Diajukan oleh</th><td>{{ $deletionRequest->requester?->name ?? '—' }}</td></tr>
                <tr><th scope="row">Waktu pengajuan</th><td>{{ $deletionRequest->created_at?->timezone('Asia/Jayapura')->format('d/m/Y H:i') ?? '—' }}</td></tr>
                @if($deletionRequest->reviewed_at)
                    <tr><th scope="row">Diproses</th><td>{{ $deletionRequest->reviewed_at->timezone('Asia/Jayapura')->format('d/m/Y H:i') }} · {{ $deletionRequest->reviewer?->name ?? '—' }}</td></tr>
                @endif
                @if($deletionRequest->admin_notes)
                    <tr><th scope="row">Catatan admin</th><td class="lw-pre-wrap">{{ $deletionRequest->admin_notes }}</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="lw-mt-4">
        <h2 class="lw-panel-section-title">Tanda tangan Ketua RT</h2>
        @if($signatureDataUri)
            <div class="lw-letter-signature-pad lw-mt-2" style="max-width:20rem;">
                <img src="{{ $signatureDataUri }}" alt="Tanda tangan Ketua RT" class="lw-letter-signature-canvas" style="height:auto;">
            </div>
        @else
            <p class="lw-panel-field-hint">Berkas tanda tangan tidak ditemukan.</p>
        @endif
    </div>

    @if($deletionRequest->isPending())
        <div class="lw-panel-form-actions lw-mt-6">
            <form method="POST" action="{{ route('admin.deletion-requests.approve', $deletionRequest) }}" class="inline" onsubmit="return confirm('Setujui dan hapus permanen data ini? Tindakan tidak dapat dibatalkan.');">
                @csrf
                <button type="submit" class="lw-panel-btn lw-panel-btn--danger">Setujui &amp; hapus</button>
            </form>
        </div>

        <form method="POST" action="{{ route('admin.deletion-requests.reject', $deletionRequest) }}" class="lw-panel-form lw-mt-4">
            @csrf
            <div class="lw-panel-field">
                <label for="admin_notes">Catatan penolakan (opsional)</label>
                <textarea id="admin_notes" name="admin_notes" rows="3" maxlength="1000">{{ old('admin_notes') }}</textarea>
                @error('admin_notes')<p class="lw-form-error">{{ $message }}</p>@enderror
            </div>
            <div class="lw-panel-form-actions">
                <button type="submit" class="lw-panel-btn lw-panel-btn--secondary">Tolak pengajuan</button>
            </div>
        </form>
    @endif
</article>
</div>
@endsection
