<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToWriteFile;

class PrivateStorageDirectory
{
    /**
     * @return list<string>
     */
    public static function requiredPaths(): array
    {
        return [
            'surat-verifications',
            'pendataan',
        ];
    }

    public static function ensureWritable(string $relativePath, string $field = 'selfie_data'): void
    {
        $disk = Storage::disk('local');

        if (! $disk->makeDirectory($relativePath)) {
            $absolute = storage_path('app/private/'.$relativePath);

            try {
                File::makeDirectory($absolute, 0755, true);
            } catch (\Throwable) {
                // Fall through to writability check below.
            }
        }

        $absolute = $disk->path($relativePath);

        if (! is_dir($absolute) || ! is_writable($absolute)) {
            throw self::unavailableException($field);
        }
    }

    public static function write(string $relativePath, string $contents, string $field = 'selfie_data'): void
    {
        $directory = trim(dirname($relativePath), '.');
        if ($directory !== '') {
            self::ensureWritable($directory, $field);
        }

        try {
            if (! Storage::disk('local')->put($relativePath, $contents)) {
                throw self::unavailableException($field);
            }
        } catch (UnableToCreateDirectory|UnableToWriteFile) {
            throw self::unavailableException($field);
        }
    }

    public static function unavailableException(string $field): ValidationException
    {
        return ValidationException::withMessages([
            $field => 'Foto verifikasi gagal disimpan. Hubungi pengurus RT/admin — folder penyimpanan server belum siap.',
        ]);
    }
}
