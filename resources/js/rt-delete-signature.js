import { exportSignatureFromCanvas, initSignaturePad } from './signature-pad';

let deletePad = null;

function getModal() {
    return document.getElementById('lw-rt-delete-modal');
}

function getErrorEl() {
    return document.getElementById('lw-rt-delete-modal-error');
}

function showError(message) {
    const el = getErrorEl();
    if (!el) {
        return;
    }
    if (message) {
        el.textContent = message;
        el.hidden = false;
    } else {
        el.textContent = '';
        el.hidden = true;
    }
}

function openModal(trigger) {
    const modal = getModal();
    const form = document.getElementById('lw-rt-delete-modal-form');
    const confirmEl = document.getElementById('lw-rt-delete-modal-confirm');
    const hiddenContainer = document.getElementById('lw-rt-delete-modal-hidden-fields');

    if (!modal || !form || !confirmEl || !hiddenContainer) {
        return;
    }

    const action = trigger.dataset.deleteAction || '';
    const confirm = trigger.dataset.deleteConfirm || '';
    let hidden = {};

    try {
        hidden = JSON.parse(trigger.dataset.deleteHidden || '{}');
    } catch {
        hidden = {};
    }

    form.action = action;
    confirmEl.textContent = confirm;
    hiddenContainer.innerHTML = '';

    Object.entries(hidden).forEach(([name, value]) => {
        if (value === null || value === '') {
            return;
        }
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = String(value);
        hiddenContainer.appendChild(input);
    });

    deletePad?.clear();
    showError('');
    modal.hidden = false;
    modal.classList.add('is-open');
    document.body.classList.add('lw-rt-delete-modal-open');

    requestAnimationFrame(() => {
        deletePad?.resize();
    });
}

function closeModal() {
    const modal = getModal();
    if (!modal) {
        return;
    }

    modal.hidden = true;
    modal.classList.remove('is-open');
    document.body.classList.remove('lw-rt-delete-modal-open');
    deletePad?.clear();
    showError('');
}

function isSignatureTooShort(data) {
    return !data || data.length < 100;
}

document.addEventListener('DOMContentLoaded', () => {
    deletePad = initSignaturePad(
        'rt-delete-signature-canvas',
        'rt-delete-signature-data',
        'rt-delete-signature-clear',
    );

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('.lw-rt-delete-trigger');
        if (trigger) {
            event.preventDefault();
            openModal(trigger);
            return;
        }

        if (event.target.closest('[data-delete-modal-close]')) {
            event.preventDefault();
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && getModal()?.classList.contains('is-open')) {
            closeModal();
        }
    });

    const form = document.getElementById('lw-rt-delete-modal-form');
    form?.addEventListener('submit', (event) => {
        const data = deletePad?.sync() || exportSignatureFromCanvas(
            document.getElementById('rt-delete-signature-canvas'),
        );

        if (isSignatureTooShort(data)) {
            event.preventDefault();
            showError('Tanda tangan Ketua RT wajib diisi pada kanvas sebelum menghapus permanen.');
            return;
        }

        showError('');
    });
});
