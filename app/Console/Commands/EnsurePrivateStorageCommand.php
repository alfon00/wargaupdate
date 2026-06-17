<?php

namespace App\Console\Commands;

use App\Support\PrivateStorageDirectory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class EnsurePrivateStorageCommand extends Command
{
    protected $signature = 'storage:ensure-private-dirs';

    protected $description = 'Pastikan folder penyimpanan private (surat-verifications, pendataan) ada dan dapat ditulis';

    public function handle(): int
    {
        $root = storage_path('app/private');
        $failed = 0;

        if (! is_dir($root)) {
            File::makeDirectory($root, 0755, true);
        }

        foreach (PrivateStorageDirectory::requiredPaths() as $relativePath) {
            $absolute = $root.'/'.$relativePath;

            if (! is_dir($absolute)) {
                File::makeDirectory($absolute, 0755, true);
            }

            if (! is_dir($absolute) || ! is_writable($absolute)) {
                $failed++;
                $this->error("FAIL {$relativePath} — tidak dapat ditulis ({$absolute})");

                continue;
            }

            $this->line("<info>OK</info>   {$relativePath}");
        }

        if ($failed > 0) {
            $this->newLine();
            $this->warn("{$failed} folder gagal. Jalankan scripts/fix-storage-permissions.sh atau perbaiki ownership ke www-data.");

            return self::FAILURE;
        }

        $this->info('Semua folder private siap.');

        return self::SUCCESS;
    }
}
