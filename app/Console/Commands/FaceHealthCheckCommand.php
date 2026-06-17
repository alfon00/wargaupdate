<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class FaceHealthCheckCommand extends Command
{
    protected $signature = 'face:health-check';

    protected $description = 'Periksa kesiapan infrastruktur ekstraksi wajah (Node, canvas, ImageMagick, model)';

    public function handle(): int
    {
        $checks = [
            'Node.js' => $this->checkNode(),
            'Modul canvas' => $this->checkCanvas(),
            'ImageMagick convert' => $this->checkImageMagick(),
            'Kebijakan PDF ImageMagick' => $this->checkPdfPolicy(),
            'Model face-api' => $this->checkModels(),
            'Skrip ekstraksi' => $this->checkScript(),
        ];

        $failed = 0;

        foreach ($checks as $label => $result) {
            if ($result['ok']) {
                $this->line("<info>OK</info>   {$label}: {$result['message']}");
            } else {
                $failed++;
                $this->error("FAIL {$label}: {$result['message']}");
            }
        }

        if ($failed > 0) {
            $this->newLine();
            $this->warn("{$failed} pemeriksaan gagal. Ekstraksi wajah dan badge \"Perlu unggah ulang\" akan tetap bermasalah.");

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Semua pemeriksaan lulus. Infrastruktur ekstraksi wajah siap.');

        return self::SUCCESS;
    }

    /** @return array{ok: bool, message: string} */
    private function checkNode(): array
    {
        $which = Process::run(['sh', '-c', 'command -v node || command -v nodejs']);

        if (! $which->successful()) {
            return ['ok' => false, 'message' => 'binary node tidak ditemukan di PATH'];
        }

        $version = Process::run(['node', '--version']);

        if (! $version->successful()) {
            return ['ok' => false, 'message' => 'node tidak dapat dijalankan'];
        }

        return ['ok' => true, 'message' => trim($version->output())];
    }

    /** @return array{ok: bool, message: string} */
    private function checkCanvas(): array
    {
        $result = Process::timeout(30)
            ->path(base_path())
            ->run([
                'node',
                '-e',
                "require('canvas'); console.log('canvas ok');",
            ]);

        if (! $result->successful()) {
            $error = trim($result->errorOutput() ?: $result->output());

            return [
                'ok' => false,
                'message' => $error !== '' ? $error : 'modul canvas tidak dapat dimuat',
            ];
        }

        return ['ok' => true, 'message' => 'modul canvas dapat dimuat'];
    }

    /** @return array{ok: bool, message: string} */
    private function checkImageMagick(): array
    {
        $result = Process::run(['convert', '-version']);

        if (! $result->successful()) {
            return ['ok' => false, 'message' => 'perintah convert tidak tersedia'];
        }

        $firstLine = strtok(trim($result->output()), "\n") ?: 'convert tersedia';

        return ['ok' => true, 'message' => $firstLine];
    }

    /** @return array{ok: bool, message: string} */
    private function checkPdfPolicy(): array
    {
        $policyPaths = [
            '/etc/ImageMagick-6/policy.xml',
            '/etc/ImageMagick-7/policy.xml',
        ];

        foreach ($policyPaths as $path) {
            if (! is_file($path)) {
                continue;
            }

            $contents = file_get_contents($path);
            if (! is_string($contents)) {
                continue;
            }

            if (preg_match('/rights="none"\s+pattern="PDF"/', $contents)) {
                return [
                    'ok' => false,
                    'message' => "PDF diblokir di {$path} — patch rights=\"read|write\" pattern=\"PDF\"",
                ];
            }

            return ['ok' => true, 'message' => "PDF diizinkan ({$path})"];
        }

        return ['ok' => true, 'message' => 'file kebijakan tidak ditemukan — asumsikan default OK'];
    }

    /** @return array{ok: bool, message: string} */
    private function checkModels(): array
    {
        $modelsDir = public_path('models/face-api');
        $required = [
            'ssd_mobilenetv1_model-weights_manifest.json',
            'face_recognition_model-weights_manifest.json',
            'face_landmark_68_model-weights_manifest.json',
        ];

        $missing = [];

        foreach ($required as $file) {
            if (! is_file($modelsDir.'/'.$file)) {
                $missing[] = $file;
            }
        }

        if ($missing !== []) {
            return [
                'ok' => false,
                'message' => 'file model hilang: '.implode(', ', $missing),
            ];
        }

        return ['ok' => true, 'message' => count($required).' manifest model ditemukan'];
    }

    /** @return array{ok: bool, message: string} */
    private function checkScript(): array
    {
        $script = base_path('scripts/extract-face-descriptors.mjs');

        if (! is_file($script)) {
            return ['ok' => false, 'message' => 'scripts/extract-face-descriptors.mjs tidak ada'];
        }

        return ['ok' => true, 'message' => 'skrip ekstraksi ada'];
    }
}
