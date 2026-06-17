/**
 * Salin teks ke clipboard dari tombol [data-copy-text].
 */
function copyTextToClipboard(text) {
    if (!text) {
        return Promise.reject(new Error('empty'));
    }

    if (navigator.clipboard?.writeText) {
        return navigator.clipboard.writeText(text);
    }

    return new Promise((resolve, reject) => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();

        try {
            const ok = document.execCommand('copy');
            document.body.removeChild(textarea);
            if (ok) {
                resolve();
            } else {
                reject(new Error('execCommand failed'));
            }
        } catch (error) {
            document.body.removeChild(textarea);
            reject(error);
        }
    });
}

function findCopyFeedback(button) {
    const card = button.closest('.lw-application-number-card, .lw-form-card, .lw-success-card');
    return card?.querySelector('.lw-copy-text-feedback') || null;
}

function resetCopyButton(button, defaultLabel) {
    window.setTimeout(() => {
        button.textContent = defaultLabel;
        button.removeAttribute('aria-disabled');
    }, 2000);
}

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy-text]');
    if (!button) {
        return;
    }

    event.preventDefault();

    const text = button.getAttribute('data-copy-text') || '';
    const defaultLabel = button.getAttribute('data-copy-default-label') || button.textContent.trim();
    const doneLabel = button.getAttribute('data-copy-done-label') || 'Tersalin!';
    const feedback = findCopyFeedback(button);

    try {
        await copyTextToClipboard(text);
        button.textContent = doneLabel;
        button.setAttribute('aria-disabled', 'true');

        if (feedback) {
            feedback.hidden = false;
            feedback.textContent = 'Nomor tersalin ke clipboard.';
            feedback.dataset.state = 'ok';
        }

        resetCopyButton(button, defaultLabel);

        if (feedback) {
            window.setTimeout(() => {
                feedback.hidden = true;
                feedback.textContent = '';
                feedback.removeAttribute('data-state');
            }, 2000);
        }
    } catch {
        if (feedback) {
            feedback.hidden = false;
            feedback.textContent = 'Gagal menyalin — salin nomor secara manual.';
            feedback.dataset.state = 'error';
        }
    }
});
