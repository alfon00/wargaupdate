{{-- Expects $rt (RtProfile). --}}
<button
    type="button"
    class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm lw-rt-applications-settings-btn"
    data-rt-stamp-settings-open
    aria-label="Pengaturan cap surat"
    aria-haspopup="dialog"
    aria-controls="lw-rt-stamp-settings-modal"
>
    @include('components.icons.settings')
    <span>Pengaturan</span>
    @unless($rt->stampUrl())
        <span class="lw-rt-settings-badge" aria-hidden="true" title="Cap belum diunggah"></span>
    @endunless
</button>
