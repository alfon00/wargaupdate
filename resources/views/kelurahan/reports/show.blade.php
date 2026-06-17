@extends('layouts.panel')

@section('title', $report->report_number)

@section('content')
<div class="lw-kel-page">
@include('kelurahan.partials.page-head', [
    'title' => $report->report_number,
    'lead' => ($report->rtProfile?->displayName() ?? '—').' · '.$report->categoryLabel().' · '.$report->status->label(),
])

<p class="lw-mb-4 lw-kel-no-print">
    <a href="{{ route('kelurahan.reports.index') }}" class="lw-panel-link">← Daftar laporan</a>
</p>

<div class="lw-panel-grid-2">
    <article class="lw-panel-card lw-panel-card--full">
        <h2 class="lw-panel-card-title mb-3">Isi laporan</h2>
        <p class="mb-2">
            <span class="lw-badge {{ $report->status->badgeClass() }}">{{ $report->status->label() }}</span>
        </p>
        <dl class="lw-panel-dl">
            <div class="lw-panel-dl-row">
                <dt>Pelapor</dt>
                <dd>{{ $report->reporter_name }} · {{ $report->phone }}</dd>
            </div>
            <div class="lw-panel-dl-row">
                <dt>Subjek</dt>
                <dd class="font-medium">{{ $report->subject }}</dd>
            </div>
            <div class="lw-panel-dl-row">
                <dt>Pesan</dt>
                <dd class="whitespace-pre-wrap">{{ $report->message }}</dd>
            </div>
        </dl>
    </article>

    <div class="space-y-4 lw-kel-no-print">
    <form method="POST" action="{{ route('kelurahan.reports.status', $report) }}" class="lw-panel-form lw-panel-form--wide h-fit">
        @csrf
        <h2 class="lw-panel-card-title mb-3">Tindak lanjut kelurahan</h2>
        <fieldset class="lw-panel-form-fieldset">
            <div class="lw-panel-field">
                <label for="status">Status <span class="lw-form-label-required">*</span></label>
                <select id="status" name="status" required>
                    <option value="ditindak" @selected(old('status', $report->status->value) === 'ditindak')>Sedang ditindak</option>
                    <option value="selesai" @selected(old('status', $report->status->value) === 'selesai')>Selesai</option>
                </select>
            </div>
            <div class="lw-panel-field">
                <label for="response_note">Catatan</label>
                <textarea id="response_note" name="response_note" rows="4" maxlength="2000">{{ old('response_note', $report->response_note) }}</textarea>
            </div>
        </fieldset>
        <div class="lw-panel-form-actions lw-panel-form-actions--flush">
            <button type="submit" class="lw-panel-btn">Simpan status</button>
            <a href="{{ route('kelurahan.reports.index') }}" class="lw-panel-btn lw-panel-btn--secondary">Batal</a>
        </div>
    </form>
    </div>
</div>
</div>
@endsection
