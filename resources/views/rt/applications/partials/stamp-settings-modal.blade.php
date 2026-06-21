{{-- Expects $rt (RtProfile). --}}
<div
    id="lw-rt-stamp-settings-modal"
    class="lw-rt-delete-modal lw-rt-stamp-settings-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="lw-rt-stamp-settings-modal-title"
    @unless($errors->has('stamp')) hidden @endunless
>
    <div class="lw-rt-delete-modal__backdrop" data-rt-stamp-settings-close tabindex="-1"></div>
    <div class="lw-rt-delete-modal__card lw-rt-stamp-settings-modal__card">
        <h2 id="lw-rt-stamp-settings-modal-title" class="lw-panel-card-title">Pengaturan cap surat</h2>
        <p class="lw-panel-card-note lw-mb-4">
            Unggah cap resmi RT untuk ditampilkan menimpa tanda tangan Ketua RT pada surat PDF yang sudah ditandatangani.
        </p>

        <div class="lw-panel-profile-photo-wrap lw-mb-4">
            @if($rt->stampUrl())
                <img
                    src="{{ $rt->stampUrl() }}"
                    alt="Cap resmi {{ $rt->displayName() }}"
                    class="lw-panel-profile-avatar"
                    id="rt-stamp-preview"
                    width="112"
                    height="112"
                    style="object-fit:contain;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px;"
                >
            @else
                <div class="lw-panel-profile-avatar lw-panel-profile-avatar--placeholder" id="rt-stamp-preview" role="img" aria-label="Belum ada cap resmi RT" style="font-size:0.875rem;line-height:1.3;padding:0.5rem;">
                    <span>Belum ada cap</span>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('rt.applications.stamp.update') }}" enctype="multipart/form-data" class="lw-panel-form">
            @csrf
            <div class="lw-panel-field">
                <label for="rt-stamp-file" class="lw-panel-field-label">{{ $rt->stampUrl() ? 'Ganti cap resmi' : 'Unggah cap resmi' }}</label>
                <input type="file" id="rt-stamp-file" name="stamp" accept="image/png,image/jpeg,image/webp" required class="lw-panel-field-input">
                <p class="lw-panel-field-hint">PNG, JPG, atau WebP. Maks. 2 MB.</p>
                @error('stamp')
                    <p class="lw-form-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="lw-panel-form-actions lw-mt-3">
                <button type="submit" class="lw-panel-btn">Simpan cap</button>
                <button type="button" class="lw-panel-btn lw-panel-btn--secondary" data-rt-stamp-settings-close>Tutup</button>
            </div>
        </form>

        @if($rt->stampUrl())
            <form method="POST" action="{{ route('rt.applications.stamp.destroy') }}" class="lw-mt-3" onsubmit="return confirm('Hapus cap resmi RT?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="lw-panel-table-link lw-panel-table-link--danger">Hapus cap</button>
            </form>
        @endif
    </div>
</div>
