<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignatureStorage
{
    public static function isBlank(?string $dataUri): bool
    {
        if (! $dataUri || ! str_starts_with($dataUri, 'data:image/png;base64,')) {
            return true;
        }

        $raw = base64_decode(substr($dataUri, 22), true);
        if ($raw === false || strlen($raw) < 200) {
            return true;
        }

        $image = @imagecreatefromstring($raw);
        if ($image === false) {
            return true;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $stepX = max(1, (int) floor($width / 40));
        $stepY = max(1, (int) floor($height / 20));
        $darkPixels = 0;

        for ($y = 0; $y < $height; $y += $stepY) {
            for ($x = 0; $x < $width; $x += $stepX) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                if ($alpha < 120 && ($r < 245 || $g < 245 || $b < 245)) {
                    $darkPixels++;
                }
            }
        }

        imagedestroy($image);

        return $darkPixels < 1;
    }

    public static function store(string $dataUri, int $applicationId): string
    {
        $raw = base64_decode(substr($dataUri, 22));
        $path = 'signatures/app-'.$applicationId.'-'.Str::random(8).'.png';
        Storage::disk('local')->put($path, $raw);

        return $path;
    }

    public static function storeDeletionRequest(string $dataUri, string $prefix): string
    {
        $raw = base64_decode(substr($dataUri, 22));
        $path = 'signatures/deletion-'.$prefix.'-'.Str::ulid().'.png';
        Storage::disk('local')->put($path, $raw);

        return $path;
    }

    public static function toDataUriFromPath(?string $path): ?string
    {
        if (! $path || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        $raw = Storage::disk('local')->get($path);
        if ($raw === '' || $raw === false) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($raw);
    }

    public static function toImgTag(?string $dataUri): string
    {
        if (! $dataUri || self::isBlank($dataUri)) {
            return '<span class="ttd-sign-placeholder">&nbsp;</span>';
        }

        return '<img src="'.e($dataUri).'" alt="Tanda tangan">';
    }

    public static function toImgTagForPdf(string $absolutePath): string
    {
        if (! is_file($absolutePath)) {
            return '<span class="ttd-sign-placeholder">&nbsp;</span>';
        }

        $raw = file_get_contents($absolutePath);
        if ($raw === false || $raw === '') {
            return '<span class="ttd-sign-placeholder">&nbsp;</span>';
        }

        $dataUri = 'data:image/png;base64,'.base64_encode($raw);

        return '<img src="'.$dataUri.'" alt="Tanda tangan">';
    }
}
