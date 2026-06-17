import {
    FaceCaptureController,
    detectMultipleCameras,
    isInAppBrowser,
    parseDescriptorInput,
    preloadFaceModels,
} from './face-capture-core';

function initPendataanWargaFace() {
    const page = document.querySelector('[data-pendataan-warga-page]');
    const form = page?.querySelector('[data-household-registration-form]');
    const root = document.getElementById('lw-face-capture');

    if (!page || !form || !root) {
        return;
    }

    const faceDescriptorInput = document.getElementById('head_face_descriptor');
    const selfieInput = document.getElementById('head_selfie_data');

    const controller = new FaceCaptureController({
        root,
        video: document.getElementById('face-capture-video'),
        canvas: document.getElementById('face-capture-canvas'),
        preview: document.getElementById('face-capture-preview'),
        previewWrap: document.getElementById('face-capture-preview-wrap'),
        startButton: document.getElementById('face-start-button'),
        captureButton: document.getElementById('face-capture-button'),
        retakeButton: document.getElementById('face-retake-button'),
        switchButton: document.getElementById('face-switch-button'),
        statusEl: document.getElementById('face-capture-status'),
        descriptorInput: faceDescriptorInput,
        selfieInput,
    });

    let faceVerified = false;

    root.addEventListener('face-capture-complete', () => {
        faceVerified = true;
        controller.setStatus('Verifikasi wajah berhasil. Anda dapat mengirim pendataan.', false, true);
    });

    root.addEventListener('face-capture-retake', () => {
        faceVerified = false;
        if (faceDescriptorInput) {
            faceDescriptorInput.value = '';
        }
        if (selfieInput) {
            selfieInput.value = '';
        }
    });

    form.addEventListener('submit', (event) => {
        const selfieDescriptor = parseDescriptorInput(faceDescriptorInput?.value);

        if (!selfieDescriptor || !selfieInput?.value || !faceVerified) {
            event.preventDefault();
            controller.setStatus('Selesaikan verifikasi wajah kepala keluarga sebelum mengirim pendataan.', true);
        }
    });

    if (isInAppBrowser()) {
        const warning = document.createElement('p');
        warning.className = 'lw-alert lw-alert--warn lw-mb-3';
        warning.setAttribute('role', 'note');
        warning.textContent = 'Untuk verifikasi wajah, buka halaman ini di browser Chrome atau Safari (bukan dari aplikasi WhatsApp/Instagram).';
        root.parentElement?.insertBefore(warning, root);
    }

    preloadFaceModels().catch(() => {
        controller.setStatus('Model verifikasi wajah gagal dimuat. Muat ulang halaman.', true);
    });

    detectMultipleCameras().then((hasMultiple) => {
        controller.setCanSwitchCamera(hasMultiple);
    });

    controller.bindEvents();
    controller.setCanStart(true);
    controller.setStatus('Ketuk Buka kamera untuk verifikasi wajah.');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPendataanWargaFace);
} else {
    initPendataanWargaFace();
}
