import * as faceapi from '@vladmandic/face-api';
import * as pdfjsLib from 'pdfjs-dist';
import { loadCaptureModels, MIN_DESCRIPTOR_LENGTH } from './face-capture-core';

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
    'pdfjs-dist/build/pdf.worker.min.mjs',
    import.meta.url,
).toString();

async function loadImageFromFile(file) {
    const url = URL.createObjectURL(file);
    try {
        const image = await faceapi.fetchImage(url);
        return image;
    } finally {
        URL.revokeObjectURL(url);
    }
}

async function loadImageFromPdf(file) {
    const buffer = await file.arrayBuffer();
    const pdf = await pdfjsLib.getDocument({ data: buffer }).promise;
    const page = await pdf.getPage(1);
    const viewport = page.getViewport({ scale: 2 });
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    canvas.width = viewport.width;
    canvas.height = viewport.height;

    await page.render({ canvasContext: context, viewport }).promise;

    return faceapi.fetchImage(canvas);
}

async function imageSourceFromFile(file) {
    const type = (file.type || '').toLowerCase();
    const name = (file.name || '').toLowerCase();

    if (type === 'application/pdf' || name.endsWith('.pdf')) {
        return loadImageFromPdf(file);
    }

    if (type.startsWith('image/') || /\.(jpe?g|png)$/i.test(name)) {
        return loadImageFromFile(file);
    }

    throw new Error('Format berkas tidak didukung untuk verifikasi wajah. Gunakan PDF/JPG/PNG.');
}

export async function extractDescriptorFromIdentityFile(file) {
    if (!file) {
        throw new Error('Berkas KTP/KIA belum dipilih.');
    }

    await loadCaptureModels();
    const image = await imageSourceFromFile(file);

    const detection = await faceapi
        .detectSingleFace(image)
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!detection) {
        throw new Error('Wajah tidak terdeteksi pada berkas KTP/KIA. Unggah foto yang lebih jelas (wajah terlihat, tidak buram).');
    }

    const descriptor = Array.from(detection.descriptor);
    if (descriptor.length !== MIN_DESCRIPTOR_LENGTH) {
        throw new Error('Data wajah dari berkas tidak valid. Coba unggah ulang foto KTP/KIA.');
    }

    return descriptor;
}
