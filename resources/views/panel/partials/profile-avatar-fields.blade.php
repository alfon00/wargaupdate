{{-- Expects $user (User). Must be inside the profile update form. --}}
@php
    $initial = mb_strtoupper(mb_substr(preg_replace('/^[^A-Za-z0-9]+/u', '', $user->name) ?: 'U', 0, 1));
@endphp

<section class="lw-panel-profile-photo" aria-labelledby="profile-photo-heading">
    <h2 id="profile-photo-heading" class="lw-panel-section-title">Foto profil</h2>
    <div class="lw-panel-profile-photo-wrap">
        @if($user->hasUploadedAvatar())
            <img
                src="{{ $user->avatarUrl() }}"
                alt="Foto profil {{ $user->name }}"
                class="lw-panel-profile-avatar"
                id="profile-avatar-preview"
                width="112"
                height="112"
            >
        @else
            <div class="lw-panel-profile-avatar lw-panel-profile-avatar--placeholder" id="profile-avatar-preview" role="img" aria-label="Belum ada foto {{ $user->name }}">
                <span>{{ $initial }}</span>
            </div>
        @endif
        <label class="lw-panel-profile-upload">
            <span class="lw-panel-profile-upload-label">Pilih foto baru</span>
            <input type="file" name="avatar" accept="image/jpeg,image/jpg,image/png,image/webp" class="lw-panel-profile-file-input lw-panel-profile-file-input--profile">
        </label>
        <p class="lw-panel-profile-hint">JPG, PNG, atau WebP. Maks. 2 MB.</p>
        @if($user->hasUploadedAvatar())
            <button
                type="submit"
                form="profile-avatar-delete-form"
                class="lw-panel-table-link lw-panel-table-link--danger"
                onclick="return confirm('Hapus foto profil Anda?');"
            >Hapus foto</button>
        @endif
    </div>
</section>

@once
@push('head')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.querySelector('.lw-panel-profile-file-input--profile');
    var preview = document.getElementById('profile-avatar-preview');
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
        img.alt = preview.getAttribute('aria-label') || 'Foto profil';
        img.width = 112;
        img.height = 112;
        img.src = URL.createObjectURL(file);
        preview.replaceWith(img);
    });
});
</script>
@endpush
@endonce
