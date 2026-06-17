import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';
import { fileURLToPath } from 'node:url';
import * as tf from '@tensorflow/tfjs';
import wasm from '@tensorflow/tfjs-backend-wasm';
import { Canvas, Image, loadImage } from 'canvas';

const require = createRequire(import.meta.url);
const faceapi = require('@vladmandic/face-api/dist/face-api.node-wasm.js');

const __dirname = path.dirname(fileURLToPath(import.meta.url));

faceapi.env.monkeyPatch({ Canvas, Image });

function parseArgs(argv) {
    const args = {};
    for (let i = 2; i < argv.length; i += 2) {
        const key = argv[i]?.replace(/^--/, '');
        args[key] = argv[i + 1];
    }
    return args;
}

async function initTensorflow() {
    const wasmDir = path.join(__dirname, '..', 'node_modules', '@tensorflow', 'tfjs-backend-wasm', 'dist');
    wasm.setWasmPaths(`${wasmDir}/`);
    await tf.setBackend('wasm');
    await tf.ready();
}

async function loadModels(modelsDir) {
    await faceapi.nets.ssdMobilenetv1.loadFromDisk(modelsDir);
    await faceapi.nets.faceLandmark68Net.loadFromDisk(modelsDir);
    await faceapi.nets.faceRecognitionNet.loadFromDisk(modelsDir);
}

async function main() {
    const args = parseArgs(process.argv);
    const imagePath = args.image;
    const modelsDir = args.models || path.join(__dirname, '..', 'public', 'models', 'face-api');

    if (!imagePath || !fs.existsSync(imagePath)) {
        console.error(JSON.stringify({ error: 'image_not_found' }));
        process.exit(1);
    }

    if (!fs.existsSync(modelsDir)) {
        console.error(JSON.stringify({ error: 'models_not_found', modelsDir }));
        process.exit(1);
    }

    await initTensorflow();
    await loadModels(modelsDir);

    const image = await loadImage(imagePath);
    const canvas = new Canvas(image.width, image.height);
    const ctx = canvas.getContext('2d');
    ctx.drawImage(image, 0, 0);

    const options = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.2, maxResults: 20 });
    const detections = await faceapi
        .detectAllFaces(canvas, options)
        .withFaceLandmarks()
        .withFaceDescriptors();

    const faces = detections.map((detection, index) => ({
        face_index: index,
        descriptor: Array.from(detection.descriptor),
    }));

    process.stdout.write(JSON.stringify({ faces }));
}

main().catch((error) => {
    console.error(JSON.stringify({ error: error.message }));
    process.exit(1);
});
