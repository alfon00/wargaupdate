<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class FaceDescriptorExtractor
{
    protected ?string $lastError = null;

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * @return array<int, array{descriptor: array<int, float>, face_index: int}>
     */
    public function extractFromStoragePath(string $diskPath): array
    {
        $this->lastError = null;

        $absolute = Storage::disk('local')->path($diskPath);
        if (! is_file($absolute)) {
            $this->lastError = 'file tidak ditemukan';

            Log::warning('FaceDescriptorExtractor: file not found', [
                'path' => $diskPath,
            ]);

            return [];
        }

        $workingPath = $this->resolveReadableImagePath($absolute);
        if ($workingPath === null) {
            $this->lastError ??= 'format file tidak didukung atau konversi gagal';

            return [];
        }

        return $this->runNodeExtractor($workingPath);
    }

    protected function resolveReadableImagePath(string $absolute): ?string
    {
        $mime = mime_content_type($absolute) ?: '';
        if (str_starts_with($mime, 'image/')) {
            return $absolute;
        }

        if ($mime === 'application/pdf' || str_ends_with(strtolower($absolute), '.pdf')) {
            return $this->convertPdfFirstPage($absolute);
        }

        $this->lastError = 'format file tidak didukung';

        Log::warning('FaceDescriptorExtractor: unsupported mime type', [
            'path' => $absolute,
            'mime' => $mime,
        ]);

        return null;
    }

    protected function convertPdfFirstPage(string $pdfPath): ?string
    {
        $output = $pdfPath.'.face-preview.png';

        $convert = Process::run([
            'convert',
            '-density', '150',
            $pdfPath.'[0]',
            $output,
        ]);

        if ($convert->successful() && is_file($output)) {
            return $output;
        }

        $this->lastError = 'konversi PDF gagal (ImageMagick convert tidak tersedia atau file rusak)';

        Log::warning('FaceDescriptorExtractor: PDF conversion failed', [
            'path' => $pdfPath,
            'error' => $convert->errorOutput(),
        ]);

        return null;
    }

    protected function resolveNodeBinary(): ?string
    {
        $candidates = array_filter([
            env('NODE_BINARY'),
            '/usr/bin/node',
            '/usr/bin/nodejs',
        ]);

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        $which = Process::run(['sh', '-c', 'command -v node || command -v nodejs']);
        if ($which->successful()) {
            $binary = trim($which->output());
            if ($binary !== '' && is_executable($binary)) {
                return $binary;
            }
        }

        return null;
    }

    /**
     * @return array<int, array{descriptor: array<int, float>, face_index: int}>
     */
    protected function runNodeExtractor(string $imagePath): array
    {
        $nodeBinary = $this->resolveNodeBinary();
        if ($nodeBinary === null) {
            $this->lastError = 'Node.js tidak tersedia di server';

            Log::error('FaceDescriptorExtractor: Node.js binary not found');

            return [];
        }

        $script = base_path('scripts/extract-face-descriptors.mjs');
        if (! is_file($script)) {
            throw new RuntimeException('Face extraction script tidak ditemukan.');
        }

        $modelsPath = public_path('models/face-api');
        $result = Process::timeout(120)->run([
            $nodeBinary,
            $script,
            '--image', $imagePath,
            '--models', $modelsPath,
        ]);

        if (! $result->successful()) {
            $this->lastError = 'skrip ekstraksi wajah gagal: '.trim($result->errorOutput() ?: $result->output());

            Log::warning('FaceDescriptorExtractor: node script failed', [
                'path' => $imagePath,
                'error' => $result->errorOutput(),
                'output' => $result->output(),
            ]);

            return [];
        }

        $decoded = json_decode(trim($result->output()), true);
        if (! is_array($decoded) || ! isset($decoded['faces']) || ! is_array($decoded['faces'])) {
            $this->lastError = 'output skrip ekstraksi tidak valid';

            Log::warning('FaceDescriptorExtractor: invalid script output', [
                'path' => $imagePath,
                'output' => $result->output(),
            ]);

            return [];
        }

        $faces = [];
        foreach ($decoded['faces'] as $face) {
            if (! is_array($face['descriptor'] ?? null)) {
                continue;
            }

            $descriptor = array_map('floatval', $face['descriptor']);
            if (count($descriptor) !== 128) {
                continue;
            }

            $faces[] = [
                'descriptor' => $descriptor,
                'face_index' => (int) ($face['face_index'] ?? count($faces)),
            ];
        }

        if ($faces === []) {
            $this->lastError = 'tidak ada wajah terdeteksi pada dokumen';
        }

        return $faces;
    }
}
