/**
 * Kanvas tanda tangan RT untuk penyusunan surat.
 */

import { exportSignatureFromCanvas, initSignaturePad } from './signature-pad';

/**
 * Salin isi kanvas surat ke semua input signature_data di halaman compose.
 */
window.syncLetterSignatureInputs = function syncLetterSignatureInputs() {
    const canvas = document.getElementById('letter-signature-canvas');
    const data = exportSignatureFromCanvas(canvas);

    document.querySelectorAll('input[name="signature_data"]').forEach((input) => {
        if (input.id === 'rt-delete-signature-data') {
            return;
        }
        input.value = data;
    });

    scheduleSignatureAutoSave();

    return data;
};

let signatureSaveTimer = null;

function scheduleSignatureAutoSave() {
    const saveConfig = window.letterSignatureSaveConfig;
    if (!saveConfig?.url) {
        return;
    }

    clearTimeout(signatureSaveTimer);
    signatureSaveTimer = setTimeout(() => {
        const data = document.getElementById('signature_data')?.value || '';
        if (data.length > 0 && data.length < 100) {
            return;
        }

        const body = new FormData();
        body.append('_token', saveConfig.csrfToken || '');
        body.append('signature_data', data);

        fetch(saveConfig.url, {
            method: 'POST',
            body,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        }).catch(() => {
            // Auto-save gagal diam-diam; pengguna masih bisa simpan draf manual.
        });
    }, 800);
}

function bootstrapSignatureSaveConfig() {
    const root = document.getElementById('letter-compose-root');
    if (!root?.dataset.composeConfig) {
        return;
    }

    try {
        const config = JSON.parse(root.dataset.composeConfig);
        if (config.signatureSaveUrl) {
            window.letterSignatureSaveConfig = {
                url: config.signatureSaveUrl,
                csrfToken: config.csrfToken
                    || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || '',
            };
        }
    } catch {
        // ignore
    }
}

document.addEventListener('DOMContentLoaded', () => {
    bootstrapSignatureSaveConfig();

    const notifyChange = () => {
        window.dispatchEvent(new CustomEvent('letter-signature-changed'));
    };

    initSignaturePad('letter-signature-canvas', 'signature_data', 'letter-signature-clear', {
        onSync: () => {
            window.syncLetterSignatureInputs();
            notifyChange();
        },
    });

    document.querySelectorAll('[data-letter-signature-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            window.syncLetterSignatureInputs();
        });
    });
});
