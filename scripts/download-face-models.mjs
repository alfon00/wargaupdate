import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const targetDir = path.join(__dirname, '..', 'public', 'models', 'face-api');
const sourceDir = path.join(__dirname, '..', 'node_modules', '@vladmandic', 'face-api', 'model');

if (!fs.existsSync(sourceDir)) {
    console.error('Model source tidak ditemukan. Jalankan npm install terlebih dahulu.');
    process.exit(1);
}

fs.mkdirSync(targetDir, { recursive: true });

for (const entry of fs.readdirSync(sourceDir)) {
    const from = path.join(sourceDir, entry);
    const to = path.join(targetDir, entry);
    if (fs.statSync(from).isFile()) {
        fs.copyFileSync(from, to);
    }
}

console.log(`Model face-api disalin ke ${targetDir}`);
