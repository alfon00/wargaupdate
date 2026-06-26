/**
 * Halaman susun surat RT: terbitkan, draf, validasi field layanan.
 */

export function initLetterCompose(config) {
    const form = document.getElementById('letter-compose-form');
    const statusEl = document.getElementById('letter-compose-status');
    const publishBtn = document.getElementById('letter-publish-btn');
    const hasPublishedPdf = Boolean(config.hasPublishedPdf);

    if (!form) {
        return;
    }

    const applicantEditToggle = document.getElementById('letter-applicant-edit-toggle');
    const applicantFields = Array.from(form.querySelectorAll('[data-applicant-field]'));

    const setApplicantFieldsEditable = (editable) => {
        applicantFields.forEach((input) => {
            if (editable) {
                input.removeAttribute('readonly');
            } else {
                input.setAttribute('readonly', 'readonly');
            }
        });

        if (!applicantEditToggle) {
            return;
        }

        applicantEditToggle.textContent = editable ? 'Selesai ubah' : 'Ubah data pemohon';
        applicantEditToggle.setAttribute('aria-pressed', editable ? 'true' : 'false');
    };

    setApplicantFieldsEditable(false);

    applicantEditToggle?.addEventListener('click', () => {
        const isEditing = applicantEditToggle.getAttribute('aria-pressed') === 'true';
        setApplicantFieldsEditable(!isEditing);
    });

    const setStatus = (message, type = 'error') => {
        if (!statusEl) {
            return;
        }

        if (!message) {
            statusEl.hidden = true;
            statusEl.textContent = '';
            statusEl.dataset.state = '';
            return;
        }

        statusEl.hidden = false;
        statusEl.textContent = message;
        statusEl.dataset.state = type;
    };

    const getRequiredLetterFieldInputs = () => {
        return Array.from(form.querySelectorAll('[data-letter-field][data-required="1"]'));
    };

    const getMissingRequiredFieldLabels = () => {
        const missing = [];

        getRequiredLetterFieldInputs().forEach((input) => {
            const value = (input.value || '').trim();
            if (!value) {
                const label = form.querySelector(`label[for="${input.id}"]`);
                const text = label?.textContent?.replace(/\*/g, '').trim() || input.name;
                missing.push(text);
            }
        });

        return missing;
    };

    const hasRequiredLetterFields = () => getMissingRequiredFieldLabels().length === 0;

    const setPublishEnabled = () => {
        if (!publishBtn) {
            return;
        }

        const canPublish = hasPublishedPdf || hasRequiredLetterFields();
        publishBtn.disabled = !canPublish;
        publishBtn.setAttribute('aria-disabled', canPublish ? 'false' : 'true');
    };

    const validateBeforePublish = () => {
        const missingFields = getMissingRequiredFieldLabels();
        if (missingFields.length > 0) {
            return {
                ok: false,
                message: 'Lengkapi data tambahan surat: '.concat(missingFields.join(', '), '.'),
            };
        }

        return { ok: true, message: '' };
    };

    form.addEventListener('submit', (event) => {
        const check = validateBeforePublish();
        if (!check.ok) {
            event.preventDefault();
            setStatus(check.message, 'error');
        } else {
            setStatus('');
        }
    });

    document.getElementById('letter-draft-form')?.addEventListener('submit', () => {
        document.querySelectorAll('.draft-field-sync').forEach((el) => {
            const key = el.dataset.fieldKey;
            const source = form.querySelector('[name="fields[' + key + ']"]');
            if (source) {
                el.value = source.value;
            }
        });
    });

    form.querySelectorAll('[data-letter-field]').forEach((input) => {
        input.addEventListener('input', () => {
            setPublishEnabled();
            if (hasRequiredLetterFields()) {
                setStatus('');
            }
        });
    });

    setPublishEnabled();
}

function bootLetterCompose() {
    const root = document.getElementById('letter-compose-root');
    if (!root) {
        return;
    }

    let config = {};
    try {
        config = JSON.parse(root.dataset.composeConfig || '{}');
    } catch {
        config = {};
    }

    initLetterCompose(config);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootLetterCompose);
} else {
    bootLetterCompose();
}
