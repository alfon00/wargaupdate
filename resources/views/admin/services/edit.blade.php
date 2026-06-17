@extends('layouts.panel')

@section('title', 'Edit Layanan')

@section('content')
<div class="lw-admin-page">
@include('admin.partials.page-head', [
    'title' => 'Edit layanan',
    'lead' => 'Kode layanan tidak dapat diubah karena dipakai di URL publik.',
])

<form method="POST" action="{{ route('admin.services.update', $service) }}" class="lw-panel-form lw-panel-form--wide">
    @csrf
    @method('PUT')

    <fieldset class="lw-panel-form-fieldset">
        <legend class="lw-panel-form-legend">Informasi layanan</legend>
        <div class="lw-panel-field">
            <label for="code">Kode</label>
            <input id="code" value="{{ $service->code }}" readonly disabled class="opacity-70">
            <p class="lw-panel-field-hint">Kode dipakai di URL publik dan tidak dapat diubah.</p>
        </div>
        <div class="lw-panel-field">
            <label for="name">Nama <span class="lw-form-label-required">*</span></label>
            <input id="name" name="name" value="{{ old('name', $service->name) }}" required>
        </div>
        <div class="lw-panel-field">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="4">{{ old('description', $service->description) }}</textarea>
        </div>
    </fieldset>

    <fieldset class="lw-panel-form-fieldset">
        <legend class="lw-panel-form-legend">Status publikasi</legend>
        <div class="lw-panel-field">
            <label class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active))>
                Layanan aktif (tampil di portal publik)
            </label>
        </div>
    </fieldset>

    <div class="lw-panel-form-actions">
        <button type="submit" class="lw-panel-btn">Simpan</button>
        <a href="{{ route('admin.services.index') }}" class="lw-panel-btn lw-panel-btn--secondary">Batal</a>
    </div>
</form>

@if($service->applications_count === 0)
<section class="lw-panel-danger-zone mt-8" aria-labelledby="danger-zone-service">
    <h2 id="danger-zone-service" class="lw-panel-section-title text-red-800">Zona berbahaya</h2>
    <p class="text-sm text-slate-600 mb-3">Menghapus jenis layanan dari katalog. Jika sudah ada permohonan, nonaktifkan saja.</p>
    @include('admin.partials.delete-form', [
        'action' => route('admin.services.destroy', $service),
        'confirm' => 'Hapus layanan '.$service->catalogLabel().' dari katalog?',
    ])
</section>
@else
<p class="lw-mt-6 lw-form-hint">Layanan ini memiliki {{ $service->applications_count }} permohonan dan tidak dapat dihapus. Nonaktifkan lewat checkbox di atas.</p>
@endif
</div>
@endsection
