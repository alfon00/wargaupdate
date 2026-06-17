<div id="lw-rt-delete-modal" class="lw-rt-delete-modal" role="dialog" aria-modal="true" aria-labelledby="lw-rt-delete-modal-title" hidden>
    <div class="lw-rt-delete-modal__backdrop" data-delete-modal-close tabindex="-1"></div>
    <div class="lw-rt-delete-modal__card">
        <h2 id="lw-rt-delete-modal-title" class="lw-panel-card-title">Verifikasi tanda tangan Ketua RT</h2>
        <p id="lw-rt-delete-modal-confirm" class="lw-panel-card-note lw-mb-3"></p>
        <p class="lw-panel-card-note lw-mb-3">Gambar tanda tangan di kanvas (mouse atau sentuhan) untuk mengonfirmasi penghapusan permanen.</p>

        <div class="lw-letter-signature-pad">
            <canvas id="rt-delete-signature-canvas" class="lw-letter-signature-canvas touch-none"></canvas>
        </div>
        <button type="button" id="rt-delete-signature-clear" class="lw-panel-link lw-letter-signature-clear">
            Hapus tanda tangan
        </button>
        <p id="lw-rt-delete-modal-error" class="lw-form-error lw-mt-2" hidden></p>

        <form id="lw-rt-delete-modal-form" method="POST" action="#" class="lw-mt-4">
            @csrf
            @method('DELETE')
            <div id="lw-rt-delete-modal-hidden-fields"></div>
            <input type="hidden" name="signature_data" id="rt-delete-signature-data" value="">
            <div class="lw-panel-form-actions">
                <button type="submit" class="lw-panel-btn lw-panel-btn--danger">Ajukan hapus permanen</button>
                <button type="button" class="lw-panel-btn lw-panel-btn--secondary" data-delete-modal-close>Batal</button>
            </div>
        </form>
    </div>
</div>
