@extends('layouts.panel')

@section('title', ($publication->exists ? 'Edit' : 'Tambah').' '.$type->label())

@section('content')
<div class="lw-rt-page">
@php
    $isKegiatan = $type === \App\Enums\RtPublicationType::Kegiatan;
    $indexRoute = $isKegiatan ? route('rt.kegiatan.index') : route('rt.pengumuman.index');
    $storeRoute = $publication->exists
        ? ($isKegiatan ? route('rt.kegiatan.update', $publication) : route('rt.pengumuman.update', $publication))
        : ($isKegiatan ? route('rt.kegiatan.store') : route('rt.pengumuman.store'));
    $destroyRoute = $publication->exists
        ? ($isKegiatan ? route('rt.kegiatan.destroy', $publication) : route('rt.pengumuman.destroy', $publication))
        : null;
    $whatsappRoute = $publication->exists
        ? ($isKegiatan ? route('rt.kegiatan.whatsapp', $publication) : route('rt.pengumuman.whatsapp', $publication))
        : null;
    $publicationBroadcastLogs = $publicationBroadcastLogs ?? collect();
@endphp

@include('rt.partials.page-head', [
    'eyebrow' => 'Panel RT · '.$rt->displayName(),
    'title' => ($publication->exists ? 'Edit' : 'Tambah').' '.$type->label(),
    'lead' => 'Publikasikan ke halaman Kegiatan portal warga.',
])

<form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" class="lw-panel-form lw-panel-form--wide lw-panel-form--labeled">
    @csrf
    @if($publication->exists)
        @method('PUT')
    @endif

    <fieldset class="lw-panel-form-fieldset">
        <legend class="lw-panel-form-legend">Informasi {{ strtolower($type->label()) }}</legend>
        <div class="lw-panel-field">
            <label for="judul" class="lw-panel-field-label">Judul <span class="lw-form-label-required">*</span></label>
            <input id="judul" name="judul" value="{{ old('judul', $publication->judul) }}" class="lw-panel-field-input" required maxlength="255">
        </div>
        <div class="lw-panel-field lw-panel-field--span2">
            <label for="ringkasan" class="lw-panel-field-label">Ringkasan</label>
            <textarea id="ringkasan" name="ringkasan" rows="4" class="lw-panel-field-input" maxlength="5000">{{ old('ringkasan', $publication->ringkasan) }}</textarea>
        </div>
        <div class="lw-panel-field">
            <label for="tanggal" class="lw-panel-field-label">Tanggal @if($isKegiatan)<span class="lw-form-label-required">*</span>@endif</label>
            <input type="date" id="tanggal" name="tanggal" class="lw-panel-field-input" value="{{ old('tanggal', $publication->tanggal?->format('Y-m-d')) }}" @if($isKegiatan) required @endif>
        </div>
        @if(! $isKegiatan)
            <div class="lw-panel-field">
                <label for="expires_at" class="lw-panel-field-label">Berlaku hingga</label>
                <input type="date" id="expires_at" name="expires_at" class="lw-panel-field-input" value="{{ old('expires_at', $publication->expires_at?->format('Y-m-d')) }}">
                <p class="lw-panel-field-hint">Kosongkan untuk otomatis hilang dari portal {{ \App\Models\RtPublication::PUBLIC_VISIBILITY_DAYS }} hari setelah dipublikasi.</p>
            </div>
        @endif
    </fieldset>

    @if($isKegiatan)
        <fieldset class="lw-panel-form-fieldset">
            <legend class="lw-panel-form-legend">Detail kegiatan</legend>
            <div class="lw-panel-field">
                <label for="lokasi" class="lw-panel-field-label">Lokasi</label>
                <input id="lokasi" name="lokasi" class="lw-panel-field-input" value="{{ old('lokasi', $publication->lokasi) }}" maxlength="255" placeholder="Contoh: Balai RT">
            </div>
            <div class="lw-panel-field lw-panel-field--span2">
                <label for="foto" class="lw-panel-field-label">Foto dokumentasi</label>
                @if($publication->fotoUrl())
                    <img src="{{ $publication->fotoUrl() }}" alt="" class="lw-panel-publication-preview">
                @endif
                <input type="file" id="foto" name="foto" class="lw-panel-field-input" accept="image/jpeg,image/jpg,image/png,image/webp">
                <p class="lw-panel-field-hint">JPG, PNG, atau WebP. Maks. 2 MB. Opsional.</p>
            </div>
        </fieldset>
    @endif

    <div class="lw-panel-form-actions">
        <button type="submit" class="lw-panel-btn">Simpan</button>
        <a href="{{ $indexRoute }}" class="lw-panel-btn lw-panel-btn--secondary">Batal</a>
    </div>
</form>

@if($whatsappRoute)
    <section class="lw-panel-section lw-mt-4">
        <h2 class="lw-panel-section-title">Notifikasi WhatsApp</h2>
        <p class="lw-panel-card-note lw-mb-3">
            Kirim pengumuman ke semua warga RT yang mengaktifkan notifikasi WhatsApp.
        </p>
        <form method="POST" action="{{ $whatsappRoute }}"
            onsubmit="return confirm('Kirim notifikasi WhatsApp ke semua warga RT yang mengaktifkan notifikasi?');">
            @csrf
            <button type="submit" class="lw-panel-btn lw-panel-btn--secondary">Kirim WhatsApp ke warga</button>
        </form>
        @include('rt.partials.whatsapp-notification-logs', [
            'logs' => $publicationBroadcastLogs,
            'contextLabel' => 'Riwayat broadcast WhatsApp untuk '.strtolower($type->label()).' ini.',
        ])
    </section>
@endif

@if($destroyRoute)
    @include('rt.partials.instant-delete-zone', [
        'action' => $destroyRoute,
        'label' => 'Hapus '.$type->label(),
        'description' => 'Menghapus '.strtolower($type->label()).' ini dari portal warga dan panel RT.',
        'confirm' => 'Hapus '.strtolower($type->label()).' ini? Tindakan ini tidak dapat dibatalkan.',
    ])
@endif
</div>
@endsection
