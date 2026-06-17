import * as faceapi from '@vladmandic/face-api';

export const MODEL_URL = '/models/face-api';
export const MIN_DESCRIPTOR_LENGTH = 128;
const DETECT_INTERVAL_MS = 66;
const STABLE_FACE_FRAMES = 8;
const POSITIONING_MISS_GRACE = 5;
const MIN_FACE_AREA_RATIO = 0.12;
const MAX_FACE_CENTER_OFFSET = 0.30;

const LIVENESS_PHASE = {
    NO_FACE: 'no_face',
    POSITIONING: 'positioning',
    READY: 'ready',
};

let liveModelsLoaded = false;
let captureModelsLoaded = false;

const tinyDetectorOptions = new faceapi.TinyFaceDetectorOptions({
    inputSize: 320,
    scoreThreshold: 0.4,
});

export async function loadLiveModels() {
    if (liveModelsLoaded) {
        return;
    }

    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
    liveModelsLoaded = true;
}

export async function loadCaptureModels() {
    if (captureModelsLoaded) {
        return;
    }

    await Promise.all([
        faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);

    captureModelsLoaded = true;
}

export async function preloadFaceModels() {
    await Promise.all([loadLiveModels(), loadCaptureModels()]);
}

export async function detectMultipleCameras() {
    if (!navigator.mediaDevices?.enumerateDevices) {
        return true;
    }

    try {
        const devices = await navigator.mediaDevices.enumerateDevices();

        return devices.filter((device) => device.kind === 'videoinput').length >= 2;
    } catch {
        return true;
    }
}

export function cameraErrorMessage(error) {
    const name = error?.name ?? '';

    switch (name) {
        case 'NotAllowedError':
            return 'Izin kamera ditolak. Ketuk Buka kamera, pilih Izinkan, atau aktifkan kamera di pengaturan browser/perangkat.';
        case 'NotFoundError':
            return 'Kamera tidak ditemukan di perangkat ini.';
        case 'NotReadableError':
            return 'Kamera sedang digunakan aplikasi lain. Tutup aplikasi lain lalu coba lagi.';
        case 'SecurityError':
            return 'Akses kamera hanya tersedia melalui koneksi aman (HTTPS).';
        case 'OverconstrainedError':
            return 'Kamera depan tidak tersedia. Coba perangkat lain atau browser Chrome/Safari.';
        default:
            return 'Gagal mengakses kamera. Buka halaman ini di Chrome atau Safari (bukan browser dalam aplikasi WhatsApp/Instagram), lalu coba lagi.';
    }
}

export function isInAppBrowser() {
    const ua = navigator.userAgent || '';
    return /FBAN|FBAV|Instagram|Line\/|WhatsApp|wv\)/i.test(ua);
}

export function parseDescriptorInput(value) {
    if (!value) {
        return null;
    }

    try {
        const parsed = typeof value === 'string' ? JSON.parse(value) : value;
        return Array.isArray(parsed) && parsed.length === MIN_DESCRIPTOR_LENGTH ? parsed : null;
    } catch {
        return null;
    }
}

export function euclideanDistance(a, b) {
    if (!Array.isArray(a) || !Array.isArray(b) || a.length !== MIN_DESCRIPTOR_LENGTH || b.length !== MIN_DESCRIPTOR_LENGTH) {
        return Number.POSITIVE_INFINITY;
    }

    let sum = 0;
    for (let i = 0; i < MIN_DESCRIPTOR_LENGTH; i += 1) {
        const diff = a[i] - b[i];
        sum += diff * diff;
    }

    return Math.sqrt(sum);
}

export function descriptorsMatch(selfieDescriptor, referenceDescriptor, threshold) {
    const distance = euclideanDistance(selfieDescriptor, referenceDescriptor);
    return {
        matched: distance <= threshold,
        distance,
    };
}

function isFaceWellPositioned(detection, videoWidth, videoHeight) {
    const box = detection.box;
    const faceArea = box.width * box.height;
    const frameArea = videoWidth * videoHeight;
    const areaRatio = faceArea / frameArea;

    if (areaRatio < MIN_FACE_AREA_RATIO) {
        return false;
    }

    const faceCenterX = box.x + box.width / 2;
    const faceCenterY = box.y + box.height / 2;
    const offsetX = Math.abs(faceCenterX - videoWidth / 2) / videoWidth;
    const offsetY = Math.abs(faceCenterY - videoHeight / 2) / videoHeight;

    return offsetX <= MAX_FACE_CENTER_OFFSET && offsetY <= MAX_FACE_CENTER_OFFSET;
}

export class FaceCaptureController {
    constructor(elements) {
        this.root = elements.root;
        this.video = elements.video;
        this.canvas = elements.canvas;
        this.preview = elements.preview;
        this.previewWrap = elements.previewWrap;
        this.startButton = elements.startButton;
        this.captureButton = elements.captureButton;
        this.retakeButton = elements.retakeButton ?? null;
        this.switchButton = elements.switchButton ?? null;
        this.statusEl = elements.statusEl;
        this.descriptorInput = elements.descriptorInput;
        this.selfieInput = elements.selfieInput;

        this.stream = null;
        this.cameraActive = false;
        this.facingMode = 'user';
        this.canSwitchCamera = true;
        this.rafId = null;
        this.lastDetectTime = 0;
        this.detecting = false;
        this.canStart = false;
        this.livenessState = {
            phase: LIVENESS_PHASE.NO_FACE,
            stableFaceFrames: 0,
            positioningMissFrames: 0,
        };
    }

    setCanStart(enabled) {
        this.canStart = enabled;
        if (this.startButton) {
            this.startButton.disabled = !enabled;
        }
    }

    setCanSwitchCamera(enabled) {
        this.canSwitchCamera = enabled;
        if (!enabled) {
            this.setSwitchButtonVisible(false);
        }
    }

    setSwitchButtonVisible(visible) {
        if (!this.switchButton || !this.canSwitchCamera) {
            return;
        }

        this.switchButton.hidden = !visible;
    }

    updateVideoMirror() {
        this.video?.classList.toggle('is-mirrored', this.facingMode === 'user');
    }

    setStatus(message, isError = false, isSuccess = false) {
        if (!this.statusEl) {
            return;
        }

        this.statusEl.textContent = message;
        this.statusEl.classList.toggle('lw-form-error', isError);
        this.statusEl.classList.toggle('lw-form-hint', !isError && !isSuccess);
        this.statusEl.classList.toggle('lw-face-capture__status--success', isSuccess);
    }

    updateStepIndicator(phase) {
        const steps = this.root?.querySelectorAll('[data-face-step]') ?? [];

        steps.forEach((step) => {
            const stepId = step.getAttribute('data-face-step');
            step.classList.remove('is-active', 'is-done');

            if (stepId === 'position') {
                if (phase === LIVENESS_PHASE.POSITIONING) {
                    step.classList.add('is-active');
                } else if (phase === LIVENESS_PHASE.READY) {
                    step.classList.add('is-done');
                }
            }

            if (stepId === 'capture' && phase === LIVENESS_PHASE.READY) {
                step.classList.add('is-active', 'is-done');
            }
        });

        this.previewWrap?.classList.toggle('is-ready', phase === LIVENESS_PHASE.READY);
    }

    resetLivenessState() {
        this.livenessState.phase = LIVENESS_PHASE.NO_FACE;
        this.livenessState.stableFaceFrames = 0;
        this.livenessState.positioningMissFrames = 0;
        this.updateStepIndicator(LIVENESS_PHASE.NO_FACE);
    }

    isReadyToCapture() {
        return this.livenessState.phase === LIVENESS_PHASE.READY;
    }

    updateCaptureButton() {
        if (this.captureButton) {
            this.captureButton.disabled = !this.isReadyToCapture();
        }
    }

    handlePositioningMiss() {
        if (this.livenessState.phase !== LIVENESS_PHASE.READY) {
            return false;
        }

        this.livenessState.positioningMissFrames += 1;

        if (this.livenessState.positioningMissFrames < POSITIONING_MISS_GRACE) {
            this.setStatus('Tahan wajah di lingkaran kamera...');
            this.updateCaptureButton();
            return true;
        }

        this.livenessState.phase = LIVENESS_PHASE.POSITIONING;
        this.livenessState.stableFaceFrames = 0;
        return false;
    }

    updateStatusMessage() {
        if (!this.cameraActive) {
            return;
        }

        switch (this.livenessState.phase) {
            case LIVENESS_PHASE.NO_FACE:
                this.setStatus('Posisikan wajah Anda di dalam lingkaran kamera.');
                break;
            case LIVENESS_PHASE.POSITIONING:
                this.setStatus('Dekatkan wajah ke lingkaran kamera.');
                break;
            case LIVENESS_PHASE.READY:
                this.setStatus('Wajah terdeteksi. Ketuk Ambil foto selfie.', false, true);
                break;
            default:
                break;
        }

        this.updateStepIndicator(this.livenessState.phase);
    }

    markReady() {
        this.livenessState.phase = LIVENESS_PHASE.READY;
        this.livenessState.positioningMissFrames = 0;
        this.setStatus('Wajah terdeteksi. Ketuk Ambil foto selfie.', false, true);
        this.updateStepIndicator(LIVENESS_PHASE.READY);
        this.updateCaptureButton();
    }

    processDetection(detection) {
        const videoWidth = this.video.videoWidth;
        const videoHeight = this.video.videoHeight;

        if (!detection || !videoWidth || !videoHeight) {
            if (this.handlePositioningMiss()) {
                return;
            }

            this.livenessState.phase = LIVENESS_PHASE.NO_FACE;
            this.livenessState.stableFaceFrames = 0;
            this.livenessState.positioningMissFrames = 0;
            this.updateStatusMessage();
            this.updateCaptureButton();
            return;
        }

        if (!isFaceWellPositioned(detection, videoWidth, videoHeight)) {
            if (this.handlePositioningMiss()) {
                return;
            }

            this.livenessState.phase = LIVENESS_PHASE.POSITIONING;
            this.livenessState.stableFaceFrames = 0;
            this.livenessState.positioningMissFrames = 0;
            this.updateStatusMessage();
            this.updateCaptureButton();
            return;
        }

        this.livenessState.positioningMissFrames = 0;

        if (this.livenessState.phase !== LIVENESS_PHASE.READY) {
            this.livenessState.stableFaceFrames += 1;

            if (this.livenessState.stableFaceFrames >= STABLE_FACE_FRAMES) {
                this.markReady();
                return;
            }

            this.livenessState.phase = LIVENESS_PHASE.POSITIONING;
        }

        this.updateStatusMessage();
        this.updateCaptureButton();
    }

    stopLivenessLoop() {
        if (this.rafId !== null) {
            cancelAnimationFrame(this.rafId);
            this.rafId = null;
        }

        this.lastDetectTime = 0;
        this.detecting = false;
    }

    startLivenessLoop() {
        this.stopLivenessLoop();

        const tick = async (timestamp) => {
            if (!this.cameraActive || this.video.hidden) {
                this.stopLivenessLoop();
                return;
            }

            this.rafId = requestAnimationFrame(tick);

            if (this.detecting || timestamp - this.lastDetectTime < DETECT_INTERVAL_MS) {
                return;
            }

            this.lastDetectTime = timestamp;
            this.detecting = true;

            try {
                await loadLiveModels();
                const detection = await faceapi.detectSingleFace(this.video, tinyDetectorOptions);
                this.processDetection(detection);
            } catch {
                this.setStatus('Verifikasi wajah terganggu. Coba muat ulang halaman.', true);
            } finally {
                this.detecting = false;
            }
        };

        this.rafId = requestAnimationFrame(tick);
    }

    async startCamera() {
        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('Browser tidak mendukung akses kamera.');
        }

        if (this.stream) {
            this.stream.getTracks().forEach((track) => track.stop());
            this.stream = null;
            this.stopLivenessLoop();
        }

        const constraints = {
            video: {
                facingMode: { ideal: this.facingMode },
                width: { ideal: 640 },
                height: { ideal: 480 },
            },
            audio: false,
        };

        try {
            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
        } catch (error) {
            if (error?.name === 'OverconstrainedError') {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            } else {
                throw error;
            }
        }

        this.video.srcObject = this.stream;
        this.video.hidden = false;
        this.updateVideoMirror();
        this.previewWrap?.classList.remove('is-placeholder');

        await this.video.play();

        await new Promise((resolve) => {
            if (this.video.videoWidth > 0) {
                resolve();
                return;
            }

            this.video.addEventListener('loadeddata', () => resolve(), { once: true });
        });

        this.cameraActive = true;
    }

    async switchCamera() {
        if (!this.cameraActive || this.video.hidden) {
            return;
        }

        const previous = this.facingMode;
        const next = previous === 'user' ? 'environment' : 'user';
        this.facingMode = next;
        this.stopLivenessLoop();

        try {
            await this.startCamera();
            this.resetLivenessState();
            this.startLivenessLoop();
            this.setSwitchButtonVisible(true);
            this.setStatus('Kamera diganti. Posisikan wajah di lingkaran kamera.');
        } catch {
            this.facingMode = previous;

            try {
                await this.startCamera();
                this.resetLivenessState();
                this.startLivenessLoop();
                this.setSwitchButtonVisible(true);
            } catch {
                this.stopCamera();
            }

            this.setStatus('Kamera ini tidak tersedia di perangkat Anda.', true);
        }
    }

    stopCamera() {
        this.stopLivenessLoop();

        if (!this.stream) {
            return;
        }

        this.stream.getTracks().forEach((track) => track.stop());
        this.stream = null;
        this.cameraActive = false;
    }

    resetCapturedSelfie() {
        if (this.descriptorInput) {
            this.descriptorInput.value = '';
        }
        if (this.selfieInput) {
            this.selfieInput.value = '';
        }
        if (this.preview) {
            this.preview.hidden = true;
            this.preview.removeAttribute('src');
        }
        if (this.video) {
            this.video.hidden = false;
        }
        this.setSwitchButtonVisible(false);
        if (this.retakeButton) {
            this.retakeButton.hidden = true;
        }
        if (this.captureButton) {
            this.captureButton.disabled = true;
        }
    }

    async captureSelfie() {
        if (!this.isReadyToCapture()) {
            throw new Error('Posisikan wajah di lingkaran kamera terlebih dahulu.');
        }

        await loadCaptureModels();

        const width = this.video.videoWidth;
        const height = this.video.videoHeight;
        if (!width || !height) {
            throw new Error('Kamera belum siap. Tunggu sebentar lalu coba lagi.');
        }

        this.canvas.width = width;
        this.canvas.height = height;
        const ctx = this.canvas.getContext('2d');
        ctx.drawImage(this.video, 0, 0, width, height);

        const detection = await faceapi
            .detectSingleFace(this.canvas)
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            throw new Error('Wajah tidak terdeteksi. Pastikan wajah terlihat jelas di kamera.');
        }

        const descriptor = Array.from(detection.descriptor);
        const dataUri = this.canvas.toDataURL('image/jpeg', 0.85);

        this.descriptorInput.value = JSON.stringify(descriptor);
        this.selfieInput.value = dataUri;

        this.preview.src = dataUri;
        this.preview.hidden = false;
        this.video.hidden = true;
        this.stopLivenessLoop();
        this.captureButton.disabled = true;
        this.setSwitchButtonVisible(false);

        return { descriptor, dataUri };
    }

    resumeLivePreview() {
        this.preview.hidden = true;
        this.preview.removeAttribute('src');
        this.video.hidden = false;
        this.resetLivenessState();
        this.updateCaptureButton();
        this.setSwitchButtonVisible(true);
        this.startLivenessLoop();
        this.setStatus('Posisikan wajah Anda di dalam lingkaran kamera.');
    }

    bindEvents() {
        this.startButton?.addEventListener('click', async () => {
            if (!this.canStart) {
                this.setStatus('Verifikasi wajah belum tersedia. Muat ulang halaman.', true);
                return;
            }

            this.startButton.disabled = true;
            this.setStatus('Meminta izin kamera...');

            try {
                this.resetLivenessState();
                await this.startCamera();
                this.startButton.hidden = true;
                this.captureButton.disabled = true;
                if (this.retakeButton) {
                    this.retakeButton.hidden = true;
                }
                this.startLivenessLoop();
                this.setSwitchButtonVisible(true);
                this.setStatus('Posisikan wajah Anda di dalam lingkaran kamera.');
            } catch (error) {
                this.startButton.disabled = !this.canStart;
                this.setStatus(cameraErrorMessage(error), true);
            }
        });

        this.switchButton?.addEventListener('click', () => {
            this.switchCamera();
        });

        this.captureButton?.addEventListener('click', async () => {
            if (!this.cameraActive) {
                this.setStatus('Buka kamera terlebih dahulu.', true);
                return;
            }

            this.captureButton.disabled = true;
            try {
                const result = await this.captureSelfie();
                if (this.retakeButton) {
                    this.retakeButton.hidden = false;
                }
                this.root?.dispatchEvent(new CustomEvent('face-capture-complete', { detail: result }));
            } catch (error) {
                this.resetCapturedSelfie();
                if (!this.video.hidden) {
                    this.video.hidden = false;
                }
                this.setSwitchButtonVisible(true);
                this.updateCaptureButton();
                this.setStatus(error.message || 'Gagal mengambil foto selfie.', true);
            }
        });

        this.retakeButton?.addEventListener('click', () => {
            this.resetCapturedSelfie();
            this.retakeButton.hidden = true;
            this.resumeLivePreview();
            this.root?.dispatchEvent(new CustomEvent('face-capture-retake'));
        });

        window.addEventListener('beforeunload', () => this.stopCamera());
    }
}
