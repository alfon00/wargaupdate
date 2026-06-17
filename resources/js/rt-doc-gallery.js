function initRtDocGallery() {
    const modal = document.querySelector('[data-rt-doc-modal]');
    if (!modal) return;

    const modalImage = modal.querySelector('[data-rt-doc-modal-image]');
    const modalTitle = modal.querySelector('[data-rt-doc-modal-title]');
    const modalDate = modal.querySelector('[data-rt-doc-modal-date]');
    const closeTargets = modal.querySelectorAll('[data-rt-doc-modal-close]');

    const closeModal = () => {
        modal.setAttribute('hidden', '');
        document.body.classList.remove('lw-rt-doc-modal-open');
        if (modalImage) {
            modalImage.setAttribute('src', '');
            modalImage.setAttribute('alt', '');
        }
        if (modalTitle) modalTitle.textContent = '';
        if (modalDate) modalDate.textContent = '';
    };

    const openModal = (trigger) => {
        const imageUrl = trigger.getAttribute('data-doc-image-url');
        if (!imageUrl) return;

        const title = trigger.getAttribute('data-doc-title') || 'Lampiran';
        const date = trigger.getAttribute('data-doc-date') || '';

        if (modalImage) {
            modalImage.setAttribute('src', imageUrl);
            modalImage.setAttribute('alt', title);
        }
        if (modalTitle) modalTitle.textContent = title;
        if (modalDate) modalDate.textContent = date ? `Diunggah: ${date}` : '';

        modal.removeAttribute('hidden');
        document.body.classList.add('lw-rt-doc-modal-open');
    };

    document.querySelectorAll('.lw-rt-doc-modal-trigger').forEach((trigger) => {
        trigger.addEventListener('click', () => openModal(trigger));
    });

    closeTargets.forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hasAttribute('hidden')) {
            closeModal();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRtDocGallery);
} else {
    initRtDocGallery();
}
