@php
    $canAccept = $application->status->canAcceptByRt();
    $canReject = $application->status->canRejectByRt();
@endphp

<section class="lw-rt-review-actions lw-rt-application-detail__card" aria-label="Keputusan permohonan">
    <h3 class="lw-rt-application-detail__card-title">Keputusan permohonan</h3>
    <p class="lw-panel-card-note lw-mb-3">
        @if($application->status === \App\Enums\ApplicationStatus::VerifikasiRt)
            Permohonan diterima. Lanjutkan susun, tandatangani, dan terbitkan PDF surat.
        @else
            Periksa data pemohon dan lampiran. Jika berkas lengkap, terima permohonan lalu susun dan terbitkan surat PDF.
        @endif
    </p>

    <div class="lw-rt-review-actions__buttons">
        @if($canAccept)
        <form method="POST" action="{{ route('rt.applications.verify', $application) }}"
            onsubmit="return confirm('Terima permohonan ini? Anda akan diarahkan ke halaman susun surat.');">
            @csrf
            <button type="submit" class="lw-panel-btn">
                Terima — lanjut susun surat
            </button>
        </form>
        @endif

        @if($canReject)
        <button type="button" class="lw-panel-btn lw-panel-btn--danger" data-rt-reject-open>
            Tolak permohonan
        </button>
        @endif
    </div>
</section>

@if($canReject)
<div id="lw-rt-reject-modal" class="lw-rt-delete-modal" role="dialog" aria-modal="true" aria-labelledby="lw-rt-reject-modal-title" hidden>
    <div class="lw-rt-delete-modal__backdrop" data-rt-reject-close tabindex="-1"></div>
    <div class="lw-rt-delete-modal__card lw-rt-reject-modal__card">
        <h2 id="lw-rt-reject-modal-title" class="lw-panel-card-title">Tolak permohonan</h2>
        <p class="lw-panel-card-note lw-mb-3">Alasan penolakan akan dikirim ke WhatsApp warga. Permohonan dan semua lampiran akan dihapus dari sistem.</p>

        <form method="POST" action="{{ route('rt.applications.reject', $application) }}">
            @csrf
            <div class="lw-panel-field">
                <label for="rejection_message">Alasan penolakan <span class="lw-form-label-required">*</span></label>
                <textarea id="rejection_message" name="rejection_message" rows="4" required
                    placeholder="Contoh: KK tidak jelas, mohon ajukan ulang dengan berkas lengkap.">{{ old('rejection_message', $rejectMessageTemplate) }}</textarea>
                @error('rejection_message')
                    <p class="lw-form-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="lw-panel-form-actions lw-mt-3">
                <button type="submit" class="lw-panel-btn lw-panel-btn--danger" onclick="return confirm('Tolak permohonan ini? Data permohonan akan dihapus dan pesan dikirim ke warga.');">
                    Kirim &amp; hapus permohonan
                </button>
                <button type="button" class="lw-panel-btn lw-panel-btn--secondary" data-rt-reject-close>Batal</button>
            </div>
        </form>
    </div>
</div>
@endif

@once
@push('scripts')
<script>
(function () {
    function bindModal(modalId, openSelector, closeSelector, focusSelector, openOnLoad) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const open = () => {
            modal.hidden = false;
            document.body.classList.add('lw-rt-delete-modal-open');
            const focusEl = focusSelector ? modal.querySelector(focusSelector) : null;
            if (focusEl) focusEl.focus();
        };

        const close = () => {
            modal.hidden = true;
            if (!document.querySelector('.lw-rt-delete-modal:not([hidden])')) {
                document.body.classList.remove('lw-rt-delete-modal-open');
            }
        };

        document.querySelectorAll(openSelector).forEach((btn) => {
            btn.addEventListener('click', open);
        });

        modal.querySelectorAll(closeSelector).forEach((el) => {
            el.addEventListener('click', close);
        });

        if (openOnLoad) open();
    }

    bindModal(
        'lw-rt-reject-modal',
        '[data-rt-reject-open]',
        '[data-rt-reject-close]',
        '#rejection_message',
        {{ $errors->has('rejection_message') ? 'true' : 'false' }}
    );

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.lw-rt-delete-modal:not([hidden])').forEach((modal) => {
            modal.hidden = true;
        });
        document.body.classList.remove('lw-rt-delete-modal-open');
    });
})();
</script>
@endpush
@endonce
