<?php

namespace App\Support;

use App\Models\RtProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RtStampStorage
{
    public static function toImgTagForPdf(string $absolutePath): string
    {
        if (! is_file($absolutePath)) {
            return '';
        }

        $raw = file_get_contents($absolutePath);
        if ($raw === false || $raw === '') {
            return '';
        }

        $mime = match (strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/png',
        };

        return '<img src="data:'.$mime.';base64,'.base64_encode($raw).'" alt="Cap resmi RT">';
    }

    public static function storeUploadedFile(UploadedFile $file, RtProfile $profile): string
    {
        $filename = 'rt-'.RtProfile::normalizeRtNumber($profile->rt_number).'.'.$file->getClientOriginalExtension();
        $path = 'stamps/'.$filename;

        if ($profile->stamp_path && $profile->stamp_path !== $path) {
            Storage::disk('public')->delete($profile->stamp_path);
        }

        $file->storeAs('stamps', $filename, 'public');

        return $path;
    }

    public static function deleteStoredPath(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
