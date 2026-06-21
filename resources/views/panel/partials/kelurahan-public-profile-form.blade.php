{{-- Expects $lurahOfficial (KelurahanOfficial) --}}
@php
    $lurahDefaults = config('kelurahan.lurah', []);
    $lurahPhotoPreview = $lurahOfficial->photoUrl()
        ?? (filled($lurahDefaults['photo'] ?? null) ? asset($lurahDefaults['photo']) : null);
    $lurahName = $lurahOfficial->nama ?: ($lurahDefaults['nama'] ?? 'Lurah');
@endphp

<header class="lw-panel-page-head">
    <div>
        <p class="lw-panel-page-eyebrow">Admin sistem · Konten portal</p>
        <h1 class="lw-panel-page-title">Edit profil lurah</h1>
        <p class="lw-panel-page-lead">
            Data lurah yang ditampilkan di halaman
            <a href="{{ route('profile.index') }}" class="lw-panel-link" target="_blank" rel="noopener">Profil</a> publik.
            Email kontak di sini bukan email login pengurus.
        </p>
    </div>
</header>

<form method="POST" action="{{ route('admin.profile.kelurahan.update') }}" enctype="multipart/form-data" class="lw-panel-profile-form">
    @csrf
    @method('PUT')

    <div class="lw-panel-profile">
        <section class="lw-panel-profile-photo" aria-labelledby="lurah-photo-heading">
            <h2 id="lurah-photo-heading" class="lw-panel-section-title">Foto lurah</h2>
            <div class="lw-panel-profile-photo-wrap">
                @if($lurahPhotoPreview)
                    <img
                        src="{{ $lurahPhotoPreview }}"
                        alt="Foto {{ $lurahName }}"
                        class="lw-panel-profile-avatar"
                        id="lurah-photo-preview"
                        width="112"
                        height="112"
                    >
                @else
                    <div class="lw-panel-profile-avatar lw-panel-profile-avatar--placeholder" id="lurah-photo-preview" role="img" aria-label="Belum ada foto {{ $lurahName }}">
                        <span>{{ mb_strtoupper(mb_substr(preg_replace('/^[^A-Za-z0-9]+/u', '', $lurahName) ?: 'L', 0, 1)) }}</span>
                    </div>
                @endif
                <label class="lw-panel-profile-upload">
                    <span class="lw-panel-profile-upload-label">Pilih foto baru</span>
                    <input type="file" name="photo" accept="image/jpeg,image/jpg,image/png,image/webp" class="lw-panel-profile-file-input lw-panel-profile-file-input--lurah">
                </label>
                <p class="lw-panel-profile-hint">JPG, PNG, atau WebP. Maks. 2 MB.</p>
                @if($lurahOfficial->hasUploadedPhoto())
                    @include('admin.partials.delete-form', [
                        'action' => route('admin.profile.kelurahan.photo.destroy'),
                        'deleteLabel' => 'Hapus foto',
                        'confirm' => 'Hapus foto lurah dari halaman publik?',
                    ])
                @endif
            </div>
        </section>

        <section class="lw-panel-profile-fields" aria-labelledby="lurah-data-heading">
            <h2 id="lurah-data-heading" class="lw-panel-section-title">Data pejabat</h2>
            <div class="lw-panel-form lw-panel-form--wide">
                <div class="lw-panel-field">
                    <label for="lurah_jabatan">Jabatan <span class="lw-form-label-required">*</span></label>
                    <input id="lurah_jabatan" name="jabatan" value="{{ old('jabatan', $lurahOfficial->jabatan) }}" required>
                </div>
                <div class="lw-panel-field">
                    <label for="lurah_nama">Nama</label>
                    <input id="lurah_nama" name="nama" value="{{ old('nama', $lurahOfficial->nama) }}">
                </div>
            </div>

            <h2 class="lw-panel-section-title lw-mt-4">Kontak & jam layanan</h2>
            <div class="lw-panel-form lw-panel-form--wide">
                <div class="lw-panel-field">
                    <label for="lurah_telepon">Telepon</label>
                    <x-phone-input id="lurah_telepon" name="telepon" :value="old('telepon', $lurahOfficial->telepon)" />
                </div>
                <div class="lw-panel-field">
                    <label for="lurah_whatsapp">WhatsApp</label>
                    <x-phone-input id="lurah_whatsapp" name="whatsapp" :value="old('whatsapp', $lurahOfficial->whatsapp)" />
                </div>
                <div class="lw-panel-field">
                    <label for="lurah_email">Email kontak kantor</label>
                    <input type="email" id="lurah_email" name="email" value="{{ old('email', $lurahOfficial->email) }}" autocomplete="email">
                    <p class="lw-panel-field-hint">Ditampilkan di halaman Profil publik. Bukan email untuk masuk panel.</p>
                </div>
                <div class="lw-panel-field">
                    <label for="lurah_alamat_kantor">Alamat kantor</label>
                    <input id="lurah_alamat_kantor" name="alamat_kantor" value="{{ old('alamat_kantor', $lurahOfficial->alamat_kantor) }}">
                </div>
                <div class="lw-panel-field">
                    <label for="lurah_jam_layanan">Jam layanan</label>
                    <input id="lurah_jam_layanan" name="jam_layanan" value="{{ old('jam_layanan', $lurahOfficial->jam_layanan) }}">
                </div>
            </div>

            <h2 class="lw-panel-section-title lw-mt-4">Visi & misi</h2>
            <div class="lw-panel-form lw-panel-form--wide">
                <div class="lw-panel-field">
                    <label for="lurah_visi">Visi</label>
                    <textarea id="lurah_visi" name="visi" rows="3">{{ old('visi', $lurahOfficial->visi) }}</textarea>
                </div>
                <div class="lw-panel-field">
                    <label for="lurah_misi">Misi</label>
                    <textarea id="lurah_misi" name="misi" rows="3">{{ old('misi', $lurahOfficial->misi) }}</textarea>
                </div>
            </div>

            <div class="lw-panel-form-actions">
                <button type="submit" class="lw-panel-btn">Simpan perubahan</button>
                <a href="{{ route('admin.profile.kelurahan.show') }}" class="lw-panel-btn lw-panel-btn--secondary">Batal</a>
            </div>
        </section>
    </div>
</form>

@once
@push('head')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.querySelector('.lw-panel-profile-file-input--lurah');
    var preview = document.getElementById('lurah-photo-preview');
    if (!input || !preview) return;
    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (!file) return;
        if (preview.tagName === 'IMG') {
            preview.src = URL.createObjectURL(file);
            return;
        }
        var img = document.createElement('img');
        img.id = preview.id;
        img.className = 'lw-panel-profile-avatar';
        img.alt = preview.getAttribute('aria-label') || 'Foto lurah';
        img.src = URL.createObjectURL(file);
        preview.replaceWith(img);
    });
});
</script>
@endpush
@endonce
