@extends('layouts.panel')

@section('title', $profile->exists ? 'Edit Profil RT' : 'Tambah Profil RT')

@section('content')
<div class="lw-admin-page">
@include('admin.partials.page-head', [
    'title' => ($profile->exists ? 'Edit' : 'Tambah').' Profil RT',
    'lead' => $profile->exists ? 'Perbarui data RT di halaman publik.' : 'Buat entitas RT baru untuk wilayah portal.',
])

<form method="POST" action="{{ $profile->exists ? route('admin.rt-profiles.update', $profile) : route('admin.rt-profiles.store') }}" class="lw-panel-form lw-panel-form--wide">
    @csrf
    @if($profile->exists) @method('PUT') @endif

    <fieldset class="lw-panel-form-fieldset">
        <legend class="lw-panel-form-legend">Identitas RT</legend>
        <div class="lw-panel-field">
            <label for="rt_number">Nomor RT <span class="lw-form-label-required">*</span></label>
            <input id="rt_number" name="rt_number" value="{{ old('rt_number', $profile->rt_number) }}" required>
        </div>
        <div class="lw-panel-field">
            <label for="rw_number">Nomor RW</label>
            <input id="rw_number" name="rw_number" value="{{ old('rw_number', $profile->rw_number) }}">
        </div>
        <div class="lw-panel-field">
            <label for="kelurahan">Wilayah administratif <span class="lw-form-label-required">*</span></label>
            <input id="kelurahan" name="kelurahan" value="{{ old('kelurahan', $profile->kelurahan) }}" required>
        </div>
        <div class="lw-panel-field">
            <label for="alamat_kantor">Alamat kantor RT</label>
            <input id="alamat_kantor" name="alamat_kantor" value="{{ old('alamat_kantor', $profile->alamat_kantor) }}">
        </div>
    </fieldset>

    <fieldset class="lw-panel-form-fieldset">
        <legend class="lw-panel-form-legend">Pengurus RT</legend>
        <div class="lw-panel-field">
            <label for="ketua_rt">Nama ketua RT</label>
            <input id="ketua_rt" name="ketua_rt" value="{{ old('ketua_rt', $profile->ketua_rt) }}">
            <p class="lw-panel-field-hint">Akan disinkron dari akun ketua RT jika sudah terdaftar.</p>
        </div>
        <div class="lw-panel-field">
            <label for="ketua_rw">Nama ketua RW</label>
            <input id="ketua_rw" name="ketua_rw" value="{{ old('ketua_rw', $profile->ketua_rw) }}">
            <p class="lw-panel-field-hint">Muncul di blok &quot;Mengetahui&quot; pada surat pengantar.</p>
        </div>
        <div class="lw-panel-field">
            <label for="sekretaris_rt">Nama sekretaris RT</label>
            <input id="sekretaris_rt" name="sekretaris_rt" value="{{ old('sekretaris_rt', $profile->sekretaris_rt) }}">
        </div>
    </fieldset>

    <fieldset class="lw-panel-form-fieldset">
        <legend class="lw-panel-form-legend">Kontak & jam layanan</legend>
        <div class="lw-panel-field">
            <label for="phone">Telepon</label>
            <x-phone-input id="phone" name="phone" :value="old('phone', $profile->phone)" />
        </div>
        <div class="lw-panel-field">
            <label for="whatsapp">WhatsApp</label>
            <x-phone-input id="whatsapp" name="whatsapp" :value="old('whatsapp', $profile->whatsapp)" />
        </div>
        <div class="lw-panel-field">
            <label for="email">Email kontak RT</label>
            <input type="email" id="email" name="email" value="{{ old('email', $profile->email) }}" autocomplete="email">
            <p class="lw-panel-field-hint">Email yang dapat dihubungi warga di halaman profil (bukan email login pengurus).</p>
        </div>
        <div class="lw-panel-field">
            <label for="jam_layanan">Jam layanan</label>
            <input id="jam_layanan" name="jam_layanan" value="{{ old('jam_layanan', $profile->jam_layanan) }}">
        </div>
    </fieldset>

    <div class="lw-panel-form-actions">
        <button type="submit" class="lw-panel-btn">Simpan</button>
        <a href="{{ route('admin.rt-profiles.index') }}" class="lw-panel-btn lw-panel-btn--secondary">Batal</a>
    </div>
</form>

@if($profile->exists)
    @if($profile->canBeDeletedByAdmin())
    <section class="lw-panel-danger-zone mt-8" aria-labelledby="danger-zone-rt">
        <h2 id="danger-zone-rt" class="lw-panel-section-title text-red-800">Zona berbahaya</h2>
        <p class="text-sm text-slate-600 mb-3">Hapus profil RT yang salah dibuat dan belum memiliki data warga atau pengurus.</p>
        @include('admin.partials.delete-form', [
            'action' => route('admin.rt-profiles.destroy', $profile),
            'confirm' => 'Hapus profil '.$profile->displayName().'? Tindakan ini tidak dapat dibatalkan.',
        ])
    </section>
    @elseif($reason = $profile->deletionBlockReason())
    <p class="mt-6 text-sm text-slate-500">{{ $reason }} Profil tidak dapat dihapus dari panel.</p>
    @endif
@endif
</div>
@endsection
