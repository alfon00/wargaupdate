@extends('layouts.panel')

@section('title', 'Verifikasi Pendataan')

@section('content')
@php
    $categoryLabel = $head->household?->pendataanCategoryLabel() ?? 'Pendataan';
@endphp
<div class="lw-rt-page">
@include('rt.partials.page-head', [
    'title' => 'Verifikasi: '.$head->name,
    'lead' => ($head->household?->rtProfile?->displayName() ?? '—').' · Dikirim '.$head->updated_at->timezone('Asia/Jayapura')->format('d/m/Y H:i').' WIT · Status: '.($head->domicile_status?->label() ?? '—'),
])

<p class="lw-mb-4">
    <a href="{{ route('rt.pendataan.index') }}" class="lw-panel-page-back">← Daftar pendataan</a>
</p>

<div class="lw-panel-grid-2">
    <article class="lw-panel-card lw-panel-card--full">
        <section class="lw-panel-section">
            <h2 class="lw-panel-card-title">Ringkasan pengajuan</h2>
            <dl class="lw-panel-dl lw-panel-dl--reference">
                <div class="lw-panel-dl-row"><dt>Kategori</dt><dd>{{ $head->household?->pendataanCategoryLabel() }}</dd></div>
                <div class="lw-panel-dl-row"><dt>Anggota</dt><dd>{{ $members->count() }} orang</dd></div>
                <div class="lw-panel-dl-row"><dt>Notifikasi WA</dt><dd>{{ $head->whatsapp_notify ? 'Ya' : 'Tidak' }}</dd></div>
            </dl>
            <p class="lw-form-hint lw-mb-0">Bandingkan berkas dengan data di bawah. Gunakan <strong>Perbarui</strong> untuk mengedit data sebelum menyetujui atau menolak.</p>
        </section>

        <section class="lw-panel-section">
            <div class="lw-rt-pendataan-review-head">
                <h2 class="lw-panel-section-title">Data KK</h2>
                <div class="lw-rt-pendataan-review-actions">
                    @if($kkDocument)
                        <a href="{{ route('rt.pendataan.document.view', [$head, $kkDocument]) }}" target="_blank" rel="noopener" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Detail berkas</a>
                    @endif
                    @if($head->household)
                        <a href="{{ route('rt.households.edit', array_merge([$head->household], $pendataanQuery)) }}" class="lw-panel-btn lw-panel-btn--sm">Perbarui data KK</a>
                    @endif
                </div>
            </div>
            <dl class="lw-panel-dl lw-panel-dl--reference">
                <div class="lw-panel-dl-row"><dt>No. KK</dt><dd>{{ $head->household?->family_card_number ?: '—' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>Alamat</dt><dd>{{ $head->household?->address ?: '—' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>No. rumah</dt><dd>{{ $head->household?->house_number ?: '—' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>Status rumah</dt><dd>{{ $head->household?->status_rumah_tinggal ?: '—' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>Suku</dt><dd>{{ $head->household?->suku ?: '—' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>Kontak WA</dt><dd>{{ $head->phone ?: '—' }}</dd></div>
            </dl>
            @if(! $kkDocument)
            <p class="lw-panel-alert lw-panel-alert--warn lw-mb-0">Berkas KK belum diunggah.</p>
            @endif
        </section>

        @foreach($memberDocuments as $row)
        @php
            /** @var \App\Models\Resident $member */
            $member = $row['member'];
            $document = $row['document'];
            $isHead = $member->is_head_of_family;
        @endphp
        <section class="lw-panel-section">
            <div class="lw-rt-pendataan-review-head">
                <h2 class="lw-panel-section-title">{{ $isHead ? 'Kepala keluarga' : 'Anggota: '.$member->name }}</h2>
                <div class="lw-rt-pendataan-review-actions">
                    @if($document)
                        <a href="{{ route('rt.pendataan.document.view', [$head, $document]) }}" target="_blank" rel="noopener" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Detail berkas</a>
                    @endif
                    <a href="{{ route('rt.residents.edit', array_merge([$member], $pendataanQuery, ['household' => $head->household_id])) }}" class="lw-panel-btn lw-panel-btn--sm">Perbarui data</a>
                </div>
            </div>
            <dl class="lw-panel-dl lw-panel-dl--reference">
                <div class="lw-panel-dl-row"><dt>Nama</dt><dd>{{ $member->name }}</dd></div>
                <div class="lw-panel-dl-row"><dt>NIK</dt><dd>{{ $member->nik ?: 'belum ada' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>TTL</dt><dd>{{ $member->birthPlaceDate() }}</dd></div>
                <div class="lw-panel-dl-row"><dt>Jenis kelamin</dt><dd>{{ $member->gender ?: '—' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>Hubungan</dt><dd>{{ $member->relationship_to_head ?: '—' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>Pekerjaan</dt><dd>{{ $member->occupation ?: '—' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>Pendidikan</dt><dd>{{ $member->education ?: '—' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>Agama</dt><dd>{{ $member->religion ?: '—' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>Status perkawinan</dt><dd>{{ $member->marital_status ?: '—' }}</dd></div>
                <div class="lw-panel-dl-row"><dt>Kewarganegaraan</dt><dd>{{ $member->citizenship ?: '—' }}</dd></div>
            </dl>
            @if(! $document)
            <p class="lw-panel-alert lw-panel-alert--warn lw-mb-0">Berkas KTP/KIA belum diunggah untuk anggota ini.</p>
            @endif
        </section>
        @endforeach

        <x-rt.pendataan-documents
            :household="$head->household"
            :head="$head"
            variant="full"
            class="lw-panel-section"
        />

        @if($head->verification_notes)
        <div class="lw-alert lw-alert--warn">
            <p class="lw-alert__title">Catatan sebelumnya:</p>
            <p class="lw-pre-wrap-block">{{ $head->verification_notes }}</p>
        </div>
        @endif

        @include('rt.partials.whatsapp-notification-logs', [
            'logs' => $notificationLogs,
            'contextLabel' => 'Riwayat notifikasi WhatsApp ke kepala keluarga untuk pendataan ini.',
        ])
    </article>

    <div class="lw-panel-stack">
        <x-rt.sidebar-action-card
            title="Setujui pendataan"
            note="Data dan berkas sudah sesuai. Warga terdata aktif di RT ini."
            tag="form"
            method="POST"
            action="{{ route('rt.pendataan.approve', $head) }}"
            onsubmit="return confirm('Setujui {{ $categoryLabel }} ini? Warga akan terdata aktif dan menerima notifikasi WhatsApp.');">
            @csrf
            <button type="submit" class="lw-panel-btn lw-panel-btn--block">Setujui — warga terdata</button>
        </x-rt.sidebar-action-card>

        <x-rt.sidebar-action-card
            title="Tolak pendataan"
            note="Berkas tidak sesuai atau tidak dapat diterima. Warga kembali status aktif dan menerima notifikasi."
            variant="warn"
            tag="form"
            method="POST"
            action="{{ route('rt.pendataan.reject', $head) }}"
            onsubmit="return confirm('Tolak {{ $categoryLabel }} ini? Warga akan menerima notifikasi WhatsApp.');">
            @csrf
            <div class="lw-panel-field">
                <label for="rejection_notes">Alasan penolakan</label>
                <textarea id="rejection_notes" name="rejection_notes" rows="4" required
                    placeholder="Contoh: Scan KK buram, NIK tidak cocok, berkas KTP kedaluwarsa, dll.">{{ old('rejection_notes') }}</textarea>
                @error('rejection_notes')
                <p class="lw-form-error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="lw-panel-btn lw-panel-btn--block lw-panel-btn--danger">Tolak pendataan</button>
        </x-rt.sidebar-action-card>

        <x-rt.sidebar-action-card
            title="Minta lengkapi berkas"
            note="Warga perlu mengunggah ulang atau melengkapi berkas tertentu."
            variant="warn"
            tag="form"
            method="POST"
            action="{{ route('rt.pendataan.request-completion', $head) }}">
            @csrf
            <div class="lw-panel-field">
                <label for="verification_notes">Berkas / keterangan yang perlu dilengkapi</label>
                <textarea id="verification_notes" name="verification_notes" rows="4" required
                    placeholder="Contoh: Unggah ulang scan KK, KTP anak belum jelas, dll.">{{ old('verification_notes', $head->verification_notes) }}</textarea>
                @error('verification_notes')
                <p class="lw-form-error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="lw-panel-btn lw-panel-btn--block lw-panel-btn--warn">Kirim permintaan lengkapi berkas</button>
        </x-rt.sidebar-action-card>
    </div>
</div>
</div>
@endsection

<style>
.lw-rt-pendataan-review-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.lw-rt-pendataan-review-head .lw-panel-section-title {
    margin-bottom: 0;
}
.lw-rt-pendataan-review-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
</style>
